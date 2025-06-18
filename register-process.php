<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords don't match!";
        redirect('register.php');
    }

    // Cek apakah username/email sudah ada
    $check = $conn->query("SELECT * FROM users WHERE username = '$username' OR email = '$email'");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Username or email already exists!";
        redirect('register.php');
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $sql = "INSERT INTO users (username, email, password, user_type) VALUES ('$username', '$email', '$hashed_password', 'user')";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = "Registration successful! Please login.";
        redirect('login.php');
    } else {
        $_SESSION['error'] = "Registration failed: " . $conn->error;
        redirect('register.php');
    }
} else {
    redirect('register.php');
}
?>