<?php
session_start();
require_once '../config/database.php';

// Process registration form
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } else if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    }  else {
        // Check if username or email already exists
        $checkSql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $existingUser = $checkResult->fetch_assoc();
            if ($existingUser['username'] === $username) {
                $error = "Username already exists";
            } else {
                $error = "Email already exists";
            }
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user with default user type 'user'
            $insertSql = "INSERT INTO users (username, email, password, user_type, created_at) VALUES ('$username', '$email', '$hashed_password', 'user', NOW())";
            
            if ($conn->query($insertSql) === TRUE) {
                $success = true;
                $_SESSION['info_message'] = "Registration successful! You can now login.";
                header('Location: login.php');
                exit;
            } else {
                $error = "Error registering user: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Djaya Roasters</title>
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
        
        .register-container {
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
        
        .register-title {
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
        
        .btn-register {
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
        
        .btn-register:hover {
            background: rgba(243, 226, 202, 0.2);
        }
        .btn-register:focus,
        .btn-register:active {
            background: rgba(243, 226, 202, 0.2);
            box-shadow: none;
            outline: none;
        }
        
        .login-link {
            text-align: center;
            color: #F3E2CA;
            font-size: 14px;
        }
        
        .login-link a {
            color: #F3E2CA;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        .login-link a:focus {
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
            background-color: rgba(243, 226, 202, 0.34);
            border: 1px solid #F3E2CA;
            color: #F3E2CA;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
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
            .register-container {
                padding: 30px 20px;
            }
            
            .register-title {
                font-size: 1.8rem;
            }
        }
        
        .form-text {
            color: rgba(243, 226, 202, 0.8);
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    
    <div class="register-container">
        <h2 class="register-title">Signup Form</h2>
        
        <div class="tab-container">
            <div class="tab" onclick="location.href='login.php'">Login</div>
            <div class="tab active">Signup</div>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert"><?= $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Username" required 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <small class="form-text">Password must be at least 6 characters long</small>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            
            <button type="submit" class="btn-register">Signup</button>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>