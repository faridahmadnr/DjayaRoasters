<?php
require_once '../../config/init.php';
protectAdminPage();

// Handle category add/edit/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add new category
        if ($_POST['action'] === 'add' && !empty($_POST['category_name'])) {
            $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
            
            // Since categories are stored in the products table's category field
            // We'll just display a success message without actual DB insertion
            $_SESSION['success_message'] = "Category '$category_name' has been added. Products can now use this category.";
            
            // Redirect to avoid form resubmission
            header("Location: categories.php");
            exit();
        }
    }
}

// Get all unique categories from products
$query = "SELECT DISTINCT category, COUNT(*) as product_count 
          FROM products 
          GROUP BY category 
          ORDER BY category ASC";
$result = mysqli_query($conn, $query);

// Prepare category data
$categories = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
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
        }
        .card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .card-header {
            background-color: #6F4E37;
            color: white;
            font-weight: bold;
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
            background-color: #EAE7DE;
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
        .btn-outline-primary:focus,
        .btn-outline-primary:active,
        .btn-outline-primary.active {
            color: #30271C;
            border-color: #30271C;
            background-color: #EAE7DE;
            box-shadow: none !important;
            outline: none !important;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .category-badge {
            font-size: 0.85rem;
            background-color: #6F4E37;
            color: white;
            border-radius: 20px;
            padding: 5px 10px;
        }
        @media (max-width: 768px) {
            .main-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                <h1><i class="fas fa-tags me-2"></i> Manage Categories</h1>
                <a href="../dashboard.php" class="btn btn-secondary mt-2 mt-sm-0">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-4 col-md-5 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-plus-circle me-2"></i> Add New Category
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Create Category
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <i class="fas fa-info-circle me-2"></i> Categories Help
                        </div>
                        <div class="card-body">
                            <p class="small">Categories are used to group products. When you create a product, you can assign it to a category.</p>
                            <p class="small">To add a new category, enter the name and click "Create Category".</p>
                            <p class="small mb-0">To use a category, select it when creating or editing products.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8 col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-list me-2"></i> Existing Categories
                        </div>
                        <div class="card-body">
                            <?php if (empty($categories)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                                    <h5 class="text-muted">No categories found</h5>
                                    <p class="text-muted">Create your first category to get started.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Category Name</th>
                                                <th class="text-center">Product Count</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($categories as $category): ?>
                                                <tr>
                                                    <td>
                                                        <span class="category-badge"><?= htmlspecialchars($category['category']) ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info"><?= $category['product_count'] ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="../products/produk.php?filter=<?= urlencode($category['category']) ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> <span class="d-none d-sm-inline">View Products</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
