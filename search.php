<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
$username = $_SESSION['username'];

include 'db.php';

$profileImage = 'default.png';
$stmt = $conn->prepare("SELECT profile_image FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($dbProfileImage);
if ($stmt->fetch() && !empty($dbProfileImage)) {
    $profileImage = $dbProfileImage;
}
$stmt->close();

$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $bahan = [
        $_GET['bahan1'] ?? '',
        $_GET['bahan2'] ?? '',
        $_GET['bahan3'] ?? '',
        $_GET['bahan4'] ?? ''
    ];
    $bahan = array_filter($bahan, fn($item) => !empty(trim($item)));

    if (!empty($bahan)) {
        // Prepare for dynamic number of parameters
        $types = str_repeat('s', count($bahan));
        $params = [];
        $likeConditions = [];

        foreach ($bahan as $b) {
            $likeConditions[] = "ingredients LIKE ?";
            $params[] = '%' . $conn->real_escape_string($b) . '%';
        }

        $sql = "SELECT * FROM recipes WHERE " . implode(" OR ", $likeConditions);
        $stmt_recipes = $conn->prepare($sql);

        if ($stmt_recipes) {
            // Bind parameters dynamically
            $stmt_recipes->bind_param($types, ...$params);
            $stmt_recipes->execute();
            $result = $stmt_recipes->get_result();

            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            $stmt_recipes->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cari Resep - ResepApp</title>
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
            -moz-osx-font-smoothing: grayscale;
        }

        /* Header Styling (Consistent with other pages) */
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
        /* Main Content */
        main {
            padding: 3rem 2rem;
            width: 100%;
            flex: 1;
            background-color: var(--light-grey);
            border-radius: var(--border-radius);
            margin: 2rem auto;
            max-width: 1280px; /* Max width for main content */
            box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
            box-sizing: border-box; /* Include padding in width */
        }
        h2 {
            text-align: center;
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 2rem;
            font-weight: 700;
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

        .search-layout {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            justify-content: center;
            gap: 3rem; /* Increased gap between form and results */
            padding-top: 1rem;
        }

        .search-form {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            display: flex; /* Changed to flex for better control */
            flex-direction: column;
            gap: 1.2rem; /* Spacing between inputs */
            width: 100%;
            max-width: 350px; /* Slightly wider form */
            box-sizing: border-box;
            border: 1px solid #e0e0e0;
        }

        .search-form input {
            padding: 1rem; /* Increased padding */
            border: 1px solid #c0c0c0; /* Softer border */
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Quicksand', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .search-form input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(230,126,34,0.2);
        }

        .search-form button {
            margin-top: 0.8rem; /* Space above button */
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 1rem 1.5rem; /* Increased padding */
            font-weight: 700; /* Bolder font */
            border-radius: 30px; /* Pill shape */
            cursor: pointer;
            font-size: 1.1rem; /* Slightly larger font */
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .search-form button:hover {
            background-color: var(--button-hover-bg);
            transform: translateY(-3px); /* Lift effect */
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        .results-wrapper {
            flex: 1; /* Take remaining space */
            display: flex;
            justify-content: center; /* Center results if they don't fill space */
            align-items: flex-start;
            padding: 0 1rem; /* Add some padding */
            box-sizing: border-box;
            min-width: 0; /* Allow shrinking */
        }
        .results {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Adaptive grid for results */
            gap: 1.5rem; /* Spacing between result cards */
            justify-content: center; /* Center cards in the grid */
            width: 100%; /* Ensure grid takes full width available */
        }

        .recipe-card {
            background: white;
            border: 1px solid #e0e0e0; /* Softer border */
            border-radius: var(--border-radius);
            overflow: hidden;
            text-decoration: none;
            color: var(--text);
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 280px; /* Ensure consistent card height */
        }
        .recipe-card:hover {
            transform: translateY(-8px); /* More pronounced lift */
            box-shadow: var(--hover-shadow);
        }
        .recipe-card img {
            width: 100%;
            height: 160px; /* Slightly larger image height */
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .recipe-card:hover img {
            transform: scale(1.08); /* More pronounced zoom */
        }
        .recipe-card .name {
            padding: 1rem; /* Increased padding */
            font-weight: 700;
            text-align: center;
            color: var(--secondary); /* Changed to secondary for consistency */
            font-size: 1.15rem; /* Slightly larger font */
            flex-grow: 1; /* Allow name to take available space */
            display: flex;
            align-items: center; /* Vertically center name */
            justify-content: center;
            word-break: break-word; /* Allow long words to break */
        }
        .no-results {
            text-align: center;
            padding: 3rem 2rem;
            font-size: 1.3rem;
            color: var(--dark-grey);
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin: 2rem auto;
            max-width: 600px;
            line-height: 1.6;
            box-sizing: border-box;
        }

        /* Footer (Consistent with other pages) */
        footer {
            text-align: center;
            padding: 2.5rem;
            font-size: 0.95rem;
            color: var(--dark-grey);
            margin-top: auto;
            background-color: #fcfcfc;
            border-top: 1px solid var(--light-grey);
        }

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .search-layout {
                gap: 2rem;
            }
            .results {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
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
                margin: 1.5rem 1rem;
            }
            h2 {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }
            .search-layout {
                flex-direction: column; /* Stack form and results vertically */
                align-items: center; /* Center items when stacked */
                gap: 2rem;
            }
            .search-form {
                max-width: 90%; /* Allow form to expand more on smaller screens */
                padding: 1.5rem;
            }
            .results-wrapper {
                padding: 0; /* Remove horizontal padding from wrapper */
            }
            .results {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Adjust card size */
                gap: 1rem;
            }
            .recipe-card {
                min-height: unset; /* Allow height to adjust naturally */
            }
            .recipe-card img {
                height: 120px; /* Reduce image height */
            }
            .recipe-card .name {
                font-size: 1rem;
                padding: 0.8rem;
            }
            .no-results {
                padding: 2rem 1rem;
                font-size: 1.1rem;
            }
        }
        @media (max-width: 480px) {
            header {
                padding: 1rem 1rem;
            }
            .center-nav {
                gap: 1rem;
                font-size: 0.9rem;
            }
            .user-button {
                font-size: 1rem;
            }
            h2 {
                font-size: 1.8rem;
            }
            .search-form {
                max-width: 95%;
            }
            .results {
                grid-template-columns: 1fr; /* Single column on very small screens */
            }
        }
    </style>
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById("userDropdown");
            dropdown.classList.toggle('show');
        }
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                const dropdown = document.getElementById("userDropdown");
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
        <button class="user-button" onclick="toggleDropdown()">
            <img src="images/<?= htmlspecialchars($profileImage) ?>" alt="Profil">
            <?= htmlspecialchars($username) ?>
        </button>
        <div class="dropdown" id="userDropdown">
            <a href="profil.php">Profil</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</header>

<main>
    <h2>Cari Resep Berdasarkan Bahan</h2>
    <div class="search-layout">
        <form method="get" class="search-form">
            <input name="bahan1" placeholder="Bahan 1 (mis. bawang putih)" autocomplete="off" value="<?= htmlspecialchars($_GET['bahan1'] ?? '') ?>">
            <input name="bahan2" placeholder="Bahan 2 (opsional)" autocomplete="off" value="<?= htmlspecialchars($_GET['bahan2'] ?? '') ?>">
            <input name="bahan3" placeholder="Bahan 3 (opsional)" autocomplete="off" value="<?= htmlspecialchars($_GET['bahan3'] ?? '') ?>">
            <input name="bahan4" placeholder="Bahan 4 (opsional)" autocomplete="off" value="<?= htmlspecialchars($_GET['bahan4'] ?? '') ?>">
            <button type="submit">Cari Resep</button>
        </form>

        <div class="results-wrapper">
            <div class="results">
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $recipe): ?>
                        <?php
                            $imgPath = !empty($recipe['image']) && file_exists($recipe['image']) ? $recipe['image'] : 'images/uploads/default.jpg';
                        ?>
                        <a href="recipe_detail.php?id=<?= $recipe['id'] ?>" class="recipe-card">
                            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($recipe['name']) ?>">
                            <div class="name"><?= htmlspecialchars($recipe['name']) ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php elseif (!empty(array_filter([$_GET['bahan1'] ?? '', $_GET['bahan2'] ?? '', $_GET['bahan3'] ?? '', $_GET['bahan4'] ?? '']))): ?>
                    <p class="no-results">Tidak ditemukan resep yang mengandung bahan tersebut. Coba bahan lain!</p>
                <?php else: ?>
                    <p class="no-results">Masukkan satu atau lebih bahan di samping untuk mencari resep.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<footer>
    &copy; 2025 ResepApp. Semua hak dilindungi.
</footer>
</body>
</html>