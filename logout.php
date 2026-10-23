<?php
/**
 * Logout Handler
 * Job/Internship Management System
 */
require_once __DIR__ . '/config/database.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start fresh session to set flash message
session_start();
set_flash('info', 'You have been logged out successfully.');

header("Location: " . base_url('login.php'));
exit;
