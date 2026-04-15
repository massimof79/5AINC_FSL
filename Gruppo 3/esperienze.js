/**
 * esperienze.js
 * Gestione CRUD per la tabella ESPERIENZA tramite Fetch API.
 * Progetto: 5AINC_FSL — Gruppo ESPERIENZA
 */

'use strict';

// ── Costanti ──────────────────────────────────────────────────
const API_URL = './api_esperienze.php';

// ── Stato locale ──────────────────────────────────────────────
let currentEditId = null;   // null = modalità creazione, number = modalità modifica

// ── Riferimenti DOM (popolati in DOMContentLoaded) ───────────
let tableBody, modalOverlay, modalTitle, formEsperienza,
    btnNuova, btnChiudiModal, btnAnnulla, spinnerEl;

// ════════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
    // Cache DOM
    tableBody       = document.getElementById('esperienze-tbody');
    modalOverlay    = document.getElementById('modal-overlay');
    modalTitle      = document.getElementById('modal-title');
    formEsperienza  = document.getElementById('form-esperienza');
    btnNuova        = document.getElementById('btn-nuova');
    btnChiudiModal  = document.getElementById('btn-chiudi-modal');
    btnAnnulla      = document.getElementById('btn-annulla');
    spinnerEl       = document.getElementById('table-spinner');

    // Listener apertura modal (nuova esperienza)
    btnNuova.addEventListener('click', () => apriModal(null));

    // Listener chiusura modal
    btnChiudiModal.addEventListener('click', chiudiModal);
    btnAnnulla.addEventListener('click', chiudiModal);

    // Chiudi cliccando lo sfondo
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) chiudiModal();
    });

    // Chiudi con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') chiudiModal();
    });

    // Submit del form
    formEsperienza.addEventListener('submit', handleFormSubmit);

    // Caricamento iniziale
    loadEsperienze();
});

// ════════════════════════════════════════════════════════════
//  FETCH HELPERS
// ════════════════════════════════════════════════════════════

/**
 * Wrapper attorno a fetch che gestisce sempre JSON e gli errori HTTP.
 * @param {string} url
 * @param {RequestInit} options
 * @returns {Promise<{success: boolean, data: any, message: string}>}
 */
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

    if (!response.ok && !json.success) {
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

// ════════════════════════════════════════════════════════════
//  READ — carica e renderizza la tabella
// ════════════════════════════════════════════════════════════

async function loadEsperienze() {
    setTableLoading(true);
    try {
        const { data } = await apiFetch(API_URL);
        renderTable(data ?? []);
    } catch (err) {
        handleError(err, 'Caricamento esperienze');
        tableBody.innerHTML = `
            <tr>
              <td colspan="9" class="table-empty text-danger">
                Impossibile caricare i dati. ${escHtml(err.message)}
              </td>
            </tr>`;
    } finally {
        setTableLoading(false);
    }
}

function renderTable(rows) {
    if (rows.length === 0) {
        tableBody.innerHTML = `
            <tr>
              <td colspan="9" class="table-empty">
                Nessuna esperienza registrata. Clicca "+ Nuova" per iniziare.
              </td>
            </tr>`;
        return;
    }

    tableBody.innerHTML = rows.map(r => `
        <tr data-id="${r.codice_esperienza}">
          <td>${r.codice_esperienza}</td>
          <td>${escHtml(r.periodo_effettivo)}</td>
          <td class="text-center">${r.numero_ore_previste}</td>
          <td class="text-center">${r.numero_ore_svolte}</td>
          <td class="text-center">${r.numero_studenti}</td>
          <td>${escHtml(r.nome_tutor_scolastico ?? '—')}</td>
          <td>${escHtml(r.nome_tutor_aziendale  ?? '—')}</td>
          <td>${escHtml(r.data_disponibilita   ?? '—')}</td>
          <td>
            <div class="td-actions">
                <button class="btn btn-warning btn-sm" title="Modifica" onclick="apriModal(${r.codice_esperienza})" style="width: auto; height: auto; padding: 4px 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="open">
                        <path d="M17 3l4 4L7 21H3v-4L17 3z"/>
                        <path d="M15 5l4 4"/>
                    </svg>
                </button>
                <button class="btn btn-danger btn-sm" title="Elimina" onclick="eliminaEsperienza(${r.codice_esperienza})" style="width: auto; height: auto; padding: 4px 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7h16"/>
                        <path d="M10 11v6"/>
                        <path d="M14 11v6"/>
                        <path d="M5 7l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
                        <path d="M9 3h6a2 2 0 0 1 2 2v2H7V5a2 2 0 0 1 2-2z"/>
                    </svg>
                </button>
            </div>
           </td>
         </tr>
    `).join('');
}

function setTableLoading(loading) {
    if (!spinnerEl) return;
    spinnerEl.style.display = loading ? 'flex' : 'none';
}

// ════════════════════════════════════════════════════════════
//  MODAL — apri / chiudi
// ════════════════════════════════════════════════════════════

async function apriModal(id) {
    currentEditId = id;
    modalTitle.textContent = id ? `Modifica Esperienza #${id}` : 'Nuova Esperienza';
    formEsperienza.reset();
    clearFormErrors();

    // Popola le <select> con i dati dal server
    await Promise.all([
        populateSelect('sel-docente',       `${API_URL}?resource=tutor_scolastico`),
        populateSelect('sel-disponibilita', `${API_URL}?resource=disponibilita`),
        populateSelect('sel-tutor',         `${API_URL}?resource=tutor_aziendale`),
    ]);

    // Se è una modifica, precompila il form
    if (id) {
        try {
            const { data } = await apiFetch(`${API_URL}?id=${id}`);
            if (data) fillForm(data);
        } catch (err) {
            handleError(err, 'Caricamento dati esperienza');
            return;
        }
    }

    modalOverlay.classList.add('is-open');
    // Focus sul primo campo
    formEsperienza.querySelector('.form-control')?.focus();
}

function chiudiModal() {
    modalOverlay.classList.remove('is-open');
    currentEditId = null;
    formEsperienza.reset();
    clearFormErrors();
}

// ════════════════════════════════════════════════════════════
//  FORM — submit (crea o aggiorna)
// ════════════════════════════════════════════════════════════

async function handleFormSubmit(e) {
    e.preventDefault();
    clearFormErrors();

    const payload = buildPayload();

    const btnSubmit = formEsperienza.querySelector('[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.textContent = currentEditId ? 'Salvataggio…' : 'Creazione…';

    try {
        let result;
        if (currentEditId) {
            result = await apiFetch(`${API_URL}?id=${currentEditId}`, {
                method: 'PUT',
                body: payload,
            });
        } else {
            result = await apiFetch(API_URL, {
                method: 'POST',
                body: payload,
            });
        }

        showToast('success', 'Operazione completata', result.message);
        chiudiModal();
        loadEsperienze();

    } catch (err) {
        if (err.status === 422) {
            // Errori di validazione
            showToast('danger', 'Dati non validi', err.message);
        } else if (err.status === 401) {
            showToast('danger', 'Sessione scaduta', 'Effettua di nuovo il login.');
        } else {
            handleError(err, 'Salvataggio');
        }
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.textContent = currentEditId ? 'Salva modifiche' : 'Crea esperienza';
    }
}

// ════════════════════════════════════════════════════════════
//  DELETE
// ════════════════════════════════════════════════════════════

async function eliminaEsperienza(id) {
    const confirmed = confirm(`Sei sicuro di voler eliminare l'esperienza #${id}?\nQuesta azione è irreversibile.`);
    if (!confirmed) return;

    try {
        const result = await apiFetch(`${API_URL}?id=${id}`, { method: 'DELETE' });
        showToast('success', 'Eliminata', result.message);
        loadEsperienze();
    } catch (err) {
        handleError(err, 'Eliminazione');
    }
}

// ════════════════════════════════════════════════════════════
//  UTILITY — Form helpers
// ════════════════════════════════════════════════════════════

/** Raccoglie i valori del form e li trasforma in payload JSON. */
function buildPayload() {
    const fd = new FormData(formEsperienza);
    return {
        periodo_effettivo:    fd.get('periodo_effettivo'),
        numero_ore_previste:  Number(fd.get('numero_ore_previste')),
        numero_ore_svolte:    Number(fd.get('numero_ore_svolte')),
        numero_studenti:      Number(fd.get('numero_studenti')),
        codice_docente:       Number(fd.get('codice_docente')),
        codice_disponibilita: Number(fd.get('codice_disponibilita')),
        codice_tutor:         Number(fd.get('codice_tutor')),
    };
}

/** Precompila il form con i dati di una esperienza esistente. */
function fillForm(data) {
    formEsperienza.elements['periodo_effettivo'].value    = data.periodo_effettivo    ?? '';
    formEsperienza.elements['numero_ore_previste'].value  = data.numero_ore_previste  ?? '';
    formEsperienza.elements['numero_ore_svolte'].value    = data.numero_ore_svolte    ?? '';
    formEsperienza.elements['numero_studenti'].value      = data.numero_studenti      ?? '';
    formEsperienza.elements['codice_docente'].value       = data.codice_docente       ?? '';
    formEsperienza.elements['codice_disponibilita'].value = data.codice_disponibilita ?? '';
    formEsperienza.elements['codice_tutor'].value         = data.codice_tutor         ?? '';
}

/** Popola un elemento <select> con opzioni caricate dall'API. */
async function populateSelect(selectId, url) {
    const sel = document.getElementById(selectId);
    if (!sel) return;

    sel.innerHTML = '<option value="">— Caricamento… —</option>';
    try {
        const { data } = await apiFetch(url);
        sel.innerHTML = '<option value="">— Seleziona —</option>' +
            (data ?? []).map(item =>
                `<option value="${item.id}">${escHtml(item.label)}</option>`
            ).join('');
    } catch {
        sel.innerHTML = '<option value="">— Errore caricamento —</option>';
    }
}

function clearFormErrors() {
    formEsperienza.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    formEsperienza.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

// ════════════════════════════════════════════════════════════
//  UTILITY — Toast
// ════════════════════════════════════════════════════════════

/**
 * Mostra un messaggio toast.
 * @param {'success'|'danger'|'warning'|'info'} type
 * @param {string} title
 * @param {string} message
 * @param {number} duration  ms prima della scomparsa automatica
 */
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

    // Rimozione automatica
    const remove = () => {
        el.classList.add('toast-hide');
        el.addEventListener('animationend', () => el.remove(), { once: true });
    };

    const timer = setTimeout(remove, duration);
    // Clic per chiuderlo anticipatamente
    el.addEventListener('click', () => { clearTimeout(timer); remove(); });
}

// ════════════════════════════════════════════════════════════
//  UTILITY — Errori generici
// ════════════════════════════════════════════════════════════

function handleError(err, context = '') {
    const prefix = context ? `[${context}] ` : '';
    console.error(`${prefix}${err.message}`, err);

    if (err.status === 401) {
        showToast('danger', 'Sessione scaduta', 'Verrai reindirizzato al login…', 3000);
        setTimeout(() => window.location.href = 'login.php', 3000);
    } else {
        showToast('danger', 'Errore', err.message || 'Si è verificato un errore imprevisto.');
    }
}

// ════════════════════════════════════════════════════════════
//  UTILITY — Escape HTML (prevenzione XSS)
// ════════════════════════════════════════════════════════════

function escHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
