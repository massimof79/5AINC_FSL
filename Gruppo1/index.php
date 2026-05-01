<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireLoginPage();

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
  <link rel="stylesheet" href="global.css" />
  <link rel="stylesheet" href="aziende.css" />
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <span style="color:var(--accent);">◈</span>
      FSL <span class="logo-accent">Panel</span>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-section-label">Operazioni</div>
      <a href="#" class="active" data-view="list" onclick="showView('list'); return false;">
        <span class="nav-icon">≡</span> Elenco Aziende
      </a>
      <a href="#" data-view="create" onclick="showView('create'); return false;">
        <span class="nav-icon">+</span> Nuova Azienda
      </a>
      <div class="sidebar-section-label" style="margin-top:1rem;">Ricerca</div>
      <div style="padding:0 1.25rem 0.5rem;">
        <input type="text" id="search-input" class="form-control"
               style="font-size:var(--fs-sm);"
               placeholder="Cerca per nome…" oninput="filterTable()" />
      </div>
      <div class="sidebar-section-label" style="margin-top:1rem;">Statistiche</div>
      <div style="padding:0 1.25rem;">
        <div style="display:flex;justify-content:space-between;font-size:var(--fs-sm);
                    color:rgba(255,255,255,0.6);padding:0.4rem 0;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span>Totale</span>
          <span style="color:var(--accent);font-weight:700;" id="stat-total">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:var(--fs-sm);
                    color:rgba(255,255,255,0.6);padding:0.4rem 0;">
          <span>Sedi uniche</span>
          <span style="color:var(--accent);font-weight:700;" id="stat-cities">—</span>
        </div>
      </div>
    </nav>
    <div style="padding:1.25rem; margin-top:auto;">
      <a href="logout.php" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;color:rgba(255,255,255,0.7);">
        ⏻ Esci
      </a>
    </div>
  </aside>

  <!-- TOPBAR -->
  <header class="topbar">
    <span class="topbar-title" id="topbar-title">Elenco Aziende</span>
    <div class="topbar-right">
      <span id="db-status" style="font-size:var(--fs-sm);color:var(--text-muted);">Connessione…</span>
      <div class="user-badge">
        <div class="avatar" id="user-avatar">?</div>
        <span id="header-user"><?= $username ?></span>
      </div>
    </div>
  </header>

  <!-- MAIN -->
  <main class="main-content">

    <!-- TOAST -->
    <div id="toast-container"></div>

    <!-- VIEW: LIST -->
    <div class="view" id="view-list">
      <div class="card-header page-header" style="margin-bottom:1.5rem;">
        <div>
          <h1 style="font-size:var(--fs-xl);font-weight:700;color:var(--primary);">
            Aziende <span id="view-count" style="font-size:var(--fs-sm);font-weight:400;color:var(--text-muted);
                          background:var(--bg-light);border:1px solid var(--border);
                          padding:2px 10px;border-radius:999px;margin-left:6px;">—</span>
          </h1>
          <p class="text-muted" style="font-size:var(--fs-sm);margin-top:0.2rem;">Gestione anagrafica aziende</p>
        </div>
        <button class="btn btn-primary" onclick="showView('create')">+ Aggiungi Azienda</button>
      </div>
      <div class="card" style="padding:0;">
        <div class="table-wrapper" style="border:none;border-radius:var(--radius-lg);">
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
              <tr>
                <td colspan="6" class="table-empty">
                  <span class="spinner"></span> Caricamento…
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- VIEW: CREATE -->
    <div class="view hidden" id="view-create">
      <div class="page-header">
        <h1>Nuova Azienda</h1>
        <p class="text-muted" style="font-size:var(--fs-sm);">Compila i campi per aggiungere un'azienda</p>
      </div>
      <div class="card" style="max-width:720px;">
        <form id="create-form" onsubmit="handleCreate(event)" novalidate>
          <div class="form-row">
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label" for="c-ragione">Ragione Sociale <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="c-ragione" name="ragione_sociale" maxlength="100" required />
              <span class="form-error" id="err-c-ragione"></span>
            </div>
            <div class="form-group">
              <label class="form-label" for="c-piva">Partita IVA <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="c-piva" name="partita_iva" maxlength="11" required />
              <span class="form-error" id="err-c-piva"></span>
            </div>
            <div class="form-group">
              <label class="form-label" for="c-sede-legale">Sede Legale <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="c-sede-legale" name="sede_legale" maxlength="100" required />
              <span class="form-error" id="err-c-sede-legale"></span>
            </div>
            <div class="form-group">
              <label class="form-label" for="c-sede-op">Sede Operativa <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="c-sede-op" name="sede_operativa" maxlength="100" required />
              <span class="form-error" id="err-c-sede-op"></span>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salva Azienda</button>
            <button type="button" class="btn btn-secondary" onclick="showView('list')">Annulla</button>
          </div>
        </form>
      </div>
    </div>

    <!-- VIEW: EDIT -->
    <div class="view hidden" id="view-edit">
      <div class="page-header">
        <h1>Modifica Azienda <span id="edit-id-badge" style="font-size:var(--fs-sm);font-weight:400;
                                   color:var(--text-muted);background:var(--bg-light);border:1px solid var(--border);
                                   padding:2px 10px;border-radius:999px;margin-left:6px;">#</span></h1>
      </div>
      <div class="card" style="max-width:720px;">
        <form id="edit-form" onsubmit="handleUpdate(event)" novalidate>
          <input type="hidden" id="e-id" name="codice_azienda" />
          <div class="form-row">
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label" for="e-ragione">Ragione Sociale <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="e-ragione" name="ragione_sociale" maxlength="100" required />
              <span class="form-error" id="err-e-ragione"></span>
            </div>
            <div class="form-group">
              <label class="form-label" for="e-piva">Partita IVA <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="e-piva" name="partita_iva" maxlength="11" required />
              <span class="form-error" id="err-e-piva"></span>
            </div>
            <div class="form-group">
              <label class="form-label" for="e-sede-legale">Sede Legale <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="e-sede-legale" name="sede_legale" maxlength="100" required />
              <span class="form-error" id="err-e-sede-legale"></span>
            </div>
            <div class="form-group">
              <label class="form-label" for="e-sede-op">Sede Operativa <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="e-sede-op" name="sede_operativa" maxlength="100" required />
              <span class="form-error" id="err-e-sede-op"></span>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Aggiorna</button>
            <button type="button" class="btn btn-danger"
                    onclick="confirmDelete(document.getElementById('e-id').value)">Elimina</button>
            <button type="button" class="btn btn-secondary" onclick="showView('list')">Annulla</button>
          </div>
        </form>
      </div>
    </div>

    <!-- VIEW: DETAIL -->
    <div class="view hidden" id="view-detail">
      <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
        <div>
          <h1>Dettaglio Azienda <span id="detail-id-badge" style="font-size:var(--fs-sm);font-weight:400;
                                      color:var(--text-muted);background:var(--bg-light);border:1px solid var(--border);
                                      padding:2px 10px;border-radius:999px;margin-left:6px;">#</span></h1>
        </div>
        <div style="display:flex;gap:0.75rem;">
          <button class="btn btn-primary" id="detail-edit-btn">✎ Modifica</button>
          <button class="btn btn-secondary" onclick="showView('list')">← Elenco</button>
        </div>
      </div>
      <div class="card" style="max-width:680px;">
        <div class="form-row">
          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">Ragione Sociale</label>
            <p id="d-ragione" style="font-size:1rem;color:var(--text);font-weight:500;">—</p>
          </div>
          <div class="form-group">
            <label class="form-label">Partita IVA</label>
            <p id="d-piva" style="font-size:1rem;color:var(--text);font-family:'Courier New',monospace;">—</p>
          </div>
          <div class="form-group">
            <label class="form-label">Sede Legale</label>
            <p id="d-sede-legale" style="font-size:1rem;color:var(--text);">—</p>
          </div>
          <div class="form-group">
            <label class="form-label">Sede Operativa</label>
            <p id="d-sede-op" style="font-size:1rem;color:var(--text);">—</p>
          </div>
        </div>
      </div>
    </div>

  </main>

  <!-- MODAL ELIMINAZIONE -->
  <div class="modal-overlay" id="delete-modal">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title">⚠ Conferma Eliminazione</h2>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <p>Stai per eliminare l'azienda <strong id="modal-azienda-name"></strong>.<br/>
           L'operazione è irreversibile.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" id="confirm-delete-btn">Elimina</button>
        <button class="btn btn-secondary" onclick="closeModal()">Annulla</button>
      </div>
    </div>
  </div>

  <script src="aziende.js"></script>
</body>
</html>
