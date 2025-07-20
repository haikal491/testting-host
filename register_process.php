<?php
include 'db.php';

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

$cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
if (mysqli_num_rows($cek) > 0) {
    session_start();
    $_SESSION['register_error'] = "Email sudah terdaftar.";
    header("Location: register.php");
    exit;
}

$query = mysqli_query($conn, "INSERT INTO users (username, email, password, role)
    VALUES ('$username', '$email', MD5('$password'), 'user')");

if ($query) {
    header("Location: login.php");
    exit;
} else {
    session_start();
    $_SESSION['register_error'] = "Registrasi gagal. Silakan coba lagi.";
    header("Location: register.php");
    exit;
}
?>
