<?php
require_once '../../config/init.php';
require_once '../../config/cart_functions.php';

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    $_SESSION['info_message'] = "Please login to view your cart.";
    $_SESSION['redirect_after_login'] = '/pages/cart/cart.php';
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// Get cart items
$cart = getCartItems();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Djaya Roasters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin-top: 80px;
        }
        .cart-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .cart-title {
            color: #6F4E37;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
        }
        .cart-empty {
            text-align: center;
            padding: 50px 20px;
        }
        .cart-empty i {
            font-size: 5rem;
            color: #d0d0d0;
            margin-bottom: 20px;
            display: block;
        }
        .cart-empty p {
            font-size: 1.2rem;
            color: #888;
            margin-bottom: 30px;
        }
        
        /* Responsive table styles */
        .cart-table {
            width: 100%;
        }
        .cart-table th {
            background-color: #f5f5f5;
            padding: 15px;
            text-align: left;
            color: #555;
            font-weight: 600;
        }
        .cart-table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        
        @media (max-width: 767.98px) {
            .cart-container {
                padding: 15px;
                margin: 15px;
            }
            
            .cart-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .cart-table th, 
            .cart-table td {
                padding: 10px;
            }
            
            .product-name {
                max-width: 150px;
                white-space: normal;
            }
        }
        
        @media (max-width: 575.98px) {
            .cart-container {
                padding: 10px;
                margin: 10px;
            }
            
            .product-name {
                max-width: 120px;
                font-size: 14px;
            }
            
            .continue-shopping {
                display: block;
                text-align: center;
                margin-top: 15px;
            }
        }
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background-color: #f9f9f9;
            padding: 5px;
            border-radius: 5px;
        }
        .product-name {
            font-weight: 600;
            color: #333;
        }
        .product-price {
            color: #6F4E37;
            font-weight: 600;
        }
        .quantity-control {
            display: flex;
            align-items: center;
        }
        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #ddd;
            background-color: #f5f5f5;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .quantity-btn:hover {
            background-color: #e5e5e5;
        }
        .quantity-btn:focus {
            background-color: #e5e5e5;
            outline: none;
            box-shadow: none;
        }
        .quantity-input {
            width: 45px;
            height: 32px;
            text-align: center;
            border: 1px solid #ddd;
            margin: 0 5px;
            border-radius: 4px;
        }
        .quantity-input:focus {
            border-color: #6F4E37;
            outline: none;
            box-shadow: none;
        }
        .remove-btn {
            border: none;
            background-color: transparent;
            color: #6F4E37;
            cursor: pointer;
            transition: color 0.2s;
        }
        .remove-btn:hover {
            color: #30271C;
        }
        .remove-btn:focus {
            color: none;
            outline: none;
        }
        .cart-summary {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        .cart-summary-title {
            color: #6F4E37;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e5e5;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1rem;
            color: #555;
        }
        .summary-row.total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #333;
            border-top: 1px solid #e5e5e5;
            padding-top: 10px;
            margin-top: 10px;
        }
        .checkout-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #6F4E37;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .checkout-btn:hover {
            background-color: #5a3d2a;
        }
        .checkout-btn:focus,
        .checkout-btn:active {
            background-color: #6F4E37;
            box-shadow: none;
            outline: none;
        }
        .continue-shopping {
            display: inline-block;
            margin-top: 20px;
            color: #6F4E37;
            text-decoration: none;
        }
        .continue-shopping:hover {
            text-decoration: underline;
        }
        .continue-shopping:focus {
            color: #6F4E37;
            text-decoration: underline;
            outline: none;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
            display: none;
        }
        .alert {
            display: none;
            margin-top: 20px;
            border: 2px solid #30271C;
            background-color: rgba(234, 231, 222, 0.5);
            color: #30271C;
            border-radius: 8px;
            padding: 15px;
        }
        .alert.alert-success {
            border-color: #30271C;
            background-color: rgba(234, 231, 222, 0.5);
            color: #30271C;
        }
        .alert.alert-danger {
            border-color: #30271C;
            background-color: rgba(234, 231, 222, 0.5);
            color: #30271C;
        }
        .alert i {
            color: #30271C;
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container cart-container">
        <h1 class="cart-title">Keranjang Belanja</h1>
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> 
                <?= $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> 
                <?= $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart['items'])): ?>
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <p>Keranjang belanja Anda kosong.</p>
                <a href="<?= BASE_URL ?>/pages/products/category.php" class="btn btn-outline-secondary">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart['items'] as $item): ?>
                                <tr data-id="<?= $item['id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['product_image'])): ?>
                                                <img src="<?= BASE_URL ?>/uploads/products/<?= htmlspecialchars($item['product_image']) ?>" 
                                                     alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                     class="product-img me-3">
                                            <?php else: ?>
                                                <img src="<?= BASE_URL ?>/assets/images/placeholder.png" 
                                                     alt="No Image" 
                                                     class="product-img me-3">
                                            <?php endif; ?>
                                            
                                            <div>
                                                <a href="<?= BASE_URL ?>/pages/products/detail.php?id=<?= $item['product_id'] ?>" 
                                                   class="product-name"><?= htmlspecialchars($item['product_name']) ?></a>
                                                <div class="small text-muted">Stok: <?= $item['stock'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="product-price">
                                            Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="quantity-control">
                                            <button type="button" class="quantity-btn decrease-qty" data-id="<?= $item['id'] ?>" <?= ($item['quantity'] <= 1) ? 'disabled' : '' ?>>
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" 
                                                   class="quantity-input" data-id="<?= $item['id'] ?>" readonly>
                                            <button type="button" class="quantity-btn increase-qty" data-id="<?= $item['id'] ?>" <?= ($item['quantity'] >= $item['stock']) ? 'disabled' : '' ?>>
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <div class="spinner-border text-secondary ms-2" role="status" id="spinner-<?= $item['id'] ?>">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="product-price subtotal" data-id="<?= $item['id'] ?>">
                                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="remove-btn" data-id="<?= $item['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="alert alert-success" id="updateSuccess">
                        <i class="fas fa-check-circle me-2"></i> Keranjang berhasil diperbarui
                    </div>
                    
                    <div class="alert alert-danger" id="updateError">
                        <i class="fas fa-exclamation-circle me-2"></i> Gagal memperbarui keranjang
                    </div>
                    
                    <a href="<?= BASE_URL ?>/pages/products/category.php" class="continue-shopping">
                        <i class="fas fa-arrow-left me-2"></i> Lanjutkan Belanja
                    </a>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="cart-summary-title">Ringkasan Pesanan</h4>
                        <div class="summary-row">
                            <span>Subtotal (<?= $cart['count'] ?> item):</span>
                            <span id="cart-subtotal">Rp <?= number_format($cart['total'], 0, ',', '.') ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Biaya Pengiriman:</span>
                            <span>Dihitung saat checkout</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="cart-total">Rp <?= number_format($cart['total'], 0, ',', '.') ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>/pages/cart/checkout.php" class="checkout-btn">
                            <i class="fas fa-shopping-bag me-2"></i> Lanjut ke Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update quantity functions
            const decreaseBtns = document.querySelectorAll('.decrease-qty');
            const increaseBtns = document.querySelectorAll('.increase-qty');
            const removeBtns = document.querySelectorAll('.remove-btn');
            
            decreaseBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const qtyInput = document.querySelector(`.quantity-input[data-id="${id}"]`);
                    let quantity = parseInt(qtyInput.value);
                    
                    if (quantity > 1) {
                        quantity--;
                        qtyInput.value = quantity;
                        updateCartItem(id, quantity);
                    }
                });
            });
            
            increaseBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const qtyInput = document.querySelector(`.quantity-input[data-id="${id}"]`);
                    let quantity = parseInt(qtyInput.value);
                    const maxStock = parseInt(qtyInput.getAttribute('max'));
                    
                    if (quantity < maxStock) {
                        quantity++;
                        qtyInput.value = quantity;
                        updateCartItem(id, quantity);
                    }
                });
            });
            
            removeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    removeCartItem(id);
                });
            });
            
            // Function to update cart item
            function updateCartItem(id, quantity) {
                showSpinner(id, true);
                
                fetch('<?= BASE_URL ?>/pages/cart/update-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}&quantity=${quantity}&action=update`
                })
                .then(response => response.json())
                .then(data => {
                    showSpinner(id, false);
                    
                    if (data.success) {
                        // Update subtotal
                        const subtotalElement = document.querySelector(`.subtotal[data-id="${id}"]`);
                        subtotalElement.textContent = `Rp ${formatNumber(data.item_subtotal)}`;
                        
                        // Update cart summary
                        document.getElementById('cart-subtotal').textContent = `Rp ${formatNumber(data.cart_total)}`;
                        document.getElementById('cart-total').textContent = `Rp ${formatNumber(data.cart_total)}`;
                        
                        // Update cart count in navbar
                        updateCartCount(data.cart_count);
                        
                        // Show success message
                        showAlert('updateSuccess');
                        
                        // Enable/disable buttons based on new quantity
                        const decreaseBtn = document.querySelector(`.decrease-qty[data-id="${id}"]`);
                        const increaseBtn = document.querySelector(`.increase-qty[data-id="${id}"]`);
                        
                        decreaseBtn.disabled = (quantity <= 1);
                        increaseBtn.disabled = (quantity >= data.item_stock);
                    } else {
                        showAlert('updateError');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showSpinner(id, false);
                    showAlert('updateError');
                });
            }
            
            // Function to remove cart item
            function removeCartItem(id) {
                showSpinner(id, true);
                
                fetch('<?= BASE_URL ?>/pages/cart/update-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}&action=remove`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row from table
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        row.remove();
                        
                        // Update cart summary
                        document.getElementById('cart-subtotal').textContent = `Rp ${formatNumber(data.cart_total)}`;
                        document.getElementById('cart-total').textContent = `Rp ${formatNumber(data.cart_total)}`;
                        
                        // Update cart count in navbar
                        updateCartCount(data.cart_count);
                        
                        // Show success message
                        showAlert('updateSuccess');
                        
                        // If cart is empty, reload the page to show empty cart message
                        if (data.cart_count === 0) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    } else {
                        showAlert('updateError');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('updateError');
                });
            }
            
            // Helper functions
            function showSpinner(id, show) {
                const spinner = document.getElementById(`spinner-${id}`);
                spinner.style.display = show ? 'inline-block' : 'none';
            }
            
            function showAlert(id) {
                const alert = document.getElementById(id);
                alert.style.display = 'block';
                
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 3000);
            }
            
            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }
            
            function updateCartCount(count) {
                const cartCountElement = document.getElementById('cartCount');
                if (cartCountElement) {
                    cartCountElement.textContent = count;
                    
                    if (count > 0) {
                        cartCountElement.style.display = 'inline-block';
                    } else {
                        cartCountElement.style.display = 'none';
                    }
                }
            }
        });
    </script>
</body>
</html>
