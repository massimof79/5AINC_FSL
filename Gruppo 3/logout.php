<?php
/**
 * logout.php
 * Distrugge la sessione e reindirizza al login.
 * Progetto: 5AINC_FSL
 *
 * Usa logoutUser() da auth.php (Gruppo 4).
 */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

logoutUser();

header('Location: login.php?logout=1');
exit;