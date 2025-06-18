<?php
require_once '../config/init.php';
require_once '../config/cart_functions.php';

// Store the cart session ID before destroying the session
$cart_session_id = isset($_SESSION['cart_id']) ? $_SESSION['cart_id'] : null;

// Store a success message in session before destroying it
$_SESSION['info_message'] = "You have been successfully logged out.";

// Backup the message to store again after session destroy
$infoMessage = $_SESSION['info_message'];

// Clear all session variables
$_SESSION = array();

// If a session cookie is used, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start a new session to show the logout message
session_start();
$_SESSION['info_message'] = $infoMessage;

// Preserve the cart session ID to maintain anonymous cart
if ($cart_session_id) {
    $_SESSION['cart_id'] = $cart_session_id;
}

// Redirect to home page
header('Location: ' . BASE_URL . '/index.php');
exit();
?>