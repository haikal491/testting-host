<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

if ($recipe_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Data rating tidak valid']);
    exit;
}

// Cek apakah user sudah pernah kasih rating untuk resep ini
$stmt = $conn->prepare("SELECT id FROM recipe_ratings WHERE user_id = ? AND recipe_id = ?");
$stmt->bind_param("ii", $user_id, $recipe_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    // Update rating yang sudah ada
    $stmt = $conn->prepare("UPDATE recipe_ratings SET rating = ?, created_at = NOW() WHERE user_id = ? AND recipe_id = ?");
    $stmt->bind_param("iii", $rating, $user_id, $recipe_id);
    $stmt->execute();
} else {
    // Insert rating baru
    $stmt = $conn->prepare("INSERT INTO recipe_ratings (user_id, recipe_id, rating) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $recipe_id, $rating);
    $stmt->execute();
}

echo json_encode(['success' => true]);
