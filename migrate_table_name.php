<?php
require_once 'init.php';

echo "<h1>Table Rename Migration Script</h1>";
echo "<p>This script will rename the table from 'produk' to 'products'.</p>";

$updates = [];
$errors = [];

// Check if produk table exists
$check_produk = $conn->query("SHOW TABLES LIKE 'produk'");
$check_products = $conn->query("SHOW TABLES LIKE 'products'");

if ($check_produk->num_rows > 0 && $check_products->num_rows == 0) {
    // Rename table from produk to products
    $sql = "RENAME TABLE produk TO products";
    
    if ($conn->query($sql) === TRUE) {
        $updates[] = "✓ Successfully renamed table 'produk' to 'products'";
    } else {
        $errors[] = "✗ Error renaming table: " . $conn->error;
    }
} elseif ($check_products->num_rows > 0) {
    $updates[] = "✓ Table 'products' already exists";
} else {
    $errors[] = "✗ Table 'produk' not found";
}

// Display results
if (!empty($updates)) {
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    foreach ($updates as $update) {
        echo "<p style='color: #155724; margin: 5px 0;'>$update</p>";
    }
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    foreach ($errors as $error) {
        echo "<p style='color: #721c24; margin: 5px 0;'>$error</p>";
    }
    echo "</div>";
}

echo "<p><strong>Important:</strong> After running this migration, make sure to update your application code to use 'products' table name.</p>";
echo "<p><a href='" . BASE_URL . "'>← Return to Homepage</a></p>";
echo "<p><a href='../admin/dashboard.php'>Go to Admin Dashboard →</a></p>";
?>
