<?php
/**
 * login.php
 * Pagina di autenticazione utenti.
 * Progetto: 5AINC_FSL
 *
 * Sessione gestita tramite auth.php (Gruppo 4).
 */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';   // isLoggedIn, loginUser, verifyUserPassword
require_once __DIR__ . '/config.php'; // $pdo

// Se già loggato, vai alla dashboard
if (isLoggedIn()) {
    header('Location: esperienze.php');
    exit;
}

$error   = '';
$success = '';

// Messaggio di conferma dopo il logout o registrazione
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $success = 'Disconnessione avvenuta con successo.';
}
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $success = 'Registrazione completata. Ora puoi accedere.';
}

// ── Gestione POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Inserisci username e password.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT ID, username, password FROM utenti WHERE username = :username LIMIT 1'
            );
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && verifyUserPassword($password, (string) $user['password'])) {
                loginUser($user);           // session_regenerate_id + $_SESSION
                header('Location: esperienze.php');
                exit;
            } else {
                $error = 'Credenziali non valide. Riprova.';
            }
        } catch (PDOException $e) {
            error_log('[login] PDOException: ' . $e->getMessage());
            $error = 'Errore del server. Riprova più tardi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — PCTOConnect</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="../global.css" />

  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: var(--bg-light);
    }

    .login-wrapper {
      width: 100%;
      max-width: 420px;
      padding: 1rem;
    }

    .login-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      padding: 2.5rem 2rem;
    }

    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .login-logo {
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--primary);
      letter-spacing: -0.5px;
      margin-bottom: 0.5rem;
    }

    .login-logo .logo-accent { color: var(--accent); }

    .login-logo-icon {
      background: var(--primary);
      border-radius: var(--radius);
      padding: 0.4rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-logo-icon svg { color: #fff; }

    .login-subtitle {
      font-size: var(--fs-sm);
      color: var(--text-muted);
      margin-top: 0.25rem;
    }

    .alert {
      padding: 0.75rem 1rem;
      border-radius: var(--radius);
      font-size: var(--fs-sm);
      font-weight: 500;
      margin-bottom: 1.25rem;
      border-left: 4px solid;
    }

    .alert-danger  { background: #fee2e2; color: #991b1b; border-color: var(--danger); }
    .alert-success { background: #d1fae5; color: #065f46; border-color: var(--accent); }

    .form-label-row {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      margin-bottom: 0.35rem;
    }

    .form-label-row svg { color: var(--text-muted); }

    .btn-login {
      width: 100%;
      justify-content: center;
      padding: 0.65rem 1rem;
      font-size: var(--fs-base);
      margin-top: 0.5rem;
    }

    .login-footer {
      text-align: center;
      margin-top: 1.5rem;
      font-size: var(--fs-sm);
      color: var(--text-muted);
    }

    .login-divider {
      border: none;
      border-top: 1px solid var(--border);
      margin: 1.5rem 0;
    }

    .register-link {
      text-align: center;
      margin-top: 1rem;
      font-size: var(--fs-sm);
    }

    .register-link a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s ease;
    }

    .register-link a:hover { color: var(--accent); text-decoration: underline; }
  </style>
</head>
<body>

<div class="login-wrapper">
  <div class="login-card">

    <div class="login-header">
      <div class="login-logo">
        <div class="login-logo-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
          </svg>
        </div>
        PCTO<span class="logo-accent">Connect</span>
      </div>
      <p class="login-subtitle">Gestione esperienze alternanza scuola-lavoro</p>
    </div>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
      <div class="alert alert-success" role="alert">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>

      <div class="form-group">
        <label class="form-label-row" for="username">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span class="form-label" style="margin:0">Username</span>
        </label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-control"
          placeholder="Il tuo username"
          value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          autocomplete="username"
          required
          autofocus
        />
      </div>

      <div class="form-group">
        <label class="form-label-row" for="password">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <span class="form-label" style="margin:0">Password</span>
        </label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          placeholder="••••••••"
          autocomplete="current-password"
          required
        />
      </div>

      <button type="submit" class="btn btn-primary btn-login">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
          <polyline points="10 17 15 12 10 7"/>
          <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
        Accedi
      </button>

    </form>

    <hr class="login-divider" />

    <div class="login-footer">
      Classe 5AINC &mdash; Progetto FSL
    </div>
    <div class="register-link">
      Non hai un account? <a href="register.php">Registrati</a>
    </div>

  </div>
</div>

</body>
</html>