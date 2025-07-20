<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$profileImage = 'default.png';
$stmtUser = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
if ($stmtUser) {
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $stmtUser->bind_result($img);
    if ($stmtUser->fetch() && $img && file_exists("images/$img")) {
        $profileImage = htmlspecialchars($img);
    }
    $stmtUser->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $ingredients = $_POST['ingredients'];
    $steps = [];
    for ($i = 1; $i <= 6; $i++) {
        $step = trim($_POST["step$i"]);
        if ($step !== '') {
            $steps[] = $step;
        }
    }
    $steps_json = json_encode($steps);

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid('img_', true) . '.' . $fileExtension;
            $uploadFileDir = 'images/uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = $dest_path;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO recipes (user_id, name, ingredients, steps, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $name, $ingredients, $steps_json, $image_path);
    $stmt->execute();
    $stmt->close();

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Resep - ResepApp</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #e67e22; /* Orange */
            --secondary: #2c3e50; /* Dark Blue-Gray for contrast */
            --bg: #fefefe; /* White-ish background */
            --text: #34495e; /* Darker text for readability */
            --light-grey: #ecf0f1; /* Lighter grey for backgrounds */
            --dark-grey: #7f8c8d; /* Medium grey for subtle text */
            --card-shadow: 0 4px 15px rgba(0,0,0,0.08);
            --hover-shadow: 0 8px 25px rgba(0,0,0,0.15);
            --border-radius: 12px;
            --button-hover-bg: #d35400;
        }
        body {
            margin: 0;
            font-family: 'Quicksand', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        8    -moz-osx-font-smoothing: grayscale;
        }

        /* Header Styling (Consistent with index.php) */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3rem;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--light-grey);
        }
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .logo:hover {
            color: var(--button-hover-bg);
        }
        .center-nav {
            display: flex;
            gap: 2.5rem;
        }
        .center-nav a {
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
            padding: 0.7rem 0;
            position: relative;
            transition: color 0.3s ease;
        }
        .center-nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        .center-nav a:hover {
            color: var(--primary);
        }
        .center-nav a:hover::after,
        .center-nav a.active::after {
            width: 100%;
        }
        .user-menu {
            position: relative;
        }
        .user-button {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.05rem;
            padding: 0.6rem 0.8rem;
            border-radius: var(--border-radius);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .user-button:hover {
            background-color: var(--light-grey);
            color: var(--button-hover-bg);
        }
        .user-button img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
            transition: border-color 0.3s ease;
        }
        .user-button:hover img {
            border-color: var(--button-hover-bg);
        }
        .dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 60px;
            background: white;
            border: 1px solid var(--light-grey);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            min-width: 150px;
            z-index: 10;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            pointer-events: none; /* Disable pointer events when hidden */
        }
        .dropdown.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto; /* Enable pointer events when shown */
        }
        .dropdown a {
            display: block;
            padding: 12px 18px;
            text-decoration: none;
            color: var(--text);
            transition: background 0.2s ease, color 0.2s ease;
            font-weight: 500;
        }
        .dropdown a:hover {
            background: var(--light-grey);
            color: var(--primary);
        }

        /* Main Content - Hero Section */
        main {
            padding: 3rem 2rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* Align to top */
            background-color: var(--light-grey); /* Subtle background for the main area */
        }
        h2 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 2rem;
            font-weight: 700;
            text-align: center;
            position: relative;
            padding-bottom: 10px;
        }
        h2::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--primary);
            margin: 15px auto 0;
            border-radius: 2px;
        }
        form {
            width: 100%;
            max-width: 800px; /* Slightly adjusted max-width */
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            box-sizing: border-box;
            border: 1px solid #e0e0e0;
        }
        .form-group {
            margin-bottom: 1.5rem; /* Increased margin */
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 0.7rem; /* Increased margin */
            color: var(--secondary);
            font-size: 1.1rem;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 1rem; /* Increased padding */
            border: 1px solid #c0c0c0; /* Softer border */
            border-radius: 8px; /* Slightly more rounded */
            font-size: 1rem;
            font-family: 'Quicksand', sans-serif;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            resize: vertical; /* Allow vertical resizing for textarea */
            min-height: 50px; /* Min height for text inputs */
        }
        input[type="file"] {
            padding: 0.75rem 0; /* Adjust padding for file input */
        }
        input:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(230,126,34,0.2); /* Focus glow */
        }
        textarea {
            min-height: 100px; /* Min height for ingredients */
        }
        button[type="submit"] {
            margin-top: 2rem; /* Increased margin */
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 1rem 2rem; /* Increased padding */
            width: 100%;
            border-radius: 30px; /* Pill shape */
            font-size: 1.1rem; /* Slightly larger font */
            font-weight: 700; /* Bolder font */
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        button[type="submit"]:hover {
            background-color: var(--button-hover-bg);
            transform: translateY(-3px); /* Lift effect */
       8     box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        /* Footer (Consistent with index.php) */
        footer {
            text-align: center;
            padding: 2.5rem;
            font-size: 0.95rem;
            color: var(--dark-grey);
            margin-top: auto; /* Push footer to the bottom */
            background-color: #fcfcfc;
            border-top: 1px solid var(--light-grey);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1.2rem 1.5rem;
            }
            .center-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1.5rem;
            }
            .logo {
                font-size: 1.8rem;
            }
            .user-button {
                justify-content: center;
                width: 100%;
                padding: 0.8rem;
            }
            .dropdown {
                position: static;
                width: 100%;
                box-shadow: none;
                border: none;
                text-align: center;
                margin-top: 10px;
                background-color: var(--light-grey);
            }
            .dropdown a {
                padding: 10px 15px;
            }
            main {
                padding: 2rem 1rem;
            }
            h2 {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }
            form {
                padding: 1.5rem;
                border-radius: 10px;
            }
            label {
                font-size: 1rem;
                margin-bottom: 0.6rem;
            }
            input[type="text"],
            textarea {
                padding: 0.8rem;
                font-size: 0.95rem;
                border-radius: 6px;
            }
            button[type="submit"] {
                padding: 0.9rem 1.5rem;
                font-size: 1rem;
                margin-top: 1.5rem;
            }
        }
    </style>
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to current page link in header
            const currentPath = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.center-nav a');
            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href').split('/').pop();
                if (linkPath === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</head>
<body>
<header>
    <a href="index.php" class="logo">E-Katalog Cookies</a>
    <nav class="center-nav">
        <a href="index.php">Beranda</a>
        <a href="add_recipe.php">Tambah</a>
        <a href="recipes.php">Resep</a>
        <a href="search.php">Cari</a>
    </nav>
    <div class="user-menu">
        <?php if ($username): ?>
            <button class="user-button" onclick="toggleDropdown()">
                <img src="images/<?= $profileImage ?>" alt="Profil">
                <?= htmlspecialchars($username) ?>
            </button>
            <div class="dropdown" id="userDropdown">
                <a href="profil.php">Profil</a>
                <a href="logout.php">Logout</a>
            </div>
        <?php else: ?>
            <a href="login.php" style="color: var(--primary); font-weight: 600; padding: 0.7rem 1.2rem; border: 2px solid var(--primary); border-radius: var(--border-radius); transition: background-color 0.3s ease, color 0.3s ease;">Login</a>
        <?php endif; ?>
    </div>
</header>

<main>
    <h2>Tambah Resep Baru</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nama Resep:</label>
            <input type="text" id="name" name="name" required placeholder="Contoh: Nasi Goreng Spesial" />
        </div>
        <div class="form-group">
            <label for="ingredients">Bahan-bahan: (Pisahkan setiap bahan dengan baris baru)</label>
            <textarea id="ingredients" name="ingredients" rows="6" required placeholder="Contoh:&#10;2 piring nasi putih&#10;1 butir telur&#10;50 gr udang"></textarea>
        </div>
        <?php for ($i = 1; $i <= 7; $i++): ?>
            <div class="form-group">
                <label for="step<?= $i ?>">Langkah <?= $i ?>:</label>
                <input type="text" id="step<?= $i ?>" name="step<?= $i ?>" placeholder="Langkah <?= $i ?> membuat resep ini" />
            </div>
        <?php endfor; ?>
        <div class="form-group">
            <label for="image">Gambar Resep: (Opsional)</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif" />
        </div>
        <button type="submit">Simpan Resep</button>
    </form>
</main>

<footer>
    &copy; 2025 ResepApp. Semua hak dilindungi.
</footer>
</body>
</html>