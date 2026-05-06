/**
 * esperienze.js
 * Gestione CRUD per la tabella ESPERIENZA tramite Fetch API.
 * Progetto: 5AINC_FSL — Gruppo ESPERIENZA
 */

'use strict';

// ── Costanti ──────────────────────────────────────────────────
const API_URL = './api_esperienze.php';

// ── Stato locale ──────────────────────────────────────────────
let currentEditId = null;   // null = creazione, number = modifica

// ── Riferimenti DOM ───────────────────────────────────────────
let tableBody, modalOverlay, modalTitle, formEsperienza,
    btnNuova, btnChiudiModal, btnAnnulla, spinnerEl, btnSubmit;

// ════════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
    tableBody      = document.getElementById('esperienze-tbody');
    modalOverlay   = document.getElementById('modal-overlay');
    modalTitle     = document.getElementById('modal-title');
    formEsperienza = document.getElementById('form-esperienza');
    btnNuova       = document.getElementById('btn-nuova');
    btnChiudiModal = document.getElementById('btn-chiudi-modal');
    btnAnnulla     = document.getElementById('btn-annulla');
    spinnerEl      = document.getElementById('table-spinner');
    btnSubmit      = document.getElementById('btn-submit');

    btnNuova.addEventListener('click', () => apriModal(null));
    btnChiudiModal.addEventListener('click', chiudiModal);
    btnAnnulla.addEventListener('click', chiudiModal);

    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) chiudiModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') chiudiModal();
    });

    formEsperienza.addEventListener('submit', handleFormSubmit);

    loadEsperienze();
});

// ════════════════════════════════════════════════════════════
//  FETCH HELPERS
// ════════════════════════════════════════════════════════════

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
          <td>${escHtml(r.data_disponibilita    ?? '—')}</td>
          <td>
            <div class="td-actions">
              <button class="btn btn-warning btn-sm" title="Modifica"
                      onclick="apriModal(${r.codice_esperienza})"
                      style="width:auto;height:auto;padding:4px 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 3l4 4L7 21H3v-4L17 3z"/>
                  <path d="M15 5l4 4"/>
                </svg>
              </button>
              <button class="btn btn-danger btn-sm" title="Elimina"
                      onclick="eliminaEsperienza(${r.codice_esperienza})"
                      style="width:auto;height:auto;padding:4px 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
    aggiornaTestoBottone(id);

    // Reset PRIMA di popolare le select, così il form è pulito
    formEsperienza.reset();
    clearFormErrors();

    // Popola le tre select in parallelo
    await Promise.all([
        populateSelect('sel-docente',       `${API_URL}?resource=tutor_scolastico`),
        populateSelect('sel-disponibilita', `${API_URL}?resource=disponibilita`),
        populateSelect('sel-tutor',         `${API_URL}?resource=tutor_aziendale`),
    ]);

    // Se è modifica, carica i dati e precompila il form
    // Fatto DOPO il popolamento delle select, così i valori vengono selezionati correttamente
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
    const primoInput = formEsperienza.querySelector('input.form-control');
    if (primoInput) primoInput.focus();
}

function chiudiModal() {
    modalOverlay.classList.remove('is-open');
    currentEditId = null;
    clearFormErrors();
}

function aggiornaTestoBottone(id) {
    if (!btnSubmit) return;
    btnSubmit.textContent = id ? 'Salva modifiche' : 'Crea esperienza';
}

// ════════════════════════════════════════════════════════════
//  FORM — submit (crea o aggiorna)
// ════════════════════════════════════════════════════════════

async function handleFormSubmit(e) {
    e.preventDefault();
    clearFormErrors();

    // Snapshot dell'id PRIMA di operazioni asincrone:
    // chiudiModal() resetta currentEditId a null, quindi lo snapshot
    // serve per sapere in finally se stavamo creando o modificando.
    const editingId = currentEditId;
    const payload   = buildPayload();

    if (btnSubmit) {
        btnSubmit.disabled    = true;
        btnSubmit.textContent = editingId ? 'Salvataggio…' : 'Creazione…';
    }

    try {
        let result;
        if (editingId) {
            result = await apiFetch(`${API_URL}?id=${editingId}`, {
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
            showToast('danger', 'Dati non validi', err.message, 6000);
        } else if (err.status === 401) {
            showToast('danger', 'Sessione scaduta', 'Verrai reindirizzato al login…', 3000);
            setTimeout(() => window.location.href = 'login.php', 3000);
        } else {
            handleError(err, 'Salvataggio');
        }
    } finally {
        // Usa editingId (snapshot) non currentEditId (già resettato da chiudiModal)
        if (btnSubmit) {
            btnSubmit.disabled    = false;
            btnSubmit.textContent = editingId ? 'Salva modifiche' : 'Crea esperienza';
        }
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

function buildPayload() {
    const fd = new FormData(formEsperienza);

    const get    = (k) => fd.get(k);
    const getNum = (k) => { const v = fd.get(k); return (v !== null && v !== '') ? Number(v) : null; };

    return {
        periodo_effettivo:    (get('periodo_effettivo') || '').trim(),
        numero_ore_previste:  getNum('numero_ore_previste'),
        numero_ore_svolte:    getNum('numero_ore_svolte'),
        numero_studenti:      getNum('numero_studenti'),
        codice_docente:       getNum('codice_docente'),
        codice_disponibilita: getNum('codice_disponibilita'),
        codice_tutor:         getNum('codice_tutor'),
    };
}

function fillForm(data) {
    setField('periodo_effettivo',    data.periodo_effettivo);
    setField('numero_ore_previste',  data.numero_ore_previste);
    setField('numero_ore_svolte',    data.numero_ore_svolte);
    setField('numero_studenti',      data.numero_studenti);
    setField('codice_docente',       data.codice_docente);
    setField('codice_disponibilita', data.codice_disponibilita);
    setField('codice_tutor',         data.codice_tutor);
}

function setField(name, value) {
    const el = formEsperienza.elements[name];
    if (el && value !== undefined && value !== null) {
        el.value = value;
    }
}

async function populateSelect(selectId, url) {
    const sel = document.getElementById(selectId);
    if (!sel) return;

    sel.innerHTML = '<option value="">— Caricamento… —</option>';
    sel.disabled  = true;

    try {
        const { data } = await apiFetch(url);
        const opzioni = (data ?? []).map(item =>
            `<option value="${escHtml(String(item.id))}">${escHtml(item.label)}</option>`
        ).join('');
        sel.innerHTML = '<option value="">— Seleziona —</option>' + opzioni;
    } catch (err) {
        sel.innerHTML = '<option value="">— Errore caricamento —</option>';
        console.error(`[populateSelect] Errore ${selectId}:`, err.message);
    } finally {
        sel.disabled = false;
    }
}

function clearFormErrors() {
    formEsperienza.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    formEsperienza.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

// ════════════════════════════════════════════════════════════
//  UTILITY — Toast
// ════════════════════════════════════════════════════════════

function showToast(type, title, message, duration = 4000) {
    // type="success"/"danger"/"alert"/"info"; Per vedere tutte le icone (modifica un record)
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Definiamo gli SVG come stringhe
    const icons = {
        success: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
        danger: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`,
        info: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`
    };

    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    
    // Inserimento dell'icona scelta
    el.innerHTML = `
        <span class="toast-icon">${icons[type] ?? icons.info}</span>
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

// ════════════════════════════════════════════════════════════
//  UTILITY — Errori generici
// ════════════════════════════════════════════════════════════

function handleError(err, context = '') {
    console.error(`[${context}] ${err.message}`, err);

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