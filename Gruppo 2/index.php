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

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Redirect a login.php se non autenticato.
requireLoginPage();

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
  <link rel="stylesheet" href="global.css">
</head>
<body>

<div class="app-shell">

  <!-- ── Sidebar ─────────────────────────────────────────── -->
  <aside class="sidebar">

    <div class="sidebar-logo">
      <span style="color:var(--accent); font-size:1.3rem;">◈</span>
      FSL Panel
    </div>

    <nav>
      <a href="esperienze.php">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/></svg>
        Dashboard
      </a>
      <a href="index.php" class="active">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/></svg>
        Disponibilità
      </a>
    </nav>

    <div style="padding:1.25rem; margin-top:auto;">
      <a href="logout.php" class="btn btn-secondary btn-sm"
         style="width:100%; justify-content:center; color:rgba(255,255,255,0.7);">
        ⏻ Esci
      </a>
    </div>
  </aside>

  <!-- ── Topbar ────────────────────────────────────────────── -->
  <header class="topbar">
    <span class="topbar-title">Gestione Disponibilità</span>
    <div class="topbar-right">
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
