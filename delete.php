<?php
require_once '../../config/init.php';
protectAdminPage();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Cast to integer for security
    
    // Get the product details including image filename
    $result_image = mysqli_query($conn, "SELECT product_image FROM products WHERE id = $id");
    
    if (!$result_image) {
        $_SESSION['error_message'] = "Error retrieving product information: " . mysqli_error($conn);
        header("Location: produk.php");
        exit();
    }
    
    $row_image = mysqli_fetch_assoc($result_image);
    $image_to_delete = $row_image['product_image'];

    // Check if the product is referenced in order_items (completed orders)
    $check_orders_query = "SELECT COUNT(*) as total FROM order_items WHERE product_id = $id";
    $check_orders_result = mysqli_query($conn, $check_orders_query);
    
    if (!$check_orders_result) {
        $_SESSION['error_message'] = "Error checking order history: " . mysqli_error($conn);
        header("Location: produk.php");
        exit();
    }
    
    $orders_row = mysqli_fetch_assoc($check_orders_result);
    
    if ($orders_row['total'] > 0) {
        $_SESSION['error_message'] = "Produk tidak dapat dihapus karena sudah ada dalam riwayat pesanan.";
        header("Location: produk.php");
        exit();
    }

    // Check if the product is referenced in cart
    $check_cart_query = "SELECT COUNT(*) as total FROM cart WHERE product_id = $id";
    $check_cart_result = mysqli_query($conn, $check_cart_query);
    
    if (!$check_cart_result) {
        $_SESSION['error_message'] = "Error checking cart usage: " . mysqli_error($conn);
        header("Location: produk.php");
        exit();
    }
    
    $cart_row = mysqli_fetch_assoc($check_cart_result);
    
    // Start transaction
    mysqli_autocommit($conn, false);
    
    try {
        // If product is in cart, remove it first
        if ($cart_row['total'] > 0) {
            $delete_cart_result = mysqli_query($conn, "DELETE FROM cart WHERE product_id = $id");
            if (!$delete_cart_result) {
                throw new Exception("Failed to remove product from cart: " . mysqli_error($conn));
            }
        }
        
        // Delete the product
        $result = mysqli_query($conn, "DELETE FROM products WHERE id = $id");
        
        if ($result) {
            // Commit transaction
            mysqli_commit($conn);
            
            // Delete the associated image file
            if (!empty($image_to_delete)) {
                $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/djaya_roasters/uploads/products/' . $image_to_delete;
                
                if (file_exists($upload_path)) {
                    unlink($upload_path);
                }
            }
            
            $_SESSION['success_message'] = "Produk berhasil dihapus.";
        } else {
            throw new Exception("Failed to delete product: " . mysqli_error($conn));
        }
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Gagal menghapus produk: " . $e->getMessage();
    }
    
    // Reset autocommit
    mysqli_autocommit($conn, true);
    
    header("Location: produk.php");
    exit();
} else {
    $_SESSION['error_message'] = "ID produk tidak ditentukan.";
    header("Location: produk.php");
    exit();
}
?>
