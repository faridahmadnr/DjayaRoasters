<?php
require_once '../../config/init.php';
protectAdminPage();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Kopi</title>
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
            text-align: center;
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
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-primary:hover {
            background-color: #CAA782;
            border-color: #CAA782;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-primary:focus,
        .btn-primary:active,
        .btn-primary.active {
            background-color: #30271C;
            border-color: #30271C;
            box-shadow: none !important;
            outline: none !important;
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
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-secondary:focus,
        .btn-secondary:active,
        .btn-secondary.active {
            background-color: #6c757d;
            border-color: #6c757d;
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
        .form-label {
            font-weight: 600;
            color: #6F4E37;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="form-container">
                    <h1><i class="fas fa-coffee"></i> Tambah Produk Kopi</h1>
                    
                    <?php
                    if(isset($_POST['submit'])) {
                        $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
                        $category = mysqli_real_escape_string($conn, $_POST['category']); 
                        $price = mysqli_real_escape_string($conn, $_POST['price']);
                        $stock = mysqli_real_escape_string($conn, $_POST['stock']);
                        $description = mysqli_real_escape_string($conn, $_POST['description']);
                        $date_added = date('Y-m-d');
                        
                        $product_image = ''; 
                        $errors = array();
                        
                        // Handle image upload
                        if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                            $target_dir = "../../uploads/products/";
                            if (!is_dir($target_dir)) {
                                mkdir($target_dir, 0777, true);
                            }
                            $product_image = basename($_FILES["product_image"]["name"]);
                            $target_file = $target_dir . $product_image;
                            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                            
                            $check = getimagesize($_FILES["product_image"]["tmp_name"]);
                            if($check !== false) {
                                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                                    $errors[] = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
                                }
                                if ($_FILES["product_image"]["size"] > 5000000) {
                                    $errors[] = "Maaf, ukuran file gambar terlalu besar. Maksimal 5MB.";
                                }
                                if (file_exists($target_file)) {
                                    $product_image = time() . '_' . $product_image;
                                    $target_file = $target_dir . $product_image;
                                }
                            } else {
                                $errors[] = "File bukan gambar.";
                            }
                        }

                        if(empty($product_name)) {
                            $errors[] = "Nama produk tidak boleh kosong";
                        }
                        
                        if(empty($category)) {
                            $errors[] = "Jenis category harus dipilih";
                        }
                        
                        if(empty($price) || $price <= 0) {
                            $errors[] = "Harga harus diisi dan lebih dari 0";
                        }
                        
                        if(empty($stock) || $stock < 0) {
                            $errors[] = "Stok harus diisi dan tidak boleh negatif";
                        }
                        
                        if(empty($errors)) {
                            if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] == 0) {
                                if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                                    $errors[] = "Terjadi kesalahan saat mengunggah gambar.";
                                }
                            }

                            if (empty($errors)) {
                                $result = mysqli_query($conn, "INSERT INTO products(product_name, category, price, stock, description, date_added, product_image) 
                                                               VALUES('$product_name', '$category', $price, $stock, '$description', '$date_added', '$product_image')");
                                
                                if($result) {
                                    echo "<div class='alert alert-success'>";
                                    echo "Produk berhasil ditambahkan. <a href='produk.php'>Lihat Daftar Produk</a>";
                                    echo "</div>";
                                } else {
                                    echo "<div class='alert alert-danger'>";
                                    echo "Error: " . mysqli_error($conn);
                                    echo "</div>";
                                }
                            }
                        }
                        
                        if (!empty($errors)) {
                            echo "<div class='alert alert-danger'>";
                            echo "<ul class='mb-0'>";
                            foreach($errors as $error) {
                                echo "<li>$error</li>";
                            }
                            echo "</ul>";
                            echo "</div>";
                        }
                    }
                    ?>
                    
                    <form action="tambah_produk.php" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="product_name" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" name="product_name" id="product_name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Produk Kategori</label>
                                <select name="category" id="category" class="form-select" required>
                                    <option value="">Pilih Jenis Kategori</option>
                                    <option value="Classic Coffee">Classic Coffee</option>
                                    <option value="Filter Coffee">Filter Coffee</option>
                                    <option value="Speciality">Speciality</option>
                                    <option value="Drip Bag">Drip Bag</option>
                                    <option value="Coffee Gems">Coffee Gems</option>
                                    <option value="Merchandise">Merchandise</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Harga (Rp)</label>
                                <input type="number" class="form-control" name="price" id="price" min="0" step="1000" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stok</label>
                                <input type="number" class="form-control" name="stock" id="stock" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Produk</label>
                            <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="product_image" class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" name="product_image" id="product_image" accept="image/*">
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                            <a href="produk.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan
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