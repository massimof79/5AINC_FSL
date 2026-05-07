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
let allEsperienze = [];

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

function filterTable() {
    const q = (document.getElementById('search-input')?.value ?? '').toLowerCase().trim();
    const filtered = q
        ? allEsperienze.filter(r =>
            String(r.periodo_effettivo ?? '').toLowerCase().includes(q) ||
            String(r.numero_studenti ?? '').includes(q) ||
            String(r.codice_esperienza ?? '').includes(q)
          )
        : allEsperienze;
    renderTable(filtered);
}

async function loadEsperienze() {
    setTableLoading(true);
    setDbStatus('loading');
    try {
        const { data } = await apiFetch(API_URL);
        allEsperienze = data ?? [];
        renderTable(allEsperienze);
        setDbStatus('ok');
    } catch (err) {
        handleError(err, 'Caricamento esperienze');
        setDbStatus('err');
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
                      onclick="apriModal(${r.codice_esperienza})">
                Modifica
              </button>
              <button class="btn btn-danger btn-sm" title="Elimina"
                      onclick="eliminaEsperienza(${r.codice_esperienza})">
                Elimina
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
            setTimeout(() => window.location.href = '../login.php', 3000);
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
        success: ICONS.success,
        danger:  ICONS.danger,
        warning: ICONS.warning,
        info:    ICONS.info,
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
        setTimeout(() => window.location.href = '../login.php', 3000);
    } else {
        showToast('danger', 'Errore', err.message || 'Si è verificato un errore imprevisto.');
    }
}

// ════════════════════════════════════════════════════════════
//  UTILITY — Escape HTML (prevenzione XSS)
// ════════════════════════════════════════════════════════════

function setDbStatus(state) {
    const el = document.getElementById('db-status');
    if (!el) return;
    const map = { loading: [ICONS.dbLoading, 'Connessione…'], ok: [ICONS.dbOk, 'DB connesso'], err: [ICONS.dbErr, 'Errore DB'] };
    const [icon, label] = map[state] || ['', '—'];
    el.innerHTML = `${icon} ${label}`;
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