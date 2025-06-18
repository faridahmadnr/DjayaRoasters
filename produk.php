<?php
require_once '../../config/init.php';
protectAdminPage();

// Display success/error messages if any
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Pagination settings
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : '';

// Build WHERE clause
$where_clause = [];
if (!empty($search)) {
    $where_clause[] = "(product_name LIKE '%$search%' OR category LIKE '%$search%')";
}
if (!empty($filter)) {
    $where_clause[] = "category = '$filter'";
}

$where_sql = empty($where_clause) ? "" : " WHERE " . implode(" AND ", $where_clause);

// Count total records
$count_query = "SELECT COUNT(*) AS total FROM products" . $where_sql;
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Get products
$query = "SELECT * FROM products" . $where_sql . " ORDER BY date_added DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Get categories for filter dropdown
$categories_query = "SELECT DISTINCT category FROM products ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
if ($categories_result && mysqli_num_rows($categories_result) > 0) {
    while ($row = mysqli_fetch_assoc($categories_result)) {
        $categories[] = $row['category'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk Kopi</title>
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
        .btn-edit {
            background-color: #30271C;
            border-color: #30271C;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-edit:hover {
            background-color: #CAA782;
            border-color: #CAA782;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-edit:focus,
        .btn-edit:active,
        .btn-edit.active {
            background-color: #30271C;
            border-color: #30271C;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-delete {
            background-color: #30271C;
            border-color: #30271C;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-delete:hover {
            background-color: #CAA782;
            border-color: #CAA782;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-delete:focus,
        .btn-delete:active,
        .btn-delete.active {
            background-color: #30271C;
            border-color: #30271C;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-warning {
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
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-warning:hover {
            background-color: #CAA782;
            border-color: #CAA782;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-warning:focus,
        .btn-warning:active,
        .btn-warning.active {
            background-color: #30271C;
            border-color: #30271C;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-outline-primary {
            --bs-btn-color: #30271C;
            --bs-btn-border-color: #30271C;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #30271C;
            --bs-btn-hover-border-color: #30271C;
            --bs-btn-focus-shadow-rgb: 48, 39, 28;
            --bs-btn-active-color: #30271C;
            --bs-btn-active-bg: transparent;
            --bs-btn-active-border-color: #30271C;
            --bs-btn-active-shadow: none;
            --bs-btn-disabled-color: #30271C;
            --bs-btn-disabled-bg: transparent;
            --bs-btn-disabled-border-color: #30271C;
            color: #30271C;
            border-color: #30271C;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-outline-primary:hover {
            background-color: #30271C;
            border-color: #30271C;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-outline-primary:focus,
        .btn-outline-primary:active,
        .btn-outline-primary.active {
            color: #30271C;
            border-color: #30271C;
            background-color: transparent;
            box-shadow: none !important;
            outline: none !important;
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
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-outline-secondary:focus,
        .btn-outline-secondary:active,
        .btn-outline-secondary.active {
            color: #6c757d;
            border-color: #6c757d;
            background-color: transparent;
            box-shadow: none !important;
            outline: none !important;
        }
        /* Remove all shadow and outline effects from all elements */
        *:focus,
        *:active,
        *.active,
        .btn:focus,
        .btn:active,
        .btn.active,
        .form-control:focus,
        .form-select:focus,
        .page-link:focus,
        .page-link:active {
            box-shadow: none !important;
            outline: none !important;
        }
        .page-link {
            color: #30271C;
            border-color: #dee2e6;
            box-shadow: none !important;
            outline: none !important;
        }
        .page-link:hover {
            color: white;
            background-color: #CAA782;
            border-color: #CAA782;
            box-shadow: none !important;
            outline: none !important;
        }
        .page-item.active .page-link {
            color: white;
            background-color: #30271C;
            border-color: #30271C;
            box-shadow: none !important;
            outline: none !important;
        }
        .page-item.active .page-link:hover {
            background-color: #CAA782;
            border-color: #CAA782;
            box-shadow: none !important;
            outline: none !important;
        }
        .page-link:focus,
        .page-link:active,
        .page-link.active {
            background-color: #EAE7DE;
            border-color: #30271C;
            color: #30271C;
            box-shadow: none !important;
            outline: none !important;
        }
        .page-item.active .page-link:focus,
        .page-item.active .page-link:active,
        .page-item.active .page-link.active {
            background-color: #30271C;
            border-color: #30271C;
            color: white;
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
        .stok-warning {
            color: #dc3545;
            font-weight: bold;
        }
        .product-image-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .table-actions {
            white-space: nowrap;
        }
        .table-actions .btn {
            padding: 4px 8px;
            margin: 1px;
            font-size: 0.875rem;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .search-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 576px) {
            .main-container {
                padding: 15px;
            }
            .table-actions .btn {
                padding: 2px 6px;
                font-size: 0.75rem;
            }
            .product-image-thumb {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="main-container">
            <h1>Kelola Produk Kopi</h1>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="search-section">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
                        <a href="tambah_produk.php" class="btn btn-primary w-100">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </a>
                    </div>
                    
                    <div class="col-lg-9 col-md-8">
                        <form action="produk.php" method="GET" class="row g-2">
                            <div class="col-lg-4 col-md-6">
                                <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            
                            <div class="col-lg-3 col-md-6">
                                <select name="filter" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <?php $selected = (isset($_GET['filter']) && $_GET['filter'] == $category) ? 'selected' : ''; ?>
                                        <option value="<?= htmlspecialchars($category) ?>" <?= $selected ?>><?= htmlspecialchars($category) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-lg-2 col-md-4">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i> <span class="d-none d-sm-inline">Cari</span>
                                </button>
                            </div>
                            
                            <?php if (!empty($search) || !empty($filter)): ?>
                                <div class="col-lg-2 col-md-4">
                                    <a href="produk.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-times"></i> <span class="d-none d-sm-inline">Reset</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Jenis Kopi</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th class="d-none d-md-table-cell">Tanggal Masuk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = $offset + 1; 
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>".$no++."</td>";
                                echo "<td>";
                                if (!empty($row['product_image'])) {
                                    echo "<img src='../../uploads/products/".$row['product_image']."' alt='".$row['product_name']."' class='product-image-thumb'>";
                                } else {
                                    echo "<img src='../../assets/images/placeholder.png' alt='No Image' class='product-image-thumb'>";
                                }
                                echo "</td>"; 
                                echo "<td>".htmlspecialchars($row['product_name'])."</td>";
                                echo "<td><span class='badge bg-secondary'>".htmlspecialchars($row['category'])."</span></td>";
                                echo "<td>Rp ".number_format($row['price'], 0, ',', '.')."</td>";
                                
                                if ($row['stock'] < 5) {
                                    echo "<td class='stok-warning'>".htmlspecialchars($row['stock'])." <i class='fas fa-exclamation-triangle'></i></td>";
                                } else {
                                    echo "<td>".htmlspecialchars($row['stock'])."</td>";
                                }
                                
                                echo "<td class='d-none d-md-table-cell'>".date('d/m/Y', strtotime($row['date_added']))."</td>";
                                echo "<td class='table-actions'>";
                                echo "<a href='edit_produk.php?id=".$row['id']."' class='btn btn-edit btn-sm' title='Edit'><i class='fas fa-edit'></i></a> ";
                                echo "<a href='restok_produk.php?id=".$row['id']."' class='btn btn-warning btn-sm' title='Restok'><i class='fas fa-plus'></i></a> ";
                                echo "<a href='javascript:void(0);' onclick='confirmDelete(".$row['id'].")' class='btn btn-delete btn-sm' title='Hapus'><i class='fas fa-trash'></i></a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center py-4'>Tidak ada data produk</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= ($page - 1) ?><?= !empty($search) ? "&search=" . urlencode($search) : "" ?><?= !empty($filter) ? "&filter=" . urlencode($filter) : "" ?>">
                                    <span>&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? "&search=" . urlencode($search) : "" ?><?= !empty($filter) ? "&filter=" . urlencode($filter) : "" ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= ($page + 1) ?><?= !empty($search) ? "&search=" . urlencode($search) : "" ?><?= !empty($filter) ? "&filter=" . urlencode($filter) : "" ?>">
                                    <span>&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="../dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                window.location.href = 'delete.php?id=' + id;
            }
        }
    </script>
</body>
</html>