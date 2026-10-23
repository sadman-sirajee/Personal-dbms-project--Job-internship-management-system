<?php
/**
 * Database Connection & Global Configuration
 * Job/Internship Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'job_internship_db');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

/**
 * Get Base URL dynamically regardless of folder name or nesting
 */
function base_url($path = '') {
    // Determine HTTP or HTTPS
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');
    $scriptFilename = isset($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: '') : '';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    $baseDir = '';
    if ($projectRoot && $scriptFilename && strpos($scriptFilename, $projectRoot) === 0) {
        $relPath = substr($scriptFilename, strlen($projectRoot));
        if ($relPath !== '' && substr($scriptName, -strlen($relPath)) === $relPath) {
            $baseDir = substr($scriptName, 0, strlen($scriptName) - strlen($relPath));
        } else {
            $encodedRel = implode('/', array_map('rawurlencode', explode('/', $relPath)));
            if ($encodedRel !== '' && substr($scriptName, -strlen($encodedRel)) === $encodedRel) {
                $baseDir = substr($scriptName, 0, strlen($scriptName) - strlen($encodedRel));
            } else {
                $baseDir = dirname($scriptName);
                if (preg_match('#/(users|jobs|applications|config|includes)$#i', $baseDir)) {
                    $baseDir = dirname($baseDir);
                }
            }
        }
    } else {
        $baseDir = dirname($scriptName);
        if (preg_match('#/(users|jobs|applications|config|includes)$#i', $baseDir)) {
            $baseDir = dirname($baseDir);
        }
    }

    $baseDir = rtrim(str_replace('\\', '/', $baseDir), '/');
    $base = $protocol . $host . $baseDir;

    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Authentication Helpers
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('danger', 'Please log in to access this page.');
        header("Location: " . base_url('login.php'));
        exit;
    }
}

function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin';
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'Access denied: Administrator privileges required.');
        header("Location: " . base_url('dashboard.php'));
        exit;
    }
}

function current_user() {
    return [
        'id'    => $_SESSION['user_id'] ?? null,
        'name'  => $_SESSION['user_name'] ?? 'Guest',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'User'
    ];
}

/**
 * Flash Message Helpers
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Input sanitization helper
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
