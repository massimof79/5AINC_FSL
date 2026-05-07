'use strict';

const API_URL = '../api.php?entity=tutor_scolastici';
const SESSION_URL = '../api_session.php';
let currentEditId = null;
let allRows = [];

let tableBody, modalOverlay, modalTitle, formEntity,
    btnNuova, btnChiudiModal, btnAnnulla, spinnerEl;

document.addEventListener('DOMContentLoaded', () => {
    tableBody = document.getElementById('tutor-scolastici-tbody');
    modalOverlay = document.getElementById('modal-overlay');
    modalTitle = document.getElementById('modal-title');
    formEntity = document.getElementById('form-tutor-scolastico');
    btnNuova = document.getElementById('btn-nuova');
    btnChiudiModal = document.getElementById('btn-chiudi-modal');
    btnAnnulla = document.getElementById('btn-annulla');
    spinnerEl = document.getElementById('table-spinner');

    btnNuova.addEventListener('click', () => apriModal(null));
    btnChiudiModal.addEventListener('click', chiudiModal);
    btnAnnulla.addEventListener('click', chiudiModal);

    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) chiudiModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') chiudiModal();
    });

    formEntity.addEventListener('submit', handleFormSubmit);
    loadSessionBadge();
    loadRows();
});

async function loadSessionBadge() {
    try {
        const response = await fetch(SESSION_URL, { credentials: 'same-origin' });
        const json = await response.json();

        if (!response.ok || !json.success) {
            window.location.href = '../login.php';
            return;
        }

        const username = String(json.data?.username ?? 'Utente');
        const userNameEl = document.getElementById('user-name');
        const userAvatarEl = document.getElementById('user-avatar');
        if (userNameEl) userNameEl.textContent = username;
        if (userAvatarEl) userAvatarEl.textContent = username.charAt(0).toUpperCase() || 'U';
    } catch {
        window.location.href = '../login.php';
    }
}

async function apiFetch(url, options = {}) {
    const defaults = {
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        credentials: 'same-origin',
    };
    const merged = { ...defaults, ...options };
    if (merged.body && typeof merged.body !== 'string') {
        merged.body = JSON.stringify(merged.body);
    }

    const response = await fetch(url, merged);
    const json = await response.json();

    if (!response.ok || !json.success) {
        throw new ApiError(json.message || `HTTP ${response.status}`, response.status);
    }

    return json;
}

class ApiError extends Error {
    constructor(message, status) {
        super(message);
        this.status = status;
    }
}

function filterTable() {
    const q = (document.getElementById('search-input')?.value ?? '').toLowerCase().trim();
    const filtered = q
        ? allRows.filter(r =>
            String(r.nome ?? '').toLowerCase().includes(q) ||
            String(r.cognome ?? '').toLowerCase().includes(q) ||
            String(r.tipo ?? '').toLowerCase().includes(q)
          )
        : allRows;
    renderTable(filtered);
}

async function loadRows() {
    setTableLoading(true);
    setDbStatus('loading');
    try {
        const { data } = await apiFetch(API_URL);
        allRows = data ?? [];
        renderTable(allRows);
        setDbStatus('ok');
    } catch (err) {
        handleError(err, 'Caricamento tutor scolastici');
        setDbStatus('err');
        tableBody.innerHTML = `<tr><td colspan="6" class="table-empty text-danger">Impossibile caricare i dati. ${escHtml(err.message)}</td></tr>`;
    } finally {
        setTableLoading(false);
    }
}

function renderTable(rows) {
    if (rows.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="table-empty">Nessun tutor scolastico registrato.</td></tr>';
        return;
    }

    tableBody.innerHTML = rows.map(r => `
        <tr>
          <td>${r.codice_docente}</td>
          <td>${escHtml(r.nome)}</td>
          <td>${escHtml(r.cognome)}</td>
          <td>${escHtml(r.tipo)}</td>
          <td>${r.numero_studenti}</td>
          <td>
            <div class="td-actions">
              <button class="btn btn-warning btn-sm" title="Modifica" onclick="apriModal(${r.codice_docente})">Modifica</button>
              <button class="btn btn-danger btn-sm" title="Elimina" onclick="eliminaRiga(${r.codice_docente})">Elimina</button>
            </div>
          </td>
        </tr>
    `).join('');
}

function setTableLoading(loading) {
    if (!spinnerEl) return;
    spinnerEl.style.display = loading ? 'flex' : 'none';
}

async function apriModal(id) {
    currentEditId = id;
    modalTitle.textContent = id ? `Modifica Tutor Scolastico #${id}` : 'Nuovo Tutor Scolastico';
    formEntity.reset();

    if (id) {
        try {
            const { data } = await apiFetch(`${API_URL}&id=${id}`);
            fillForm(data);
        } catch (err) {
            handleError(err, 'Caricamento tutor scolastico');
            return;
        }
    }

    modalOverlay.classList.add('is-open');
    formEntity.querySelector('.form-control')?.focus();
}

function chiudiModal() {
    modalOverlay.classList.remove('is-open');
    currentEditId = null;
    formEntity.reset();
}

async function handleFormSubmit(e) {
    e.preventDefault();

    const payload = buildPayload();
    const btnSubmit = document.querySelector('[type="submit"][form="form-tutor-scolastico"]');
    if (!btnSubmit) {
        handleError(new Error('Pulsante submit non trovato nel DOM.'), 'UI form tutor scolastico');
        return;
    }
    btnSubmit.disabled = true;
    btnSubmit.textContent = currentEditId ? 'Salvataggio...' : 'Creazione...';

    try {
        const result = currentEditId
            ? await apiFetch(`${API_URL}&id=${currentEditId}`, { method: 'PUT', body: payload })
            : await apiFetch(API_URL, { method: 'POST', body: payload });

        showToast('success', 'Operazione completata', result.message);
        chiudiModal();
        loadRows();
    } catch (err) {
        handleError(err, 'Salvataggio tutor scolastico');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.textContent = currentEditId ? 'Salva modifiche' : 'Crea tutor';
    }
}

async function eliminaRiga(id) {
    if (!confirm(`Eliminare il tutor scolastico #${id}?`)) return;

    try {
        const result = await apiFetch(`${API_URL}&id=${id}`, { method: 'DELETE' });
        showToast('success', 'Eliminato', result.message);
        loadRows();
    } catch (err) {
        handleError(err, 'Eliminazione tutor scolastico');
    }
}

function buildPayload() {
    const fd = new FormData(formEntity);

    return {
        nome: fd.get('nome'),
        cognome: fd.get('cognome'),
        tipo: fd.get('tipo'),
        numero_studenti: Number(fd.get('numero_studenti')),
    };
}

function fillForm(data) {
    formEntity.elements['nome'].value = data.nome ?? '';
    formEntity.elements['cognome'].value = data.cognome ?? '';
    formEntity.elements['tipo'].value = data.tipo ?? '';
    formEntity.elements['numero_studenti'].value = data.numero_studenti ?? '';
}

function showToast(type, title, message, duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = { success: '✅', danger: '❌', warning: '⚠️', info: 'ℹ️' };

    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.innerHTML = `
        <span class="toast-icon">${icons[type] ?? 'ℹ️'}</span>
        <div class="toast-body">
          <div class="toast-title">${escHtml(title)}</div>
          <div class="toast-msg">${escHtml(message)}</div>
        </div>`;

    container.appendChild(el);

    const remove = () => {
        el.classList.add('toast-hide');
        el.addEventListener('animationend', () => el.remove(), { once: true });
    };

    const timer = setTimeout(remove, duration);
    el.addEventListener('click', () => {
        clearTimeout(timer);
        remove();
    });
}

function handleError(err, context = '') {
    const prefix = context ? `[${context}] ` : '';
    console.error(`${prefix}${err.message}`, err);

    if (err.status === 401) {
        showToast('danger', 'Sessione scaduta', 'Verrai reindirizzato al login...', 2000);
        setTimeout(() => window.location.href = '../login.php', 2000);
    } else {
        showToast('danger', 'Errore', err.message || 'Errore imprevisto.');
    }
}

function setDbStatus(state) {
    const el = document.getElementById('db-status');
    if (!el) return;
    const map = { loading: ['🟡', 'Connessione…'], ok: ['🟢', 'DB connesso'], err: ['🔴', 'Errore DB'] };
    const [icon, label] = map[state] || ['', '—'];
    el.textContent = `${icon} ${label}`;
}

function escHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
