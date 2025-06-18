<?php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/config/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/config/init.php';
} else {
    // Fallback if init.php isn't available
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/config/config.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/config/config.php';
    } else {
        define('BASE_URL', '/djaya_roasters');
        define('SITE_NAME', 'Djaya Roasters');
    }
}

// Load cart functions if they exist and the user is logged in
$cart_count = 0;
if (isset($_SESSION['user_id']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/config/cart_functions.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/config/cart_functions.php';
    $cart_count = getCartCount();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn && isset($_SESSION['username']) ? $_SESSION['username'] : '';
$isAdmin = $isLoggedIn && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';

// Determine current page
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- Google Fonts untuk Oswald -->
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&display=swap" rel="stylesheet">

<style>
/* Custom CSS untuk navbar */
.navbar-custom {
    transition: all 0.3s ease;
    padding: 15px 0;
    font-family: 'Oswald', sans-serif;
    font-weight: 700;
}

/* Navbar style - always dark */
.navbar {
    background-color: #000000 !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    transition: transform 0.3s ease;
}

/* Hide navbar when scrolling down */
.navbar-hidden {
    transform: translateY(-100%);
}

/* Logo styling */
.navbar-brand img {
    height: 40px;
    transition: all 0.3s ease;
}

/* Menu items styling */
.navbar-nav .nav-link {
    color: white !important;
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    margin: 0 15px;
    padding: 8px 0 !important;
    position: relative;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: #ffd700 !important;
}
.navbar-nav .nav-link:focus {
    color: #ffd700 !important;
    outline: none;
    box-shadow: none;
}

/* Active page indicator */
.navbar-nav .nav-link.active {
    color: #ffd700 !important;
}

/* Underline effect on hover */
.navbar-nav .nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 50%;
    background-color: #ffd700;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.navbar-nav .nav-link:hover::after,
.navbar-nav .nav-link:focus::after {
    width: 100%;
}

/* Icons styling */
.navbar-icons .nav-link {
    padding: 8px 10px !important;
    margin: 0 5px;
}

.navbar-icons .nav-link i {
    font-size: 18px;
}
.navbar-icons .nav-link:focus {
    color: #ffd700 !important;
    outline: none;
    box-shadow: none;
}

/* User greeting */
.user-greeting {
    color: #ffd700;
    font-size: 14px;
    margin-right: 10px;
    display: flex;
    align-items: center;
}

.user-greeting i {
    margin-right: 5px;
}

.dropdown-menu {
    background-color: rgba(0, 0, 0, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 10px;
}

.dropdown-item {
    color: #ffffff;
    transition: all 0.2s;
    padding: 8px 20px;
}

.dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffd700;
}
.dropdown-item:focus {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffd700;
    outline: none;
    box-shadow: none;
}

.dropdown-divider {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* Cart badge */
.cart-badge {
    position: relative;
}
.cart-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #ffd700;
    color: #000;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
}

/* Mobile menu styling */
.navbar-toggler {
    border: none;
    padding: 4px 8px;
}

.navbar-toggler:focus {
    box-shadow: none;
    outline: none;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .navbar-collapse {
        background-color: #000000;
        margin-top: 15px;
        padding: 20px;
        border-radius: 10px;
    }
    
    .navbar-nav .nav-link {
        margin: 5px 0;
        text-align: center;
    }
    
    .user-greeting {
        justify-content: center;
        margin: 10px 0;
    }
}
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" id="mainNavbar">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>/assets/images/logo1.png" alt="<?= SITE_NAME ?>" class="img-fluid">
        </a>

        <!-- Mobile menu toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index' || $current_page == 'home') ? 'active' : '' ?>" 
                      href="<?= BASE_URL ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'about') ? 'active' : '' ?>" 
                      href="<?= BASE_URL ?>#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'category') ? 'active' : '' ?>" 
                      href="<?= BASE_URL ?>/pages/products/category.php">Product</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'gallery') ? 'active' : '' ?>" 
                      href="<?= BASE_URL ?>#gallery">Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'maps') ? 'active' : '' ?>" 
                      href="<?= BASE_URL ?>/#maps">Maps</a>
                </li>
            </ul>

            <!-- Icons -->
            <ul class="navbar-nav navbar-icons">
                <li class="nav-item">
                    <a class="nav-link" href="#" title="Search">
                        <i class="fas fa-search"></i>
                    </a>
                </li>
                
                <?php if ($isLoggedIn): ?>
                    <!-- User is logged in -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                            <span class="user-greeting"><i class="fas fa-user-circle"></i> Hello, <?= htmlspecialchars($username) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <?php if ($isAdmin): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard.php">Admin Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php else: ?>
                                <!-- <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/user/profile.php">My Profile</a></li> -->
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/user/orders.php">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="confirmLogout()">Logout</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link cart-badge" href="<?= BASE_URL ?>/pages/cart/cart.php" title="Shopping Cart">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count" id="cartCount"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php else: ?>
                    <!-- User is not logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/auth/login.php" title="Login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- JavaScript for navbar scroll effect -->
<script>
// Run immediately for proper navbar behavior
(function() {
    const navbar = document.getElementById('mainNavbar');
    let lastScrollY = window.scrollY;
    
    function updateNavbar() {
        const currentScrollY = window.scrollY;
        const isScrollingDown = currentScrollY > lastScrollY;
        
        // Hide navbar when scrolling down
        if (isScrollingDown && currentScrollY > 50) {
            navbar.classList.add('navbar-hidden');
        } else {
            // Show navbar when scrolling up
            navbar.classList.remove('navbar-hidden');
        }
        
        lastScrollY = currentScrollY;
    }
    
    // Update navbar on scroll
    window.addEventListener('scroll', updateNavbar);
})();

// Logout confirmation function
function confirmLogout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '<?= BASE_URL ?>/auth/logout.php';
    }
}
</script>