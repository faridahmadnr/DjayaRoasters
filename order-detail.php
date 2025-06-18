<?php
require_once '../../config/init.php';
protectAdminPage();

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    $_SESSION['error_message'] = "Invalid order ID.";
    header('Location: orders.php');
    exit;
}

// Get order details
$order_query = "SELECT o.*, u.username, u.email 
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = $order_id";
$order_result = mysqli_query($conn, $order_query);

if (!$order_result || mysqli_num_rows($order_result) == 0) {
    $_SESSION['error_message'] = "Order not found.";
    header('Location: orders.php');
    exit;
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_query = "SELECT oi.*, p.nama_produk, p.gambar_produk
                FROM order_items oi
                JOIN produk p ON oi.product_id = p.id
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

// Process status update if submitted
if (isset($_POST['update_status'])) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE orders SET order_status = '$status' WHERE id = $order_id";
    $update_result = mysqli_query($conn, $update_query);
    
    if ($update_result) {
        $order['order_status'] = $status; // Update in current view
        $_SESSION['success_message'] = "Order status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to update order status: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail #<?= htmlspecialchars($order['order_number']) ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px 0;
        }
        .main-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #6F4E37;
            margin-bottom: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #6F4E37;
            color: white;
            font-weight: bold;
            border-radius: 10px 10px 0 0 !important;
        }
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        .status-processing {
            background-color: #b8daff;
            color: #004085;
        }
        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f5c6cb;
            color: #721c24;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        .order-detail-row {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
        }
        .order-detail-row:last-child {
            border-bottom: none;
        }
        .order-detail-label {
            font-weight: bold;
            min-width: 150px;
            color: #555;
            margin-bottom: 5px;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
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
            margin-bottom: 10px;
        }
        .order-total {
            font-weight: bold;
            font-size: 1.2rem;
            text-align: right;
            padding: 15px;
            border-top: 2px solid #eee;
        }
        .pre-wrap {
            white-space: pre-wrap;
        }
        .btn-primary {
            background-color: #30271C;
            border-color: #30271C;
        }
        .btn-primary:hover {
            background-color: #CAA782;
            border-color: #CAA782;
        }
        .btn-outline-primary {
            color: #30271C;
            border-color: #30271C;
        }
        .btn-outline-primary:hover {
            background-color: #30271C;
            border-color: #30271C;
            color: white;
        }
        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
        .btn-secondary:focus,
        .btn-secondary:active,
        .btn-secondary.active {
            background-color: #6c757d;
            border-color: #6c757d;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-outline-primary:focus,
        .btn-outline-primary:active,
        .btn-outline-primary.active {
            color: #30271C;
            border-color: #30271C;
            background-color: #EAE7DE;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-outline-secondary:focus,
        .btn-outline-secondary:active,
        .btn-outline-secondary.active {
            color: #6c757d;
            border-color: #6c757d;
            background-color: #EAE7DE;
            box-shadow: none !important;
            outline: none !important;
        }
        /* Remove all shadow and outline effects */
        *:focus,
        *:active,
        *.active,
        .btn:focus,
        .btn:active,
        .btn.active,
        .form-control:focus,
        .form-select:focus {
            box-shadow: none !important;
            outline: none !important;
        }
        .form-control:focus {
            background-color: #EAE7DE;
            border-color: #30271C;
        }
        .form-select:focus {
            background-color: #EAE7DE;
            border-color: #30271C;
        }
        .form-select {
            background-color: white;
            border-color: #ced4da;
            color: #495057;
        }
        .form-select:hover {
            border-color: #30271C;
        }
        .form-select option {
            background-color: white !important;
            color: #495057 !important;
        }
        .form-select option:hover {
            background-color: #CAA782 !important;
            color: white !important;
        }
        .form-select option:focus {
            background-color: #EAE7DE !important;
            color: #30271C !important;
        }
        .form-select option:checked {
            background-color: #30271C !important;
            color: white !important;
        }
        .form-select option:checked:hover {
            background-color: #CAA782 !important;
            color: white !important;
        }
        @media (max-width: 768px) {
            .main-container {
                padding: 15px;
            }
            .order-detail-row {
                flex-direction: column;
            }
            .order-detail-label {
                min-width: auto;
                width: 100%;
            }
            .order-item {
                flex-direction: column;
                text-align: center;
            }
            .order-item-image {
                margin-right: 0;
                margin-bottom: 10px;
            }
            .order-total {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-file-invoice me-2"></i> Order Detail</h1>
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="main-container">
            <div class="card mb-4">
                <div class="card-header">
                    Order Information
                </div>
                <div class="card-body">
                    <div class="order-detail-row">
                        <div class="order-detail-label">Order Number:</div>
                        <div><?= htmlspecialchars($order['order_number']) ?></div>
                    </div>
                    <div class="order-detail-row">
                        <div class="order-detail-label">Order Date:</div>
                        <div><?= date('d F Y, H:i', strtotime($order['created_at'])) ?></div>
                    </div>
                    <div class="order-detail-row">
                        <div class="order-detail-label">Customer:</div>
                        <div><?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</div>
                    </div>
                    <div class="order-detail-row">
                        <div class="order-detail-label">Total Amount:</div>
                        <div>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></div>
                    </div>
                    <div class="order-detail-row">
                        <div class="order-detail-label">Status:</div>
                        <div>
                            <?php
                            $status_class = '';
                            switch ($order['order_status']) {
                                case 'pending':
                                    $status_class = 'status-pending';
                                    break;
                                case 'processing':
                                    $status_class = 'status-processing';
                                    break;
                                case 'shipped':
                                    $status_class = 'status-shipped';
                                    break;
                                case 'delivered':
                                    $status_class = 'status-delivered';
                                    break;
                                case 'cancelled':
                                    $status_class = 'status-cancelled';
                                    break;
                            }
                            ?>
                            <span class="status-badge <?= $status_class ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </span>
                            <button class="btn btn-sm btn-outline-primary ms-3" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                <i class="fas fa-edit"></i> Update Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            Shipping Address
                        </div>
                        <div class="card-body">
                            <div class="pre-wrap"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            Notes
                        </div>
                        <div class="card-body">
                            <?php if (!empty($order['notes'])): ?>
                                <div class="pre-wrap"><?= nl2br(htmlspecialchars($order['notes'])) ?></div>
                            <?php else: ?>
                                <em>No notes provided</em>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    Order Items
                </div>
                <div class="card-body">
                    <?php
                    $order_total = 0;
                    if ($items_result && mysqli_num_rows($items_result) > 0): 
                        while ($item = mysqli_fetch_assoc($items_result)):
                            $subtotal = $item['quantity'] * $item['price'];
                            $order_total += $subtotal;
                    ?>
                        <div class="order-item">
                            <?php if (!empty($item['gambar_produk'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/products/<?= $item['gambar_produk'] ?>" alt="<?= $item['nama_produk'] ?>" class="order-item-image">
                            <?php else: ?>
                                <img src="<?= BASE_URL ?>/assets/images/placeholder.png" alt="No Image" class="order-item-image">
                            <?php endif; ?>
                            
                            <div class="flex-fill">
                                <h5 class="mb-1"><?= htmlspecialchars($item['nama_produk']) ?></h5>
                                <div class="text-muted">
                                    <?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <div class="fw-bold">Rp <?= number_format($subtotal, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <p class="text-center py-4">No items found in this order.</p>
                    <?php endif; ?>
                    
                    <div class="order-total">
                        Total: Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
                    </div>
                </div>
            </div>
            
            <!-- Print Order button -->
            <div class="text-center mt-4">
                <a href="print-order.php?id=<?= $order_id ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print me-2"></i> Print Order
                </a>
            </div>
            
            <!-- Update Status Modal -->
            <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Order Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" action="">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $order['order_status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $order['order_status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $order['order_status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
