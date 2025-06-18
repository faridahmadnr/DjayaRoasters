<?php
require_once '../../config/init.php';
require_once '../../config/cart_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response = [
        'success' => false, 
        'message' => 'Please login to add products to cart',
        'redirect' => BASE_URL . '/auth/login.php'
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    // Get product ID and quantity from POST data
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($product_id > 0 && $quantity > 0) {
        // Add item to cart
        if (addToCart($product_id, $quantity)) {
            $cart_count = getCartCount();
            $response = [
                'success' => true, 
                'message' => 'Item added to cart successfully',
                'cart_count' => $cart_count
            ];
        } else {
            $response['message'] = 'Failed to add item to cart';
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Redirect if accessed directly
redirect(BASE_URL);
?>
