<?php
session_start();
include 'db.php';

$username = $_SESSION['username'] ?? null;

$sql = "
SELECT r.*,
    COALESCE(AVG(rr.rating), 0) AS avg_rating
FROM recipes r
LEFT JOIN recipe_ratings rr ON r.id = rr.recipe_id
GROUP BY r.id
HAVING avg_rating >= 4
ORDER BY avg_rating DESC
LIMIT 4
";
$result = $conn->query($sql);

function getAverageRating($avg_rating) {
    return round($avg_rating, 1);
}

$profileImage = 'default.png';
if ($username) {
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultImage = $stmt->get_result()->fetch_assoc();
    if (!empty($resultImage['profile_image']) && file_exists("images/" . $resultImage['profile_image'])) {
        $profileImage = $resultImage['profile_image'];
    }
    $stmt->close();
}

$chatMessages = [];
if ($username) {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE sender = ? ORDER BY created_at ASC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultMessages = $stmt->get_result();
    
    while ($msg = $resultMessages->fetch_assoc()) {
        $chatMessages[] = $msg;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ResepApp - Beranda</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #e67e22;
            --secondary: #2c3e50;
            --bg: #fefefe;
            --text: #34495e;
            --light-grey: #ecf0f1;
            --dark-grey: #7f8c8d;
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
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

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
            pointer-events: none;
        }
        .dropdown.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
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

        .main-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 4rem;
            padding: 5rem 2.5rem;
            background: linear-gradient(to bottom right, #fff, #ffeace);
            min-height: 500px;
            text-align: center;
        }
        .main-content-part-1 {
            max-width: 600px;
            text-align: left;
        }
        .main-content-part-1 h2 {
            font-size: 3.8rem;
            margin: 0;
            line-height: 1.1;
            color: var(--secondary);
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }
        .main-content-part-1 .col-2 {
            color: var(--primary);
        }
        .main-content-p, .a-p {
            margin-top: 1.8rem;
            font-size: 1.25rem;
            color: var(--dark-grey);
            max-width: 500px;
        }
        .a-p {
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: inline-block;
            margin-top: 2rem;
            font-size: 1.3rem;
            padding-bottom: 5px;
            border-bottom: 3px solid var(--primary);
            transition: all 0.3s ease;
        }
        .a-p:hover {
            color: var(--button-hover-bg);
            border-color: var(--button-hover-bg);
            transform: translateY(-2px);
        }
        .main-content-part-2 img {
            width: 450px;
            max-width: 100%;
            border-radius: var(--border-radius);
            box-shadow: var(--hover-shadow);
            transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .main-content-part-2 img:hover {
            transform: scale(1.03) rotate(1deg);
        }

        /* Profil Section Styling */
        .profil {
            padding: 4rem 2rem 5rem;
            max-width: 1280px;
            margin: 0 auto;
        }
        .profil h2 {
            text-align: center;
            margin-bottom: 4rem;
            color: var(--primary);
            font-size: 3.2rem;
            font-weight: 700;
            position: relative;
            display: block;
            padding-bottom: 10px;
        }
        .profil h2::after {
            content: '';
            display: block;
            width: 100px;
            height: 5px;
            background: var(--primary);
            margin: 20px auto 0;
            border-radius: 3px;
        }
        .profil-container {
            display: flex;
            align-items: center;
            gap: 2.5rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--light-grey);
        }
        .profil-container:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }
        .foto-profil {
            flex-shrink: 0;
            width: 150px;
            height: 150px;
        }
        .foto-profil img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            box-shadow: 0 0 0 5px rgba(230,126,34,0.2);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .foto-profil img:hover {
            border-color: var(--button-hover-bg);
            box-shadow: 0 0 0 5px rgba(211,84,0,0.3);
        }
        .data-profil {
            text-align: left;
            flex-grow: 1;
        }
        .data-profil p {
            margin: 0.6rem 0;
            font-size: 1.1rem;
            color: var(--text);
        }
        .data-profil strong {
            color: var(--secondary);
            font-weight: 700;
        }
        .data-profil a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .data-profil a:hover {
            color: var(--button-hover-bg);
            text-decoration: underline;
        }


        main {
            padding: 4rem 2rem 5rem;
            max-width: 1280px;
            margin: 0 auto;
        }
        main h1 {
            text-align: center;
            margin-bottom: 4rem;
            color: var(--primary);
            font-size: 3.2rem;
            font-weight: 700;
            position: relative;
            display: block;
            padding-bottom: 10px;
        }
        main h1::after {
            content: '';
            display: block;
            width: 100px;
            height: 5px;
            background: var(--primary);
            margin: 20px auto 0;
            border-radius: 3px;
        }
        .recipe-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 3rem;
            justify-items: center;
        }
        .recipe-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            text-align: center;
            padding: 1.8rem;
            display: flex;
            flex-direction: column;
            min-height: 480px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            max-width: 380px;
            border: 1px solid var(--light-grey);
        }
        .recipe-card:hover {
            transform: translateY(-12px);
            box-shadow: var(--hover-shadow);
        }
        .recipe-card a {
            text-decoration: none;
            color: inherit;
        }
        .recipe-card img {
            width: 100%;
            height: 260px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 1.2rem;
            border: 1px solid var(--light-grey);
        }
        .recipe-card h3 {
            margin: 0.5rem 0 1rem;
            font-size: 1.6rem;
            color: var(--secondary);
            flex-grow: 1;
            font-weight: 700;
            line-height: 1.3;
        }
        .rating {
            color: #f39c12;
            font-size: 1.4rem;
            margin-top: auto;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding-top: 10px;
        }
        .rating .fa-star {
            color: #f39c12;
        }
        .empty-star .fa-star {
            color: #ccc;
        }
        .rating span {
            font-size: 1.1rem;
            color: var(--dark-grey);
            font-weight: 500;
            margin-left: 5px;
        }

        .chat-section {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .chat-toggle-button {
            background-color: var(--primary);
            color: white;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.25);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .chat-toggle-button:hover {
            background-color: var(--button-hover-bg);
            transform: translateY(-5px) scale(1.05);
        }
        .chat-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--hover-shadow);
            width: 380px;
            max-height: 550px;
            display: none;
            flex-direction: column;
            overflow: hidden;
            position: absolute;
            bottom: 85px;
            right: 0;
            animation: fadeIn 0.4s ease-out forwards;
            border: 1px solid var(--light-grey);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chat-header {
            background-color: var(--primary);
            color: white;
            padding: 1rem 1.5rem;
            font-size: 1.3rem;
            font-weight: 600;
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-header i {
            cursor: pointer;
            font-size: 1.1rem;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.2s ease;
        }
        .chat-header i:hover {
            background-color: rgba(255,255,255,0.2);
        }
        .chat-content {
            padding: 15px;
            overflow-y: auto;
            flex-grow: 1;
            max-height: 320px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background-color: #f7f9fb;
        }
        .chat-content p {
            margin: 0;
            padding: 0;
        }
        .message-bubble {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .message-bubble:not(.admin-reply) {
            background-color: #e0e0e0;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            color: #333;
        }

        .message-bubble.admin-reply {
            background-color: var(--primary);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message-bubble strong {
            display: block;
            margin-bottom: 5px;
            font-size: 0.85em;
            opacity: 0.8;
        }

        .message-bubble .timestamp {
            display: block;
            font-size: 0.7em;
            margin-top: 5px;
            text-align: right;
            color: #888;
        }

        .message-bubble.admin-reply .timestamp {
            color: rgba(255, 255, 255, 0.7);
        }

        .chat-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 1.2rem;
            border-top: 1px solid var(--light-grey);
            background-color: white;
        }
        .chat-form textarea {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            resize: vertical;
            min-height: 70px;
            font-family: 'Quicksand', sans-serif;
            font-size: 1rem;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
            transition: border-color 0.2s ease;
        }
        .chat-form textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(230,126,34,0.2);
        }
        .chat-form button {
            background-color: var(--primary);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.05rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }
        .chat-form button:hover {
            background-color: var(--button-hover-bg);
            transform: translateY(-2px);
        }

        footer {
            text-align: center;
            padding: 2.5rem;
            font-size: 0.95rem;
            color: var(--dark-grey);
            margin-top: 5rem;
            background-color: #fcfcfc;
            border-top: 1px solid var(--light-grey);
        }

        @media (max-width: 992px) {
            .main-content-part-1 h2 {
                font-size: 3rem;
            }
            .main-content-part-2 img {
                width: 350px;
            }
            main h1 {
                font-size: 2.8rem;
            }
            .profil h2 {
                font-size: 2.8rem;
            }
            .profil-container {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1.5rem;
            }
            .foto-profil {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            .data-profil {
                text-align: center;
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

            .main-content {
                padding: 3rem 1.5rem;
                flex-direction: column;
                text-align: center;
                gap: 2.5rem;
            }
            .main-content-part-1 {
                max-width: 100%;
            }
            .main-content-part-1 h2 {
                font-size: 2.5rem;
            }
            .main-content-p, .a-p {
                font-size: 1.1rem;
                margin-top: 1rem;
            }
            .main-content-part-2 img {
                width: 280px;
                height: auto;
            }
            main {
                padding: 3rem 1rem;
            }
            main h1, .profil h2 {
                font-size: 2.5rem;
                margin-bottom: 3rem;
            }
            .recipe-card {
                min-height: auto;
                height: auto;
                padding: 1.2rem;
                max-width: 320px;
            }
            .recipe-card img {
                height: 180px;
            }
            .recipe-card h3 {
                font-size: 1.4rem;
            }
            .recipe-list {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .profil-container {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1.5rem;
            }
            .foto-profil {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            .data-profil {
                text-align: center;
            }
            .chat-section {
                bottom: 20px;
                right: 20px;
            }
            .chat-toggle-button {
                width: 55px;
                height: 55px;
                font-size: 1.6rem;
            }
            .chat-container {
                width: 90vw;
                max-width: 320px;
                bottom: 75px;
                right: 5px;
            }
            .chat-header {
                padding: 0.8rem 1.2rem;
                font-size: 1.1rem;
            }
            .chat-content {
                padding: 1rem;
                max-height: 250px;
            }
            .message-bubble {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }
            .chat-form {
                padding: 1rem;
            }
            .chat-form textarea {
                min-height: 50px;
                font-size: 0.9rem;
            }
            .chat-form button {
                padding: 10px 15px;
                font-size: 0.95rem;
            }
        }
    </style>
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        function toggleChat() {
            const chatContainer = document.getElementById('chatContainer');
            chatContainer.style.display = chatContainer.style.display === 'flex' ? 'none' : 'flex';
            if (chatContainer.style.display === 'flex') {
                const chatContent = chatContainer.querySelector('.chat-content');
                chatContent.scrollTop = chatContent.scrollHeight;
            }
        }

        window.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
            const chatToggleButton = document.getElementById('chatToggleButton');
            const chatContainer = document.getElementById('chatContainer');

            if (chatContainer.style.display === 'flex' &&
                e.target !== chatToggleButton && !chatToggleButton.contains(e.target) &&
                e.target !== chatContainer && !chatContainer.contains(e.target)) {
                chatContainer.style.display = 'none';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const chatContent = document.querySelector('.chat-content');
            if (chatContent) {
                chatContent.scrollTop = chatContent.scrollHeight;
            }

            const currentPath = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.center-nav a');
            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href').split('/').pop();
                if (linkPath === currentPath || (currentPath === '' && linkPath === 'index.php')) {
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
                <img src="images/<?= htmlspecialchars($profileImage) ?>" alt="Profil">
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

<div class="main-content">
    <div class="main-content-part-1">
        <h2>E-Katalog Cookies</h2>
        <h2><span class="col-2">Pangan Lokal</span> "Umbi-Umbian"</h2>
       <!-- HTML Bagian E-Katalog --><!-- HTML Bagian E-Katalog -->
<section style="max-width: 800px; margin: 20px auto; font-family: Arial, sans-serif; color: #333;">
  <h2 style="color: #d2691e; border-left: 4px solid #f4a261; padding-left: 10px;"></h2>

  <p style="text-align: justify;">

  </p>

  <p style="text-align: justify;">
  </p>

  <p style="text-align: justify;">
    
  </p> 

  <p style="text-align: justify;">E-katalog dapat didefinisikan sebagai tempat penyimpanan informasi tentang produk ataupun jasa secara elektronik, E-katalog ini berdedikasi mempromosikan aneka olahan kue kering (cookies) berbasis umbi-umbian lokal, sekaligus mengedukasi masyarakat mengenai potensi dan nilai strategis komoditas tersebut sebagai bahan baku pangan alternatif.

<p style="text-align: justify;">Cookies ialah jenis kue kering yang memiliki rasa gurih dan manis, berstruktur renyah, berbentuk kecil, dan dibuat dari bahan pokok tepung, telur, dan lemak diolah dengan cara dipanggang” (Damayanti, dkk., 2020). Salah satu cara untuk menggantikan tepung terigu menjadi non terigu yaitu dengan memanfaatkan bahan pangan lokal dalam produksi makanan sehingga dapat mengurangi ketergantungan terhadap bahan pangan impor.
</p>

  <p style="text-align: justify;">Umbi-umbian merupakan bahan makanan yang berasal dari dalam tanah yang mengandung karbohidrat. Mereka kaya akan, vitamin A, vitamin C, dan mineral seperti kalium, yang sangat penting untuk menjaga kesehatan tubuh. Selain itu, umbi-umbian juga mengandung serat tinggi yang berfungsi untuk memperlancar pencernaan dan dapat membantu mencegah penyakit seperti diabetes dan penyakit jantung. Dengan mengolah umbi-umbian menjadi produk makanan, seperti cookies, kita tidak hanya meningkatkan nilai gizi, tetapi juga menciptakan inovasi kuliner yang lebih sehat dan menarik bagi konsumen. Oleh karena itu, pemanfaatan umbi-umbian dalam produk makanan dapat memberikan alternatif camilan yang lezat sekaligus bermanfaat bagi kesehatan.</p>
</section>


        <a href="recipes.php" class="a-p">Cobain resep</a>
    </div>
    <div class="main-content-part-2">
        <img src="images/UMAR.jpg" alt="Ilustrasi resep">
    </div>
</div>

<section class="profil">
    <h2>PROFIL PENULIS</h2>

    <div class="profil-container">
        <div class="foto-profil">
            <img src="images/profil_rezylia.jpg" alt="Rezylia Felisha">
        </div>
        <div class="data-profil">
            <p><strong>Nama:</strong> Rezylia Felisha</p>
            <p><strong>Tempat Tanggal Lahir:</strong> Bangka Tengah, 16 Juni 2003</p>
            <p><strong>Alamat:</strong> Gg. Flamboyan, No 947-I, Tahunan, Umbulharjo, Yogyakarta</p>
            <p><strong>Angkatan:</strong> PVKK Tata Boga 2021</p>
            <p><strong>Universitas:</strong> Universitas Sarjanawiyata Tamansiswa</p>
            <p><strong>Instagram:</strong> <a href="https://instagram.com/rezyfelishaa" target="_blank">@rezyfelishaa</a></p>
        </div>
    </div>

    <div class="profil-container">
        <div class="foto-profil">
            <img src="images/rinaaaaa.jpg" alt="Rina Setyaningsih">
        </div>
        <div class="data-profil">
            <p><strong>Nama:</strong> Rina Setyaningsih, S.Pd., M.Pd., MCE.</p>
            <p><strong>Tempat Tanggal Lahir:</strong> Sleman, 12 Februari 1989</p>
            <p><strong>Alamat:</strong> Jl. Melati No.123, Condongcatur, Sleman, Yogyakarta</p>
            <p><strong>NIDN:</strong> 0512028901</p>
            <p><strong>Universitas:</strong> Universitas Sarjanawiyata Tamansiswa</p>
            <p><strong>Instagram:</strong> <a href="https://instagram.com/rinasetya" target="_blank">@rinasetya</a></p>
        </div>
    </div>

    <div class="profil-container">
        <div class="foto-profil">
            <img src="images/profil_ika.jpg" alt="Ika Wahyu Kusuma Wati">
        </div>
        <div class="data-profil">
            <p><strong>Nama:</strong> Ika Wahyu Kusuma Wati, M.Pd.</p>
            <p><strong>Tempat Tanggal Lahir:</strong> Klaten, 10 Mei 1985</p>
            <p><strong>Alamat:</strong> Jl. Jombor, Rt 02, Rw 01, Danguran, Klaten Selatan.</p>
            <p><strong>NIDN:</strong> 0521098502</p>
            <p><strong>Universitas:</strong> Universitas Sarjanawiyata Tamansiswa</p>
            <p><strong>Instagram:</strong> <a href="https://instagram.com/lkachay" target="_blank">@lkachay</a></p>
        </div>
    </div>

      <div class="profil-container">
        <div class="foto-profil">
            <img src="images/ANI.jpg" alt="Rina Setyaningsih">
        </div>
        <div class="data-profil">
            <p><strong>Nama:</strong>Dra. Sri Wahyu Andayani, M.Pd.</p>
            <p><strong>Tempat Tanggal Lahir:</strong> Semarang, 20 Januari 1961</p>
            <p><strong>Alamat:</strong> Jl. Munggur No. 81 Demangan, Gondokusuman, Yogyakarta</p>
            <p><strong>NIDN:</strong> 0020016101</p>
            <p><strong>Universitas:</strong> Universitas Sarjanawiyata Tamansiswa</p>
        </div>
    </div>
</section>

<main>
    <h1>Resep dengan Rating Tertinggi</h1>
    <div class="recipe-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()):
                $avg_rating = getAverageRating($row['avg_rating']);
            ?>
                <div class="recipe-card">
                    <a href="recipe_detail.php?id=<?= htmlspecialchars($row['id']) ?>">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    </a>
                    <h3><a href="recipe_detail.php?id=<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['name']) ?></a></h3>
                    <div class="rating" aria-label="Rating <?= $avg_rating ?> dari 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= round($avg_rating)): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <span class="empty-star"><i class="fas fa-star"></i></span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span>(<?= $avg_rating ?>/5)</span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; width: 100%; color: var(--dark-grey); font-size: 1.1rem;">Tidak ada resep tersedia dengan rating 4 ke atas.</p>
        <?php endif; ?>
    </div>
</main>

<section class="chat-section">
    <?php if ($username): ?>
        <button class="chat-toggle-button" id="chatToggleButton" onclick="toggleChat()">
            <i class="fas fa-comment-dots"></i>
        </button>
        <div class="chat-container" id="chatContainer">
            <div class="chat-header">
                Pesan ke Admin
                <i class="fas fa-times" onclick="toggleChat()"></i>
            </div>
            <div class="chat-content">
                <?php if (!empty($chatMessages)): ?>
                    <?php foreach ($chatMessages as $msg): ?>
                        <div class="message-bubble">
                            <p><strong>Anda:</strong><br><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                            <span class="timestamp"><?= htmlspecialchars(date('H:i, d M Y', strtotime($msg['created_at']))) ?></span>
                        </div>
                        <?php if (!empty($msg['reply'])): ?>
                            <div class="message-bubble admin-reply">
                                <p><strong>Admin:</strong><br><?= nl2br(htmlspecialchars($msg['reply'])) ?></p>
                                <span class="timestamp"><?= htmlspecialchars(date('H:i, d M Y', strtotime($msg['created_at']))) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--dark-grey); text-align: center; font-style: italic;">Anda belum mengirim pesan apa pun. Mulai percakapan di bawah.</p>
                <?php endif; ?>
            </div>
            <form method="POST" action="send_message.php" class="chat-form">
                <textarea name="message" rows="4" placeholder="Tulis pesan Anda..." required></textarea>
                <button type="submit">Kirim Pesan</button>
            </form>
        </div>
    <?php else: ?>
        <a href="login.php" class="chat-toggle-button" title="Login untuk chat dengan admin">
            <i class="fas fa-comment-dots"></i>
        </a>
    <?php endif; ?>
</section>

<footer>
    &copy; 2025 ResepApp. Semua hak dilindungi.
</footer>
</body>
</html>