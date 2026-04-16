<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    header('Location: esperienze.html');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Inserisci username e password.';
    } else {
        $stmt = $pdo->prepare('SELECT ID, username, password FROM utenti WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && verifyUserPassword($password, (string) $user['password'])) {
            loginUser($user);
            header('Location: esperienze.html');
            exit;
        }

        $error = 'Credenziali non valide.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - PCTO Connect</title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg" />
  <link rel="stylesheet" href="global.css" />
</head>
<body>
  <main class="main-content" style="margin-left:0; margin-top:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.25rem;">
    <section class="card" style="width:100%; max-width:420px;">
      <div class="page-header" style="margin-bottom:1rem;">
        <h1>Accesso</h1>
        <p>Effettua il login per continuare in PCTO Connect.</p>
      </div>

      <?php if ($error !== ''): ?>
        <div class="form-error" style="margin-bottom:0.75rem; font-size:0.875rem;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" novalidate>
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input class="form-control" type="text" id="username" name="username" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input class="form-control" type="password" id="password" name="password" required />
        </div>

        <div class="form-actions" style="margin-top:0.5rem; border-top:none; padding-top:0;">
          <button class="btn btn-primary" type="submit">Login</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
