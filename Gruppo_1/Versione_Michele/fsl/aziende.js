/**
 * aziende.js — FSL Panel · CRUD Aziende
 * Gestione completa lato client: fetch, validazione, render, UX
 */

'use strict';

// ============================================================
// CONFIG
// ============================================================
const MIDDLEWARE_URL = 'aziende_middleware.php';

// ============================================================
// STATO APP
// ============================================================
let allAziende = [];
let pendingDeleteId = null;

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  loadAziende();
});

// ============================================================
// API CALLS → middleware PHP
// ============================================================

async function apiFetch(action, method = 'GET', body = null) {
  const url = `${MIDDLEWARE_URL}?action=${encodeURIComponent(action)}`;
  const options = {
    method,
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
  };
  if (body) options.body = JSON.stringify(body);

  const res = await fetch(url, options);
  if (!res.ok) {
    const err = await res.json().catch(() => ({ message: `HTTP ${res.status}` }));
    throw new Error(err.message || `Errore HTTP ${res.status}`);
  }
  return res.json();
}

// ---- READ ALL ----
async function loadAziende() {
  setDbStatus('loading');
  try {
    const data = await apiFetch('list');
    if (!data.success) throw new Error(data.message);
    allAziende = data.data;
    renderTable(allAziende);
    updateStats(allAziende);
    setDbStatus('ok');
  } catch (e) {
    setDbStatus('err');
    renderTableError(e.message);
    toast('Errore nel caricamento: ' + e.message, 'error');
  }
}

// ---- READ ONE ----
async function loadAzienda(id) {
  try {
    const data = await apiFetch(`get&id=${encodeURIComponent(id)}`);
    if (!data.success) throw new Error(data.message);
    return data.data;
  } catch (e) {
    toast('Errore nel recupero dati: ' + e.message, 'error');
    return null;
  }
}

// ---- CREATE ----
async function handleCreate(e) {
  e.preventDefault();
  if (!validateForm('create-form')) return;

  const payload = collectForm('create-form');
  const btn = e.target.querySelector('.btn-primary');
  setButtonLoading(btn, true);

  try {
    const data = await apiFetch('create', 'POST', payload);
    if (!data.success) throw new Error(data.message);
    toast('Azienda creata con successo ✓');
    resetForm('create-form');
    await loadAziende();
    showView('list');
  } catch (e) {
    toast('Errore: ' + e.message, 'error');
  } finally {
    setButtonLoading(btn, false);
  }
}

// ---- UPDATE ----
async function handleUpdate(e) {
  e.preventDefault();
  if (!validateForm('edit-form')) return;

  const payload = collectForm('edit-form');
  const btn = e.target.querySelector('.btn-primary');
  setButtonLoading(btn, true);

  try {
    const data = await apiFetch('update', 'PUT', payload);
    if (!data.success) throw new Error(data.message);
    toast('Azienda aggiornata ✓');
    await loadAziende();
    showView('list');
  } catch (e) {
    toast('Errore: ' + e.message, 'error');
  } finally {
    setButtonLoading(btn, false);
  }
}

// ---- DELETE ----
function confirmDelete(id) {
  const azienda = allAziende.find(a => String(a.codice_azienda) === String(id));
  pendingDeleteId = id;
  document.getElementById('modal-azienda-name').textContent = azienda ? azienda.ragione_sociale : `#${id}`;
  document.getElementById('delete-modal').classList.remove('hidden');
  document.getElementById('confirm-delete-btn').onclick = executeDelete;
}

async function executeDelete() {
  if (!pendingDeleteId) return;
  const btn = document.getElementById('confirm-delete-btn');
  setButtonLoading(btn, true);

  try {
    const data = await apiFetch(`delete&id=${encodeURIComponent(pendingDeleteId)}`, 'DELETE');
    if (!data.success) throw new Error(data.message);
    toast('Azienda eliminata');
    closeModal();
    await loadAziende();
    showView('list');
  } catch (e) {
    toast('Errore: ' + e.message, 'error');
  } finally {
    setButtonLoading(btn, false);
    pendingDeleteId = null;
  }
}

// ============================================================
// RENDER TABLE
// ============================================================
function renderTable(aziende) {
  const tbody = document.getElementById('table-body');
  document.getElementById('view-count').textContent = aziende.length;

  if (!aziende.length) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);font-family:var(--mono);font-size:.85rem;">Nessuna azienda trovata</td></tr>`;
    return;
  }

  tbody.innerHTML = aziende.map(a => `
    <tr data-id="${esc(a.codice_azienda)}" onclick="openDetail(${a.codice_azienda})">
      <td class="mono">${esc(a.codice_azienda)}</td>
      <td><strong>${esc(a.ragione_sociale)}</strong></td>
      <td class="mono">${esc(a.partita_iva)}</td>
      <td>${esc(a.sede_legale)}</td>
      <td>${esc(a.sede_operativa)}</td>
      <td class="col-actions" onclick="event.stopPropagation()">
        <button class="btn-icon-sm edit" title="Modifica" onclick="openEdit(${a.codice_azienda})">✎</button>
        <button class="btn-icon-sm del" title="Elimina" onclick="confirmDelete(${a.codice_azienda})">✕</button>
      </td>
    </tr>
  `).join('');
}

function renderTableError(msg) {
  document.getElementById('table-body').innerHTML = `
    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--danger);font-family:var(--mono);font-size:.82rem;">⚠ ${esc(msg)}</td></tr>`;
}

// ============================================================
// FILTRO RICERCA
// ============================================================
function filterTable() {
  const q = document.getElementById('search-input').value.toLowerCase().trim();
  const filtered = q
    ? allAziende.filter(a =>
        a.ragione_sociale.toLowerCase().includes(q) ||
        a.partita_iva.toLowerCase().includes(q) ||
        a.sede_legale.toLowerCase().includes(q) ||
        a.sede_operativa.toLowerCase().includes(q)
      )
    : allAziende;
  renderTable(filtered);
}

// ============================================================
// VIEWS
// ============================================================
function showView(name) {
  document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
  document.getElementById(`view-${name}`).classList.remove('hidden');

  // sidebar active
  document.querySelectorAll('.sidebar-btn').forEach(b => b.classList.remove('active'));
  const sideBtn = document.querySelector(`.sidebar-btn[data-view="${name}"]`);
  if (sideBtn) sideBtn.classList.add('active');

  // breadcrumb
  const labels = { list: 'Elenco', create: 'Nuova', edit: 'Modifica', detail: 'Dettaglio' };
  document.getElementById('nav-breadcrumb').textContent = labels[name] || name;
}

async function openDetail(id) {
  const az = await loadAzienda(id);
  if (!az) return;

  document.getElementById('detail-id-badge').textContent = `#${az.codice_azienda}`;
  document.getElementById('d-ragione').textContent = az.ragione_sociale;
  document.getElementById('d-piva').textContent = az.partita_iva;
  document.getElementById('d-sede-legale').textContent = az.sede_legale;
  document.getElementById('d-sede-op').textContent = az.sede_operativa;
  document.getElementById('detail-edit-btn').onclick = () => openEdit(id);

  showView('detail');
}

async function openEdit(id) {
  const az = await loadAzienda(id);
  if (!az) return;

  document.getElementById('edit-id-badge').textContent = `#${az.codice_azienda}`;
  document.getElementById('e-id').value = az.codice_azienda;
  document.getElementById('e-ragione').value = az.ragione_sociale;
  document.getElementById('e-piva').value = az.partita_iva;
  document.getElementById('e-sede-legale').value = az.sede_legale;
  document.getElementById('e-sede-op').value = az.sede_operativa;

  showView('edit');
}

// ============================================================
// FORM UTILS
// ============================================================
function collectForm(formId) {
  const form = document.getElementById(formId);
  const data = {};
  new FormData(form).forEach((v, k) => { data[k] = v.trim(); });
  return data;
}

function resetForm(formId) {
  document.getElementById(formId).reset();
  document.querySelectorAll(`#${formId} .field-err`).forEach(el => el.textContent = '');
  document.querySelectorAll(`#${formId} input`).forEach(el => el.classList.remove('invalid'));
}

function validateForm(formId) {
  const form = document.getElementById(formId);
  let valid = true;

  // Clear previous errors
  form.querySelectorAll('.field-err').forEach(e => e.textContent = '');
  form.querySelectorAll('input').forEach(e => e.classList.remove('invalid'));

  const rules = [
    { id: 'ragione_sociale', min: 2, max: 100, errId: `err-${formId === 'create-form' ? 'c' : 'e'}-ragione`, label: 'Ragione sociale' },
    { id: 'partita_iva',     regex: /^\d{11}$/, errId: `err-${formId === 'create-form' ? 'c' : 'e'}-piva`, label: 'P.IVA (11 cifre)' },
    { id: 'sede_legale',     min: 2, max: 100, errId: `err-${formId === 'create-form' ? 'c' : 'e'}-sede-legale`, label: 'Sede legale' },
    { id: 'sede_operativa',  min: 2, max: 100, errId: `err-${formId === 'create-form' ? 'c' : 'e'}-sede-op`, label: 'Sede operativa' },
  ];

  rules.forEach(rule => {
    const input = form.querySelector(`[name="${rule.id}"]`);
    if (!input) return;
    const val = input.value.trim();
    let err = '';

    if (!val) {
      err = `${rule.label} è obbligatoria`;
    } else if (rule.regex && !rule.regex.test(val)) {
      err = `Formato non valido — ${rule.label}`;
    } else if (rule.min && val.length < rule.min) {
      err = `Minimo ${rule.min} caratteri`;
    } else if (rule.max && val.length > rule.max) {
      err = `Massimo ${rule.max} caratteri`;
    }

    if (err) {
      valid = false;
      input.classList.add('invalid');
      const errEl = document.getElementById(rule.errId);
      if (errEl) errEl.textContent = err;
    }
  });

  return valid;
}

// ============================================================
// STATS
// ============================================================
function updateStats(aziende) {
  document.getElementById('stat-total').textContent = aziende.length;
  const cities = new Set(aziende.map(a => a.sede_legale));
  document.getElementById('stat-cities').textContent = cities.size;
}

// ============================================================
// MODAL
// ============================================================
function closeModal() {
  document.getElementById('delete-modal').classList.add('hidden');
  pendingDeleteId = null;
}

// Close modal on overlay click
document.getElementById('delete-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// ============================================================
// TOAST
// ============================================================
function toast(msg, type = 'success') {
  const container = document.getElementById('toast-container');
  const el = document.createElement('div');
  el.className = `toast${type !== 'success' ? ' ' + type : ''}`;
  el.textContent = msg;
  container.appendChild(el);
  setTimeout(() => {
    el.style.opacity = '0';
    el.style.transition = 'opacity 0.3s';
    setTimeout(() => el.remove(), 300);
  }, 3500);
}

// ============================================================
// DB STATUS INDICATOR
// ============================================================
function setDbStatus(state) {
  const dot = document.getElementById('db-status-dot');
  const txt = document.getElementById('db-status-text');
  const map = {
    loading: ['', 'Connessione...'],
    ok:      ['ok', 'DB connesso'],
    err:     ['err', 'Errore DB'],
  };
  const [cls, label] = map[state] || ['', '—'];
  dot.className = 'dot' + (cls ? ' ' + cls : '');
  txt.textContent = label;
}

// ============================================================
// BUTTON LOADING STATE
// ============================================================
function setButtonLoading(btn, loading) {
  if (!btn) return;
  if (loading) {
    btn.dataset.orig = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span>';
    btn.disabled = true;
  } else {
    btn.innerHTML = btn.dataset.orig || btn.innerHTML;
    btn.disabled = false;
  }
}

// ============================================================
// SANITIZE (prevenzione XSS display-side)
// ============================================================
function esc(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(String(str)));
  return d.innerHTML;
}

// ============================================================
// KEYBOARD SHORTCUTS
// ============================================================
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
});
