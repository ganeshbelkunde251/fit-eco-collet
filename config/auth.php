<?php
/**
 * Authentication and Helper Functions
 * EcoCollect Management Platform
 */

if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters where possible
    ini_set('session.cookie_httponly', 1);
    session_start();
}

require_once __DIR__ . '/database.php';

/**
 * Check if user is logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user array
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'user'
    ];
}

/**
 * Check if logged in user is admin
 */
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Require user authentication, redirect to login if not authenticated
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('danger', 'Please sign in to access this page.');
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Require admin role, redirect if unauthorized
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'Access denied. Administrator privileges required.');
        header('Location: ../user/dashboard.php');
        exit;
    }
}

/**
 * Input Sanitization Helper
 */
function sanitize_input($data): string {
    if (is_null($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF Token
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token): bool {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '')) {
        return true;
    }
    return false;
}

/**
 * Flash Messaging Helpers
 */
function set_flash(string $type, string $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Unique Request ID Generator (e.g. EC-2026-00001)
 */
function generate_request_id($pdo): string {
    $year = date('Y');
    $prefix = "EC-{$year}-";

    $stmt = $pdo->prepare("SELECT request_id FROM pickup_requests WHERE request_id LIKE :prefix ORDER BY id DESC LIMIT 1");
    $stmt->execute([':prefix' => "{$prefix}%"]);
    $lastReq = $stmt->fetchColumn();

    if ($lastReq) {
        $numPart = (int) substr($lastReq, strlen($prefix));
        $nextNum = $numPart + 1;
    } else {
        $nextNum = 1;
    }

    return $prefix . str_pad((string)$nextNum, 5, '0', STR_PAD_LEFT);
}

/**
 * Helper to compute base URL path relative to web root
 */
function base_url($path = ''): string {
    // Detect base directory dynamically
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $scriptDir = str_replace('\\', '/', $scriptDir);
    
    // Normalize path separators
    if ($path && strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }
    
    // Determine base project folder if nested
    $parts = explode('/', trim($scriptDir, '/'));
    // If inside /user, /admin, /api, go up one level
    if (in_array(end($parts), ['user', 'admin', 'api', 'includes', 'config'])) {
        array_pop($parts);
    }
    
    $base = '/' . implode('/', $parts);
    if ($base === '/') $base = '';
    
    return $base . $path;
}
