<?php
require_once "../../config/init.php";

// Check login status
$isLoggedIn = isset($_SESSION['user_id']);

// Set the page title
$page_title = "Product Categories";

// Get categories from the database
$categories_query = "SELECT DISTINCT category FROM products ORDER BY category ASC";
$categories_result = mysqli_query($conn, $categories_query);

$categories = [
    'All' => 'SEMUA PRODUK',
    'Classic Coffee' => 'CLASSIC COFFEE',
    'Filter Coffee' => 'FILTER COFFEE',
    'Speciality' => 'SPECIALITY',
    'Drip Bag' => 'DRIP BAG',
    'Coffee Gems' => 'COFFEE GEMS',
    'Merchandise' => 'MERCHANDISE'
];

// Get selected category from URL
$selected_category = isset($_GET['jenis']) ? $_GET['jenis'] : 'All';

// Add custom CSS
$custom_css = '
<style>
    .category-container {
        padding-top: 100px;
    }
    
    .product-categories {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    
    .product-categories a {
        padding: 10px 20px;
        margin: 5px;
        text-decoration: none;
        color: #30271C;
        border: 1px solid #30271C;
        border-radius: 20px;
        transition: background-color 0.3s, color 0.3s;
    }
    
    .product-categories a:hover,
    .product-categories a.active {
        background-color: #30271C;
        color: white;
    }
    
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }
    
    .product-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    
    .product-card-link:hover {
        text-decoration: none;
        color: inherit;
    }
    
    .product-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        text-align: center;
        padding: 15px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    
    .product-card img {
        max-width: 100%;
        height: 200px; 
        object-fit: contain; 
        margin-bottom: 10px;
        background-color: #F2E2D9; 
        padding: 10px; 
        border-radius: 4px;
        transition: transform 0.3s ease;
    }
    
    .product-card:hover img {
        transform: scale(1.05);
    }
    
    .product-card h3 {
        margin: 10px 0;
        color: #333;
        font-size: 1.2em;
    }
    
    .product-card .price {
        font-size: 1.1em;
        font-weight: bold;
        color: #30271C;
        margin-bottom: 10px;
    }
    
    .product-card .view-details {
        display: inline-block;
        padding: 8px 15px;
        background-color: #30271C;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        margin-top: auto;
    }
    
    .product-card:hover .view-details {
        background-color: #CAA782;
    }
    
    /* Login prompt */
    .login-prompt {
        background-color: #f8f9fa;
        border: 1px solid #e2e6ea;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .login-prompt p {
        margin-bottom: 10px;
    }
    
    .login-btn {
        display: inline-block;
        padding: 8px 15px;
        background-color: #6F4E37;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    
    .login-btn:hover {
        background-color: #5a3d2a;
        color: white;
        text-decoration: none;
    }
    .login-btn:focus {
        background-color: #5a3d2a;
        color: white;
        text-decoration: none;
        box-shadow: none;
        outline: none;
    }
    .product-categories a:focus {
        background-color: #30271C;
        color: white;
        outline: none;
    }
    
    .category-header {
        background-color: #30271C;
        color: white;
        padding: 15px 0;
        margin-bottom: 30px;
        border-radius: 8px;
        text-align: center;
    }
    
    .category-header h1 {
        margin: 0;
        font-size: 2rem;
        font-name: oswald, sans-serif;
        font-weight: bold;
    }
    
    .no-products {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        color: #666;
    }
    
    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .product-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
    }
    
    @media (max-width: 767.98px) {
        .category-container {
            padding-top: 80px;
        }
        .product-categories a {
            padding: 8px 15px;
            font-size: 14px;
        }
        .category-header h1 {
            font-size: 1.75rem;
        }
        .product-card img {
            height: 150px;
        }
        .product-card h3 {
            font-size: 1em;
        }
    }
    
    @media (max-width: 575.98px) {
        .category-container {
            padding: 70px 10px 20px;
        }
        .product-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
        }
        .product-card {
            padding: 10px;
        }
        .product-card img {
            height: 120px;
            padding: 5px;
        }
        .product-card .price {
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .product-card .view-details {
            padding: 5px 10px;
            font-size: 0.9em;
        }
        .product-card h3 {
            font-size: 0.9em;
            margin: 5px 0;
        }
        .category-header h1 {
            font-size: 1.5rem;
        }
    }
</style>
';

// Include header file
include_once "../../includes/header.php";
?>

<div class="container category-container">
    <?php if ($selected_category != 'All'): ?>
        <div class="category-header">
            <h1><?= htmlspecialchars($selected_category) ?></h1>
        </div>
    <?php else: ?>
        <div class="category-header">
            <h1>All Products</h1>
        </div>
    <?php endif; ?>
    
    <?php if (!$isLoggedIn): ?>
        <div class="login-prompt">
            <p><i class="fas fa-info-circle"></i> Untuk menambahkan produk ke keranjang, silakan login terlebih dahulu.</p>
            <a href="<?= BASE_URL ?>/auth/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    <?php endif; ?>
    
    <div class="product-categories">
        <?php foreach ($categories as $value_in_db => $label_for_display): ?>
            <?php $active_class = ($selected_category == $value_in_db) ? 'active' : ''; ?>
            <a href="category.php?jenis=<?= urlencode($value_in_db) ?>" class="<?= $active_class ?>">
                <?= $label_for_display ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="product-grid">
        <?php
        // Build the query based on selected category
        $query = "SELECT * FROM products";
        
        if ($selected_category != 'All') {
            $query .= " WHERE category = '" . mysqli_real_escape_string($conn, $selected_category) . "'";
        }
        
        $query .= " ORDER BY product_name ASC";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                ?>
                <a href="detail.php?id=<?= $row['id'] ?>" class="product-card-link">
                    <div class="product-card">
                        <?php if (!empty($row['product_image'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/products/<?= htmlspecialchars($row['product_image']) ?>" 
                                 alt="<?= htmlspecialchars($row['product_name']) ?>">
                        <?php else: ?>
                            <img src="<?= BASE_URL ?>/assets/images/placeholder.png" alt="No Image Available">
                        <?php endif; ?>
                        
                        <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                        <div class="price">Rp <?= number_format($row['price'], 0, ',', '.') ?></div>
                        <span class="view-details">View Details</span>
                    </div>
                </a>
                <?php
            }
        } else {
            ?>
            <div class="no-products">
                <i class="fas fa-box-open fa-3x mb-3"></i>
                <h3>No products found</h3>
                <p>There are currently no products available in this category.</p>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php
// Add custom JavaScript if needed
$custom_js = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Add any category-specific JavaScript functionality here
});
</script>
';

// Include footer file
include_once "../../includes/footer.php";
?>
