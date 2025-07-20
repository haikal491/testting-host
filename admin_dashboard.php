<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit;
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply']) && isset($_POST['message_id'])) {
    $reply = $conn->real_escape_string($_POST['reply']);
    $msg_id = intval($_POST['message_id']);
    
    // Using prepared statements for UPDATE
    $stmt = $conn->prepare("UPDATE messages SET reply = ? WHERE id = ?");
    $stmt->bind_param("si", $reply, $msg_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php"); // Redirect to prevent form resubmission
    exit;
}

if (isset($_GET['delete_message'])) {
    $msg_id = intval($_GET['delete_message']);
    // Using prepared statements for DELETE
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $msg_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit;
}

$keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : '';
if ($keyword !== '') {
    $stmt = $conn->prepare("SELECT * FROM recipes WHERE name LIKE ? OR ingredients LIKE ? OR steps LIKE ? ORDER BY created_at DESC");
    $like = "%$keyword%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM recipes ORDER BY created_at DESC");
}

$messages_raw = $conn->query("SELECT * FROM messages ORDER BY sender ASC, created_at ASC");
$grouped_messages = [];
if ($messages_raw && $messages_raw->num_rows > 0) {
    while ($msg = $messages_raw->fetch_assoc()) {
        $grouped_messages[$msg['sender']][] = $msg;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Resep</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="images/logo.png">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #c2e9fb, #a1c4fd);
            min-height: 100vh;
            padding: 40px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .welcome {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .logout {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .logout:hover {
            text-decoration: underline;
        }
        form {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 8px;
            width: 250px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button[type="submit"] {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        a.reset-link {
            margin-left: 10px;
            color: #e74c3c;
            text-decoration: none;
        }
        a.reset-link:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 10px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        img {
            max-width: 80px;
            border-radius: 8px;
        }
        .delete-button {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .delete-button:hover {
            color: #c0392b;
            text-decoration: underline;
        }
        .edit-button {
            color: #27ae60;
            text-decoration: none;
            font-weight: 500;
            margin-left: 10px;
        }
        .edit-button:hover {
            color: #219150;
            text-decoration: underline;
        }
        .scroll-table {
            overflow-x: auto;
        }
        
        /* Styles for Chat-like Messages */
        .message-group-container {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .sender-group {
            background: #eef5ff;
            border: 1px solid #cceeff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sender-header {
            font-size: 1.3em;
            color: #007bff;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
            text-align: center;
        }

        .chat-messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
            overflow-y: auto;
            max-height: 400px;
            padding-right: 5px;
        }

        .message-bubble {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
            word-wrap: break-word;
        }

        .user-message {
            background-color: #e0e0e0;
            align-self: flex-start;
            border-bottom-left-radius: 2px;
            color: #333;
        }

        .admin-reply-display { /* Renamed from .admin-reply to clarify it's for displaying */
            background-color: #28a745;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 2px;
        }

        .message-timestamp {
            font-size: 0.75em;
            color: #777;
            margin-top: 5px;
            text-align: right;
        }
        .user-message .message-timestamp {
            text-align: left;
            color: #555;
        }
        .admin-reply-display .message-timestamp {
            color: rgba(255, 255, 255, 0.8);
        }

        .message-reply-form { /* This is the form containing the textarea and button */
            display: flex;
            flex-direction: column;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .message-reply-form textarea {
            width: 100%;
            min-height: 80px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 1em;
            resize: vertical;
            background-color: #fff;
        }
        .message-reply-form button[type="submit"] {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            align-self: flex-end;
        }
        .message-reply-form button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .delete-message-link {
            color: #dc3545;
            text-decoration: none;
            font-size: 0.9em;
            margin-top: 10px;
            display: inline-block;
            align-self: flex-end;
            margin-left: auto;
        }
        .delete-message-link:hover {
            text-decoration: underline;
            color: #c82333;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Admin Dashboard - Resep</h1>
    <div class="welcome">
        Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> |
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <form method="GET">
        <input type="text" name="keyword" placeholder="Cari resep..." value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
        <button type="submit">Cari</button>
        <?php if (isset($_GET['keyword']) && $_GET['keyword'] !== ''): ?>
            <a href="admin_dashboard.php" class="reset-link">Reset</a>
        <?php endif; ?>
    </form>

    <div class="scroll-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Nama Resep</th>
                    <th>Bahan</th>
                    <th>Langkah</th>
                    <th>Gambar</th>
                    <th>Waktu Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['ingredients'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['steps'])) ?></td>
                    <td>
                        <?php if ($row['image']): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="Resep">
                        <?php else: ?>
                            Tidak ada
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td>
                        <a class="delete-button" href="admin_dashboard.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus resep ini?')">Hapus</a> |
                        <a class="edit-button" href="edit_recipe.php?id=<?= $row['id'] ?>">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 50px;">
        <h2 style="margin-bottom: 15px; color: #333;">Pesan dari Pengguna</h2>
        <div class="message-group-container">
            <?php if (!empty($grouped_messages)): ?>
                <?php foreach ($grouped_messages as $sender => $messages_from_sender): ?>
                    <div class="sender-group">
                        <h3 class="sender-header">Pesan dari: <?= htmlspecialchars($sender) ?></h3>
                        <div class="chat-messages">
                            <?php
                            $total_messages = count($messages_from_sender);
                            foreach ($messages_from_sender as $index => $msg):
                                $is_last_message = ($index === $total_messages - 1);
                            ?>
                                <div class="message-bubble user-message">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    <div class="message-timestamp"><?= htmlspecialchars(date('H:i', strtotime($msg['created_at']))) ?></div>
                                </div>

                                <?php if (!empty($msg['reply'])): ?>
                                    <div class="message-bubble admin-reply-display">
                                        <?= nl2br(htmlspecialchars($msg['reply'])) ?>
                                        <div class="message-timestamp"><?= htmlspecialchars(date('H:i', strtotime($msg['created_at']))) ?></div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($is_last_message || empty($msg['reply'])): ?>
                                    <form method="post" class="message-reply-form">
                                        <textarea name="reply" placeholder="Balas pesan ini..."><?= htmlspecialchars($msg['reply']) ?></textarea>
                                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                        <button type="submit">Kirim Balasan</button>
                                    </form>
                                <?php endif; ?>

                                <a href="admin_dashboard.php?delete_message=<?= $msg['id'] ?>" onclick="return confirm('Yakin ingin menghapus pesan ini?')" class="delete-message-link">Hapus Pesan Ini</a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1 / -1;">Belum ada pesan dari pengguna.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>