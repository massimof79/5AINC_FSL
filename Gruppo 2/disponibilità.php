<?php
/**
 * index.php — Gruppo 2 · Gestione Disponibilità
 *
 * Compatibile con:
 *   - global.css       → variabili CSS, layout sidebar/topbar/main, classi .card,
 *                         .btn, .btn-primary, .btn-danger, .btn-sm, .table-wrapper,
 *                         .form-group, .form-row, .page-header, .avatar, .topbar, …
 *   - auth.php         → requireLoginPage() + $_SESSION['username'] / ['user_id']
 *   - config.php       → getDbConnection()  (non usato direttamente qui)
 *   - api.php          → chiamata AJAX verso middlewere.php  (che usa DisponibilitaApi)
 *
 * Nota: la sessione viene avviata da auth.php (session_status check incluso).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Redirect a login.php se non autenticato.
requireLoginPage('../login.php');

$username = htmlspecialchars(
    (string) ($_SESSION['username'] ?? 'Utente'),
    ENT_QUOTES,
    'UTF-8'
);
$userInitial = strtoupper(substr($username, 0, 1)) ?: 'U';
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Disponibilità — FSL Panel</title>
  <link rel="stylesheet" href="../global.css">
</head>
<body>

<div class="app-shell">

  <!-- ── Sidebar ─────────────────────────────────────────── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
      </svg>
      PCTO<span class="logo-accent">Connect</span>
    </div>

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

  <!-- ── Topbar ────────────────────────────────────────────── -->
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

  <!-- ── Main ─────────────────────────────────────────────── -->
  <main class="main-content">

    <!-- Toast container (compatibile con global.css) -->
    <div id="toast-container"></div>

    <!-- Header pagina -->
    <div class="page-header" style="margin-bottom:1.5rem;">
      <div>
        <h1 style="font-size:var(--fs-xl); font-weight:700; color:var(--primary);">
          Disponibilità
          <span id="view-count"
                style="font-size:var(--fs-sm); font-weight:400; color:var(--text-muted);
                       background:var(--bg-light); border:1px solid var(--border);
                       padding:2px 10px; border-radius:999px; margin-left:6px;">—</span>
        </h1>
        <p style="font-size:var(--fs-sm); color:var(--text-muted); margin-top:.2rem;">
          Visualizza, aggiungi o rimuovi le tue disponibilità
        </p>
      </div>
    </div>

    <!-- Card: aggiungi disponibilità -->
    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-title">Aggiungi Nuova Disponibilità</div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="data">Data</label>
          <input class="form-control" type="date" id="data">
        </div>
        <div class="form-group">
          <label class="form-label" for="inizio">Inizio</label>
          <input class="form-control" type="time" id="inizio">
        </div>
        <div class="form-group">
          <label class="form-label" for="fine">Fine</label>
          <input class="form-control" type="time" id="fine">
        </div>
        <div class="form-group" style="display:flex; align-items:flex-end;">
          <button class="btn btn-primary" onclick="creaDisponibilita()">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"
                 viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5" y1="12" x2="19" y2="12"/></svg>
            Salva
          </button>
        </div>
      </div>
    </div>

    <!-- Card: tabella -->
    <div class="card" style="padding:0; overflow:hidden;">
      <div class="table-wrapper">
        <table id="tabellaDisponibilita">
          <thead>
            <tr>
              <th>#</th>
              <th>Data</th>
              <th>Inizio</th>
              <th>Fine</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="5"
                  style="text-align:center; color:var(--text-muted); padding:2rem;">
                Caricamento…
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main><!-- /.main-content -->
</div><!-- /.app-shell -->

<!-- ── Script ────────────────────────────────────────────── -->
<script>
/**
 * Tutti i fetch puntano a middlewere.php, che a sua volta
 * usa DisponibilitaApi (api.php) del flusso principale.
 *
 * La risposta JSON di DisponibilitaApi ha la forma:
 *   { success: bool, message: string, data: ... }
 */

const API = 'middlewere.php';

// ── Toast helper ──────────────────────────────────────────
function toast(msg, type = 'success') {
  const tc = document.getElementById('toast-container');
  if (!tc) return;
  const el = document.createElement('div');
  el.className = `toast toast-${type}`;
  el.textContent = msg;
  tc.appendChild(el);
  setTimeout(() => el.remove(), 3500);
}

// ── Carica e renderizza la lista ──────────────────────────
async function caricaDati() {
  const tbody = document.querySelector('#tabellaDisponibilita tbody');
  tbody.innerHTML = `<tr><td colspan="5"
    style="text-align:center;color:var(--text-muted);padding:2rem;">
    Caricamento…</td></tr>`;

  try {
    const res  = await fetch(API);
    const json = await res.json();

    if (!json.success) {
      tbody.innerHTML = `<tr><td colspan="5"
        style="text-align:center;color:var(--danger);padding:2rem;">
        ${json.message || 'Errore nel caricamento.'}</td></tr>`;
      return;
    }

    const dati = json.data ?? [];
    document.getElementById('view-count').textContent = dati.length;

    if (!dati.length) {
      tbody.innerHTML = `<tr><td colspan="5"
        style="text-align:center;color:var(--text-muted);padding:2rem;">
        Nessuna disponibilità registrata.</td></tr>`;
      return;
    }

    tbody.innerHTML = dati.map(item => `
      <tr>
        <td style="font-family:var(--font-mono); color:var(--text-muted);">${item.id}</td>
        <td>${item.data}</td>
        <td style="font-family:var(--font-mono);">${item.ora_inizio}</td>
        <td style="font-family:var(--font-mono);">${item.ora_fine}</td>
        <td class="td-actions">
          <button class="btn btn-danger btn-sm" onclick="elimina(${item.id})">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14H6L5 6"/>
              <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            Elimina
          </button>
        </td>
      </tr>`).join('');

  } catch (err) {
    console.error('[Disponibilità] Errore fetch:', err);
    tbody.innerHTML = `<tr><td colspan="5"
      style="text-align:center;color:var(--danger);padding:2rem;">
      Errore di rete. Riprova.</td></tr>`;
  }
}

// ── Crea nuova disponibilità ──────────────────────────────
async function creaDisponibilita() {
  const data       = document.getElementById('data').value.trim();
  const ora_inizio = document.getElementById('inizio').value.trim();
  const ora_fine   = document.getElementById('fine').value.trim();

  if (!data || !ora_inizio || !ora_fine) {
    toast('Compila tutti i campi.', 'error');
    return;
  }

  try {
    const res  = await fetch(API, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ data, ora_inizio, ora_fine }),
    });
    const json = await res.json();

    if (json.success) {
      toast('Disponibilità aggiunta.');
      document.getElementById('data').value   = '';
      document.getElementById('inizio').value = '';
      document.getElementById('fine').value   = '';
      caricaDati();
    } else {
      toast(json.message || 'Errore nella creazione.', 'error');
    }
  } catch (err) {
    console.error('[Disponibilità] Errore creazione:', err);
    toast('Errore di rete.', 'error');
  }
}

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
    console.error('[Disponibilità] Errore eliminazione:', err);
    toast('Errore di rete.', 'error');
  }
}

// Carica al boot
caricaDati();
</script>
</body>
</html>
