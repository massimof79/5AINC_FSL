<?php
/**
 * esperienze.php
 * Pagina principale gestione esperienze PCTO.
 * Progetto: 5AINC_FSL — Gruppo 3
 */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireLoginPage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Esperienze PCTO — 5AINC_FSL</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="global.css" />
</head>
<body>

<div class="app-shell">

  <!-- ── SIDEBAR ─────────────────────────────────────────── -->
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
      <a href="esperienze.php" class="active">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 2.25H8.25A2.25 2.25 0 006 4.5v15A2.25 2.25 0 008.25 21.75h7.5a2.25 2.25 0 002.25-2.25V4.5a2.25 2.25 0 00-2.25-2.25z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 2.25V4.5H9V2.25m3 9h3m-3 3h3m-6-3h.008v.008H9v-.008zm0 3h.008v.008H9v-.008z" />
          </svg>
        </span>Esperienze
      </a>
      <a href="index_aziende.php">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
          </svg>
        </span>Aziende
      </a>
      <a href="#">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
        </span>Tutor Scolastici
      </a>
      <a href="#">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
          </svg>
        </span>Tutor Aziendali
      </a>
      <a href="#">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
        </span>Disponibilità
      </a>

      <div class="sidebar-section-label">Studenti</div>
      <a href="#">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
        </span>Studenti
      </a>
      <a href="#">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
          </svg>
        </span>Candidature
      </a>

      <div class="sidebar-section-label">Sistema</div>
      <a href="#">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.127c-.332.183-.582.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
        </span>Impostazioni
      </a>
    </nav>
  </aside>

  <!-- ── TOPBAR ───────────────────────────────────────────── -->
  <header class="topbar">
    <button class="btn btn-secondary btn-icon" id="btn-toggle-sidebar"
            title="Apri/chiudi menu" style="display:none">☰</button>

    <span class="topbar-title">Gestione Esperienze PCTO</span>

    <div class="topbar-right">
      <div class="user-badge">
        <div class="avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['username'] ?? 'Utente', ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
    </div>
  </header>

  <!-- ── MAIN CONTENT ─────────────────────────────────────── -->
  <main class="main-content">

    <div class="page-header">
      <h1>Esperienze</h1>
      <p>Visualizza, crea e gestisci le esperienze di alternanza scuola-lavoro.</p>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Elenco Esperienze</h2>
        <button class="btn btn-primary" id="btn-nuova">＋ Nuova Esperienza</button>
      </div>

      <div id="table-spinner" class="flex-center mt-2 mb-2" style="display:none">
        <div class="spinner"></div>
        <span class="text-muted" style="margin-left:.75rem">Caricamento…</span>
      </div>

      <div class="table-wrapper">
        <table id="tabella-esperienze">
          <thead>
            <tr>
              <th>#</th>
              <th>Periodo Effettivo</th>
              <th>Ore Previste</th>
              <th>Ore Svolte</th>
              <th>Studenti</th>
              <th>Tutor Scolastico</th>
              <th>Tutor Aziendale</th>
              <th>Data disponibilità</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody id="esperienze-tbody">
            <tr><td colspan="9" class="table-empty">Caricamento in corso…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div><!-- /app-shell -->


<!-- ╔══════════════════════════════════════════════════════╗ -->
<!-- ║  MODAL — Crea / Modifica Esperienza                  ║ -->
<!-- ╚══════════════════════════════════════════════════════╝ -->
<div class="modal-overlay" id="modal-overlay" role="dialog"
     aria-modal="true" aria-labelledby="modal-title">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-title">Nuova Esperienza</h3>
      <button class="modal-close" id="btn-chiudi-modal" aria-label="Chiudi">&times;</button>
    </div>

    <form id="form-esperienza" novalidate>
    <div class="modal-body">

        <!-- Periodo effettivo -->
        <div class="form-group">
          <label class="form-label" for="inp-periodo">
            Periodo effettivo <span class="required">*</span>
          </label>
          <input type="text" id="inp-periodo" name="periodo_effettivo"
                 class="form-control"
                 placeholder="es. Mar 2026 – Apr 2026"
                 required />
          <div class="form-error" id="err-periodo"></div>
        </div>

        <!-- Ore previste / svolte affiancate -->
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="inp-ore-previste">
              Ore previste <span class="required">*</span>
            </label>
            <input type="number" id="inp-ore-previste" name="numero_ore_previste"
                   class="form-control" min="0" step="1"
                   placeholder="es. 120"
                   required />
            <div class="form-error" id="err-ore-previste"></div>
          </div>

          <div class="form-group">
            <label class="form-label" for="inp-ore-svolte">
              Ore svolte <span class="required">*</span>
            </label>
            <input type="number" id="inp-ore-svolte" name="numero_ore_svolte"
                   class="form-control" min="0" step="1"
                   placeholder="es. 115"
                   required />
            <div class="form-error" id="err-ore-svolte"></div>
          </div>
        </div>

        <!-- Numero studenti -->
        <div class="form-group">
          <label class="form-label" for="inp-studenti">
            Numero studenti <span class="required">*</span>
          </label>
          <input type="number" id="inp-studenti" name="numero_studenti"
                 class="form-control" min="1" step="1"
                 placeholder="es. 3"
                 required />
          <div class="form-error" id="err-studenti"></div>
        </div>

        <!-- Tutor Scolastico -->
        <div class="form-group">
          <label class="form-label" for="sel-docente">
            Tutor Scolastico <span class="required">*</span>
          </label>
          <select id="sel-docente" name="codice_docente" class="form-control" required>
            <option value="">— Seleziona un tutor scolastico —</option>
          </select>
          <div class="form-error" id="err-docente"></div>
        </div>

        <!-- Tutor Aziendale -->
        <div class="form-group">
          <label class="form-label" for="sel-tutor">
            Tutor Aziendale <span class="required">*</span>
          </label>
          <select id="sel-tutor" name="codice_tutor" class="form-control" required>
            <option value="">— Seleziona un tutor aziendale —</option>
          </select>
          <div class="form-error" id="err-tutor"></div>
        </div>

        <!-- Disponibilità -->
        <div class="form-group">
          <label class="form-label" for="sel-disponibilita">
            Disponibilità <span class="required">*</span>
          </label>
          <select id="sel-disponibilita" name="codice_disponibilita" class="form-control" required>
            <option value="">— Seleziona una disponibilità —</option>
          </select>
          <div class="form-error" id="err-disponibilita"></div>
        </div>

    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="btn-annulla">Annulla</button>
      <button type="submit" id="btn-submit" class="btn btn-primary">
        Crea esperienza
      </button>
    </div>
    </form>
  </div>
</div>


<!-- ── TOAST CONTAINER ──────────────────────────────────── -->
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>


<!-- ── Script ───────────────────────────────────────────── -->
<script src="esperienze.js"></script>

<script>
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
</script>

</body>
</html>