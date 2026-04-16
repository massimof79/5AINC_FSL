'use strict';

const API_URL = './api_studenti.php';
const SESSION_URL = './api_session.php';
let currentEditId = null;

let tableBody, modalOverlay, modalTitle, formEntity,
    btnNuova, btnChiudiModal, btnAnnulla, spinnerEl;

document.addEventListener('DOMContentLoaded', () => {
    tableBody = document.getElementById('studenti-tbody');
    modalOverlay = document.getElementById('modal-overlay');
    modalTitle = document.getElementById('modal-title');
    formEntity = document.getElementById('form-studente');
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
            window.location.href = 'login.php';
            return;
        }

        const username = String(json.data?.username ?? 'Utente');
        const userNameEl = document.getElementById('user-name');
        const userAvatarEl = document.getElementById('user-avatar');
        if (userNameEl) userNameEl.textContent = username;
        if (userAvatarEl) userAvatarEl.textContent = username.charAt(0).toUpperCase() || 'U';
    } catch {
        window.location.href = 'login.php';
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

async function loadRows() {
    setTableLoading(true);
    try {
        const { data } = await apiFetch(API_URL);
        renderTable(data ?? []);
    } catch (err) {
        handleError(err, 'Caricamento studenti');
        tableBody.innerHTML = `<tr><td colspan="12" class="table-empty text-danger">Impossibile caricare i dati. ${escHtml(err.message)}</td></tr>`;
    } finally {
        setTableLoading(false);
    }
}

function renderTable(rows) {
    if (rows.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="12" class="table-empty">Nessuno studente registrato.</td></tr>';
        return;
    }

    tableBody.innerHTML = rows.map(r => `
        <tr>
          <td>${r.codice_studente}</td>
          <td>${escHtml(r.cognome)} ${escHtml(r.nome)}</td>
          <td>${escHtml(r.data_di_nascita)}</td>
          <td>${escHtml(r.luogo_di_nascita)}</td>
          <td>${escHtml(r.indirizzo)}</td>
          <td>${escHtml(r.email)}</td>
          <td>${escHtml(r.classe)}</td>
          <td>${escHtml(r.indirizzo_di_studi)}</td>
          <td>${r.codice_esperienza ?? '—'}</td>
          <td>${r.codice_candidatura ?? '—'}</td>
          <td>${escHtml(r.stato_candidatura ?? '—')}</td>
          <td>
            <div class="td-actions">
              <button class="btn btn-warning btn-sm" title="Modifica" onclick="apriModal(${r.codice_studente})">✏️</button>
              <button class="btn btn-danger btn-sm" title="Elimina" onclick="eliminaRiga(${r.codice_studente})">🗑️</button>
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
    modalTitle.textContent = id ? `Modifica Studente #${id}` : 'Nuovo Studente';
    formEntity.reset();

    await Promise.all([
        populateSelect('sel-esperienza', `${API_URL}?resource=esperienze`),
        populateSelect('sel-candidatura', `${API_URL}?resource=candidature`),
    ]);

    if (id) {
        try {
            const { data } = await apiFetch(`${API_URL}?id=${id}`);
            fillForm(data);
        } catch (err) {
            handleError(err, 'Caricamento studente');
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
    const btnSubmit = document.querySelector('[type="submit"][form="form-studente"]');
    if (!btnSubmit) {
        handleError(new Error('Pulsante submit non trovato nel DOM.'), 'UI form studente');
        return;
    }
    btnSubmit.disabled = true;
    btnSubmit.textContent = currentEditId ? 'Salvataggio...' : 'Creazione...';

    try {
        const result = currentEditId
            ? await apiFetch(`${API_URL}?id=${currentEditId}`, { method: 'PUT', body: payload })
            : await apiFetch(API_URL, { method: 'POST', body: payload });

        showToast('success', 'Operazione completata', result.message);
        chiudiModal();
        loadRows();
    } catch (err) {
        handleError(err, 'Salvataggio studente');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.textContent = currentEditId ? 'Salva modifiche' : 'Crea studente';
    }
}

async function eliminaRiga(id) {
    if (!confirm(`Eliminare lo studente #${id}?`)) return;

    try {
        const result = await apiFetch(`${API_URL}?id=${id}`, { method: 'DELETE' });
        showToast('success', 'Eliminato', result.message);
        loadRows();
    } catch (err) {
        handleError(err, 'Eliminazione studente');
    }
}

function buildPayload() {
    const fd = new FormData(formEntity);
    const esperienza = fd.get('codice_esperienza');
    const candidatura = fd.get('codice_candidatura');

    return {
        nome: fd.get('nome'),
        cognome: fd.get('cognome'),
        data_di_nascita: fd.get('data_di_nascita'),
        luogo_di_nascita: fd.get('luogo_di_nascita'),
        indirizzo: fd.get('indirizzo'),
        email: fd.get('email'),
        classe: fd.get('classe'),
        indirizzo_di_studi: fd.get('indirizzo_di_studi'),
        codice_esperienza: esperienza ? Number(esperienza) : null,
        codice_candidatura: candidatura ? Number(candidatura) : null,
    };
}

function fillForm(data) {
    formEntity.elements['nome'].value = data.nome ?? '';
    formEntity.elements['cognome'].value = data.cognome ?? '';
    formEntity.elements['data_di_nascita'].value = data.data_di_nascita ?? '';
    formEntity.elements['luogo_di_nascita'].value = data.luogo_di_nascita ?? '';
    formEntity.elements['indirizzo'].value = data.indirizzo ?? '';
    formEntity.elements['email'].value = data.email ?? '';
    formEntity.elements['classe'].value = data.classe ?? '';
    formEntity.elements['indirizzo_di_studi'].value = data.indirizzo_di_studi ?? '';
    formEntity.elements['codice_esperienza'].value = data.codice_esperienza ?? '';
    formEntity.elements['codice_candidatura'].value = data.codice_candidatura ?? '';
}

async function populateSelect(selectId, url) {
    const sel = document.getElementById(selectId);
    if (!sel) return;

    sel.innerHTML = '<option value="">- Caricamento... -</option>';

    try {
        const { data } = await apiFetch(url);
        sel.innerHTML = '<option value="">- Nessuno -</option>' +
            (data ?? []).map(item => `<option value="${item.id}">${escHtml(item.label)}</option>`).join('');
    } catch {
        sel.innerHTML = '<option value="">- Errore caricamento -</option>';
    }
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
        setTimeout(() => window.location.href = 'login.php', 2000);
    } else {
        showToast('danger', 'Errore', err.message || 'Errore imprevisto.');
    }
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
