<?php
require_once 'init.php';

echo "<h1>Database Update Script</h1>";
echo "<p>This script will update your database schema to include missing columns and tables.</p>";

$updates = [];

// Check if the notes column exists in the orders table
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'notes'");
if ($check_column->num_rows == 0) {
    $sql = "ALTER TABLE orders ADD COLUMN notes TEXT AFTER payment_method";
    
    if ($conn->query($sql) === TRUE) {
        $updates[] = "Added notes column to orders table.";
    } else {
        $updates[] = "Error: Failed to add notes column - " . $conn->error;
    }
}

// Check if the payment_method column exists in the orders table
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
if ($check_column->num_rows == 0) {
    $sql = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'bank_transfer' AFTER payment_status";
    
    if ($conn->query($sql) === TRUE) {
        $updates[] = "Added payment_method column to orders table.";
    } else {
        $updates[] = "Error: Failed to add payment_method column - " . $conn->error;
    }
}

// Display update results
if (empty($updates)) {
    echo "<div style='color: green; padding: 10px; background: #d4edda; margin: 10px 0;'>Database schema is up to date!</div>";
} else {
    foreach ($updates as $update) {
        if (strpos($update, 'Error') === 0) {
            echo "<div style='color: red; padding: 10px; background: #f8d7da; margin: 10px 0;'>{$update}</div>";
        } else {
            echo "<div style='color: green; padding: 10px; background: #d4edda; margin: 10px 0;'>{$update}</div>";
        }
    }
}

echo "<p><a href='" . BASE_URL . "'>Return to homepage</a></p>";
?>
