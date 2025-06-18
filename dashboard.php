<?php
// Include the init file which loads everything in the correct order
require_once '../config/init.php';
protectAdminPage();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Penjualan Kopi</title>
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
            text-align: center;
            margin-bottom: 30px;
            color: #30271C;
        }
        .menu-item {
            background-color: #30271C;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            display: block;
            height: 100%;
        }
        .menu-item:hover {
            background-color: #5a3d2a;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .menu-item i {
            font-size: 3rem;
            display: block;
            margin-bottom: 15px;
        }
        .menu-item h5 {
            margin: 0;
            font-weight: 600;
        }
        .table th {
            background-color: #CAA782;
            color: #30271C;
            font-weight: bold;
        }
        .table tr:hover {
            background-color: #EAE7DE;
        }
        .btn-custom {
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
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.875rem;
            margin: 1px;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-custom:hover {
            background-color: #CAA782;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-custom:focus,
        .btn-custom:active,
        .btn-custom.active {
            background-color: #30271C;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-warning-custom {
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
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-warning-custom:hover {
            background-color: #CAA782;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-warning-custom:focus,
        .btn-warning-custom:active,
        .btn-warning-custom.active {
            background-color: #30271C;
            color: white;
            box-shadow: none !important;
            outline: none !important;
        }
        /* Remove all shadow and outline effects */
        *:focus,
        *:active,
        *.active,
        a:focus,
        a:active {
            box-shadow: none !important;
            outline: none !important;
        }
        .menu-item:focus,
        .menu-item:active {
            background-color: #EAE7DE;
            color: #30271C;
            border: 1px solid #30271C;
        }
        .stock-warning {
            color: #dc3545;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .main-container {
                padding: 15px;
            }
            .menu-item i {
                font-size: 2rem;
                margin-bottom: 10px;
            }
            .menu-item {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <h1><i class="fas fa-coffee"></i> ADMIN DJAYA ROASTERS</h1>
            
            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <a href="products/produk.php" class="menu-item">
                        <i class="fas fa-coffee"></i>
                        <h5>PRODUCT MANAGEMENT</h5>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="categories/categories.php" class="menu-item">
                        <i class="fas fa-tags"></i>
                        <h5>CATEGORY MANAGEMENT</h5>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="orders/orders.php" class="menu-item">
                        <i class="fas fa-chart-bar"></i>
                        <h5>ORDER MANAGEMENT</h5>
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-history"></i> Produk dengan Stok Sedikit</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($conn, "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
                                
                                if (mysqli_num_rows($result) > 0) {
                                    $no = 1;
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>".$no++."</td>";
                                        echo "<td>".htmlspecialchars($row['product_name'])."</td>";
                                        echo "<td><span class='badge bg-secondary'>".htmlspecialchars($row['category'])."</span></td>";
                                        echo "<td>Rp ".number_format($row['price'], 0, ',', '.')."</td>";
                                        echo "<td class='".($row['stock'] < 5 ? 'stock-warning' : 'text-warning')."'>".$row['stock'];
                                        if ($row['stock'] < 5) echo " <i class='fas fa-exclamation-triangle'></i>";
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<a href='products/edit_produk.php?id=".$row['id']."' class='btn-custom btn-sm' title='Edit'><i class='fas fa-edit'></i></a> ";
                                        echo "<a href='products/restok_produk.php?id=".$row['id']."' class='btn-custom btn-warning-custom btn-sm' title='Restok'><i class='fas fa-plus'></i></a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-4'>";
                                    echo "<i class='fas fa-check-circle fa-2x text-success mb-2'></i><br>";
                                    echo "Semua stok produk mencukupi";
                                    echo "</td></tr>";
                                }
                                
                                mysqli_close($conn);
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>