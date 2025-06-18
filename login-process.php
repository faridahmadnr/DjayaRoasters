<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            if ($user['user_type'] === 'admin') {
                redirect('../admin/dashboard.php');
            } else {
                redirect('../pages/home.php');
            }
        } else {
            $_SESSION['error'] = "Invalid password!";
            redirect('login.php');
        }
    } else {
        $_SESSION['error'] = "User not found!";
        redirect('login.php');
    }
} else {
    redirect('login.php');
}
?>