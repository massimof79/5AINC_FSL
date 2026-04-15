<?php
/**
 * register.php
 * Pagina di registrazione utenti.
 * Progetto: 5AINC_FSL
 *
 * - GET  → mostra il form di registrazione
 * - POST → valida i dati e crea il nuovo account
 */

declare(strict_types=1);

session_start();

// Se l'utente è già loggato, reindirizza alla pagina principale
if (!empty($_SESSION['user_id'])) {
    header('Location: esperienze.html');
    exit;
}

$error   = '';
$success = '';

// ── Gestione POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username  = trim((string) ($_POST['username'] ?? ''));
    $password  = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');

    if ($username === '' || $password === '' || $password2 === '') {
        $error = 'Tutti i campi sono obbligatori.';
    } elseif (strlen($username) < 3) {
        $error = "L'username deve contenere almeno 3 caratteri.";
    } elseif (strlen($password) < 6) {
        $error = 'La password deve contenere almeno 6 caratteri.';
    } elseif ($password !== $password2) {
        $error = 'Le password non coincidono.';
    } else {
        require_once __DIR__ . '/config.php';
        /** @var PDO $pdo */

        try {
            // Controlla username duplicato
            $check = $pdo->prepare('SELECT ID FROM utenti WHERE username = :username LIMIT 1');
            $check->execute([':username' => $username]);

            if ($check->fetch()) {
                $error = 'Username già in uso. Scegline un altro.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO utenti (username, password) VALUES (:username, :password)');
                $stmt->execute([':username' => $username, ':password' => $hash]);

                header('Location: login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            error_log('[register] PDOException: ' . $e->getMessage());
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
  <title>Registrazione — PCTOConnect</title>

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

    .login-logo .logo-accent {
      color: var(--accent);
    }

    .login-logo-icon {
      background: var(--primary);
      border-radius: var(--radius);
      padding: 0.4rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-logo-icon svg {
      color: #fff;
    }

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

    .alert-danger {
      background: #fee2e2;
      color: #991b1b;
      border-color: var(--danger);
    }

    .form-label-row {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      margin-bottom: 0.35rem;
    }

    .form-label-row svg {
      color: var(--text-muted);
    }

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
  </style>
</head>
<body>

<div class="login-wrapper">
  <div class="login-card">

    <!-- Intestazione -->
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
      <p class="login-subtitle">Crea il tuo account</p>
    </div>

    <!-- Alert errore -->
    <?php if ($error !== ''): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <!-- Form di registrazione -->
    <form method="POST" action="register.php" novalidate>

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
          placeholder="Scegli un username"
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
          autocomplete="new-password"
          required
        />
      </div>

      <div class="form-group">
        <label class="form-label-row" for="password2">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <span class="form-label" style="margin:0">Conferma password</span>
        </label>
        <input
          type="password"
          id="password2"
          name="password2"
          class="form-control"
          placeholder="••••••••"
          autocomplete="new-password"
          required
        />
      </div>

      <button type="submit" class="btn btn-primary btn-login">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <line x1="19" y1="8" x2="19" y2="14"/>
          <line x1="22" y1="11" x2="16" y2="11"/>
        </svg>
        Registrati
      </button>

    </form>

    <hr class="login-divider" />

    <div class="login-footer">
      Hai già un account? <a href="login.php">Accedi</a>
    </div>

  </div>
</div>

</body>
</html>
