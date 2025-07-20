<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$profile_image = $_FILES['profile_image'] ?? null;

if ($password !== '' && $password !== $confirm_password) {
    $_SESSION['msg'] = "Password dan konfirmasi tidak cocok.";
    header("Location: profil.php");
    exit;
}

$stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$oldData = $stmt->get_result()->fetch_assoc();
$oldImage = $oldData['profile_image'] ?? null;

$newImageName = $oldImage;
$uploadSuccess = false;

if ($profile_image && $profile_image['tmp_name']) {
    $ext = strtolower(pathinfo($profile_image['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($ext, $allowed_ext)) {
        $_SESSION['msg'] = "Format gambar tidak diperbolehkan. Gunakan jpg, jpeg, png, atau gif.";
        header("Location: profil.php");
        exit;
    }

    $newImageName = uniqid('profil_', true) . '.' . $ext;

    if (!is_dir('images')) {
        mkdir('images', 0755, true);
    }

    if (move_uploaded_file($profile_image['tmp_name'], "images/$newImageName")) {
        $uploadSuccess = true;
    } else {
        $_SESSION['msg'] = "Gagal mengunggah gambar.";
        header("Location: profil.php");
        exit;
    }
}

if ($password !== '') {
    $hashed = md5($password);
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $username, $email, $hashed, $newImageName, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $newImageName, $user_id);
}

if ($stmt->execute()) {
    if ($uploadSuccess && $oldImage && file_exists("images/$oldImage") && $oldImage !== $newImageName) {
        unlink("images/$oldImage");
    }
    $_SESSION['msg'] = "Profil berhasil diperbarui!";
} else {
    $_SESSION['msg'] = "Gagal memperbarui profil.";
}

header("Location: profil.php");
exit;
