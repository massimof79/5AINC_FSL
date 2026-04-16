<?php
    session_start();

    function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
    }
    }
    // Informativa Privacy (Placeholder per termini registrazione)
    $privacy_notice = "I dati forniti verranno trattati secondo il GDPR 2016/679.";
?>