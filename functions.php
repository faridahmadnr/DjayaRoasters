<?php
function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function protectAdminPage() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        $_SESSION['error'] = "Kamu tidak memiliki akses ke halaman ini!";
        // Check if BASE_URL is defined, otherwise use a relative path
        $redirect_url = defined('BASE_URL') ? BASE_URL . '/auth/login.php' : '../auth/login.php';
        header('Location: ' . $redirect_url);
        exit();
    }
}

function protectUserPage() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Please login first!";
        redirect('../auth/login.php');
    }
}
?>