/**
 * aziende.js — FSL Panel · CRUD Aziende
 * Gestione completa lato client: fetch, validazione, render, UX
 */

'use strict';

const MIDDLEWARE_URL = 'aziende_middleware.php';

let allAziende    = [];
let pendingDeleteId = null;

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  initUserInfo();
  loadAziende();
});

// ============================================================
// API
// ============================================================
async function apiFetch(action, method = 'GET', body = null) {
  const url = `${MIDDLEWARE_URL}?action=${action}`;
  const options = {
    method,
    headers: {
      'Content-Type':     'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
  };
  if (body) options.body = JSON.stringify(body);

  const res = await fetch(url, options);

  // Sessione scaduta → redirect al login
  if (res.status === 401) {
    window.location.href = 'login.php';
    return;
  }

  if (!res.ok) {
    const err = await res.json().catch(() => ({ message: `HTTP ${res.status}` }));
    throw new Error(err.message || `Errore HTTP ${res.status}`);
  }
  return res.json();
}

// ============================================================
// CRUD OPERATIONS
// ============================================================
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

async function handleCreate(e) {
  e.preventDefault();
  if (!validateForm('create-form')) return;
  const payload = collectForm('create-form');
  const btn = e.target.querySelector('button[type="submit"]');
  setButtonLoading(btn, true);
  try {
    const data = await apiFetch('create', 'POST', payload);
    if (!data.success) throw new Error(data.message);
    toast('Azienda creata con successo ✓');
    document.getElementById('create-form').reset();
    await loadAziende();
    showView('list');
  } catch (e) {
    toast('Errore: ' + e.message, 'error');
  } finally {
    setButtonLoading(btn, false);
  }
}

async function handleUpdate(e) {
  e.preventDefault();
  if (!validateForm('edit-form')) return;
  const payload = collectForm('edit-form');
  const btn = e.target.querySelector('button[type="submit"]');
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

function confirmDelete(id) {
  const azienda = allAziende.find(a => String(a.codice_azienda) === String(id));
  pendingDeleteId = id;
  document.getElementById('modal-azienda-name').textContent =
    azienda ? azienda.ragione_sociale : `#${id}`;
  document.getElementById('delete-modal').classList.add('is-open');
  document.getElementById('confirm-delete-btn').onclick = executeDelete;
}

async function executeDelete() {
  if (!pendingDeleteId) return;
  try {
    const data = await apiFetch(`delete&id=${pendingDeleteId}`, 'DELETE');
    if (data.success) {
      toast('Azienda eliminata');
      closeModal();
      loadAziende();
    }
  } catch (e) {
    toast(e.message, 'error');
  }
}

// ============================================================
// RENDER
// ============================================================
function renderTable(aziende) {
  const tbody = document.getElementById('table-body');
  document.getElementById('view-count').textContent = aziende.length;

  if (!aziende.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="table-empty">Nessuna azienda trovata</td></tr>`;
    return;
  }

  tbody.innerHTML = aziende.map(a => `
    <tr data-id="${esc(a.codice_azienda)}" onclick="openDetail(${a.codice_azienda})" style="cursor:pointer;">
      <td>${esc(a.codice_azienda)}</td>
      <td><strong>${esc(a.ragione_sociale)}</strong></td>
      <td style="font-family:monospace;font-size:0.85rem;">${esc(a.partita_iva)}</td>
      <td>${esc(a.sede_legale)}</td>
      <td>${esc(a.sede_operativa)}</td>
      <td onclick="event.stopPropagation()">
        <div class="td-actions">
          <button class="btn btn-info btn-sm" title="Modifica" onclick="openEdit(${a.codice_azienda})">✎</button>
          <button class="btn btn-danger btn-sm" title="Elimina" onclick="confirmDelete(${a.codice_azienda})">✕</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function renderTableError(msg) {
  document.getElementById('table-body').innerHTML =
    `<tr><td colspan="6" class="table-empty text-danger">⚠ ${esc(msg)}</td></tr>`;
}

// ============================================================
// FILTER
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

  // sidebar active link
  document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));
  const link = document.querySelector(`.sidebar-nav a[data-view="${name}"]`);
  if (link) link.classList.add('active');

  // topbar title
  const labels = { list: 'Elenco Aziende', create: 'Nuova Azienda', edit: 'Modifica Azienda', detail: 'Dettaglio Azienda' };
  const titleEl = document.getElementById('topbar-title');
  if (titleEl) titleEl.textContent = labels[name] || name;
}

async function openDetail(id) {
  const az = await loadAzienda(id);
  if (!az) return;
  document.getElementById('detail-id-badge').textContent = `#${az.codice_azienda}`;
  document.getElementById('d-ragione').textContent    = az.ragione_sociale;
  document.getElementById('d-piva').textContent       = az.partita_iva;
  document.getElementById('d-sede-legale').textContent = az.sede_legale;
  document.getElementById('d-sede-op').textContent    = az.sede_operativa;
  document.getElementById('detail-edit-btn').onclick  = () => openEdit(id);
  showView('detail');
}

async function openEdit(id) {
  const az = await loadAzienda(id);
  if (!az) return;
  document.getElementById('edit-id-badge').textContent = `#${az.codice_azienda}`;
  document.getElementById('e-id').value           = az.codice_azienda;
  document.getElementById('e-ragione').value      = az.ragione_sociale;
  document.getElementById('e-piva').value         = az.partita_iva;
  document.getElementById('e-sede-legale').value  = az.sede_legale;
  document.getElementById('e-sede-op').value      = az.sede_operativa;
  showView('edit');
}

// ============================================================
// FORM UTILS
// ============================================================
function collectForm(formId) {
  const data = {};
  new FormData(document.getElementById(formId)).forEach((v, k) => { data[k] = v.trim(); });
  return data;
}

function validateForm(formId) {
  const form = document.getElementById(formId);
  const pfx  = formId === 'create-form' ? 'c' : 'e';
  let valid = true;

  form.querySelectorAll('.form-error').forEach(el => el.textContent = '');
  form.querySelectorAll('input').forEach(el => el.classList.remove('is-invalid'));

  const rules = [
    { name: 'ragione_sociale', min: 2, max: 100, errId: `err-${pfx}-ragione`,     label: 'Ragione sociale' },
    { name: 'partita_iva',     regex: /^\d{11}$/, errId: `err-${pfx}-piva`,       label: 'P.IVA (11 cifre)' },
    { name: 'sede_legale',     min: 2, max: 100, errId: `err-${pfx}-sede-legale`, label: 'Sede legale' },
    { name: 'sede_operativa',  min: 2, max: 100, errId: `err-${pfx}-sede-op`,     label: 'Sede operativa' },
  ];

  rules.forEach(rule => {
    const input = form.querySelector(`[name="${rule.name}"]`);
    if (!input) return;
    const val = input.value.trim();
    let err = '';

    if (!val)                               err = `${rule.label} è obbligatoria`;
    else if (rule.regex && !rule.regex.test(val)) err = `Formato non valido — ${rule.label}`;
    else if (rule.min && val.length < rule.min)   err = `Minimo ${rule.min} caratteri`;
    else if (rule.max && val.length > rule.max)   err = `Massimo ${rule.max} caratteri`;

    if (err) {
      valid = false;
      input.classList.add('is-invalid');
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
  document.getElementById('stat-total').textContent  = aziende.length;
  document.getElementById('stat-cities').textContent = new Set(aziende.map(a => a.sede_legale)).size;
}

// ============================================================
// MODAL
// ============================================================
function closeModal() {
  document.getElementById('delete-modal').classList.remove('is-open');
  pendingDeleteId = null;
}

document.getElementById('delete-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// ============================================================
// TOAST
// ============================================================
function toast(msg, type = 'success') {
  const container = document.getElementById('toast-container');
  const el = document.createElement('div');
  // mappa tipi al sistema global.css
  const typeMap = { success: 'toast-success', error: 'toast-danger', warn: 'toast-warning' };
  el.className = 'toast ' + (typeMap[type] || 'toast-success');
  el.innerHTML = `
    <span class="toast-icon">${type === 'error' ? '✕' : type === 'warn' ? '⚠' : '✓'}</span>
    <div class="toast-body"><p class="toast-msg">${esc(msg)}</p></div>`;
  container.appendChild(el);
  setTimeout(() => {
    el.classList.add('toast-hide');
    setTimeout(() => el.remove(), 400);
  }, 3500);
}

// ============================================================
// DB STATUS
// ============================================================
function setDbStatus(state) {
  const el = document.getElementById('db-status');
  if (!el) return;
  const map = {
    loading: ['🟡', 'Connessione…'],
    ok:      ['🟢', 'DB connesso'],
    err:     ['🔴', 'Errore DB'],
  };
  const [icon, label] = map[state] || ['', '—'];
  el.textContent = `${icon} ${label}`;
}

// ============================================================
// BUTTON LOADING
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
// USER INFO
// ============================================================
function initUserInfo() {
  const meta   = document.querySelector('meta[name="username"]');
  const userEl = document.getElementById('header-user');
  const avatar = document.getElementById('user-avatar');
  if (meta && userEl) {
    const name = meta.getAttribute('content');
    userEl.textContent = name;
    if (avatar) avatar.textContent = name.charAt(0).toUpperCase();
  }
}

// ============================================================
// UTILS
// ============================================================
function esc(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(String(str)));
  return d.innerHTML;
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
});
