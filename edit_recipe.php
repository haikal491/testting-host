<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
$stmt->close();

if (!$recipe) {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $ingredients = $_POST['ingredients'];
    $steps = $_POST['steps'];
    $image_path = $recipe['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'images/uploads/';
        $tmp_name = $_FILES['image']['tmp_name'];
        $new_filename = $upload_dir . uniqid() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($tmp_name, $new_filename);
        $image_path = $new_filename;
    }

    $stmt = $conn->prepare("UPDATE recipes SET name=?, ingredients=?, steps=?, image=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $ingredients, $steps, $image_path, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Resep</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="images/logo.png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f2f2f2;
            padding: 40px;
        }

        .form-container {
            background: white;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }

        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-top: 5px;
            font-family: inherit;
            font-size: 14px;
        }

        button {
            margin-top: 25px;
            padding: 12px 20px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #219150;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        img.preview {
            margin-top: 10px;
            max-width: 100%;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Resep</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Nama Resep:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($recipe['name']) ?>" required>

        <label>Bahan:</label>
        <textarea name="ingredients" rows="4" required><?= htmlspecialchars($recipe['ingredients']) ?></textarea>

        <label>Langkah-langkah:</label>
        <textarea name="steps" rows="4" required><?= htmlspecialchars($recipe['steps']) ?></textarea>

        <label>Gambar Saat Ini:</label><br>
        <?php if ($recipe['image']): ?>
            <img src="<?= htmlspecialchars($recipe['image']) ?>" class="preview">
        <?php else: ?>
            <p>Tidak ada gambar.</p>
        <?php endif; ?>

        <label>Upload Gambar Baru (jika ingin ganti):</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Simpan Perubahan</button>
        <a href="admin_dashboard.php" class="back-link">← Kembali ke Dashboard</a>
    </form>
</div>

</body>
</html>
