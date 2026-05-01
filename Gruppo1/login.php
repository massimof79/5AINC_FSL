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
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Inserisci username e password.';
    } else {
        try {
            $pdo  = getDbConnection();
            $stmt = $pdo->prepare(
                'SELECT ID, username, password FROM utenti WHERE username = :username LIMIT 1'
            );
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && verifyUserPassword($password, (string) $user['password'])) {
                loginUser($user);
                header('Location: index.php');
                exit;
            }
            $error = 'Credenziali non valide.';
        } catch (PDOException $e) {
            error_log('[FSL][LOGIN] Errore DB: ' . $e->getMessage());
            $error = 'Errore del server. Riprovare più tardi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — FSL Panel</title>
  <link rel="icon" type="image/svg+xml" href="favicon.svg" />
  <link rel="stylesheet" href="global.css" />
</head>
<body>
  <main class="main-content"
        style="margin-left:0; margin-top:0; min-height:100vh;
               display:flex; align-items:center; justify-content:center; padding:1.25rem;">

    <section class="card" style="width:100%; max-width:420px;">

      <div class="page-header" style="margin-bottom:1.25rem;">
        <h1 style="display:flex; align-items:center; gap:0.5rem;">
          <span style="color:var(--accent); font-size:1.3rem;">◈</span>
          FSL <span style="color:var(--accent);">Panel</span>
        </h1>
        <p>Effettua il login per accedere all'area riservata.</p>
      </div>

      <?php if ($error !== ''): ?>
        <div class="form-error"
             style="margin-bottom:1rem; font-size:0.875rem; background:#fee2e2;
                    padding:0.6rem 0.9rem; border-radius:var(--radius); border-left:4px solid var(--danger);">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['registered'])): ?>
        <div style="margin-bottom:1rem; font-size:0.875rem; background:#d1fae5;
                    padding:0.6rem 0.9rem; border-radius:var(--radius); border-left:4px solid var(--success); color:#065f46;">
          Registrazione completata. Puoi ora effettuare il login.
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['timeout'])): ?>
        <div style="margin-bottom:1rem; font-size:0.875rem; background:#fef3c7;
                    padding:0.6rem 0.9rem; border-radius:var(--radius); border-left:4px solid var(--warning); color:#92400e;">
          Sessione scaduta. Effettua nuovamente il login.
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['logout'])): ?>
        <div style="margin-bottom:1rem; font-size:0.875rem; background:#d1fae5;
                    padding:0.6rem 0.9rem; border-radius:var(--radius); border-left:4px solid var(--success); color:#065f46;">
          Logout effettuato con successo.
        </div>
      <?php endif; ?>

      <form method="post" action="login.php" novalidate>

        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input class="form-control" type="text" id="username" name="username"
                 autocomplete="username" maxlength="50" required placeholder="Il tuo username" />
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input class="form-control" type="password" id="password" name="password"
                 autocomplete="current-password" maxlength="200" required placeholder="••••••••" />
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0; margin-top:1rem;">
          <button class="btn btn-primary btn-lg" type="submit" style="width:100%; justify-content:center;">
            Accedi
          </button>
        </div>

      </form>

      <p class="text-center text-muted mt-2" style="font-size:var(--fs-sm);">
        Non hai un account?
        <a href="registrati.php" style="color:var(--accent); font-weight:600;">Registrati</a>
      </p>

    </section>
  </main>

  <script>
    document.querySelector('form').addEventListener('submit', function(e) {
      let ok = true;
      this.querySelectorAll('input[required]').forEach(inp => {
        if (!inp.value.trim()) { inp.classList.add('is-invalid'); ok = false; }
        else inp.classList.remove('is-invalid');
      });
      if (!ok) e.preventDefault();
    });
  </script>
</body>
</html>
