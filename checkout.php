<?php
require_once "../../config/init.php";
require_once "../../config/cart_functions.php";

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/pages/cart/checkout.php';
    $_SESSION['info_message'] = "Please login to proceed with checkout.";
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get cart items
$cart = getCartItems();

// Check if cart is empty
if (empty($cart['items'])) {
    $_SESSION['info_message'] = "Your cart is empty. Please add products before checkout.";
    header("Location: " . BASE_URL . "/pages/products/category.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $errors = [];
    
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $notes = trim($_POST['notes']);
    
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($postal_code)) $errors[] = "Postal code is required";
    
    if (empty($errors)) {
        // Generate unique order number
        $order_number = 'DR' . date('Ymd') . rand(1000, 9999);
        
        // Create shipping address string
        $shipping_address = "$full_name\n$address\n$city, $postal_code\n$phone";
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order - simplified without payment method
            $order_sql = "INSERT INTO orders (user_id, order_number, total_amount, shipping_address, order_status, payment_status, notes) 
                         VALUES ($user_id, '$order_number', {$cart['total']}, '$shipping_address', 'pending', 'paid', '$notes')";
            
            if (!$conn->query($order_sql)) {
                throw new Exception("Error creating order: " . $conn->error);
            }
            
            $order_id = $conn->insert_id;
            
            // Add order items
            foreach ($cart['items'] as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                
                $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                            VALUES ($order_id, $product_id, $quantity, $price)";
                
                if (!$conn->query($item_sql)) {
                    throw new Exception("Error adding order item: " . $conn->error);
                }
                
                // Update product stock
                $update_stock_sql = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id AND stock >= $quantity";
                if (!$conn->query($update_stock_sql) || $conn->affected_rows == 0) {
                    throw new Exception("Insufficient stock for product ID: $product_id");
                }
            }
            
            // Clear cart
            if (!clearCart()) {
                throw new Exception("Error clearing cart");
            }
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to confirmation page
            $_SESSION['order_id'] = $order_id;
            $_SESSION['success_message'] = "Order placed successfully! Your order number is $order_number.";
            header("Location: " . BASE_URL . "/pages/cart/confirmation.php");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Djaya Roasters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin-top: 80px;
        }
        .checkout-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0;
        }
        .checkout-title {
            color: #6F4E37;
            margin-bottom: 30px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #6F4E37;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #555;
        }
        .form-control, .form-select {
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6F4E37;
            box-shadow: 0 0 0 0.2rem rgba(111, 78, 55, 0.25);
        }
        .btn-primary {
            background-color: #6F4E37;
            border-color: #6F4E37;
            padding: 12px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #5a3d2a;
            border-color: #5a3d2a;
        }
        .btn-primary:active, .btn-primary:focus, .btn-primary.active {
            background-color: #30271C !important;
            border-color: #30271C !important;
            box-shadow: 0 0 0 0.2rem rgba(48, 39, 28, 0.25) !important;
        }
        .btn-outline-secondary {
            color: #6F4E37;
            border-color: #6F4E37;
        }
        .btn-outline-secondary:hover {
            background-color: #6F4E37;
            color: white;
        }
        .order-summary {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            position: sticky;
            top: 100px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-total {
            font-size: 1.2rem;
            font-weight: 700;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            margin-top: 15px;
        }
        .alert {
            border-radius: 5px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .small-text {
            font-size: 0.85rem;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container checkout-container">
        <h1 class="checkout-title">Checkout</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-user me-2"></i> Shipping Information
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?= $user['username'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= $user['email'] ?? '' ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= $user['phone'] ?? '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?= $user['address'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Order Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Special instructions for delivery"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h4 class="mb-4">Order Summary</h4>
                        
                        <?php foreach ($cart['items'] as $item): ?>
                            <div class="order-item">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div class="small-text"><?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                                </div>
                                <div>
                                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <div>Subtotal</div>
                            <div>Rp <?= number_format($cart['total'], 0, ',', '.') ?></div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-2">
                            <div>Shipping</div>
                            <div>Free</div>
                        </div>
                        
                        <div class="d-flex justify-content-between order-total">
                            <div>Total</div>
                            <div>Rp <?= number_format($cart['total'], 0, ',', '.') ?></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mt-4">
                            <i class="fas fa-shopping-bag me-2"></i> Place Order
                        </button>
                        
                        <a href="<?= BASE_URL ?>/pages/cart/cart.php" class="btn btn-outline-secondary w-100 mt-3">
                            <i class="fas fa-arrow-left me-2"></i> Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
