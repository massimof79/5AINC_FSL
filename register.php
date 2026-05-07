<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim((string) ($_POST['username'] ?? ''));
    $password  = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');

    if ($username === '' || $password === '' || $password2 === '') {
        $error = 'Tutti i campi sono obbligatori.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = "Lo username deve avere tra 3 e 50 caratteri.";
    } elseif (!preg_match('/^[a-zA-Z0-9_.\-]+$/', $username)) {
        $error = 'Username: solo lettere, numeri, punto, trattino e underscore.';
    } elseif (strlen($password) < 8) {
        $error = 'La password deve contenere almeno 8 caratteri.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'La password deve contenere almeno una lettera maiuscola.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'La password deve contenere almeno un numero.';
    } elseif ($password !== $password2) {
        $error = 'Le password non coincidono.';
    } else {
        try {
            $check = $pdo->prepare('SELECT ID FROM utenti WHERE username = :username LIMIT 1');
            $check->execute([':username' => $username]);
            if ($check->fetch()) {
                $error = 'Username già in uso. Scegline un altro.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare('INSERT INTO utenti (username, password) VALUES (:username, :password)');
                $stmt->execute([':username' => $username, ':password' => $hash]);
                header('Location: login.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'Username già in uso. Scegline un altro.';
            } else {
                error_log('[register] PDOException: ' . $e->getMessage());
                $error = 'Errore del server. Riprova più tardi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrazione — FSL Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="global.css" />
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--bg-light); }
    .login-wrapper { width:100%; max-width:420px; padding:1rem; }
    .login-card {
      background:var(--bg-card); border:1px solid var(--border);
      border-radius:var(--radius-lg); box-shadow:var(--shadow-lg); padding:2.5rem 2rem;
    }
    .login-header { text-align:center; margin-bottom:2rem; }
    .login-logo {
      display:inline-flex; align-items:center; gap:0.6rem;
      font-size:1.4rem; font-weight:800; color:var(--primary); letter-spacing:-0.5px; margin-bottom:0.5rem;
    }
    .login-logo .logo-accent { color:var(--accent); }
    .login-logo-icon {
      background:var(--primary); border-radius:var(--radius); padding:0.4rem;
      display:flex; align-items:center; justify-content:center;
    }
    .login-logo-icon svg { color:#fff; }
    .login-subtitle { font-size:var(--fs-sm); color:var(--text-muted); margin-top:0.25rem; }
    .alert { padding:0.75rem 1rem; border-radius:var(--radius); font-size:var(--fs-sm); font-weight:500; margin-bottom:1.25rem; border-left:4px solid; }
    .alert-danger { background:#fee2e2; color:#991b1b; border-color:var(--danger); }
    .form-label-row { display:flex; align-items:center; gap:0.35rem; margin-bottom:0.35rem; }
    .form-label-row svg { color:var(--text-muted); }
    .btn-register { width:100%; justify-content:center; padding:0.65rem 1rem; font-size:var(--fs-base); margin-top:0.5rem; }
    .login-divider { border:none; border-top:1px solid var(--border); margin:1.5rem 0; }
    .login-footer { text-align:center; font-size:var(--fs-sm); color:var(--text-muted); }
    .login-footer a { color:var(--primary); text-decoration:none; font-weight:500; }
    .login-footer a:hover { color:var(--accent); text-decoration:underline; }
    .pw-rules { display:flex; flex-wrap:wrap; gap:4px 12px; margin-bottom:0.75rem; }
    .pw-rule { font-size:0.72rem; color:var(--text-muted); }
    .pw-rule.ok { color:var(--accent); }
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
      <p class="login-subtitle">Crea il tuo account</p>
    </div>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>

      <div class="form-group">
        <label class="form-label-row" for="username">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
          <span class="form-label" style="margin:0">Username</span>
        </label>
        <input type="text" id="username" name="username" class="form-control"
               placeholder="Scegli un username"
               value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               autocomplete="username" required autofocus />
        <span class="text-muted" style="font-size:0.75rem">3–50 caratteri: lettere, numeri, _ . -</span>
      </div>

      <div class="form-group">
        <label class="form-label-row" for="password">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <span class="form-label" style="margin:0">Password</span>
        </label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Almeno 8 caratteri" autocomplete="new-password" required
               oninput="checkPw(this.value)" />
        <div class="pw-rules">
          <span class="pw-rule" id="rule-len">✗ 8+ caratteri</span>
          <span class="pw-rule" id="rule-upper">✗ Una maiuscola</span>
          <span class="pw-rule" id="rule-num">✗ Un numero</span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label-row" for="password2">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <span class="form-label" style="margin:0">Conferma password</span>
        </label>
        <input type="password" id="password2" name="password2" class="form-control"
               placeholder="••••••••" autocomplete="new-password" required />
      </div>

      <button type="submit" class="btn btn-primary btn-register">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
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

<script>
function checkPw(pw) {
  document.getElementById('rule-len').className   = 'pw-rule' + (pw.length >= 8        ? ' ok' : '');
  document.getElementById('rule-upper').className = 'pw-rule' + (/[A-Z]/.test(pw)      ? ' ok' : '');
  document.getElementById('rule-num').className   = 'pw-rule' + (/[0-9]/.test(pw)      ? ' ok' : '');
  document.getElementById('rule-len').textContent   = (pw.length >= 8        ? '✓' : '✗') + ' 8+ caratteri';
  document.getElementById('rule-upper').textContent = (/[A-Z]/.test(pw)      ? '✓' : '✗') + ' Una maiuscola';
  document.getElementById('rule-num').textContent   = (/[0-9]/.test(pw)      ? '✓' : '✗') + ' Un numero';
}
</script>
</body>
</html>
