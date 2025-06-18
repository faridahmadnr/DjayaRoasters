<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define the order of inclusion to avoid undefined constants
require_once __DIR__ . '/database.php'; // First load database connection
require_once __DIR__ . '/config.php';   // Next load configuration with constants
require_once __DIR__ . '/functions.php'; // Finally load functions that might use constants
?>
