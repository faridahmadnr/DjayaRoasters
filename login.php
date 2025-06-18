<?php
require_once '../config/init.php';
require_once '../config/cart_functions.php';

// Get anonymous cart session ID before login
$anonymous_session_id = isset($_SESSION['cart_id']) ? $_SESSION['cart_id'] : null;

// Check if there's a redirect URL in GET parameter or session
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '');

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Store the anonymous session ID before resetting the session
            $temp_anonymous_session_id = $anonymous_session_id;
            
            // Start fresh session
            session_regenerate_id(true);
            
            // Set user session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Carry over the cart_id if it existed
            if ($temp_anonymous_session_id) {
                $_SESSION['cart_id'] = $temp_anonymous_session_id;
            }
            
            // Migrate anonymous cart to user cart
            if ($temp_anonymous_session_id) {
                migrateCartAfterLogin($user['id'], $temp_anonymous_session_id);
            }
            
            // Clear redirect URL from session
            $redirect_url = '';
            if (!empty($redirect)) {
                $redirect_url = $redirect;
                unset($_SESSION['redirect_after_login']);
            } else if (isset($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
            }
            
            if ($user['user_type'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else if (!empty($redirect_url)) {
                // Check if URL is absolute or relative
                if (strpos($redirect_url, 'http') === 0) {
                    // Absolute URL - make sure it's our domain
                    $parsed = parse_url($redirect_url);
                    if ($parsed['host'] === $_SERVER['HTTP_HOST']) {
                        header('Location: ' . $redirect_url);
                    } else {
                        header('Location: ../index.php');
                    }
                } else {
                    // Relative URL
                    if (strpos($redirect_url, '/') === 0) {
                        // URL starts with /
                        header('Location: ..' . $redirect_url);
                    } else {
                        header('Location: ../' . $redirect_url);
                    }
                }
            } else {
                header('Location: ../index.php');
            }
            exit();
        }
    }
    
    $_SESSION['error'] = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Djaya Roasters</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('../assets/images/hero.jpg');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }
        
        .login-container {
            position: relative;
            z-index: 10;
            background: rgba(150, 114, 89, 0); /* 50% transparency */
            backdrop-filter: blur(5px);
            border-radius: 10px;
            border: 2px solid #F3E2CA;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            color: #F3E2CA;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        
        .login-title {
            font-size: 2.2rem;
            font-weight: 400;
            text-align: center;
            margin-bottom: 30px;
            color: #F3E2CA;
            font-family: 'Georgia', serif;
        }
        
        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #F3E2CA;
            border-radius: 5px;
            color: #fff;
            padding: 12px 15px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            display: block;
        }
        
        .form-control::placeholder {
            color: rgba(243, 226, 202, 0.7);
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: #F3E2CA;
            box-shadow: 0 0 0 0.2rem rgba(243, 226, 202, 0.25);
            outline: none;
            color: #fff;
        }
        
        .btn-login {
            background: transparent;
            border: 2px solid #F3E2CA;
            color: #F3E2CA;
            padding: 12px 0;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
            margin-bottom: 15px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-login:hover {
            background: rgba(243, 226, 202, 0.2);
        }
        .btn-login:focus,
        .btn-login:active {
            background: rgba(243, 226, 202, 0.2);
            box-shadow: none;
            outline: none;
        }
        
        .register-link {
            text-align: center;
            color: #F3E2CA;
            font-size: 14px;
        }
        
        .register-link a {
            color: #F3E2CA;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        .register-link a:focus {
            color: #F3E2CA;
            text-decoration: underline;
            outline: none;
        }
        .tab:focus {
            color: #F3E2CA;
            background-color: rgba(243, 226, 202, 0.1);
            outline: none;
        }
        
        .alert {
            
            border: 1px solid hsl(50, 47.80%, 72.90%);
            color: #F3E2CA;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        
        .info-message {
            background-color: rgba(243, 226, 202, 0.34);
            border: 1px solid #F3E2CA;
            color: #F3E2CA;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .checkbox-group label {
            color: #F3E2CA;
            font-size: 14px;
            margin: 0;
        }
        
        .tab-container {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 1px solid #F3E2CA;
        }
        
        .tab {
            flex: 1;
            text-align: center;
            padding: 10px;
            cursor: pointer;
            color: rgba(243, 226, 202, 0.6);
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            color: #F3E2CA;
            border-bottom: 2px solid #F3E2CA;
        }
        
        .tab:hover:not(.active) {
            color: #F3E2CA;
            background-color: rgba(243, 226, 202, 0.1);
        }
        
        /* Responsive styles */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    
    <div class="login-container">
        <h2 class="login-title">Login Form</h2>
        
        <div class="tab-container">
            <div class="tab active">Login</div>
            <div class="tab" onclick="location.href='register.php'">Signup</div>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['info_message'])): ?>
            <div class="info-message">
                <i class="fas fa-info-circle me-2"></i>
                <?= $_SESSION['info_message']; unset($_SESSION['info_message']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me?</label>
            </div>
            
            <?php if ($redirect): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <?php endif; ?>
            
            <button type="submit" class="btn btn-login">Login</button>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Signup here</a>
            </div>
        </form>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>