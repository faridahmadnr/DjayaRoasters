<?php
require_once "config/init.php";

// Get top 3 best selling products
$best_sellers_query = "
    SELECT p.*, COALESCE(SUM(oi.quantity), 0) as total_sold
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id AND o.order_status IN ('delivered', 'shipped', 'processing')
    GROUP BY p.id
    ORDER BY total_sold DESC, p.id DESC
    LIMIT 3
";

$best_sellers_result = mysqli_query($conn, $best_sellers_query);
$best_sellers = [];

if ($best_sellers_result && mysqli_num_rows($best_sellers_result) > 0) {
    while ($row = mysqli_fetch_assoc($best_sellers_result)) {
        $best_sellers[] = $row;
    }
}

// If we don't have 3 best sellers from orders, fill with random products
if (count($best_sellers) < 3) {
    $remaining_needed = 3 - count($best_sellers);
    $existing_ids = array_column($best_sellers, 'id');
    $exclude_ids = !empty($existing_ids) ? "WHERE id NOT IN (" . implode(',', $existing_ids) . ")" : "";
    
    $fallback_query = "SELECT *, 0 as total_sold FROM products $exclude_ids ORDER BY RAND() LIMIT $remaining_needed";
    $fallback_result = mysqli_query($conn, $fallback_query);
    
    if ($fallback_result && mysqli_num_rows($fallback_result) > 0) {
        while ($row = mysqli_fetch_assoc($fallback_result)) {
            $best_sellers[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <img src="assets/images/logo1.png" alt="Logo Website" width="120">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Djaya Roaster</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inria+Serif&family=Oswald:wght@700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <!-- Add Fancybox CSS for lightbox effect -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>
    <style>
      body {
        font-family: "Inria Serif", serif;
        color: #333;
      }

      h1,
      h2,
      h3,
      h4,
      h5,
      h6 {
        font-family: "Oswald", sans-serif;
        font-weight: bold;
        text-transform: uppercase;
      }

      .bg-image {
        position: relative;
        background-size: cover;
        background-position: center;
      }

      .bg-overlay {
        position: relative;
      }

      .bg-overlay::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1;
      }

      .bg-overlay .content {
        position: relative;
        z-index: 2;
      }

      /* Hero Section - Fixed for navbar transparency */
      #hero {
        background-image: url("assets/images/hero.jpg");
        height: 100vh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        margin-top: 0; /* Remove negative margin */
        padding-top: 0; /* Remove padding */
        background-size: cover;
        background-position: center;
        z-index: 0; /* Lower than navbar */
      }

      #hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 0;
      }

      #hero .content {
        position: relative;
        z-index: 1;
        color: white;
        max-width: 800px;
        padding: 0 20px;
        margin-top: 70px; /* Add top margin to compensate for navbar height */
      }

      #hero h1 {
        font-size: 4rem;
        margin-bottom: 20px;
      }

      #hero p {
        font-size: 1.2rem;
        margin-bottom: 30px;
      }

      .btn-custom {
        background-color: transparent;
        color: white;
        border: 2px solid white;
        padding: 10px 30px;
        text-transform: uppercase;
        transition: all 0.3s ease;
      }

      .btn-custom:hover {
        background-color: white;
        color: #333;
      }
      .btn-custom:focus,
      .btn-custom:active {
        background-color: white;
        color: #333;
        box-shadow: none;
        outline: none;
      }

      /* About Section */
      #about {
        padding: 100px 0;
        background-color: #EAE7DE;
      }

      #about h2 {
        text-align: center;
        margin-bottom: 40px;
        font-size: 2.5rem;
      }

      #about .about-img {
        border-radius: 5px;
        overflow: hidden;
      }
      
      /* Add styling for the about section paragraph */
      #about p {
        font-family: "oswlad", serif; /* Use serif font for better readability */
        font-size: 1.2rem; /* Larger text */
        font-weight: bold; /* Bold text */
        line-height: 1.6; /* Better line spacing */
      }
      
      /* Add focus states for all links and buttons */
      a:focus {
        outline: none;
        text-decoration: none;
      }
      
      .product-overlay .btn-view:focus {
        background-color: #ffd700;
        color: #333;
        text-decoration: none;
        box-shadow: none;
      }
      
      .social-icons a:focus {
        color: #c7a17a;
        outline: none;
      }
      
      .footer-links a:focus {
        color: #c7a17a;
        outline: none;
      }
      
      .gallery-item:focus {
        outline: none;
      }
      
      .product-item a:focus {
        outline: none;
      }

      /* Best Seller Section */
      #best-seller {
        padding: 80px 0;
        background-color: #333;
        background-image: url(assets/images/bst.jpg);
        background-repeat: no-repeat; /* Prevent image repetition */
        background-size: cover; /* Make the image cover the entire section */
        background-position: center; /* Center the image */
        color: white;
        position: relative; /* Required for the absolute positioning of the overlay */
      }
      
      /* Add dark transparent overlay */
      #best-seller::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6); /* Black with 60% opacity */
        z-index: 1; /* Place above background but below content */
      }
      
      /* Ensure the content appears above the overlay */
      #best-seller .container {
        position: relative;
        z-index: 2; /* Higher than the overlay */
      }

      #best-seller h2 {
        text-align: center;
        margin-bottom: 50px;
        font-size: 2.5rem;
      }

      .product-card {
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        transition: transform 0.3s ease;
        background-color: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .product-card:hover {
        transform: translateY(-10px);
      }

      .product-card img {
        transition: transform 0.3s ease;
        width: 100%;
        height: 250px;
        object-fit: contain;
        background-color: rgba(255, 255, 255, 0.9);
        padding: 15px;
      }

      .product-card:hover img {
        transform: scale(1.05);
      }

      .product-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
        padding: 20px 15px 15px;
        color: white;
      }

      .product-overlay h4 {
        margin: 0 0 5px 0;
        font-size: 1.3rem;
        font-weight: bold;
      }

      .product-overlay .price {
        font-size: 1.1rem;
        color: #ffd700;
        font-weight: bold;
        margin-bottom: 5px;
      }

      .product-overlay .sold-count {
        font-size: 0.9rem;
        color: #ccc;
        margin-bottom: 10px;
      }

      .product-overlay .btn-view {
        background-color: transparent;
        border: 2px solid #ffd700;
        color: #ffd700;
        padding: 8px 20px;
        text-decoration: none;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: bold;
        transition: all 0.3s ease;
        display: inline-block;
      }

      .product-overlay .btn-view:hover {
        background-color: #ffd700;
        color: #333;
        text-decoration: none;
      }

      .best-seller-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background-color: #ff6b6b;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        z-index: 3;
      }

      /* About Section */
      #about {
        padding: 100px 0;
        background-color: #EAE7DE;
      }

      #about h2 {
        text-align: center;
        margin-bottom: 40px;
        font-size: 2.5rem;
      }

      #about .about-img {
        border-radius: 5px;
        overflow: hidden;
      }
      
      /* Add styling for the about section paragraph */
      #about p {
        font-family: "oswlad", serif; /* Use serif font for better readability */
        font-size: 1.2rem; /* Larger text */
        font-weight: bold; /* Bold text */
        line-height: 1.6; /* Better line spacing */
      }

      /* Products Section */
      #products {
        padding: 100px 0;
        background-color: #EAE7DE;
      }

      #products h2 {
        text-align: center;
        margin-bottom: 50px;
        font-size: 2.5rem;
      }

      .product-item {
        text-align: center;
        margin-bottom: 40px;
        transition: transform 0.3s ease;
      }

      .product-item:hover {
        transform: translateY(-10px);
      }

      .product-item img {
        width: 100%;
        height: 250px; /* Fixed height for consistency */
        object-fit: contain; /* Prevent image distortion */
        border-radius: 5px;
        margin-bottom: 15px;
        padding: 10px; /* Add padding for frame effect */
        background-color: white; /* Background for the frame */
        border: 1px solid #e0e0e0; /* Subtle border */
        box-shadow: 0 3px 10px rgba(0,0,0,0.1); /* Soft shadow for depth */
      }

      .product-item h4 {
        font-size: 1.2rem;
        margin-bottom: 10px;
      }
      
      /* Add responsive adjustments for product images */
      @media (max-width: 767.98px) {
        .product-item img {
          height: 200px;
        }
      }
      
      @media (max-width: 575.98px) {
        .product-item img {
          height: 180px;
        }
      }

      /* Gallery Section */
      #gallery {
        padding: 80px 0;
      }

      #gallery h2 {
        text-align: center;
        margin-bottom: 50px;
        font-size: 2.5rem;
      }

      .gallery-item {
        margin-bottom: 15px;
        overflow: hidden;
        border-radius: 5px;
        height: 180px; /* Fixed height for consistency */
        cursor: pointer;
        position: relative;
      }

      .gallery-item::before {
        content: "\f002"; /* FontAwesome search/zoom icon */
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 2rem;
        z-index: 2;
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .gallery-item::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .gallery-item:hover::before,
      .gallery-item:hover::after {
        opacity: 1;
      }

      .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
      }

      .gallery-item:hover img {
        transform: scale(1.05);
      }
      
      /* Gallery responsive grid */
      .gallery-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
      }
      
      .gallery-item {
        margin-bottom: 15px;
        overflow: hidden;
        border-radius: 5px;
        height: 180px; /* Fixed height for consistency */
        cursor: pointer;
        position: relative;
      }

      /* Maps Section */
      #maps {
        padding: 100px 0;
        background-color: #EAE7DE;
      }

      #maps h2 {
        text-align: center;
        margin-bottom: 50px;
        font-size: 2.5rem;
      }

      .contact-info {
        padding: 30px;
        background-color: white;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }

      .contact-info p {
        margin-bottom: 15px;
        font-size: 1.1rem;
      }

      /* Footer */
      footer {
        background-color: #222;
        color: white;
        padding: 50px 0 20px;
        text-align: center;
      }

      .social-icons {
        margin-bottom: 20px;
      }

      .social-icons a {
        color: white;
        font-size: 1.5rem;
        margin: 0 10px;
        transition: color 0.3s ease;
      }

      .social-icons a:hover {
        color: #c7a17a;
      }

      .footer-links {
        margin-bottom: 30px;
      }

      .footer-links a {
        color: white;
        margin: 0 15px;
        text-decoration: none;
        transition: color 0.3s ease;
      }

      .footer-links a:hover {
        color: #c7a17a;
      }

      .copyright {
        font-size: 0.9rem;
        opacity: 0.7;
      }

      /* Alert message styles */
      .alert-message {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1000;
        max-width: 350px;
        padding: 15px 20px;
        border-radius: 5px;
        background-color: rgba(25, 135, 84, 0.9); /* Success green with opacity */
        color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: opacity 0.5s ease-in-out;
      }
      
      .alert-message.info {
        background-color: rgba(13, 110, 253, 0.9); /* Info blue with opacity */
      }
      
      .alert-message.error {
        background-color: rgba(220, 53, 69, 0.9); /* Error red with opacity */
      }
      
      /* Responsive adjustments */
      @media (max-width: 991.98px) {
        #hero h1 {
          font-size: 3rem;
        }
        
        #about, #products, #maps {
          padding: 70px 0;
        }
        
        #best-seller, #gallery {
          padding: 50px 0;
        }
        
        .about-img iframe {
          width: 100%;
          height: 300px;
        }
        
        #maps iframe {
          width: 100%;
          height: 300px;
        }
        
        .footer-links a {
          margin: 0 10px;
        }

        .gallery-container {
          grid-template-columns: repeat(4, 1fr);
          gap: 10px;
        }
      }
      
      @media (max-width: 767.98px) {
        #hero h1 {
          font-size: 2.5rem;
        }
        
        #hero p {
          font-size: 1rem;
        }
        
        h2 {
          font-size: 2rem !important;
        }
        
        .about-img iframe {
          height: 250px;
        }
        
        .product-overlay h4 {
          font-size: 1rem;
        }
        
        .footer-links {
          display: flex;
          flex-wrap: wrap;
          justify-content: center;
        }
        
        .footer-links a {
          margin: 5px 10px;
        }

        .gallery-container {
          grid-template-columns: repeat(4, 1fr);
          gap: 8px;
        }
        
        .gallery-item {
          height: 150px;
        }
      }
      
      @media (max-width: 575.98px) {
        #hero h1 {
          font-size: 2rem;
        }
        
        .btn-custom {
          padding: 8px 20px;
          font-size: 0.9rem;
        }
        
        #about, #products, #maps {
          padding: 50px 0;
        }
        
        #best-seller, #gallery {
          padding: 40px 0;
        }
        
        h2 {
          font-size: 1.75rem !important;
          margin-bottom: 30px !important;
        }
        
        .product-item h4 {
          font-size: 1rem;
        }
        
        .contact-info p {
          font-size: 0.95rem;
        }
        
        .social-icons a {
          font-size: 1.25rem;
          margin: 0 8px;
        }
        
        .alert-message {
          left: 20px;
          right: 20px;
          max-width: none;
        }

        .gallery-container {
          grid-template-columns: repeat(4, 1fr);
          gap: 4px;
        }
        
        .gallery-item {
          margin-bottom: 8px;
          height: 100px;
        }
        
        #gallery {
          padding: 40px 0;
        }
      }

      /* Simple Lightbox CSS */
      .lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        cursor: pointer;
      }
      
      .lightbox.active {
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .lightbox img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 5px;
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
      }
      
      .lightbox-close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
      }
      
      .lightbox-close:hover {
        color: #ccc;
      }
      
      .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        font-size: 30px;
        font-weight: bold;
        cursor: pointer;
        user-select: none;
        padding: 10px 15px;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 5px;
      }
      
      .lightbox-nav:hover {
        background-color: rgba(0, 0, 0, 0.8);
        color: #ccc;
      }
      
      .lightbox-prev {
        left: 20px;
      }
      
      .lightbox-next {
        right: 20px;
      }
      
      .lightbox-counter {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        font-size: 16px;
        background-color: rgba(0, 0, 0, 0.5);
        padding: 10px 20px;
        border-radius: 20px;
      }
      
      /* Responsive lightbox */
      @media (max-width: 768px) {
        .lightbox img {
          max-width: 95%;
          max-height: 85%;
        }
        
        .lightbox-close {
          top: 10px;
          right: 15px;
          font-size: 30px;
        }
        
        .lightbox-nav {
          font-size: 25px;
          padding: 8px 12px;
        }
        
        .lightbox-prev {
          left: 10px;
        }
        
        .lightbox-next {
          right: 10px;
        }
        
        .lightbox-counter {
          bottom: 10px;
          font-size: 14px;
          padding: 8px 15px;
        }
      }
    </style>
  </head>

  <body>
    <!-- Include the centralized navbar -->
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Info Message Display -->
    <?php if (isset($_SESSION['info_message'])): ?>
    <div class="alert-message info">
      <i class="fas fa-info-circle me-2"></i>
      <?= $_SESSION['info_message']; ?>
      <button type="button" class="btn-close btn-close-white float-end" onclick="this.parentElement.style.display='none'"></button>
    </div>
    <script>
      setTimeout(function() {
        document.querySelector('.alert-message').style.opacity = '0';
        setTimeout(function() {
          document.querySelector('.alert-message').style.display = 'none';
        }, 500);
      }, 5000);
    </script>
    <?php unset($_SESSION['info_message']); ?>
    <?php endif; ?>
    
    <!-- Hero Section -->
    <section id="hero" class="bg-image">
      <div class="content">
        <h1>DJAYA ROASTER</h1>
        <p>
          Proudly honor palate details; comprising vivid flavours, smooth
          tactile and memorable aftertaste.
        </p>
        <?php if (!$isLoggedIn): ?>
          <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-custom">Login to Order</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/pages/products/category.php" class="btn btn-custom">Order Now</a>
        <?php endif; ?>
      </div>
    </section>

    <!-- About Section -->
    <section id="about">
      <div class="container">
        <h2>ABOUT ME</h2>
        <div class="row align-items-center">
          <div class="col-md-6 mb-4 mb-md-0">
            <div class="about-img">
              <iframe
                width="560"
                height="315"
                src="https://www.youtube.com/embed/3-ScOxTP088?si=_2PU4d93MuePPtGc"
                title="YouTube video player"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
              ></iframe>
            </div>
          </div>
          <div class="col-md-6">
            <p>
              Menggunakan biji kakao pilihan dan proses yang teliti, kami
              menciptakan coklat dengan cita rasa autentik dan berkualitas.
            </p>
            <p>
              Bagi kami, coklat adalah kebahagiaan dalam setiap gigitan
              sederhana, manis, dan penuh kenangan.
            </p>
            <p>
              Kami memulai dari kecintaan terhadap coklat dan keinginan untuk
              menghadirkan sesuatu yang lebih dari sekadar camilan.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Best Seller Section -->
    <section id="best-seller" class="bg-dark">
      <div class="container">
        <h2>BEST SELLER</h2>
        <div class="row">
          <?php if (!empty($best_sellers)): ?>
            <?php foreach ($best_sellers as $index => $product): ?>
              <div class="col-md-4">
                <div class="product-card">
                  <?php if ($product['total_sold'] > 0): ?>
                    <div class="best-seller-badge">
                      #<?= $index + 1 ?> Best Seller
                    </div>
                  <?php endif; ?>
                  
                  <?php if (!empty($product['product_image'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/products/<?= htmlspecialchars($product['product_image']) ?>" 
                         alt="<?= htmlspecialchars($product['product_name']) ?>" class="img-fluid" />
                  <?php else: ?>
                    <img src="<?= BASE_URL ?>/assets/images/placeholder.png" 
                         alt="<?= htmlspecialchars($product['product_name']) ?>" class="img-fluid" />
                  <?php endif; ?>
                  
                  <div class="product-overlay">
                    <h4><?= htmlspecialchars($product['product_name']) ?></h4>
                    <div class="price">Rp <?= number_format($product['price'], 0, ',', '.') ?></div>
                    
                    <?php if ($product['total_sold'] > 0): ?>
                      <div class="sold-count">
                        <i class="fas fa-fire"></i> <?= $product['total_sold'] ?> sold
                      </div>
                    <?php else: ?>
                      <div class="sold-count">
                        <i class="fas fa-star"></i> Featured Product
                      </div>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>/pages/products/detail.php?id=<?= $product['id'] ?>" class="btn-view">
                      View Details
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <!-- Fallback content if no products found -->
            <div class="col-12 text-center">
              <p class="lead">No products available at the moment.</p>
              <a href="<?= BASE_URL ?>/pages/products/category.php" class="btn btn-outline-light">
                View All Products
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Products Section -->
    <section id="products">
      <div class="container">
        <h2>PRODUCT</h2>
        <div class="row">
          <div class="col-md-4 col-sm-6">
            <div class="product-item">
              <a href="<?= BASE_URL ?>/pages/products/category.php?category=Classic+Coffee" class="text-decoration-none text-dark">
                <img src="assets/images/produk/1.jpg" alt="Classic Coffee" class="img-fluid" />
                <h4>Classic Coffee</h4>
                <p>House Blend & Signature</p>
              </a>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="product-item">
              <a href="<?= BASE_URL ?>/pages/products/category.php?jenis=Filter+Coffee" class="text-decoration-none text-dark">
                <img src="assets/images/produk/6.jpg" alt="Filter Coffee" class="img-fluid" />
                <h4>Filter Coffee</h4>
                <p>Discover The Origin</p>
              </a>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="product-item">
              <a href="<?= BASE_URL ?>/pages/products/category.php?jenis=Speciality" class="text-decoration-none text-dark">
                <img src="assets/images/produk/5.jpg" alt="Specialty" class="img-fluid" />
                <h4>Specialty</h4>
                <p>Arabica Espresso</p>
              </a>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="product-item">
              <a href="<?= BASE_URL ?>/pages/products/category.php?jenis=Drip+Bag" class="text-decoration-none text-dark">
                <img src="assets/images/produk/3.jpg" alt="Drip Bag" class="img-fluid" />
                <h4>Drip Bag</h4>
                <p>Instant Coffee</p>
              </a>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="product-item">
              <a href="<?= BASE_URL ?>/pages/products/category.php?jenis=Coffee+Gems" class="text-decoration-none text-dark">
                <img src="assets/images/produk/6.jpg" alt="Coffee Gems" class="img-fluid" />
                <h4>Coffee Gems</h4>
                <p>Ultra Specialty</p>
              </a>
            </div>
          </div>
          <div class="col-md-4 col-sm-6">
            <div class="product-item">
              <a href="<?= BASE_URL ?>/pages/products/category.php?jenis=Merchandise" class="text-decoration-none text-dark">
                <img src="assets/images/produk/2.jpg" alt="Merchandise" class="img-fluid" />
                <h4>Merchandise</h4>
                <p>T-shirts & Goods</p>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery">
      <div class="container">
        <h2>GALLERY</h2>
        <div class="gallery-container">
          <div class="gallery-item" onclick="openLightbox(0)">
            <img
              src="assets/images/galeri/unnamed (1).webp"
              alt="Gallery Image"
              class="img-fluid"
            />
          </div>
          <div class="gallery-item" onclick="openLightbox(1)">
            <img
              src="assets/images/galeri/unnamed (10).webp"
              alt="Gallery Image"
              class="img-fluid"
            />
          </div>
          <div class="gallery-item" onclick="openLightbox(2)">
            <img
              src="assets/images/galeri/unnamed (11).webp"
              alt="Gallery Image"
              class="img-fluid"
            />
          </div>
          <div class="gallery-item" onclick="openLightbox(3)">
            <img
              src="assets/images/galeri/unnamed (12).webp"
              alt="Gallery Image"
              class="img-fluid"
            />
          </div>
          <div class="gallery-item" onclick="openLightbox(4)">
            <img
              src="assets/images/galeri/unnamed (2).webp"
              alt="Gallery Image"
              class="img-fluid"
            />
          </div>
          <div class="gallery-item" onclick="openLightbox(5)">
            <img
              src="assets/images/galeri/unnamed (3).webp"
              alt="Gallery Image"
              class="img-fluid"
            />
          </div>
          <div class="gallery-item" onclick="openLightbox(6)">
            <img 
              src="assets/images/galeri/unnamed (4).webp" 
              alt="Gallery Image" 
              class="img-fluid"
            />
          </div>
          <div class="gallery-item" onclick="openLightbox(7)">
            <img
              src="assets/images/galeri/unnamed (5).webp"
              alt="Gallery Image"
              class="img-fluid"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Simple Lightbox -->
    <div class="lightbox" id="lightbox" onclick="closeLightbox()">
      <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
      <div class="lightbox-nav lightbox-prev" onclick="event.stopPropagation(); prevImage()">&#10094;</div>
      <img id="lightbox-img" src="" alt="Lightbox Image">
      <div class="lightbox-nav lightbox-next" onclick="event.stopPropagation(); nextImage()">&#10095;</div>
      <div class="lightbox-counter" id="lightbox-counter"></div>
    </div>

    <!-- Maps Section -->
    <section id="maps">
      <div class="container">
        <h2>MAPS</h2>
        <div class="row">
          <div class="col-md-6 mb-4 mb-md-0">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d8791.103369852788!2d110.38803212084768!3d-7.7310068636364555!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a59f6bfde65f9%3A0x8429eb8295ea5fc6!2sDjaya%20Roasters!5e0!3m2!1sid!2sid!4v1747624177982!5m2!1sid!2sid"
              width="600"
              height="450"
              style="border: 0"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>
          <div class="col-md-6">
            <div class="contact-info">
              <p>
                <strong>📍 Alamat:</strong> Jl. Kaliurang KM 7 No. 15, Sleman,
                Yogyakarta
              </p>
              <p>
                <strong>🕒 Jam Buka:</strong> Setiap Hari – 09.00 WIB s/d 21.00
                WIB
              </p>
              <p><strong>📞 Kontak:</strong> 0895262368652 (WhatsApp)</p>
              <img src="assets/images/qrcode.png" alt="Qrcode" class="img-fluid" style="width: 150px; height: 150px; border-radius: 10px;">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer>
      <div class="container">
        <div class="social-icons">
          <a href="https://www.facebook.com/djayacoffee/"><i class="fab fa-facebook"></i></a>
          <a href="https://www.instagram.com/djayacoffee/"><i class="fab fa-instagram"></i></a>
          <a href="https://www.tiktok.com/@djayacoffee"><i class="fab fa-tiktok"></i></a>
          <a href=""><i class="fab fa-whatsapp"></i></a>
        </div>
        <div class="footer-links">
          <a href="#home">Home</a>
          <a href="#about">About</a>
          <a href="#products">Products</a>
          <a href="#gallery">Gallery</a>
          <a href="#maps">Contact</a>
        </div>
        <p class="copyright">
          &copy; 2025 Djaya Roasters. All Rights Reserved.
        </p>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Remove Fancybox JS -->
    <script>
      // Simple Lightbox JavaScript
      const galleryImages = [
        'assets/images/galeri/unnamed (1).webp',
        'assets/images/galeri/unnamed (10).webp',
        'assets/images/galeri/unnamed (11).webp',
        'assets/images/galeri/unnamed (12).webp',
        'assets/images/galeri/unnamed (2).webp',
        'assets/images/galeri/unnamed (3).webp',
        'assets/images/galeri/unnamed (4).webp',
        'assets/images/galeri/unnamed (5).webp'
      ];
      
      let currentImageIndex = 0;
      
      function openLightbox(index) {
        currentImageIndex = index;
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const counter = document.getElementById('lightbox-counter');
        
        lightboxImg.src = galleryImages[currentImageIndex];
        counter.textContent = `${currentImageIndex + 1} / ${galleryImages.length}`;
        lightbox.classList.add('active');
        
        // Prevent body scroll when lightbox is open
        document.body.style.overflow = 'hidden';
      }
      
      function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        lightbox.classList.remove('active');
        
        // Restore body scroll
        document.body.style.overflow = 'auto';
      }
      
      function nextImage() {
        currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
        const lightboxImg = document.getElementById('lightbox-img');
        const counter = document.getElementById('lightbox-counter');
        
        lightboxImg.src = galleryImages[currentImageIndex];
        counter.textContent = `${currentImageIndex + 1} / ${galleryImages.length}`;
      }
      
      function prevImage() {
        currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
        const lightboxImg = document.getElementById('lightbox-img');
        const counter = document.getElementById('lightbox-counter');
        
        lightboxImg.src = galleryImages[currentImageIndex];
        counter.textContent = `${currentImageIndex + 1} / ${galleryImages.length}`;
      }
      
      // Keyboard navigation
      document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('lightbox');
        if (lightbox.classList.contains('active')) {
          if (e.key === 'Escape') {
            closeLightbox();
          } else if (e.key === 'ArrowRight') {
            nextImage();
          } else if (e.key === 'ArrowLeft') {
            prevImage();
          }
        }
      });
    </script>
  </body>
</html>
