<?php
require_once '../../config/init.php';
protectAdminPage();

// Check if product ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "ID produk tidak ditentukan.";
    header("Location: produk.php");
    exit();
}

$id = (int)$_GET['id'];

// Get product data
$query = "SELECT id, product_name, stock FROM products WHERE id = $id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Produk tidak ditemukan.";
    header("Location: produk.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $additional_stock = isset($_POST['additional_stock']) ? (int)$_POST['additional_stock'] : 0;
    
    if ($additional_stock <= 0) {
        $error = "Jumlah stok yang ditambahkan harus lebih dari 0.";
    } else {
        // Update stock
        $new_stock = $product['stock'] + $additional_stock;
        $update_query = "UPDATE products SET stock = $new_stock WHERE id = $id";
        $update_result = mysqli_query($conn, $update_query);
        
        if ($update_result) {
            $_SESSION['success_message'] = "Stok produk {$product['product_name']} berhasil ditambah dari {$product['stock']} menjadi $new_stock.";
            header("Location: produk.php");
            exit();
        } else {
            $error = "Gagal memperbarui stok produk: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restok Produk - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px 0;
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #6F4E37;
            margin-bottom: 30px;
        }
        .btn-primary {
            --bs-btn-color: #fff;
            --bs-btn-bg: #30271C;
            --bs-btn-border-color: #30271C;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #CAA782;
            --bs-btn-hover-border-color: #CAA782;
            --bs-btn-focus-shadow-rgb: 48, 39, 28;
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #30271C;
            --bs-btn-active-border-color: #30271C;
            --bs-btn-active-shadow: none;
            --bs-btn-disabled-color: #fff;
            --bs-btn-disabled-bg: #30271C;
            --bs-btn-disabled-border-color: #30271C;
            background-color: #30271C;
            border-color: #30271C;
        }
        .btn-primary:hover {
            background-color: #CAA782;
            border-color: #CAA782;
        }
        .btn-secondary {
            --bs-btn-color: #fff;
            --bs-btn-bg: #6c757d;
            --bs-btn-border-color: #6c757d;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #5a6268;
            --bs-btn-hover-border-color: #5a6268;
            --bs-btn-focus-shadow-rgb: 108, 117, 125;
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #6c757d;
            --bs-btn-active-border-color: #6c757d;
            --bs-btn-active-shadow: none;
            --bs-btn-disabled-color: #fff;
            --bs-btn-disabled-bg: #6c757d;
            --bs-btn-disabled-border-color: #6c757d;
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="form-container">
                    <h1><i class="fas fa-boxes"></i> Restok Produk</h1>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                            <p class="card-text">Stok Saat Ini: <strong class="fs-5 text-primary"><?= $product['stock'] ?></strong></p>
                        </div>
                    </div>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="additional_stock" class="form-label">Tambah Stok</label>
                            <input type="number" class="form-control" id="additional_stock" name="additional_stock" min="1" required>
                            <div class="form-text">Masukkan jumlah stok yang ingin ditambahkan</div>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between">
                            <a href="produk.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Tambah Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
