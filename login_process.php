<?php
session_start();
include 'db.php';

if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['login_error'] = "Email atau password tidak boleh kosong.";
    header("Location: login.php");
    exit();
}

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password=MD5('$password')");
$user = mysqli_fetch_assoc($query);

if ($user) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_id'] = $user['id'];

    if ($user['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
} else {
    $_SESSION['login_error'] = "Login gagal. Email atau password salah.";
    header("Location: login.php");
    exit();
}
?>
