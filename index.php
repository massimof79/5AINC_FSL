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
  <title>Dashboard — FSL Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="global.css" />
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
      <div class="sidebar-section-label">Moduli</div>
      <a href="Gruppo1/index_aziende.php">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
          </svg>
        </span>Aziende
      </a>
      <a href="Gruppo%202/disponibilit%C3%A0.php">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
        </span>Disponibilità
      </a>
      <a href="Gruppo%203/esperienze.php">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 2.25H8.25A2.25 2.25 0 006 4.5v15A2.25 2.25 0 008.25 21.75h7.5a2.25 2.25 0 002.25-2.25V4.5a2.25 2.25 0 00-2.25-2.25z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 2.25V4.5H9V2.25m3 9h3m-3 3h3m-6-3h.008v.008H9v-.008zm0 3h.008v.008H9v-.008z" />
          </svg>
        </span>Esperienze
      </a>
      <div class="sidebar-section-label">Personale</div>
      <a href="Gruppo4/tutor_scolastici.html">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
        </span>Tutor Scolastici
      </a>
      <a href="Gruppo4/tutor_aziendali.html">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
          </svg>
        </span>Tutor Aziendali
      </a>
      <a href="Gruppo4/studenti.html">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
        </span>Studenti
      </a>
      <div class="sidebar-section-label">Sistema</div>
      <a href="logout.php">
        <span class="nav-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
          </svg>
        </span>Logout
      </a>
    </nav>
  </aside>

  <header class="topbar">
    <button class="btn btn-secondary btn-icon" id="btn-toggle-sidebar"
            title="Apri/chiudi menu" style="display:none">☰</button>
    <span class="topbar-title">Dashboard</span>
    <div class="topbar-right">
      <span id="db-status" style="font-size:var(--fs-sm);color:var(--text-muted);">Connessione…</span>
      <div class="user-badge">
        <div class="avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
        <span><?= $username ?></span>
      </div>
    </div>
  </header>

  <main class="main-content">

    <!-- ── Hero ─────────────────────────────────────────────── -->
    <div style="
      background: linear-gradient(135deg, var(--primary) 0%, #1a252f 100%);
      border-radius: var(--radius-lg, 12px);
      padding: 2.5rem 2rem;
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      position: relative;
      overflow: hidden;
    ">
      <div style="
        position:absolute; right:-40px; top:-40px;
        width:220px; height:220px; border-radius:50%;
        background:rgba(255,255,255,0.04);
      "></div>
      <div style="
        position:absolute; right:60px; bottom:-60px;
        width:160px; height:160px; border-radius:50%;
        background:rgba(255,255,255,0.03);
      "></div>
      <div style="
        width:56px; height:56px; border-radius:14px;
        background:rgba(255,255,255,0.12);
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
      ">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
          <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
      </div>
      <div style="position:relative;">
        <div style="font-size:.75rem; font-weight:600; letter-spacing:.08em;
                    color:rgba(255,255,255,0.5); text-transform:uppercase; margin-bottom:.3rem;">
          PCTOConnect · Dashboard
        </div>
        <h1 style="font-size:1.75rem; font-weight:800; color:#fff; margin:0 0 .35rem;">
          Benvenuto, <?= $username ?>
        </h1>
        <p style="color:rgba(255,255,255,0.6); margin:0; font-size:.9rem;">
          Gestione integrata dei percorsi per le competenze trasversali e l'orientamento.
        </p>
      </div>
    </div>

    <!-- ── Sezione moduli ─────────────────────────────────────── -->
    <div style="margin-bottom:1rem;">
      <h2 style="font-size:var(--fs-sm); font-weight:600; letter-spacing:.06em;
                 text-transform:uppercase; color:var(--text-muted); margin:0 0 1rem;">
        Moduli
      </h2>
      <div style="
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1rem;
      ">

        <!-- Aziende -->
        <a href="Gruppo1/index_aziende.php" style="text-decoration:none;" class="dash-module-card">
          <div class="card" style="
            padding:1.5rem; cursor:pointer; transition:transform .15s,box-shadow .15s;
            border-top:3px solid var(--accent);
          ">
            <div style="
              width:44px; height:44px; border-radius:10px;
              background:rgba(39,174,96,0.12);
              display:flex; align-items:center; justify-content:center; margin-bottom:1rem;
            ">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke-width="1.5" stroke="var(--accent)" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
              </svg>
            </div>
            <div style="font-size:.7rem; font-weight:600; text-transform:uppercase;
                        letter-spacing:.07em; color:var(--text-muted); margin-bottom:.25rem;">
              Gruppo 1
            </div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--primary); margin-bottom:.4rem;">
              Aziende
            </div>
            <div style="font-size:.82rem; color:var(--text-muted); line-height:1.45;">
              Anagrafica aziende ospitanti: ragione sociale, sedi, partita IVA.
            </div>
          </div>
        </a>

        <!-- Disponibilità -->
        <a href="Gruppo%202/disponibilit%C3%A0.php" style="text-decoration:none;" class="dash-module-card">
          <div class="card" style="
            padding:1.5rem; cursor:pointer; transition:transform .15s,box-shadow .15s;
            border-top:3px solid var(--info);
          ">
            <div style="
              width:44px; height:44px; border-radius:10px;
              background:rgba(41,128,185,0.12);
              display:flex; align-items:center; justify-content:center; margin-bottom:1rem;
            ">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke-width="1.5" stroke="var(--info)" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
            </div>
            <div style="font-size:.7rem; font-weight:600; text-transform:uppercase;
                        letter-spacing:.07em; color:var(--text-muted); margin-bottom:.25rem;">
              Gruppo 2
            </div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--primary); margin-bottom:.4rem;">
              Disponibilità
            </div>
            <div style="font-size:.82rem; color:var(--text-muted); line-height:1.45;">
              Fasce orarie e periodi disponibili per i tirocini aziendali.
            </div>
          </div>
        </a>

        <!-- Esperienze -->
        <a href="Gruppo%203/esperienze.php" style="text-decoration:none;" class="dash-module-card">
          <div class="card" style="
            padding:1.5rem; cursor:pointer; transition:transform .15s,box-shadow .15s;
            border-top:3px solid #8e44ad;
          ">
            <div style="
              width:44px; height:44px; border-radius:10px;
              background:rgba(142,68,173,0.12);
              display:flex; align-items:center; justify-content:center; margin-bottom:1rem;
            ">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke-width="1.5" stroke="#8e44ad" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 2.25H8.25A2.25 2.25 0 006 4.5v15A2.25 2.25 0 008.25 21.75h7.5a2.25 2.25 0 002.25-2.25V4.5a2.25 2.25 0 00-2.25-2.25z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 2.25V4.5H9V2.25m3 9h3m-3 3h3m-6-3h.008v.008H9v-.008zm0 3h.008v.008H9v-.008z" />
              </svg>
            </div>
            <div style="font-size:.7rem; font-weight:600; text-transform:uppercase;
                        letter-spacing:.07em; color:var(--text-muted); margin-bottom:.25rem;">
              Gruppo 3
            </div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--primary); margin-bottom:.4rem;">
              Esperienze
            </div>
            <div style="font-size:.82rem; color:var(--text-muted); line-height:1.45;">
              Registro delle esperienze PCTO: ore, periodo, tutor assegnati.
            </div>
          </div>
        </a>

        <!-- Tutor Scolastici -->
        <a href="Gruppo4/tutor_scolastici.html" style="text-decoration:none;" class="dash-module-card">
          <div class="card" style="
            padding:1.5rem; cursor:pointer; transition:transform .15s,box-shadow .15s;
            border-top:3px solid var(--warning);
          ">
            <div style="
              width:44px; height:44px; border-radius:10px;
              background:rgba(243,156,18,0.12);
              display:flex; align-items:center; justify-content:center; margin-bottom:1rem;
            ">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke-width="1.5" stroke="var(--warning)" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
              </svg>
            </div>
            <div style="font-size:.7rem; font-weight:600; text-transform:uppercase;
                        letter-spacing:.07em; color:var(--text-muted); margin-bottom:.25rem;">
              Gruppo 4
            </div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--primary); margin-bottom:.4rem;">
              Tutor Scolastici
            </div>
            <div style="font-size:.82rem; color:var(--text-muted); line-height:1.45;">
              Docenti referenti per dipartimento o area disciplinare.
            </div>
          </div>
        </a>

        <!-- Tutor Aziendali -->
        <a href="Gruppo4/tutor_aziendali.html" style="text-decoration:none;" class="dash-module-card">
          <div class="card" style="
            padding:1.5rem; cursor:pointer; transition:transform .15s,box-shadow .15s;
            border-top:3px solid var(--danger);
          ">
            <div style="
              width:44px; height:44px; border-radius:10px;
              background:rgba(192,57,43,0.12);
              display:flex; align-items:center; justify-content:center; margin-bottom:1rem;
            ">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke-width="1.5" stroke="var(--danger)" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
              </svg>
            </div>
            <div style="font-size:.7rem; font-weight:600; text-transform:uppercase;
                        letter-spacing:.07em; color:var(--text-muted); margin-bottom:.25rem;">
              Gruppo 4
            </div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--primary); margin-bottom:.4rem;">
              Tutor Aziendali
            </div>
            <div style="font-size:.82rem; color:var(--text-muted); line-height:1.45;">
              Referenti aziendali: ruolo, contatto e-mail, disponibilità.
            </div>
          </div>
        </a>

        <!-- Studenti -->
        <a href="Gruppo4/studenti.html" style="text-decoration:none;" class="dash-module-card">
          <div class="card" style="
            padding:1.5rem; cursor:pointer; transition:transform .15s,box-shadow .15s;
            border-top:3px solid #16a085;
          ">
            <div style="
              width:44px; height:44px; border-radius:10px;
              background:rgba(22,160,133,0.12);
              display:flex; align-items:center; justify-content:center; margin-bottom:1rem;
            ">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke-width="1.5" stroke="#16a085" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
            </div>
            <div style="font-size:.7rem; font-weight:600; text-transform:uppercase;
                        letter-spacing:.07em; color:var(--text-muted); margin-bottom:.25rem;">
              Gruppo 4
            </div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--primary); margin-bottom:.4rem;">
              Studenti
            </div>
            <div style="font-size:.82rem; color:var(--text-muted); line-height:1.45;">
              Anagrafica studenti: classe, candidatura, esperienza assegnata.
            </div>
          </div>
        </a>

      </div>
    </div>

  </main>

</div>

<style>
.dash-module-card .card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.10);
}
</style>
<script src="icons/icons.js"></script>
<script>
const sidebar  = document.getElementById('sidebar');
const btnToggle = document.getElementById('btn-toggle-sidebar');
btnToggle.addEventListener('click', () => sidebar.classList.toggle('is-open'));
function checkMobile() {
  btnToggle.style.display = window.innerWidth <= 900 ? '' : 'none';
  if (window.innerWidth > 900) sidebar.classList.remove('is-open');
}
window.addEventListener('resize', checkMobile);
checkMobile();

const dbEl = document.getElementById('db-status');
if (dbEl) dbEl.innerHTML = `${ICONS.dbLoading} Connessione…`;

fetch('api.php?entity=aziende', { credentials: 'same-origin' }).then(r => r.json()).then(j => {
  const el = document.getElementById('db-status');
  if (!el) return;
  el.innerHTML = j.success ? `${ICONS.dbOk} DB connesso` : `${ICONS.dbErr} Errore DB`;
}).catch(() => {
  const el = document.getElementById('db-status');
  if (el) el.innerHTML = `${ICONS.dbErr} Errore DB`;
});
</script>
</body>
</html>
