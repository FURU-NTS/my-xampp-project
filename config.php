<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'LeaseAndMaintenanceDB');
define('DB_USER', 'root'); // 実際のユーザー名に変更
define('DB_PASS', '');     // 実際のパスワードに変更

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>