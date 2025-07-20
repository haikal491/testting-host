<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php'; // Make sure this path is correct
session_start();

header('Content-Type: application/json');

// Check for authenticated user
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

// Validate input
if ($recipe_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Data rating tidak valid.']);
    exit;
}

// Check if a rating by this user for this recipe already exists
$stmt = $conn->prepare("SELECT id FROM recipe_ratings WHERE user_id = ? AND recipe_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for checking existing rating: ' . $conn->error]);
    exit;
}
$stmt->bind_param("ii", $user_id, $recipe_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    // Update existing rating
    $stmt_update = $conn->prepare("UPDATE recipe_ratings SET rating = ?, created_at = NOW() WHERE user_id = ? AND recipe_id = ?");
    if (!$stmt_update) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare update statement: ' . $conn->error]);
        exit;
    }
    $stmt_update->bind_param("iii", $rating, $user_id, $recipe_id);
    if (!$stmt_update->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update rating: ' . $stmt_update->error]);
        exit;
    }
    $stmt_update->close();
} else {
    // Insert new rating
    $stmt_insert = $conn->prepare("INSERT INTO recipe_ratings (user_id, recipe_id, rating) VALUES (?, ?, ?)");
    if (!$stmt_insert) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare insert statement: ' . $conn->error]);
        exit;
    }
    $stmt_insert->bind_param("iii", $user_id, $recipe_id, $rating);
    if (!$stmt_insert->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to insert rating: ' . $stmt_insert->error]);
        exit;
    }
    $stmt_insert->close();
}

// Calculate the new average rating for the recipe
$stmt_avg = $conn->prepare("SELECT ROUND(AVG(rating), 1) AS avg_rating FROM recipe_ratings WHERE recipe_id = ?");
if (!$stmt_avg) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare average rating statement: ' . $conn->error]);
    exit;
}
$stmt_avg->bind_param("i", $recipe_id);
$stmt_avg->execute();
$avg_result = $stmt_avg->get_result();
$avg_row = $avg_result->fetch_assoc();
$new_avg_rating = $avg_row['avg_rating'] ?? 0; // Default to 0 if no ratings

$stmt_avg->close();
$conn->close();

echo json_encode(['success' => true, 'message' => 'Rating berhasil disimpan!', 'new_avg_rating' => $new_avg_rating, 'user_rating' => $rating]);
?>