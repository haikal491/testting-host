<?php
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT username, email, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['msg'] = "Data pengguna tidak ditemukan.";
    header("Location: login.php");
    exit;
}

$profileImg = $user['profile_image'] ? 'images/' . htmlspecialchars($user['profile_image']) : 'images/default-user.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="images/logo.png">
    <style>
        body {
            background-color: #fff8f0;
            font-family: 'Poppins', sans-serif;
        }
        .profile-container {
            max-width: 600px;
            margin: 60px auto;
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.6s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #e67e22;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .profile-pic:hover {
            transform: scale(1.05);
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .btn-primary {
            background-color: #e67e22;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #cf711f;
        }
        .btn-outline-secondary {
            color: #e67e22;
            border-color: #e67e22;
        }
        .btn-outline-secondary:hover {
            background-color: #fae5d3;
            border-color: #e67e22;
        }
        .alert {
            font-size: 0.95rem;
        }
        .fullscreen-img {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.9);
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .fullscreen-img img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .close-btn {
            background-color: #ffffff;
            color: #000;
            padding: 8px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .close-btn:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<div class="profile-container text-center">
    <a href="index.php" class="btn btn-outline-secondary mb-3">&larr; Kembali ke Beranda</a>
    <h3 class="mb-4" style="color:#e67e22;">Profil Pengguna</h3>

    <img src="<?= $profileImg ?>" class="profile-pic" id="profileImage" alt="Foto Profil">

    <div class="fullscreen-img" id="fullscreenContainer">
        <img src="<?= $profileImg ?>" alt="Fullscreen Image">
        <button class="close-btn" id="closeFullscreen">Tutup</button>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-info mt-3"><?= $_SESSION['msg'] ?></div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <form action="update_profil.php" method="POST" enctype="multipart/form-data" class="text-start mt-4">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Ubah Password (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="confirm_password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Unggah Foto Profil</label>
            <input type="file" name="profile_image" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary w-100">Perbarui Profil</button>
    </form>
</div>
2
<script>
    const profileImage = document.getElementById('profileImage');
    const fullscreenContainer = document.getElementById('fullscreenContainer');
    const closeFullscreen = document.getElementById('closeFullscreen');

    profileImage.addEventListener('click', () => {
        fullscreenContainer.style.display = 'flex';
    });

    closeFullscreen.addEventListener('click', () => {
        fullscreenContainer.style.display = 'none';
    });
</script>

</body>
</html>
