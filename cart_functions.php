<?php
require_once 'database.php';
require_once 'functions.php';

/**
 * Get or create a cart session ID
 */
function getCartSessionId() {
    if (!isset($_SESSION['cart_id'])) {
        $_SESSION['cart_id'] = bin2hex(random_bytes(16)); // Generate a random session ID
    }
    return $_SESSION['cart_id'];
}

/**
 * Check if user is logged in
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    return true;
}

/**
 * Add item to cart
 */
function addToCart($product_id, $quantity = 1) {
    global $conn;
    
    $session_id = getCartSessionId();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    
    // If user not logged in and we're requiring login, return false
    if (!$user_id && requireLogin()) {
        return false;
    }
    
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;
    
    if ($quantity <= 0) {
        return false;
    }
    
    // Check if product exists and has enough stock
    $product_query = "SELECT stock FROM products WHERE id = $product_id";
    $product_result = $conn->query($product_query);
    
    if ($product_result->num_rows == 0) {
        return false;
    }
    
    $product = $product_result->fetch_assoc();
    if ($product['stock'] < $quantity) {
        return false;
    }

    // Check if item already in cart - for this user or session
    $check_query = "SELECT id, quantity FROM cart WHERE ";
    
    if ($user_id) {
        $check_query .= "user_id = $user_id";
    } else {
        $check_query .= "session_id = '$session_id' AND user_id IS NULL";
    }
    
    $check_query .= " AND product_id = $product_id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // Update existing cart item
        $cart_item = $check_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        if ($new_quantity > $product['stock']) {
            $new_quantity = $product['stock'];
        }
        
        $update_query = "UPDATE cart SET quantity = $new_quantity WHERE id = {$cart_item['id']}";
        return $conn->query($update_query);
    } else {
        // Insert new cart item
        $user_id_sql = $user_id ? $user_id : "NULL";
        $insert_query = "INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES ($user_id_sql, '$session_id', $product_id, $quantity)";
        return $conn->query($insert_query);
    }
}

/**
 * Update cart item quantity
 */
function updateCartItem($cart_item_id, $quantity) {
    global $conn;
    
    $cart_item_id = (int)$cart_item_id;
    $quantity = (int)$quantity;
    $session_id = getCartSessionId();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    
    if ($quantity <= 0) {
        // Remove the item from cart
        return removeCartItem($cart_item_id);
    }
    
    // Build the WHERE clause based on whether user is logged in
    $where_clause = "";
    if ($user_id) {
        $where_clause = "user_id = $user_id";
    } else {
        $where_clause = "session_id = '$session_id' AND user_id IS NULL";
    }
    
    // Get the product ID from cart item
    $cart_query = "SELECT product_id FROM cart WHERE id = $cart_item_id AND $where_clause";
    $cart_result = $conn->query($cart_query);
    
    if ($cart_result->num_rows == 0) {
        return false;
    }
    
    $cart_item = $cart_result->fetch_assoc();
    
    // Check stock availability
    $product_query = "SELECT stock FROM products WHERE id = {$cart_item['product_id']}";
    $product_result = $conn->query($product_query);
    $product = $product_result->fetch_assoc();
    
    if ($quantity > $product['stock']) {
        $quantity = $product['stock'];
    }
    
    // Update cart item
    $update_query = "UPDATE cart SET quantity = $quantity WHERE id = $cart_item_id AND $where_clause";
    return $conn->query($update_query);
}

/**
 * Remove item from cart
 */
function removeCartItem($cart_item_id) {
    global $conn;
    
    $cart_item_id = (int)$cart_item_id;
    $session_id = getCartSessionId();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    
    if (!$user_id && requireLogin()) {
        return false;
    }
    
    // Build the WHERE clause based on whether user is logged in
    $where_clause = "";
    if ($user_id) {
        $where_clause = "user_id = $user_id";
    } else {
        $where_clause = "session_id = '$session_id' AND user_id IS NULL";
    }
    
    $delete_query = "DELETE FROM cart WHERE id = $cart_item_id AND $where_clause";
    return $conn->query($delete_query);
}

/**
 * Get cart items
 */
function getCartItems() {
    global $conn;
    
    $session_id = getCartSessionId();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

    if (!$user_id && requireLogin()) {
        return [
            'items' => [],
            'total' => 0,
            'count' => 0
        ];
    }
    
    // Build the WHERE clause based on whether user is logged in
    $where_clause = "";
    if ($user_id) {
        $where_clause = "c.user_id = $user_id";
    } else {
        $where_clause = "c.session_id = '$session_id' AND c.user_id IS NULL";
    }
    
    // Get cart items - Updated table name
    $query = "SELECT c.id, c.product_id, c.quantity, p.product_name, p.price, p.product_image, p.stock 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE $where_clause";
    
    $result = $conn->query($query);
    
    $items = [];
    $total = 0;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['subtotal'] = $row['price'] * $row['quantity'];
            $total += $row['subtotal'];
            $items[] = $row;
        }
    }
    
    return [
        'items' => $items,
        'total' => $total,
        'count' => count($items)
    ];
}

/**
 * Get cart count
 */
function getCartCount() {
    global $conn;
    
    $session_id = getCartSessionId();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    
    if (!$user_id && requireLogin()) {
        return 0;
    }
    
    // Build the WHERE clause based on whether user is logged in
    $where_clause = "";
    if ($user_id) {
        $where_clause = "user_id = $user_id";
    } else {
        $where_clause = "session_id = '$session_id' AND user_id IS NULL";
    }
    
    $query = "SELECT SUM(quantity) AS count FROM cart WHERE $where_clause";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    return $row['count'] ? (int)$row['count'] : 0;
}

/**
 * Clear cart
 */
function clearCart() {
    global $conn;
    
    $session_id = getCartSessionId();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    
    if (!$user_id && requireLogin()) {
        return false;
    }
    
    // Build the WHERE clause based on whether user is logged in
    $where_clause = "";
    if ($user_id) {
        $where_clause = "user_id = $user_id";
    } else {
        $where_clause = "session_id = '$session_id' AND user_id IS NULL";
    }
    
    $query = "DELETE FROM cart WHERE $where_clause";
    return $conn->query($query);
}

/**
 * Migrate anonymous cart to user cart after login
 * Call this function when a user logs in
 * @param int $user_id The ID of the user
 * @param string $session_id The session ID of the anonymous cart
 * @return bool Success status
 */
function migrateCartAfterLogin($user_id, $session_id) {
    global $conn;
    
    if (empty($session_id) || empty($user_id)) {
        return false;
    }

    // First, check if there are any anonymous cart items with this session ID
    $check_query = "SELECT COUNT(*) as count FROM cart WHERE session_id = '$session_id' AND user_id IS NULL";
    $check_result = $conn->query($check_query);
    $count_row = $check_result->fetch_assoc();
    
    if ($count_row['count'] == 0) {
        return true; // No items to migrate, consider it successful
    }
    
    // Start transaction to ensure data integrity
    $conn->begin_transaction();
    
    try {
        // For each anonymous cart item, check if the user already has it in their cart
        $get_items_query = "SELECT product_id, quantity FROM cart WHERE session_id = '$session_id' AND user_id IS NULL";
        $items_result = $conn->query($get_items_query);
        
        while ($item = $items_result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Check if user already has this product in their cart
            $existing_query = "SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id";
            $existing_result = $conn->query($existing_query);
            
            if ($existing_result->num_rows > 0) {
                // User already has this product in cart, update the quantity
                $existing_item = $existing_result->fetch_assoc();
                $new_quantity = $existing_item['quantity'] + $quantity;
                
                // Check stock limit
                $stock_query = "SELECT stock FROM products WHERE id = $product_id";
                $stock_result = $conn->query($stock_query);
                $stock_data = $stock_result->fetch_assoc();
                
                if ($new_quantity > $stock_data['stock']) {
                    $new_quantity = $stock_data['stock'];
                }
                
                // Update the existing cart item
                $update_query = "UPDATE cart SET quantity = $new_quantity WHERE id = {$existing_item['id']}";
                $conn->query($update_query);
                
                // Delete the anonymous cart item
                $delete_query = "DELETE FROM cart WHERE session_id = '$session_id' AND user_id IS NULL AND product_id = $product_id";
                $conn->query($delete_query);
            } else {
                // User doesn't have this product, update the anonymous item to belong to the user
                $update_query = "UPDATE cart SET user_id = $user_id WHERE session_id = '$session_id' AND user_id IS NULL AND product_id = $product_id";
                $conn->query($update_query);
            }
        }
        
        // Commit the transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Something went wrong, rollback
        $conn->rollback();
        return false;
    }
}

/**
 * Has Cart
 * Check if the user has any items in their cart
 */
function hasCart($user_id) {
    global $conn;
    
    if (empty($user_id)) {
        return false;
    }
    
    $query = "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}
?>
