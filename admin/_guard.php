<?php
// admin/_guard.php - include this at the top of any protected admin page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Includes/functions/functions.php';

if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }
}

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}
