<?php
require_once 'config/init.php';

echo "<h1>Database Migration Script</h1>";
echo "<p>This script will update your database schema to use English column names.</p>";

$migrations = [];
$errors = [];

// Check if old columns exist and migrate them
$column_mappings = [
    'harga' => ['new' => 'price', 'type' => 'DECIMAL(10,2) NOT NULL'],
    'stok' => ['new' => 'stock', 'type' => 'INT NOT NULL DEFAULT 0'],
    'deskripsi' => ['new' => 'description', 'type' => 'TEXT'],
    'tanggal_masuk' => ['new' => 'date_added', 'type' => 'DATE'],
    'gambar_produk' => ['new' => 'product_image', 'type' => 'VARCHAR(255)'],
    'nama_produk' => ['new' => 'product_name', 'type' => 'VARCHAR(255) NOT NULL'],
    'jenis_kopi' => ['new' => 'category', 'type' => 'VARCHAR(100) NOT NULL']
];

// Check existing columns
$check_columns = $conn->query("SHOW COLUMNS FROM produk");
$existing_columns = [];
while ($row = $check_columns->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

foreach ($column_mappings as $old_column => $config) {
    $new_column = $config['new'];
    $type = $config['type'];
    
    if (in_array($old_column, $existing_columns) && !in_array($new_column, $existing_columns)) {
        // Rename column
        $sql = "ALTER TABLE produk CHANGE `$old_column` `$new_column` $type";
        
        if ($conn->query($sql) === TRUE) {
            $migrations[] = "✓ Renamed column '$old_column' to '$new_column'";
        } else {
            $errors[] = "✗ Error renaming column '$old_column' to '$new_column': " . $conn->error;
        }
    } elseif (in_array($new_column, $existing_columns)) {
        $migrations[] = "✓ Column '$new_column' already exists";
    }
}

// Create user directory if it doesn't exist
$user_dir = 'pages/user';
if (!is_dir($user_dir)) {
    mkdir($user_dir, 0777, true);
    $migrations[] = "✓ Created user directory";
}

// Display results
echo "<h2>Migration Results:</h2>";

if (!empty($migrations)) {
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    foreach ($migrations as $migration) {
        echo "<p style='color: #155724; margin: 5px 0;'>$migration</p>";
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

if (empty($migrations) && empty($errors)) {
    echo "<div style='background: #d1ecf1; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<p style='color: #0c5460;'>✓ Database schema is already up to date!</p>";
    echo "</div>";
}

echo "<p><a href='index.php'>← Return to Homepage</a></p>";
echo "<p><a href='admin/dashboard.php'>Go to Admin Dashboard →</a></p>";
?>
