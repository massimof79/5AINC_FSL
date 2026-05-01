<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username        = trim(strip_tags($_POST['username'] ?? ''));
    $password        = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Lo username deve avere tra 3 e 50 caratteri.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.\-]+$/', $username)) {
        $errors[] = 'Username: solo lettere, numeri, punto, trattino e underscore.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'La password deve avere almeno 8 caratteri.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'La password deve contenere almeno una lettera maiuscola.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'La password deve contenere almeno un numero.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Le due password non coincidono.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $chk = $pdo->prepare('SELECT 1 FROM utenti WHERE username = :u LIMIT 1');
            $chk->execute([':u' => $username]);
            if ($chk->fetchColumn()) {
                $errors[] = 'Username già in uso. Scegline un altro.';
            }
        } catch (PDOException $e) {
            error_log('[FSL][REGISTER] ' . $e->getMessage());
            $errors[] = 'Errore del server. Riprovare più tardi.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo  = getDbConnection();
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $ins  = $pdo->prepare('INSERT INTO utenti (username, password) VALUES (:u, :p)');
            $ins->execute([':u' => $username, ':p' => $hash]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Username già in uso. Scegline un altro.';
            } else {
                error_log('[FSL][REGISTER] ' . $e->getMessage());
                $errors[] = 'Errore durante la registrazione. Riprovare più tardi.';
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
  <link rel="icon" type="image/svg+xml" href="favicon.svg" />
  <link rel="stylesheet" href="global.css" />
</head>
<body>
  <main class="main-content"
        style="margin-left:0; margin-top:0; min-height:100vh;
               display:flex; align-items:center; justify-content:center; padding:1.25rem;">

    <section class="card" style="width:100%; max-width:440px;">

      <div class="page-header" style="margin-bottom:1.25rem;">
        <h1 style="display:flex; align-items:center; gap:0.5rem;">
          <span style="color:var(--accent); font-size:1.3rem;">◈</span>
          FSL <span style="color:var(--accent);">Panel</span>
        </h1>
        <p>Crea un account per accedere al pannello.</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div style="margin-bottom:1rem; background:#fee2e2; padding:0.75rem 1rem;
                    border-radius:var(--radius); border-left:4px solid var(--danger);">
          <?php foreach ($errors as $e): ?>
            <p class="form-error" style="margin:0 0 0.2rem;"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" action="registrati.php" novalidate id="reg-form">

        <div class="form-group">
          <label class="form-label" for="username">Username <span class="required">*</span></label>
          <input class="form-control" type="text" id="username" name="username"
                 value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                 autocomplete="username" maxlength="50" required
                 placeholder="Scegli uno username" />
          <span class="text-muted" style="font-size:0.75rem;">3–50 caratteri: lettere, numeri, _ . -</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password <span class="required">*</span></label>
          <input class="form-control" type="password" id="password" name="password"
                 autocomplete="new-password" maxlength="200" required
                 placeholder="Almeno 8 caratteri"
                 oninput="checkStrength(this.value)" />
          <div style="height:4px; background:var(--border); border-radius:2px; margin-top:6px; overflow:hidden;">
            <div id="strength-fill" style="height:100%; width:0; border-radius:2px; transition:width .3s,background .3s;"></div>
          </div>
          <span id="strength-label" style="font-size:0.72rem; color:var(--text-muted); min-height:16px; display:block; margin-top:2px;"></span>
        </div>

        <div class="form-group">
          <label class="form-label" for="password_confirm">Conferma Password <span class="required">*</span></label>
          <input class="form-control" type="password" id="password_confirm" name="password_confirm"
                 autocomplete="new-password" maxlength="200" required placeholder="Ripeti la password" />
          <span class="form-error" id="err-confirm"></span>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:6px 14px; margin-bottom:1rem;">
          <span class="text-muted" style="font-size:0.75rem;" id="rule-len">✗ 8+ caratteri</span>
          <span class="text-muted" style="font-size:0.75rem;" id="rule-upper">✗ Una maiuscola</span>
          <span class="text-muted" style="font-size:0.75rem;" id="rule-num">✗ Un numero</span>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0; margin-top:0.5rem;">
          <button class="btn btn-primary btn-lg" type="submit" style="width:100%; justify-content:center;">
            Crea Account
          </button>
        </div>
      </form>

      <p class="text-center text-muted mt-2" style="font-size:var(--fs-sm);">
        Hai già un account?
        <a href="login.php" style="color:var(--accent); font-weight:600;">Accedi</a>
      </p>

    </section>
  </main>

  <script>
    function checkStrength(pw) {
      let score = 0;
      const rLen   = document.getElementById('rule-len');
      const rUpper = document.getElementById('rule-upper');
      const rNum   = document.getElementById('rule-num');
      const fill   = document.getElementById('strength-fill');
      const label  = document.getElementById('strength-label');

      if (pw.length >= 8)    { score++; rLen.style.color   = 'var(--accent)'; rLen.textContent   = '✓ 8+ caratteri'; }
      else                   { rLen.style.color   = 'var(--text-muted)'; rLen.textContent   = '✗ 8+ caratteri'; }
      if (/[A-Z]/.test(pw))  { score++; rUpper.style.color = 'var(--accent)'; rUpper.textContent = '✓ Una maiuscola'; }
      else                   { rUpper.style.color = 'var(--text-muted)'; rUpper.textContent = '✗ Una maiuscola'; }
      if (/[0-9]/.test(pw))  { score++; rNum.style.color   = 'var(--accent)'; rNum.textContent   = '✓ Un numero'; }
      else                   { rNum.style.color   = 'var(--text-muted)'; rNum.textContent   = '✗ Un numero'; }
      if (/[^a-zA-Z0-9]/.test(pw)) score++;

      const pct    = Math.min((score / 4) * 100, 100);
      const colors = ['#c0392b','#f39c12','#f39c12','#27ae60','#27ae60'];
      const labels = ['','Debole','Discreta','Buona','Ottima'];
      fill.style.width      = pct + '%';
      fill.style.background = colors[score] || colors[0];
      label.textContent     = labels[score] || '';
    }

    document.getElementById('reg-form').addEventListener('submit', function(e) {
      const pw  = document.getElementById('password').value;
      const pw2 = document.getElementById('password_confirm').value;
      const err = document.getElementById('err-confirm');
      let ok = true;

      this.querySelectorAll('input[required]').forEach(inp => {
        if (!inp.value) { inp.classList.add('is-invalid'); ok = false; }
        else inp.classList.remove('is-invalid');
      });

      if (pw !== pw2) {
        err.textContent = 'Le password non coincidono.';
        document.getElementById('password_confirm').classList.add('is-invalid');
        ok = false;
      } else {
        err.textContent = '';
      }

      if (!ok) e.preventDefault();
    });
  </script>
</body>
</html>
