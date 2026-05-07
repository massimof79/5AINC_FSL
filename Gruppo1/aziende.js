'use strict';

const MIDDLEWARE_URL = 'aziende_middleware.php';

let allAziende = [];
let currentEditId = null;
let pendingDeleteId = null;

let tableBody, crudModal, crudModalTitle, formEntity,
    btnNuova, btnChiudiModal, btnAnnulla, btnSubmit,
    deleteModal, btnChiudiDelete, btnAnnullaDelete;

document.addEventListener('DOMContentLoaded', () => {
    tableBody       = document.getElementById('table-body');
    crudModal       = document.getElementById('crud-modal');
    crudModalTitle  = document.getElementById('crud-modal-title');
    formEntity      = document.getElementById('form-azienda');
    btnNuova        = document.getElementById('btn-nuova');
    btnChiudiModal  = document.getElementById('btn-chiudi-modal');
    btnAnnulla      = document.getElementById('btn-annulla');
    btnSubmit       = document.getElementById('btn-submit-azienda');
    deleteModal     = document.getElementById('delete-modal');
    btnChiudiDelete = document.getElementById('btn-chiudi-delete');
    btnAnnullaDelete = document.getElementById('btn-annulla-delete');

    btnNuova.addEventListener('click', () => apriModal(null));
    btnChiudiModal.addEventListener('click', chiudiModal);
    btnAnnulla.addEventListener('click', chiudiModal);
    btnChiudiDelete.addEventListener('click', chiudiDeleteModal);
    btnAnnullaDelete.addEventListener('click', chiudiDeleteModal);

    crudModal.addEventListener('click', (e) => { if (e.target === crudModal) chiudiModal(); });
    deleteModal.addEventListener('click', (e) => { if (e.target === deleteModal) chiudiDeleteModal(); });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { chiudiModal(); chiudiDeleteModal(); }
    });

    formEntity.addEventListener('submit', handleFormSubmit);
    initUserInfo();
    loadAziende();
});

async function apiFetch(action, method = 'GET', body = null) {
    const url = `${MIDDLEWARE_URL}?action=${action}`;
    const options = {
        method,
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    };
    if (body) options.body = JSON.stringify(body);

    const res = await fetch(url, options);
    if (res.status === 401) { window.location.href = '../login.php'; return; }
    if (!res.ok) {
        const err = await res.json().catch(() => ({ message: `HTTP ${res.status}` }));
        throw new Error(err.message || `Errore HTTP ${res.status}`);
    }
    return res.json();
}

async function loadAziende() {
    setDbStatus('loading');
    try {
        const data = await apiFetch('list');
        if (!data.success) throw new Error(data.message);
        allAziende = data.data ?? [];
        renderTable(allAziende);
        setDbStatus('ok');
    } catch (e) {
        setDbStatus('err');
        tableBody.innerHTML = `<tr><td colspan="6" class="table-empty text-danger">Impossibile caricare i dati. ${esc(e.message)}</td></tr>`;
        toast('Errore nel caricamento: ' + e.message, 'error');
    }
}

function renderTable(aziende) {
    const countEl = document.getElementById('view-count');
    if (countEl) countEl.textContent = aziende.length;

    if (!aziende.length) {
        tableBody.innerHTML = `<tr><td colspan="6" class="table-empty">Nessuna azienda trovata</td></tr>`;
        return;
    }

    tableBody.innerHTML = aziende.map(a => `
        <tr>
          <td>${esc(a.codice_azienda)}</td>
          <td><strong>${esc(a.ragione_sociale)}</strong></td>
          <td style="font-family:monospace;font-size:0.85rem;">${esc(a.partita_iva)}</td>
          <td>${esc(a.sede_legale)}</td>
          <td>${esc(a.sede_operativa)}</td>
          <td>
            <div class="td-actions">
              <button class="btn btn-warning btn-sm" title="Modifica" onclick="apriModal(${a.codice_azienda})">Modifica</button>
              <button class="btn btn-danger btn-sm" title="Elimina" onclick="confirmDelete(${a.codice_azienda})">Elimina</button>
            </div>
          </td>
        </tr>
    `).join('');
}

function filterTable() {
    const q = (document.getElementById('search-input')?.value ?? '').toLowerCase().trim();
    const filtered = q
        ? allAziende.filter(a =>
            String(a.ragione_sociale ?? '').toLowerCase().includes(q) ||
            String(a.partita_iva ?? '').toLowerCase().includes(q) ||
            String(a.sede_legale ?? '').toLowerCase().includes(q) ||
            String(a.sede_operativa ?? '').toLowerCase().includes(q)
          )
        : allAziende;
    renderTable(filtered);
}

async function apriModal(id) {
    currentEditId = id;
    crudModalTitle.textContent = id ? `Modifica Azienda #${id}` : 'Nuova Azienda';
    formEntity.reset();
    document.getElementById('inp-id').value = '';
    btnSubmit.textContent = id ? 'Salva modifiche' : 'Crea azienda';

    if (id) {
        try {
            const data = await apiFetch(`get&id=${encodeURIComponent(id)}`);
            if (!data.success) throw new Error(data.message);
            fillForm(data.data);
        } catch (err) {
            toast('Errore caricamento azienda: ' + err.message, 'error');
            return;
        }
    }

    crudModal.classList.add('is-open');
    formEntity.querySelector('.form-control')?.focus();
}

function chiudiModal() {
    crudModal.classList.remove('is-open');
    currentEditId = null;
    formEntity.reset();
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const payload = buildPayload();
    btnSubmit.disabled = true;
    btnSubmit.textContent = currentEditId ? 'Salvataggio...' : 'Creazione...';

    try {
        let result;
        if (currentEditId) {
            payload.codice_azienda = currentEditId;
            result = await apiFetch('update', 'PUT', payload);
        } else {
            result = await apiFetch('create', 'POST', payload);
        }
        if (!result.success) throw new Error(result.message);
        toast(result.message || 'Operazione completata');
        chiudiModal();
        loadAziende();
    } catch (err) {
        toast('Errore: ' + err.message, 'error');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.textContent = currentEditId ? 'Salva modifiche' : 'Crea azienda';
    }
}

function buildPayload() {
    const fd = new FormData(formEntity);
    return {
        ragione_sociale: fd.get('ragione_sociale'),
        partita_iva:     fd.get('partita_iva'),
        sede_legale:     fd.get('sede_legale'),
        sede_operativa:  fd.get('sede_operativa'),
    };
}

function fillForm(data) {
    formEntity.elements['ragione_sociale'].value = data.ragione_sociale ?? '';
    formEntity.elements['partita_iva'].value     = data.partita_iva ?? '';
    formEntity.elements['sede_legale'].value     = data.sede_legale ?? '';
    formEntity.elements['sede_operativa'].value  = data.sede_operativa ?? '';
}

function confirmDelete(id) {
    const azienda = allAziende.find(a => String(a.codice_azienda) === String(id));
    pendingDeleteId = id;
    document.getElementById('modal-azienda-name').textContent =
        azienda ? azienda.ragione_sociale : `#${id}`;
    document.getElementById('confirm-delete-btn').onclick = executeDelete;
    deleteModal.classList.add('is-open');
}

async function executeDelete() {
    if (!pendingDeleteId) return;
    try {
        const data = await apiFetch(`delete&id=${pendingDeleteId}`, 'DELETE');
        if (!data.success) throw new Error(data.message);
        toast('Azienda eliminata');
        chiudiDeleteModal();
        loadAziende();
    } catch (e) {
        toast(e.message, 'error');
    }
}

function chiudiDeleteModal() {
    deleteModal.classList.remove('is-open');
    pendingDeleteId = null;
}

function setDbStatus(state) {
    const el = document.getElementById('db-status');
    if (!el) return;
    const map = { loading: [ICONS.dbLoading, 'Connessione…'], ok: [ICONS.dbOk, 'DB connesso'], err: [ICONS.dbErr, 'Errore DB'] };
    const [icon, label] = map[state] || ['', '—'];
    el.innerHTML = `${icon} ${label}`;
}

function toast(msg, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const icons = { success: ICONS.success, error: ICONS.danger, warn: ICONS.warning };
    const cssType = type === 'error' ? 'danger' : type === 'warn' ? 'warning' : 'success';
    const el = document.createElement('div');
    el.className = `toast toast-${cssType}`;
    el.innerHTML = `
        <span class="toast-icon">${icons[type] ?? ICONS.info}</span>
        <div class="toast-body"><div class="toast-msg">${esc(msg)}</div></div>`;
    container.appendChild(el);
    const remove = () => {
        el.classList.add('toast-hide');
        el.addEventListener('animationend', () => el.remove(), { once: true });
    };
    const timer = setTimeout(remove, 4000);
    el.addEventListener('click', () => { clearTimeout(timer); remove(); });
}

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

function esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(String(str ?? '')));
    return d.innerHTML;
}
