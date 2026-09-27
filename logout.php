<?php
// logout.php - Sign Out Handler
require_once __DIR__ . '/config/auth.php';

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Start new session just to store the logout message
session_start();
set_flash('info', 'You have been signed out safely.');

header('Location: login.php');
exit;
