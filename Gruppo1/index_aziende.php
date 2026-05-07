<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth.php';
requireLoginPage('../login.php');

$username = htmlspecialchars((string) ($_SESSION['username'] ?? 'Utente'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestione Aziende — FSL Panel</title>
  <meta name="username" content="<?= $username ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../global.css" />
</head>
<body>
<div class="app-shell">

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
      <a href="index_aziende.php" class="active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
          </svg>
        </span>Aziende
      </a>
      <a href="../Gruppo%202/gestione_disponibilita.html">
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

  <header class="topbar">
    <button class="btn btn-secondary btn-icon" id="btn-toggle-sidebar" title="Apri/chiudi menu" style="display:none">☰</button>
    <span class="topbar-title">Gestione Aziende</span>
    <div class="topbar-right">
      <span id="db-status" style="font-size:var(--fs-sm);color:var(--text-muted);">Connessione…</span>
      <div class="user-badge">
        <div class="avatar" id="user-avatar">?</div>
        <span id="header-user"><?= $username ?></span>
      </div>
    </div>
  </header>

  <main class="main-content">
    <div id="toast-container"></div>

    <div class="page-header">
      <h1>Aziende <span id="view-count" style="font-size:var(--fs-sm);font-weight:400;color:var(--text-muted);background:var(--bg-light);border:1px solid var(--border);padding:2px 10px;border-radius:999px;margin-left:6px;">—</span></h1>
      <p class="text-muted" style="font-size:var(--fs-sm);">Gestione anagrafica aziende</p>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Elenco Aziende</h2>
        <div style="display:flex;gap:.75rem;align-items:center;">
          <input type="search" id="search-input" class="form-control"
                 style="width:220px;font-size:var(--fs-sm);"
                 placeholder="Cerca…" oninput="filterTable()" />
          <button class="btn btn-primary" id="btn-nuova">＋ Aggiungi Azienda</button>
        </div>
      </div>
      <div class="table-wrapper">
        <table id="aziende-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Ragione Sociale</th>
              <th>P.IVA</th>
              <th>Sede Legale</th>
              <th>Sede Operativa</th>
              <th style="text-align:center;">Azioni</th>
            </tr>
          </thead>
          <tbody id="table-body">
            <tr><td colspan="6" class="table-empty"><span class="spinner"></span> Caricamento…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- MODAL CRUD -->
<div class="modal-overlay" id="crud-modal" role="dialog" aria-modal="true" aria-labelledby="crud-modal-title">
  <div class="modal" style="max-width:620px;">
    <div class="modal-header">
      <h3 class="modal-title" id="crud-modal-title">Nuova Azienda</h3>
      <button class="modal-close" id="btn-chiudi-modal" aria-label="Chiudi">&times;</button>
    </div>
    <div class="modal-body">
      <form id="form-azienda" novalidate>
        <input type="hidden" id="inp-id" name="codice_azienda" />
        <div class="form-group">
          <label class="form-label" for="inp-ragione">Ragione Sociale <span class="required">*</span></label>
          <input class="form-control" type="text" id="inp-ragione" name="ragione_sociale" maxlength="100" required />
        </div>
        <div class="form-group">
          <label class="form-label" for="inp-piva">Partita IVA <span class="required">*</span></label>
          <input class="form-control" type="text" id="inp-piva" name="partita_iva" maxlength="11" pattern="\d{11}" placeholder="11 cifre" required />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="inp-sede-legale">Sede Legale <span class="required">*</span></label>
            <input class="form-control" type="text" id="inp-sede-legale" name="sede_legale" maxlength="100" required />
          </div>
          <div class="form-group">
            <label class="form-label" for="inp-sede-op">Sede Operativa <span class="required">*</span></label>
            <input class="form-control" type="text" id="inp-sede-op" name="sede_operativa" maxlength="100" required />
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="btn-annulla">Annulla</button>
      <button type="submit" form="form-azienda" class="btn btn-primary" id="btn-submit-azienda">Crea azienda</button>
    </div>
  </div>
</div>

<!-- MODAL ELIMINAZIONE -->
<div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title">Conferma Eliminazione</h2>
      <button class="modal-close" id="btn-chiudi-delete">&times;</button>
    </div>
    <div class="modal-body">
      <p>Stai per eliminare l'azienda <strong id="modal-azienda-name"></strong>.<br/>L'operazione è irreversibile.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" id="confirm-delete-btn">Elimina</button>
      <button class="btn btn-secondary" id="btn-annulla-delete">Annulla</button>
    </div>
  </div>
</div>

<script src="../icons/icons.js"></script>
<script src="aziende.js"></script>
<script>
  const btnToggle = document.getElementById('btn-toggle-sidebar');
  const sidebar   = document.getElementById('sidebar');
  function checkMobile() {
    btnToggle.style.display = window.innerWidth <= 900 ? 'inline-flex' : 'none';
    if (window.innerWidth > 900) sidebar.classList.remove('is-open');
  }
  btnToggle.addEventListener('click', () => sidebar.classList.toggle('is-open'));
  window.addEventListener('resize', checkMobile);
  checkMobile();
</script>
</body>
</html>
