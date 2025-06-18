<?php
define('BASE_URL', '/djaya_roasters');
define('SITE_NAME', 'Djaya Roasters');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/uploads/products');
define('ASSETS_PATH', $_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/assets');

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
?>
