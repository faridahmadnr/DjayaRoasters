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
$query = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Produk tidak ditemukan.";
    header("Location: produk.php");
    exit();
}

$product = mysqli_fetch_assoc($result);
$current_image = $product['product_image']; // FIXED: Changed from gambar_produk

// Handle form submission
if(isset($_POST['submit'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']); 
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $errors = [];
    $product_image = $current_image; // Keep current image by default
    
    // Check if a new image was uploaded
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "../../uploads/products/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $product_image = time() . '_' . basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $product_image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if($check !== false) {
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                $errors[] = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
            }
            // Check file size (limit to 5MB)
            if ($_FILES["product_image"]["size"] > 5000000) {
                $errors[] = "Maaf, ukuran file gambar terlalu besar. Maksimal 5MB.";
            }
        } else {
            $errors[] = "File bukan gambar.";
        }
    }

    // Validate required fields
    if(empty($product_name)) {
        $errors[] = "Nama produk tidak boleh kosong";
    }
    
    if(empty($category)) {
        $errors[] = "Jenis kopi harus dipilih";
    }
    
    if(empty($price) || $price <= 0) {
        $errors[] = "Harga harus diisi dan lebih dari 0";
    }
    
    if($stock < 0) {
        $errors[] = "Stok tidak boleh negatif";
    }
    
    if(empty($errors)) {
        // Upload new image if provided
        if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            if(!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $errors[] = "Terjadi kesalahan saat mengunggah gambar.";
            } else {
                // Delete old image if it exists and a new one is uploaded
                if (!empty($current_image)) {
                    $old_image_path = "../../uploads/products/" . $current_image;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
            }
        }
        
        if(empty($errors)) {
            // Update product in database
            $update_query = "UPDATE products SET 
                            product_name = '$product_name', 
                            category = '$category', 
                            price = $price, 
                            stock = $stock, 
                            description = '$description'";
            
            // Only include image in update if it changed
            if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                $update_query .= ", product_image = '$product_image'";
            }
            
            $update_query .= " WHERE id = $id";
            
            $result = mysqli_query($conn, $update_query);
            
            if($result) {
                $_SESSION['success_message'] = "Produk berhasil diperbarui.";
                header("Location: produk.php");
                exit();
            } else {
                $errors[] = "Gagal memperbarui produk: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Admin</title>
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
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-label {
            font-weight: 600;
            color: #6F4E37;
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
        .btn-outline-secondary {
            --bs-btn-color: #6c757d;
            --bs-btn-border-color: #6c757d;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #6c757d;
            --bs-btn-hover-border-color: #6c757d;
            --bs-btn-focus-shadow-rgb: 108, 117, 125;
            --bs-btn-active-color: #6c757d;
            --bs-btn-active-bg: transparent;
            --bs-btn-active-border-color: #6c757d;
            --bs-btn-active-shadow: none;
            --bs-btn-disabled-color: #6c757d;
            --bs-btn-disabled-bg: transparent;
            --bs-btn-disabled-border-color: #6c757d;
            color: #6c757d;
            border-color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
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
        .current-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="form-container">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                        <h1><i class="fas fa-edit me-2"></i> Edit Produk</h1>
                        <a href="produk.php" class="btn btn-outline-secondary mt-2 mt-sm-0">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="product_name" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Kategori Produk</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php
                                    $categories_query = "SELECT DISTINCT category FROM products ORDER BY category";
                                    $categories_result = mysqli_query($conn, $categories_query);
                                    
                                    $predefined_categories = [
                                        'Classic Coffee',
                                        'Filter Coffee',
                                        'Speciality',
                                        'Drip Bag',
                                        'Coffee Gems',
                                        'Merchandise'
                                    ];
                                    
                                    $existing_categories = [];
                                    if ($categories_result) {
                                        while ($category = mysqli_fetch_assoc($categories_result)) {
                                            $existing_categories[] = $category['category'];
                                        }
                                    }
                                    
                                    $all_categories = array_unique(array_merge($predefined_categories, $existing_categories));
                                    sort($all_categories);
                                    
                                    foreach ($all_categories as $category) {
                                        $selected = ($product['category'] == $category) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($category) . "' $selected>" . htmlspecialchars($category) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Harga (Rp)</label>
                                <input type="number" class="form-control" id="price" name="price" step="1000" min="0" value="<?= $product['price'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?= $product['stock'] ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Produk</label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="product_image" class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                            
                            <?php if (!empty($product['product_image'])): ?>
                                <div class="mt-2">
                                    <p class="mb-1">Gambar Saat Ini:</p>
                                    <img src="../../uploads/products/<?= htmlspecialchars($product['product_image']) ?>" alt="Current Product Image" class="current-image">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='produk.php'">
                                <i class="fas fa-times me-1"></i> Batal
                            </button>
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
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
