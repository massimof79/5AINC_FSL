<?php
/**
 * disponibilità.php — Gruppo 2 · Gestione Disponibilità
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

requireLoginPage('../login.php');

$username    = htmlspecialchars((string) ($_SESSION['username'] ?? 'Utente'), ENT_QUOTES, 'UTF-8');
$userInitial = strtoupper(substr($username, 0, 1)) ?: 'U';
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Disponibilità — PCTOConnect</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link id="theme-link" rel="stylesheet" href="../global2.css">
  <script src="../theme.js"></script>
</head>
<body>

<div class="app-shell">

  <!-- ── SIDEBAR ─────────────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">
    <a href="../index.php" class="sidebar-logo" style="text-decoration:none;color:inherit;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
      </svg>
      PCTO<span class="logo-accent">Connect</span>
    </a>

    <nav class="sidebar-nav">
      <div class="sidebar-section-label">Gestione</div>
      <a href="../Gruppo1/index_aziende.php">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
          </svg>
        </span>Aziende
      </a>
      <a href="disponibilità.php" class="active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
        </span>Disponibilità
      </a>
      <a href="../Gruppo%203/esperienze.php">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 2.25H8.25A2.25 2.25 0 006 4.5v15A2.25 2.25 0 008.25 21.75h7.5a2.25 2.25 0 002.25-2.25V4.5a2.25 2.25 0 00-2.25-2.25z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 2.25V4.5H9V2.25m3 9h3m-3 3h3m-6-3h.008v.008H9v-.008zm0 3h.008v.008H9v-.008z" />
          </svg>
        </span>Esperienze
      </a>
      <div class="sidebar-section-label">Personale</div>
      <a href="../Gruppo4/tutor_scolastici.html">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
        </span>Tutor Scolastici
      </a>
      <a href="../Gruppo4/tutor_aziendali.html">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
          </svg>
        </span>Tutor Aziendali
      </a>
      <a href="../Gruppo4/studenti.html">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
        </span>Studenti
      </a>
      <div class="sidebar-section-label">Tema</div>
      <div class="theme-switcher" id="theme-switcher" role="group" aria-label="Selezione tema">
        <button class="theme-tab" data-theme="global.css"
                aria-pressed="false" aria-label="Attiva tema Classic">
          <span class="theme-tab-swatch theme-swatch-classic" aria-hidden="true"></span>
          Classic
        </button>
        <button class="theme-tab" data-theme="global2.css"
                aria-pressed="false" aria-label="Attiva tema Aurora">
          <span class="theme-tab-swatch theme-swatch-aurora" aria-hidden="true"></span>
          Aurora
        </button>
        <button class="theme-tab" data-theme="global3.css"
                aria-pressed="false" aria-label="Attiva tema Brutale">
          <span class="theme-tab-swatch theme-swatch-brutale" aria-hidden="true"></span>
          Brutale
        </button>
      </div>
      <div class="sidebar-section-label">Sistema</div>
      <a href="../logout.php">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
          </svg>
        </span>Logout
      </a>
    </nav>
  </aside>

  <!-- ── TOPBAR ───────────────────────────────────────────── -->
  <header class="topbar">
    <button class="btn btn-secondary btn-icon" id="btn-toggle-sidebar"
            title="Apri/chiudi menu" style="display:none">☰</button>
    <span class="topbar-title">Gestione Disponibilità</span>
    <div class="topbar-right">
      <span id="db-status" style="font-size:var(--fs-sm);color:var(--text-muted);">Connessione…</span>
      <div class="user-badge">
        <div class="avatar"><?= $userInitial ?></div>
        <span><?= $username ?></span>
      </div>
    </div>
  </header>

  <!-- ── MAIN CONTENT ─────────────────────────────────────── -->
  <main class="main-content">

    <div class="page-header">
      <h1>Disponibilità <span id="view-count" style="font-size:var(--fs-sm);font-weight:400;color:var(--text-muted);background:var(--bg-light);border:1px solid var(--border);padding:2px 10px;border-radius:999px;margin-left:6px;">—</span></h1>
      <p>Visualizza, aggiungi e gestisci le disponibilità.</p>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Elenco Disponibilità</h2>
        <div style="display:flex;gap:.75rem;align-items:center;">
          <input type="search" id="search-input" class="form-control"
                 style="width:280px;font-size:var(--fs-sm);"
                 placeholder="Cerca per periodo, descrizione…" oninput="filterTable()" />
          <button class="btn btn-primary" id="btn-nuova">＋ Nuova Disponibilità</button>
        </div>
      </div>

      <div id="table-spinner" class="flex-center mt-2 mb-2" style="display:none">
        <div class="spinner"></div>
        <span class="text-muted" style="margin-left:.75rem">Caricamento…</span>
      </div>

      <div class="table-wrapper">
        <table id="tabella-disponibilita">
          <thead>
            <tr>
              <th>#</th>
              <th>Periodo</th>
              <th>Descrizione</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody id="disponibilita-tbody">
            <tr><td colspan="4" class="table-empty">Caricamento in corso…</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div id="pagination-bar" style="display:none;align-items:center;justify-content:flex-end;gap:.5rem;padding:.6rem 1rem;border-top:1px solid var(--border);">
        <span id="pagination-info" style="font-size:var(--fs-sm);color:var(--text-muted);margin-right:.25rem;"></span>
        <button class="btn btn-secondary btn-sm" id="btn-prev" onclick="goPage(-1)" title="Pagina precedente"></button>
        <button class="btn btn-secondary btn-sm" id="btn-next" onclick="goPage(+1)" title="Pagina successiva"></button>
      </div>
    </div>

  </main>
</div><!-- /app-shell -->


<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  MODAL — Crea / Modifica Disponibilità               ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div class="modal-overlay" id="modal-overlay" role="dialog"
     aria-modal="true" aria-labelledby="modal-title">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-title">Nuova Disponibilità</h3>
      <button class="modal-close" id="btn-chiudi-modal" aria-label="Chiudi">&times;</button>
    </div>

    <form id="form-disponibilita" novalidate>
    <div class="modal-body">

      <div class="form-group">
        <label class="form-label" for="inp-periodo">Periodo <span class="required">*</span></label>
        <input type="text" id="inp-periodo" name="periodo_previsto" class="form-control"
               placeholder="es. Marzo 2026 – Aprile 2026" required />
      </div>

      <div class="form-group">
        <label class="form-label" for="inp-descrizione">Descrizione</label>
        <input type="text" id="inp-descrizione" name="descrizione" class="form-control"
               placeholder="es. Stage presso Azienda XYZ" />
      </div>

    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="btn-annulla">Annulla</button>
      <button type="submit" id="btn-submit" class="btn btn-primary">Crea disponibilità</button>
    </div>
    </form>
  </div>
</div>


<!-- ── TOAST CONTAINER ──────────────────────────────────── -->
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>


<!-- ── Script ───────────────────────────────────────────── -->
<script src="../icons/icons.js"></script>
<script>
// ╔══════════════════════════════════════════════════════════╗
// ║  CONFIG — modifica qui per test                          ║
// ╚══════════════════════════════════════════════════════════╝
const ROWS_PER_PAGE = 10;   // numero righe per pagina


// ── Stato ─────────────────────────────────────────────────
const API    = 'middlewere.php';
let   editId = null;
let   allRows    = [];   // tutti i record caricati
let   filtered   = [];   // dopo filterTable()
let   currentPage = 1;

// ── DB Status ─────────────────────────────────────────────
function setDbStatus(state) {
  const el = document.getElementById('db-status');
  if (!el) return;
  const map = {
    loading: [ICONS.dbLoading, 'Connessione…'],
    ok:      [ICONS.dbOk,      'DB connesso'],
    err:     [ICONS.dbErr,     'Errore DB'],
  };
  const [icon, label] = map[state] || ['', '—'];
  el.innerHTML = `${icon} ${label}`;
}

// ── Escape HTML ───────────────────────────────────────────
function escHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// ── Toast ─────────────────────────────────────────────────
function toast(msg, type = 'success') {
  const tc = document.getElementById('toast-container');
  if (!tc) return;
  const el = document.createElement('div');
  el.className = `toast toast-${type}`;
  el.textContent = msg;
  tc.appendChild(el);
  setTimeout(() => el.remove(), 3500);
}

// ── Modal ─────────────────────────────────────────────────
function apriModal(id = null) {
  editId = id;
  const titleEl  = document.getElementById('modal-title');
  const submitEl = document.getElementById('btn-submit');
  titleEl.textContent  = id ? 'Modifica Disponibilità' : 'Nuova Disponibilità';
  submitEl.textContent = id ? 'Salva modifiche'        : 'Crea disponibilità';
  document.getElementById('form-disponibilita').reset();

  if (id) {
    fetch(`${API}?id=${id}`)
      .then(r => r.json())
      .then(json => {
        if (json.success && json.data) {
          document.getElementById('inp-periodo').value     = json.data.periodo_previsto ?? '';
          document.getElementById('inp-descrizione').value = json.data.descrizione      ?? '';
        }
      })
      .catch(() => toast('Errore nel caricamento dei dati.', 'error'));
  }

  document.getElementById('modal-overlay').classList.add('is-open');
}

function chiudiModal() {
  document.getElementById('modal-overlay').classList.remove('is-open');
  editId = null;
}

// ── Render pagina corrente ─────────────────────────────────
function renderPage() {
  const tbody    = document.getElementById('disponibilita-tbody');
  const totalPages = Math.ceil(filtered.length / ROWS_PER_PAGE) || 1;
  if (currentPage > totalPages) currentPage = totalPages;
  const countEl = document.getElementById('view-count');
  if (countEl) countEl.textContent = filtered.length;

  const start = (currentPage - 1) * ROWS_PER_PAGE;
  const slice = filtered.slice(start, start + ROWS_PER_PAGE);

  if (!slice.length) {
    tbody.innerHTML = '<tr><td colspan="4" class="table-empty">Nessuna disponibilità trovata.</td></tr>';
  } else {
    tbody.innerHTML = slice.map(item => `
      <tr>
        <td>${item.id}</td>
        <td>${escHtml(item.periodo_previsto ?? '')}</td>
        <td>${escHtml(item.descrizione ?? '')}</td>
        <td>
          <div class="td-actions">
            <button class="btn btn-warning btn-sm" onclick="apriModal(${item.id})">Modifica</button>
            <button class="btn btn-danger btn-sm"  onclick="elimina(${item.id})">Elimina</button>
          </div>
        </td>
      </tr>`).join('');
  }

  // Pagination bar
  const bar = document.getElementById('pagination-bar');
  if (filtered.length > ROWS_PER_PAGE) {
    bar.style.display = 'flex';
    document.getElementById('pagination-info').textContent =
      `Pagina ${currentPage} di ${totalPages}`;
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    btnPrev.innerHTML = ICONS.chevronLeft;
    btnNext.innerHTML = ICONS.chevronRight;
    btnPrev.disabled = currentPage <= 1;
    btnNext.disabled = currentPage >= totalPages;
  } else {
    bar.style.display = 'none';
  }
}

function goPage(delta) {
  const totalPages = Math.ceil(filtered.length / ROWS_PER_PAGE) || 1;
  currentPage = Math.max(1, Math.min(totalPages, currentPage + delta));
  renderPage();
}

// ── Filtro ricerca ────────────────────────────────────────
function filterTable() {
  const q = document.getElementById('search-input').value.toLowerCase();
  filtered = q
    ? allRows.filter(item =>
        (item.periodo_previsto ?? '').toLowerCase().includes(q) ||
        (item.descrizione      ?? '').toLowerCase().includes(q) ||
        String(item.id).includes(q)
      )
    : allRows.slice();
  currentPage = 1;
  renderPage();
}

// ── Carica dati ───────────────────────────────────────────
async function caricaDati() {
  const spinner = document.getElementById('table-spinner');
  spinner.style.display = 'flex';
  setDbStatus('loading');
  document.getElementById('disponibilita-tbody').innerHTML = '';

  try {
    const res  = await fetch(API);
    const json = await res.json();

    setDbStatus(json.success ? 'ok' : 'err');

    if (!json.success) {
      document.getElementById('disponibilita-tbody').innerHTML =
        `<tr><td colspan="4" class="table-empty" style="color:var(--danger);">${json.message || 'Errore nel caricamento.'}</td></tr>`;
      return;
    }

    allRows  = json.data ?? [];
    filtered = allRows.slice();
    currentPage = 1;
    renderPage();

  } catch (err) {
    console.error('[Disponibilità]', err);
    document.getElementById('disponibilita-tbody').innerHTML =
      '<tr><td colspan="4" class="table-empty" style="color:var(--danger);">Errore di rete. Riprova.</td></tr>';
    setDbStatus('err');
  } finally {
    spinner.style.display = 'none';
  }
}

// ── Submit form ───────────────────────────────────────────
document.getElementById('form-disponibilita').addEventListener('submit', async (e) => {
  e.preventDefault();

  const periodo_previsto = document.getElementById('inp-periodo').value.trim();
  const descrizione      = document.getElementById('inp-descrizione').value.trim();

  if (!periodo_previsto) {
    toast('Il campo Periodo è obbligatorio.', 'error');
    return;
  }

  const isEdit = editId !== null;
  const url    = isEdit ? `${API}?id=${editId}` : API;
  const method = isEdit ? 'PUT' : 'POST';

  try {
    const res  = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ periodo_previsto, descrizione }),
    });
    const json = await res.json();

    if (json.success) {
      toast(isEdit ? 'Disponibilità aggiornata.' : 'Disponibilità aggiunta.');
      chiudiModal();
      caricaDati();
    } else {
      toast(json.message || 'Errore.', 'error');
    }
  } catch (err) {
    console.error('[Disponibilità]', err);
    toast('Errore di rete.', 'error');
  }
});

// ── Elimina ───────────────────────────────────────────────
async function elimina(id) {
  if (!confirm('Eliminare questa disponibilità?')) return;

  try {
    const res  = await fetch(`${API}?id=${id}`, { method: 'DELETE' });
    const json = await res.json();

    if (json.success) {
      toast('Disponibilità eliminata.');
      caricaDati();
    } else {
      toast(json.message || 'Errore nell\'eliminazione.', 'error');
    }
  } catch (err) {
    console.error('[Disponibilità]', err);
    toast('Errore di rete.', 'error');
  }
}

// ── Event listeners ───────────────────────────────────────
document.getElementById('btn-nuova').addEventListener('click', () => apriModal());
document.getElementById('btn-chiudi-modal').addEventListener('click', chiudiModal);
document.getElementById('btn-annulla').addEventListener('click', chiudiModal);
document.getElementById('modal-overlay').addEventListener('click', e => {
  if (e.target === document.getElementById('modal-overlay')) chiudiModal();
});

// Toggle sidebar mobile
const btnToggle = document.getElementById('btn-toggle-sidebar');
const sidebar   = document.getElementById('sidebar');

function checkMobile() {
  btnToggle.style.display = window.innerWidth <= 900 ? 'inline-flex' : 'none';
  if (window.innerWidth > 900) sidebar.classList.remove('is-open');
}

btnToggle.addEventListener('click', () => sidebar.classList.toggle('is-open'));
window.addEventListener('resize', checkMobile);
checkMobile();

caricaDati();
</script>

</body>
</html>
