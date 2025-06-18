<?php
require_once 'init.php';

echo "<h1>Database Column Migration Script</h1>";
echo "<p>This script will rename product columns from Indonesian to English.</p>";

$updates = [];
$errors = [];

// Check if old columns exist
$check_columns = $conn->query("SHOW COLUMNS FROM produk");
$existing_columns = [];
while ($row = $check_columns->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

// Migration mappings
$column_mappings = [
    'harga' => 'price',
    'stok' => 'stock', 
    'deskripsi' => 'description',
    'tanggal_masuk' => 'date_added',
    'gambar_produk' => 'product_image'
];

foreach ($column_mappings as $old_column => $new_column) {
    if (in_array($old_column, $existing_columns) && !in_array($new_column, $existing_columns)) {
        // Determine column type based on old column
        $column_type = '';
        switch ($old_column) {
            case 'harga':
                $column_type = 'DECIMAL(10,2) NOT NULL';
                break;
            case 'stok':
                $column_type = 'INT NOT NULL DEFAULT 0';
                break;
            case 'deskripsi':
                $column_type = 'TEXT';
                break;
            case 'tanggal_masuk':
                $column_type = 'DATE';
                break;
            case 'gambar_produk':
                $column_type = 'VARCHAR(255)';
                break;
        }
        
        // Rename column
        $sql = "ALTER TABLE produk CHANGE `$old_column` `$new_column` $column_type";
        
        if ($conn->query($sql) === TRUE) {
            $updates[] = "Renamed column '$old_column' to '$new_column'";
        } else {
            $errors[] = "Error renaming column '$old_column' to '$new_column': " . $conn->error;
        }
    } elseif (in_array($new_column, $existing_columns)) {
        $updates[] = "Column '$new_column' already exists (migration already completed)";
    } else {
        $errors[] = "Column '$old_column' does not exist in the table";
    }
}

// Display results
if (empty($updates) && empty($errors)) {
    echo "<div style='color: green; padding: 10px; background: #d4edda; margin: 10px 0;'>All columns are already migrated!</div>";
} else {
    foreach ($updates as $update) {
        echo "<div style='color: green; padding: 10px; background: #d4edda; margin: 10px 0;'>{$update}</div>";
    }
    
    foreach ($errors as $error) {
        echo "<div style='color: red; padding: 10px; background: #f8d7da; margin: 10px 0;'>{$error}</div>";
    }
}

echo "<p><a href='" . BASE_URL . "'>Return to homepage</a></p>";
echo "<p><a href='../admin/dashboard.php'>Go to Admin Dashboard</a></p>";
?>
