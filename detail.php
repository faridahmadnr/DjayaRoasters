<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fix potential path issues with absolute paths
$rootPath = $_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters';
require_once $rootPath . "/config/init.php";
require_once $rootPath . "/config/cart_functions.php";

// Check login status
$isLoggedIn = isset($_SESSION['user_id']);
$redirectToLogin = false;

// Get product ID from URL and sanitize it
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Debug output (remove in production)
echo "<!-- Debug: Product ID = $product_id -->";

// If no valid ID provided, redirect to category page
if ($product_id <= 0) {
    header("Location: category.php");
    exit;
}

// Fetch product details with safer query
$query = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    // Product not found
    header("Location: category.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

// Handle form submission if user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$isLoggedIn) {
        // Store intended redirect in session
        $_SESSION['redirect_after_login'] = "pages/products/detail.php?id=$product_id";
        $_SESSION['info_message'] = "Please login to add products to your cart.";
        $redirectToLogin = true;
    } else {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if (addToCart($product_id, $quantity)) {
            $_SESSION['success_message'] = "Product added to cart successfully!";
            header("Location: ".$_SERVER['REQUEST_URI']);
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to add product to cart.";
        }
    }
}

// Redirect to login if needed
if ($redirectToLogin) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?> - Djaya Roasters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .product-container {
            margin-top: 100px;
            padding: 30px;
        }
        .product-image {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        .product-image img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
        }
        .product-details {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .product-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #30271C;
        }
        .product-category {
            display: inline-block;
            background-color: #30271C;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #30271C;
            margin-bottom: 20px;
        }
        .product-description {
            margin: 20px 0;
            line-height: 1.6;
        }
        .btn-add-cart {
            border : 1px solid #30271C;
            color: 30271C;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-add-cart:hover {
            background-color: #30271C;
            color: white;
        }
        .btn-add-cart:focus,
        .btn-add-cart:active {
            background-color: #30271C;
            color: white;
            box-shadow: none;
            outline: none;
        }
        .stock-info {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .in-stock {
            color: #30271C;
        }
        .low-stock {
            color:rgba(48, 39, 28, 0.81);
        }
        .out-of-stock {
            color: red;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .quantity-selector button {
            background-color: #30271C;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .quantity-selector button:focus {
            background-color: #30271C;
            box-shadow: none;
            outline: none;
        }
        .quantity-selector input {
            width: 50px;
            height: 30px;
            text-align: center;
            font-size: 16px;
            border: 1px solid #ddd;
            margin: 0 10px;
        }
        .quantity-selector input:focus {
            border-color: #30271C;
            box-shadow: none;
            outline: none;
        }
        .related-products {
            margin-top: 50px;
        }
        .related-products h3 {
            color: #30271C;
            margin-bottom: 20px;
        }
        .card {
            height: 100%;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        
        /* Add new styles for toast notification */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 15px;
            z-index: 1050;
        }
        .toast {
            background-color: rgba(255, 255, 255, 0.9);
            border-left: 4px solid #6F4E37;
        }
        .toast-success {
            border-left-color: #F2E2D9;
        }
        .toast-error {
            border-left-color: #dc3545;
        }
        
        /* Auth prompt */
        .auth-prompt {
            background-color: #f8f9fa;
            border: 1px solid #e2e6ea;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .auth-prompt p {
            margin-bottom: 10px;
        }
        
        .auth-prompt .btn {
            background-color: #6F4E37;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .auth-prompt .btn:hover {
            background-color: #5a3d2a;
        }

        /* Alert messages */
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
        }
        
        .alert-success {
            background-color: #F2E2D9;
            color: #5a3d2a;
            border: 1px solid #5a3d2a;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .product-container {
                padding: 15px;
            }
            .product-title {
                font-size: 24px;
            }
            .product-price {
                font-size: 20px;
            }
            .product-image {
                padding: 10px;
            }
            .product-details {
                padding: 20px;
            }
            .related-products .card-img-top {
                height: 140px;
            }
        }
        
        @media (max-width: 575.98px) {
            .product-container {
                margin-top: 80px;
                padding: 10px;
            }
            .product-title {
                font-size: 20px;
            }
            .quantity-selector {
                justify-content: center;
            }
            .btn-add-cart {
                width: 100%;
                padding: 10px;
            }
            .card-title {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <?php include $rootPath . '/includes/navbar.php'; ?>
    
    <div class="container product-container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-5">
                <div class="product-image">
                    <?php if (!empty($product['product_image'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/products/<?= htmlspecialchars($product['product_image']) ?>" 
                             alt="<?= htmlspecialchars($product['product_name']) ?>">
                    <?php else: ?>
                        <img src="<?= BASE_URL ?>/assets/images/placeholder.png" alt="No Image Available">
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-7">
                <div class="product-details">
                    <div class="product-category">
                        <?= htmlspecialchars($product['category']) ?>
                    </div>
                    <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
                    <div class="product-price">
                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                    </div>
                    
                    <div class="stock-info">
                        <?php if ($product['stock'] > 10): ?>
                            <span class="in-stock"><i class="fas fa-check-circle"></i> Stok Tersedia (<?= $product['stock'] ?>)</span>
                        <?php elseif ($product['stock'] > 0): ?>
                            <span class="low-stock"><i class="fas fa-exclamation-circle"></i> Stok Terbatas (<?= $product['stock'] ?>)</span>
                        <?php else: ?>
                            <span class="out-of-stock"><i class="fas fa-times-circle"></i> Stok Habis</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <?php if ($isLoggedIn): ?>
                            <!-- User is logged in, show add to cart form -->
                            <form method="post" action="">
                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                
                                <div class="quantity-selector">
                                    <button type="button" onclick="decreaseQuantity()">-</button>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                                    <button type="button" onclick="increaseQuantity(<?= $product['stock'] ?>)">+</button>
                                </div>
                                
                                <button type="submit" name="add_to_cart" class="btn-add-cart">
                                    <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- User is not logged in, show login prompt -->
                            <div class="auth-prompt">
                                <p>Silakan login untuk menambahkan produk ke keranjang.</p>
                                <a href="<?= BASE_URL ?>/auth/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn">
                                    <i class="fas fa-sign-in-alt"></i> Login untuk Berbelanja
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn-add-cart" disabled style="background-color: #ccc; cursor: not-allowed">
                            <i class="fas fa-shopping-cart"></i> Stok Habis
                        </button>
                    <?php endif; ?>
                    
                    <div class="product-description">
                        <h4>Deskripsi:</h4>
                        <?php if (!empty($product['description'])): ?>
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        <?php else: ?>
                            <p>Tidak ada deskripsi untuk produk ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <div class="related-products">
            <h3>Produk Serupa</h3>
            <div class="row">
                <?php
                // Fetch related products (same category, different product)
                $related_query = "SELECT * FROM products 
                                 WHERE category = '".mysqli_real_escape_string($conn, $product['category'])."' 
                                 AND id != $product_id 
                                 LIMIT 4";
                $related_result = mysqli_query($conn, $related_query);
                
                if (mysqli_num_rows($related_result) > 0) {
                    while ($related = mysqli_fetch_assoc($related_result)) {
                        ?>
                        <div class="col-md-3 col-6 mb-4">
                            <div class="card h-100">
                                <div style="height: 180px; display: flex; align-items: center; justify-content: center; padding: 15px;">
                                    <?php if (!empty($related['product_image'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/products/<?= htmlspecialchars($related['product_image']) ?>" 
                                            class="card-img-top" style="max-height: 100%; object-fit: contain;" 
                                            alt="<?= htmlspecialchars($related['product_name']) ?>">
                                    <?php else: ?>
                                        <img src="<?= BASE_URL ?>/assets/images/placeholder.png" 
                                            class="card-img-top" style="max-height: 100%; object-fit: contain;"
                                            alt="No Image Available">
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($related['product_name']) ?></h5>
                                    <p class="card-text">Rp <?= number_format($related['price'], 0, ',', '.') ?></p>
                                    <a href="detail.php?id=<?= $related['id'] ?>" class="btn btn-sm btn-outline-secondary w-100">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='col-12'><p>Tidak ada produk serupa saat ini.</p></div>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function decreaseQuantity() {
            const quantityElement = document.getElementById('quantity');
            let quantity = parseInt(quantityElement.value);
            if (quantity > 1) {
                quantityElement.value = quantity - 1;
            }
        }
        
        function increaseQuantity(maxStock) {
            const quantityElement = document.getElementById('quantity');
            let quantity = parseInt(quantityElement.value);
            if (quantity < maxStock) {
                quantityElement.value = quantity + 1;
            }
        }
    </script>
</body>
</html>
