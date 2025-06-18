<?php
require_once "../../config/init.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['info_message'] = "Please login to view your orders.";
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's orders
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Djaya Roasters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin-top: 80px;
        }
        .orders-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .orders-title {
            color: #6F4E37;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .order-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        .order-body {
            padding: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cff4fc; color: #055160; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container orders-container">
        <h1 class="orders-title">My Orders</h1>
        
        <?php if (mysqli_num_rows($orders_result) == 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                <h3>No Orders Found</h3>
                <p class="text-muted">You haven't placed any orders yet.</p>
                <a href="<?= BASE_URL ?>/pages/products/category.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart me-2"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>Order #<?= $order['order_number'] ?></strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <span class="status-badge status-<?= $order['order_status'] ?>">
                                    <?= ucfirst($order['order_status']) ?>
                                </span>
                            </div>
                            <div class="col-md-3 text-end">
                                <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="order-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Shipping Address:</h6>
                                <p class="small"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Payment Status:</h6>
                                <p class="small"><?= ucfirst($order['payment_status']) ?></p>
                                <?php if (!empty($order['notes'])): ?>
                                    <h6>Notes:</h6>
                                    <p class="small"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/pages/products/category.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Continue Shopping
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
