<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'djaya_roasters';

$conn = new mysqli($host, $username, $password, $database);
$BASE_URL = "/djaya_roasters/";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if not exists
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    user_type ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating table: " . $conn->error);
}

// Create products table with English column names
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    description TEXT,
    date_added DATE,
    product_image VARCHAR(255)
)";

if (!$conn->query($sql)) {
    die("Error creating products table: " . $conn->error);
}

// Create cart table if not exists - update foreign key reference
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Add index to cart table for performance
$sql_index = "CREATE INDEX IF NOT EXISTS idx_cart_session_user ON cart(session_id, user_id)";

if (!$conn->query($sql)) {
    die("Error creating cart table: " . $conn->error);
}

if (!$conn->query($sql_index)) {
    die("Error creating cart index: " . $conn->error);
}

// Create the orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(20) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_method VARCHAR(50) DEFAULT 'bank_transfer',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    die("Error creating orders table: " . $conn->error);
}

// Create the order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
)";

if (!$conn->query($sql)) {
    die("Error creating order_items table: " . $conn->error);
}

// Insert default admin if not exists
$admin_check = $conn->query("SELECT * FROM users WHERE username = 'admin' AND user_type = 'admin'");
if ($admin_check->num_rows === 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, email, password, user_type) VALUES ('admin', 'admin@djayaroasters.com', '$admin_password', 'admin')");
} else {
    // Reset admin password to ensure it works
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$admin_password' WHERE username = 'admin' AND user_type = 'admin'");
}
?>