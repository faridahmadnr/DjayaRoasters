<?php
require_once "../../config/init.php";

// Check if order ID exists in session
if (!isset($_SESSION['order_id'])) {
    header('Location: ' . BASE_URL . '/pages/cart/cart.php');
    exit();
}

// Get order details
$order_id = $_SESSION['order_id'];
$user_id = $_SESSION['user_id'];

// Clear order ID from session to prevent refreshing
$order_id_copy = $order_id;
unset($_SESSION['order_id']);

// Get order information
$order_query = "SELECT * FROM orders WHERE id = $order_id_copy AND user_id = $user_id";
$order_result = mysqli_query($conn, $order_query);

// If order doesn't exist or doesn't belong to user, redirect
if (mysqli_num_rows($order_result) === 0) {
    header('Location: ' . BASE_URL . '/pages/cart/cart.php');
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, p.product_name, p.product_image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id_copy";
$items_result = mysqli_query($conn, $items_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Djaya Roasters</title>
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
        .confirmation-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0;
        }
        .confirmation-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .confirmation-header .icon {
            font-size: 60px;
            color: #6F4E37;
            margin-bottom: 20px;
        }
        .confirmation-header h1 {
            color: #6F4E37;
            margin-bottom: 15px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
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
        .order-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-detail:last-child {
            border-bottom: none;
        }
        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item-image {
            width: 70px;
            height: 70px;
            object-fit: contain;
            background-color: #f9f9f9;
            padding: 5px;
            border-radius: 5px;
            margin-right: 15px;
        }
        .order-total {
            font-size: 1.2rem;
            font-weight: 700;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            margin-top: 15px;
        }
        .btn-primary {
            background-color: #6F4E37;
            border-color: #6F4E37;
            padding: 12px 25px;
        }
        .btn-primary:hover {
            background-color: #5a3d2a;
            border-color: #5a3d2a;
        }
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #6F4E37;
            border-color: #6F4E37;
            box-shadow: none;
        }
        .btn-outline-secondary {
            color: #6F4E37;
            border-color: #6F4E37;
        }
        .btn-outline-secondary:hover {
            background-color: #6F4E37;
            color: white;
        }
        .btn-outline-secondary:focus,
        .btn-outline-secondary:active {
            background-color: #6F4E37;
            color: white;
            border-color: #6F4E37;
            box-shadow: none;
        }
        .alert-success {
            background-color: rgba(234, 231, 222, 0.5);
            border: 2px solid #30271C;
            color: #30271C;
            border-radius: 8px;
            padding: 15px;
        }
        .alert-success i {
            color: #30271C;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container confirmation-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="confirmation-header">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <h1>Order Confirmed!</h1>
            <p class="lead">Thank you for your order. Your order has been placed successfully.</p>
            <p><strong>Order Number:</strong> <?= $order['order_number'] ?></p>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="order-detail">
                            <strong>Order Date:</strong>
                            <span><?= date('d F Y H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="order-detail">
                            <strong>Payment Method:</strong>
                            <span><?= isset($order['payment_method']) ? ucfirst(str_replace('_', ' ', $order['payment_method'])) : 'Not specified' ?></span>
                        </div>
                        <div class="order-detail">
                            <strong>Payment Status:</strong>
                            <span><?= isset($order['payment_status']) ? ucfirst($order['payment_status']) : 'Pending' ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="order-detail">
                            <strong>Shipping Address:</strong>
                            <span><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></span>
                        </div>
                        <div class="order-detail">
                            <strong>Notes:</strong>
                            <span><?= !empty($order['notes']) ? nl2br(htmlspecialchars($order['notes'])) : 'No notes' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                            <div class="order-item">
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/products/<?= htmlspecialchars($item['product_image']) ?>" 
                                         alt="<?= htmlspecialchars($item['product_name']) ?>"
                                         class="order-item-image">
                                <?php else: ?>
                                    <img src="<?= BASE_URL ?>/assets/images/placeholder.png" 
                                         alt="No Image"
                                         class="order-item-image">
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h6><?= htmlspecialchars($item['product_name']) ?></h6>
                                    <div class="d-flex justify-content-between">
                                        <span>Qty: <?= $item['quantity'] ?></span>
                                        <span>Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <div class="order-total text-end">
                            Total: Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/pages/products/category.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-shopping-bag me-1"></i> Continue Shopping
            </a>
            <a href="<?= BASE_URL ?>/pages/user/orders.php" class="btn btn-primary">
                <i class="fas fa-list-alt me-1"></i> My Orders
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
