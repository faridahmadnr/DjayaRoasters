<?php
require_once '../../config/init.php';
require_once '../../config/cart_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$response = ['success' => false, 'message' => 'Invalid action'];

$action = $_POST['action'] ?? '';
$cart_item_id = (int)($_POST['id'] ?? 0);

if ($cart_item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item ID']);
    exit;
}

try {
    if ($action === 'update') {
        $quantity = (int)($_POST['quantity'] ?? 0);
        
        if ($quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
            exit;
        }
        
        // Update cart item
        if (updateCartItem($cart_item_id, $quantity)) {
            // Get updated cart data
            $cart = getCartItems();
            
            // Find the updated item to get its details
            $updated_item = null;
            foreach ($cart['items'] as $item) {
                if ($item['id'] == $cart_item_id) {
                    $updated_item = $item;
                    break;
                }
            }
            
            if ($updated_item) {
                $response = [
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'item_subtotal' => $updated_item['subtotal'],
                    'item_stock' => $updated_item['stock'],
                    'cart_total' => $cart['total'],
                    'cart_count' => $cart['count']
                ];
            } else {
                $response = ['success' => false, 'message' => 'Item not found after update'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Failed to update cart item'];
        }
        
    } elseif ($action === 'remove') {
        // Remove cart item
        if (removeCartItem($cart_item_id)) {
            // Get updated cart data
            $cart = getCartItems();
            
            $response = [
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_total' => $cart['total'],
                'cart_count' => $cart['count']
            ];
        } else {
            $response = ['success' => false, 'message' => 'Failed to remove cart item'];
        }
    }
    
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
