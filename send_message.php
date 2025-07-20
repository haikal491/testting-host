<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || empty($_POST['message'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$message = trim($_POST['message']);

$stmt = $conn->prepare("INSERT INTO messages (sender, message) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $message);
$stmt->execute();
$stmt->close();

header("Location: index.php");
exit;
