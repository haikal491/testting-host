<?php
include 'db.php'; // Make sure this path is correct
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$profileImage = 'default.png'; // Default profile image
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

// Fetch all recipes along with their average rating and the current user's rating for each recipe
$stmt = $conn->prepare("
    SELECT r.id, r.name, r.image, r.steps,
           (SELECT ROUND(AVG(rating),1) FROM recipe_ratings WHERE recipe_id = r.id) as avg_rating,
           (SELECT rating FROM recipe_ratings WHERE recipe_id = r.id AND user_id = ?) as user_current_rating
    FROM recipes r
");
if (!$stmt) {
    die('Error preparing statement: ' . $conn->error);
}
$stmt->bind_param("i", $user_id); // Bind user_id for fetching user's individual rating
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Semua Resep - ResepApp</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #e67e22; /* Orange-ish */
            --secondary: #2c3e50; /* Dark Blue */
            --bg: #fdfdfd; /* Off-white background */
            --text: #34495e; /* Slightly lighter dark blue for text */
            --light-grey: #ecf0f1; /* Light grey */
            --dark-grey: #7f8c8d; /* Medium grey */
            --card-shadow: 0 6px 20px rgba(0,0,0,0.08); /* Softer, larger shadow */
            --hover-shadow: 0 12px 30px rgba(0,0,0,0.15); /* More pronounced hover shadow */
            --border-radius: 12px;
            --button-hover-bg: #d35400; /* Darker orange for button hover */
            --star-gold: #f1c40f; /* Gold for stars */
            --star-grey: #cccccc; /* Lighter grey for unselected stars */
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

        /* --- Header Styling --- */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3rem;
            background: white;
            box-shadow: var(--card-shadow); /* Using defined shadow variable */
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--light-grey);
        }
        .logo {
            font-size: 2.2rem; /* Slightly larger logo */
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
            min-width: 160px; /* Slightly wider dropdown */
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

        /* --- Main Content & Recipe List --- */
        main {
            padding: 3rem 2rem;
            max-width: 1280px;
            margin: 2rem auto; /* Consistent margin */
            flex: 1;
            background-color: white; /* Changed main background to white */
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow); /* Added shadow to main content area */
        }
        h2 {
            text-align: center;
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 2.5rem; /* Increased bottom margin */
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
        .recipe-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 1rem 0;
        }
        .recipe-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); /* Lighter shadow for cards */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #e0e0e0;
            min-height: 380px;
            position: relative;
        }
        .recipe-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--hover-shadow); /* More pronounced hover shadow */
        }
        .recipe-card a {
            text-decoration: none;
            color: inherit;
        }
        .recipe-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .recipe-card:hover img {
            transform: scale(1.08);
        }
        .recipe-card .content {
            padding: 1.2rem 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .name {
            font-weight: 700;
            color: var(--secondary);
            font-size: 1.3rem;
            margin-bottom: 0.7rem;
            white-space: normal;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.3;
            min-height: 2.6em;
            transition: color 0.2s ease;
        }
        .recipe-card:hover .name {
            color: var(--primary);
        }
        .steps {
            font-size: 0.95rem;
            color: var(--dark-grey);
            flex: 1;
            line-height: 1.6;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            margin-bottom: 1rem;
            min-height: 4.8em;
        }
        .rating-display {
            font-size: 1.2rem;
            color: var(--star-gold); /* Using star gold variable */
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 5px;
            padding-top: 0.5rem;
            border-top: 1px solid var(--light-grey);
        }
        .rating-display .avg-text {
            font-size: 1rem;
            color: var(--dark-grey);
            font-weight: 600;
        }

        .user-rating {
            display: flex;
            justify-content: flex-start;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }
        .user-rating button {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--star-grey); /* Using star grey variable */
            transition: color 0.2s ease, transform 0.1s ease;
            padding: 0 3px;
        }
        .user-rating button:hover,
        .user-rating button.selected {
            color: var(--star-gold);
            transform: scale(1.1);
        }
        .user-rating button:focus {
            outline: none;
        }

        .no-recipes-message {
            text-align: center;
            padding: 4rem 2rem;
            font-size: 1.3rem;
            color: var(--dark-grey);
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin: 2rem auto;
            max-width: 600px;
            line-height: 1.6;
        }
        .no-recipes-message a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }
        .no-recipes-message a:hover {
            color: var(--button-hover-bg);
            text-decoration: underline;
        }

        /* --- Galeri Produk Styling --- */
        #galeri {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 3rem 2rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .umbi-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .umbi-item {
            background-color: var(--light-grey);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .umbi-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .umbi-item img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 2px solid var(--primary);
            transition: border-color 0.3s ease;
        }
        .umbi-item:hover img {
            border-color: var(--button-hover-bg);
        }

        .umbi-item .deskripsi {
            font-size: 0.95rem;
            color: var(--dark-grey);
            line-height: 1.5;
            flex-grow: 1; /* Allows description to take available space */
        }

        /* --- Footer Styling --- */
        footer {
            text-align: center;
            padding: 2.5rem;
            font-size: 0.95rem;
            color: var(--dark-grey);
            margin-top: auto;
            background-color: #fcfcfc;
            border-top: 1px solid var(--light-grey);
            box-shadow: 0 -3px 10px rgba(0,0,0,0.03); /* Subtle shadow on footer */
        }

        /* --- Responsive Adjustments --- */
        @media (max-width: 1200px) {
            main, #galeri {
                padding: 2.5rem 1.5rem;
            }
            .recipe-list {
                gap: 1.5rem;
            }
            .umbi-container {
                gap: 1.5rem;
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
            main, #galeri {
                padding: 2rem 1rem;
                margin: 1.5rem 1rem;
            }
            h2 {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }
            .recipe-list {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.2rem;
            }
            .recipe-card {
                min-height: 350px;
            }
            .name {
                font-size: 1.2rem;
            }
            .steps {
                font-size: 0.9rem;
            }
            .umbi-container {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            .umbi-item {
                padding: 1rem;
            }
            .umbi-item img {
                width: 120px;
                height: 120px;
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
            main, #galeri {
                padding: 1.5rem 0.5rem;
                margin: 1rem 0.5rem;
            }
            h2 {
                font-size: 1.8rem;
            }
            .recipe-list {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .recipe-card {
                min-height: unset;
            }
            .recipe-card .content {
                padding: 1rem;
            }
            .name {
                font-size: 1.1rem;
            }
            .steps {
                font-size: 0.85rem;
                -webkit-line-clamp: 4;
            }
            .umbi-container {
                grid-template-columns: 1fr;
            }
            .umbi-item img {
                width: 100px;
                height: 100px;
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
            const currentPath = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.center-nav a');
            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href').split('/').pop();
                if (linkPath === currentPath) {
                    link.classList.add('active');
                }
            });

            document.querySelectorAll('.user-rating button').forEach(button => {
                button.addEventListener('click', function() {
                    const recipeId = this.dataset.recipeId;
                    const rating = this.dataset.rating;
                    submitRating(recipeId, rating, this);
                });

                button.addEventListener('mouseover', function() {
                    const parentDiv = this.closest('.user-rating');
                    const hoverRating = parseInt(this.dataset.rating);
                    parentDiv.querySelectorAll('button').forEach(btn => {
                        if (parseInt(btn.dataset.rating) <= hoverRating) {
                            btn.style.color = 'var(--star-gold)'; // Use CSS variable
                        } else {
                            btn.style.color = 'var(--star-grey)'; // Use CSS variable
                        }
                    });
                });

                button.addEventListener('mouseout', function() {
                    const parentDiv = this.closest('.user-rating');
                    const userRatingHiddenInput = parentDiv.querySelector('input[type="hidden"][name="user_rating"]');
                    const currentRating = userRatingHiddenInput ? parseInt(userRatingHiddenInput.value) : 0;

                    parentDiv.querySelectorAll('button').forEach(btn => {
                        if (parseInt(btn.dataset.rating) <= currentRating) {
                            btn.style.color = 'var(--star-gold)'; // Use CSS variable
                        } else {
                            btn.style.color = 'var(--star-grey)'; // Use CSS variable
                        }
                    });
                });
            });

            // Set initial stars based on user's current rating
            document.querySelectorAll('.user-rating').forEach(ratingDiv => {
                const currentRating = parseInt(ratingDiv.querySelector('input[type="hidden"]').value);
                ratingDiv.querySelectorAll('button').forEach(btn => {
                    if (parseInt(btn.dataset.rating) <= currentRating) {
                        btn.style.color = 'var(--star-gold)'; // Use CSS variable
                        btn.classList.add('selected');
                    }
                });
            });
        });

        function submitRating(recipeId, rating, clickedButton) {
            fetch('rate_recipe.php', { // Ensure this points to your new PHP file
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: recipe_id=${recipeId}&rating=${rating}
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(HTTP error! status: ${response.status});
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const recipeCard = clickedButton.closest('.recipe-card');
                    const avgRatingDisplayDiv = recipeCard.querySelector('.rating-display');
                    const userRatingHiddenInput = recipeCard.querySelector(.user-rating input[type="hidden"][name="user_rating"]);

                    // Update the hidden input with the newly submitted rating
                    userRatingHiddenInput.value = data.user_rating;

                    // Update the average rating display
                    avgRatingDisplayDiv.innerHTML = ''; // Clear existing stars
                    const newAvgRating = parseFloat(data.new_avg_rating);
                    const fullStars = Math.floor(newAvgRating);
                    const halfStar = (newAvgRating - fullStars) >= 0.5;
                    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);

                    for (let i = 0; i < fullStars; i++) {
                        avgRatingDisplayDiv.innerHTML += '<i class="fa-solid fa-star"></i>';
                    }
                    if (halfStar) {
                        avgRatingDisplayDiv.innerHTML += '<i class="fa-solid fa-star-half-stroke"></i>';
                    }
                    for (let i = 0; i < emptyStars; i++) {
                        avgRatingDisplayDiv.innerHTML += '<i class="fa-regular fa-star"></i>';
                    }
                    avgRatingDisplayDiv.innerHTML += <span class="avg-text"> (${data.new_avg_rating})</span>;

                    // Update the user's interactive star selection
                    const userRatingButtons = clickedButton.closest('.user-rating').querySelectorAll('button');
                    userRatingButtons.forEach(btn => {
                        btn.classList.remove('selected');
                        btn.style.color = 'var(--star-grey)'; // Reset color to grey first
                        if (parseInt(btn.dataset.rating) <= data.user_rating) {
                            btn.classList.add('selected');
                            btn.style.color = 'var(--star-gold)';
                        }
                    });

                } else {
                    alert('Gagal memberikan rating: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat berkomunikasi dengan server.');
            });
        }
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

<section id="galeri">
    <h2>Umbi-umbian</h2>
    <div class="umbi-container">
      <div class="umbi-item">
         <img src="images/UWI.jpg" alt="Umbi Uwi" class="umbi-clickable" data-key="Uwi">
         <p class="umbi-justify">Umbi uwi atau disebut sebagai Dioscorea alata,
            termasuk dalam jenis gadung-gadungan dan memiliki kandungan gizi yang lengkap serta baik di antara kelompok umbi-umbian. 
            Nutrisi yang terdapat dalam umbi ini meliputi karbohidrat, protein, lemak, vitamin, dan mineral. Selain itu, uwi adalah tanaman yang mudah untuk dibudidayakan. 
            Karakteristik umbi uwi yaitu memiliki kulit yang bewarna coklat ungu, teksturnya halus. Umbi uwi kaya akan karbohidrat, serat, vitamin C, vitamin B, serta mineral. (sumber:Alfianti, 2023)</p>
      </div>
      <div class="umbi-item">
  <img src="images/Singkong.jpg" alt="Ubi Kayu" class="umbi-clickable" data-key="singkong">
  <p class="umbi-description">
    Ubi kayu memiliki nama latin Manihot Esculenta Crantz cassava. Ubi kayu berbeda dengan ubi jalar, karena ubi kayu tumbuh menjulang ke atas bukan merambat. Singkong menjadi bahan pokok di beberapa daerah setelah padi dan jagung karena memiliki kandungan karbohidrat yang tinggi. Karakteristik umbi singkong yaitu memiliki kulit yang berwarna coklat muda, teksturnya kasar dan berkerut. Umbi singkong kaya akan kalium yang membantu mengatur tekanan darah. (sumber: Zana, 2023)
  </p>
</div>

      <div class="umbi-item">
        <img src="images/Umbi Kuning.jpg" alt="Umbi Kuning" class="umbi-clickable" data-key="jintubi">
           <p class="umbi-description">Ubi jalar memiliki nama latin Ipomoea batatas (L.) Lam. kuning merupakan makanan yang banyak
mengandung beta karoten yang berfungsi sebagai antioksidan dan
membantu mengatasi zat kimia penyebab kanker yang dapat merusak
jaringan mata dan membantu mencegah katarak. Karakteristik umbi kuning yaitu memiliki daging berwarna kuning cerah, teksturnya lembut dan manis. ubi kuning kaya akan karbohidrat 24gr, menjadikannya sumber energi yang baik. (sumber: Putri, 2023)</p>
      </div>
      <div class="umbi-item">
        <img src="images/umbi suweg.jpg" alt="Umbi Suweg" class="umbi-clickable" data-key="kiesu">
           <p class="umbi-description">Suweg memiliki nama latin Amorphophallus paeoniifolius (Dennst.) adalah tanaman anggota genus Amorphophallus dan masih
berkerabat dekat dengan bunga bangkai raksasa dan iles-iles. Suweg sering
dicampurbaurkan dengan iles-iles karena keduanya menghasilkan umbi
batang yang dapat dimakan dan ada kemiripan dalam morfologi daun pada
fase vegetatifnya. Karakteristik umbi suweg yaitu memiliki daging yang berwarna kecoklatan, ukuran umbi bervariasi, kaya akan karbohidrat, kalsium dan zat besi. (sumber: Lewu, 2022)</p>
      </div>
      <div class="umbi-item">
        <img src="images/Umbi Cilembu.jpg" alt="Tepung Mocaf" class="umbi-clickable" data-key="cookies">
          <p class="umbi-description">Ubi Cilembu memiliki nama latin Ipomoea batatas 'Cilembu' adalah varietas ubi jalar lokal yang memiliki ciri khas rasa
manis legit dan aroma khas saat dipanggang, karena adanya kandungan
gula alami yang tinggi, terutama jenis maltosa. Ubi ini biasanya berwarna
kulit krem atau cokelat muda dengan daging umbi berwarna oranye pucat
hingga kekuningan. Kandungan karbohidratnya tinggi dan menjadi salah
satu sumber energi yang baik. Karakteristik pada umbi cilembu yaitu memiliki bentuk yang panjang, kulit umbi berwarna coklat, kaya akan karbohidrat, serat, vitamin dan mineral. (sumber: Solihin, 2022)</p> 
      </div>
      <div class="umbi-item">
        <img src="images/umbi gembili.jpg" alt="Umbi Gembili" class="umbi-clickable" data-key="choco-gembili">
          <p class="umbi-description">Gembili memiliki nama ilmiah (Dioscorea esculentaL.) adalah
salah satu jenis umbi yang termasuk dalam keluarga Dioscoreaceae, yang
memiliki peranan penting dalam dunia pangan. Di Indonesia, famili
Dioscoreaceae mencakup beberapa spesies lain seperti Dioscorea alata,
Dioscorea hispida, Dioscorea pentaphylla, dan Dioscorea bulbifera. Salah
satu keunggulan dari jenis Dioscorea adalah kandungan senyawa fungsional
atau bioaktif, di samping senyawa nutrisional yang bermanfaat sebagai
pangan lokal. karakteristik umbi gembili yaitu memiliki rasa yang lebih pahit, berwarna putih, memiliki lendir yang tinngi, Kandungan karbohidrat pada gembili 31,30gr.(sumber: Solihin, 2017)</p>
      </div>
      <div class="umbi-item">
        <img src="images/umbi talas.jpg" alt="Umbi Talas" class="umbi-clickable" data-key="kertal-cookies">
          <p class="umbi-description">Talas memiliki nama latin (Colocasia esculenta) merupakan salah satu jenis umbi-umbian
yang cukup banyak dibudidayakan di Indonesia. Tanaman ini berasal dari
genus Colocasia dan termasuk dalam keluarga Araceae, yang terdiri atas
sekitar 118 genus dan lebih dari 3.000 spesies. Talas merupakan spesies
polimorfik atau benyak bentuk, dengan paling sedikit 2 variates yaitu
dasheen taro dan eddoe. Talas termasuk umbi yang sudah lama
dibudidayakan dan dikonsumsi oleh masyarakat, bagian umbinya yang
dapat dijadikan sebagai bahan pengganti nasi dibeberapa daerah di
Indonesia. karakteristik umbi talas yaitu, dagingnya berwarna putih, memiliki rasa yang manis, dan berbentuk oval. Kandungan gizi karbohidrat pada talas 25gr.(sumber: Arifsyah, 2022)</p>
      </div>
      <div class="umbi-item">
        <img src="images/umbi bengkoang.jpg" alt="Umbi Bengkoang" class="umbi-clickable" data-key="jicama-cookies">
          <p class="umbi-description">Bengkoang memiliki nama latin (Pachyhizus erosus) yang
merupakan tanaman famili Leguminosae pada umumnya memberikan hasil
dalam bentuk umbian. Umbi bengkoang merupakan bahan pangan yang
dapat langsung dikonsumsi ataupun diolah menjadi bentuk lain. Bengkoang
mengandung vitamin C, vitamin B1, Protein, dan serat kasar relative yang
tinggi. Bengkoang merupakan diet rendah kalori, 39 kkal/100g karena
mengandung inul.Karakteristik pada bengkoang yaitu bentuk bulat, dagingnya berwarna putih, tekstur berair dan crunchy.(sumber:KKRI, 2023)</p>
      </div>
      <div class="umbi-item">
        <img src="images/umbi ungu.jpg" alt="Umbi Ungu" class="umbi-clickable" data-key="f-resep-salbingu">
           <p class="umbi-description">Ubi ungu memiliki nama latin Ipomoea batatas (L.) Lam adalah salah satu komoditas pertanian yang telah banyak
dibudidayakan di Indonesia dan dikenal memiliki tingkat produktivitas yang
tinggi. Berbagai varietas ubi ungu yang telah dikembangkan menunjukkan
potensi hasil panen antara 15 hingga 25,70 ton per hektar. Produksi yang
melimpah ini semakin dimanfaatkan untuk berbagai jenis olahan makanan,
sejalan dengan meningkatnya kesadaran masyarakat akan pentingnya
konsumsi pangan sehat yang juga berperan dalam mendukung kesehatan
tubuh. Karakteristik pada umbi ungu yaitu memiliki kulit tebal berwarna ungu, rasa yang manis, tanaman ini kaya akan serat, vitamin, mineral dan antioksidan. Kandungan gizi pada umbi ungu yaitu 18,26gr. (Sumber: Fatsecret, 2024)</p>
      </div>
      <div class="umbi-item">
        <img src="images/kentang.jpg" alt="Kentang" class="umbi-clickable" data-key="garlic-cheese-kies-cookies">
           <p class="umbi-description">Kentang memiliki nama latin (Solanum tuberosum, L.) adalah salah
                                          satu jenis umbi-umbian yang memiliki peranan penting dalam penyediaan
                                          sumber karbohidrat atau makanan pokok bagi masyarakat di seluruh dunia,
                                          setelah gandum, jagung, dan beras. Sebagai umbi-umbian, kentang memiliki
                                          kandungan gizi yang cukup signifikan. Karakteristik dari umbi kentang yaitu dagingnya berwarna kuning, berbentuk oval dan bulat, teksturnya lembut. kandungan karbohidrat pada kentang 13,50gr.(sumber: Niederhauser, 2019)</p>
      </div>
      <div class="umbi-item">
        <img src="images/umbi ganyong.jpg" alt="Umbi Ganyong" class="umbi-clickable" data-key="gelyong-cookies">
           <p class="umbi-description">Umbi gayong memiliki nama latin "Canna edulis Kerr" merupakan umbi-umbian yang berasal dari tanaman yang termasuk dalam keluarga Convolvulaceae. Tanaman ini dikenal karena umbinya yang dapat dimakan dan kaya akan nutrisi. Umbi gayong memiliki berbagai warna, termasuk oranye, kuning, dan ungu, tergantung pada varietasnya. Umbi ganyong memiliki karakteristik yaitu kulit umbi berwarna coklat cenderung kasar dan berkerut, daging umbi berwarna putih. Kandungan karbohidrat pada umbi ganyong yaitu 22,60gr.(sumber:pratiwi, 2022)</p>
      </div>
    </div>
</section>
<style>
  .tepung-umbi-card {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    padding: 24px;
    margin: 40px auto;
    max-width: 900px;
    font-family: 'Arial', sans-serif;
    line-height: 1.8;
    color: #333;
  }

  .tepung-judul {
    color: #d76d00; /* Warna oranye selaras dengan judul cookies */
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 16px;
    text-align: center;
  }

  .tepung-umbi-card p {
    text-align: justify;
    margin-bottom: 20px;
  }

  .tepung-umbi-card ol {
    padding-left: 20px;
    margin: 0;
  }

  .tepung-umbi-card ol li {
    margin-bottom: 12px;
  }
</style>

<div class="tepung-umbi-card">
  <h3 class="tepung-judul">Metode Pembuatan <q>Cookies</q></h3>

  <p>
   Proses pengolahan memiliki peranan penting dalam menentukan mutu akhir cookies, mencakup aspek tekstur, warna, cita rasa, hingga nilai gizinya. Pemilihan metode sangat memengaruhi karakteristik akhir cookies, Berikut penjelasan metode pembentukan cookies umum digunakan
  </p>

  <ol type="A">
    <li style="text-align: justify;">Metode Cut Cookies, yaitu adonan cookies digulung menjadi lembaran datar dan kemudian dipotong menggunakan cetakan atau pisau. Cookies yang dihasilkan biasanya memiliki bentuk yang beragam dan dapat dihias sesuai keinginan. Contoh cookies yang menggunakan metode ini adalah sugar cookies.</li>
    <li style="text-align: justify;">Metode Drop Cookies, Adonan cukup dijatuhkan ke atas loyang menggunakan sendok atau alat takar. Tidak perlu dibentuk secara khusus. Cookies yang dihasilkan biasanya tebal dan kenyal. Contoh: chocolate chip cookies.</li>
    <li style="text-align: justify;">Metode Pressed Cookies Adonan ditekan menggunakan alat cetak atau cookie press untuk membentuk pola tertentu. Hasilnya biasanya renyah dan memiliki tampilan yang menarik. Contoh: spritz cookies.</li>
    <li style="text-align: justify;">Metode Shaped and Molded Cookies, dalam metode ini, adonan dibentuk dengan tangan atau menggunakan cetakan untuk menciptakan bentuk tertentu. Cookies yang dihasilkan bisa bervariasi dalam bentuk dan ukuran. Contoh cookies yang menggunakan metode ini adalah peanut butter cookies yang sering ditekan dengan garpu </li>
    <li style="text-align: justify;">Rolled cookies yaitu adonan diletakkan di atas papan atau meja kerja kemudian digiling dengan menggunakan rolling pin lalu adonan dicetak sesuai dengan selera. Adonan diratakan dengan menggunakan rolling pin. Adonan dicetak dengan menggunakan cetakan cookies. Contoh cookies yang menggunakan metode ini adalah putri salju.</li>
    <li style="text-align: justify;">Bar cookies yaitu adonan yang dimasukkan kedalam Loyang pembakaran yang sudah dialas kertas roti dengan ketebalan ½ cm, dimasak setengah matang lalu dipotong bujur sangkar, kemudian dibakar kembali sampai matang. Adonan diratakan dalam Loyang. kemudian dipotong dan dibakar kembali hingga matang merata. (Brown, 2020)</li>
  </ol>
</div>


    <h2>Resep dan Cara Membuat Cookies</h2>
    <div class="recipe-list">
        <?php if (count($recipes) > 0): ?>
            <?php foreach ($recipes as $recipe): ?>
                <div class="recipe-card">
                    <a href="recipe_detail.php?id=<?= $recipe['id'] ?>" style="text-decoration: none; color: inherit;">
                        <?php if ($recipe['image'] && file_exists($recipe['image'])): ?>
                            <img src="<?= htmlspecialchars($recipe['image']) ?>" alt="<?= htmlspecialchars($recipe['name']) ?>">
                        <?php else: ?>
                            <img src="images/uploads/default.jpg" alt="Gambar default">
                        <?php endif; ?>
                    </a>
                    <div class="content">
                        <a href="recipe_detail.php?id=<?= $recipe['id'] ?>" class="name"><?= htmlspecialchars($recipe['name']) ?></a>
                        <div class="steps">
                            <?php
                            $decodedSteps = json_decode($recipe['steps'], true);
                            if (!empty($decodedSteps)) {
                                $firstFewSteps = implode(" ", array_slice($decodedSteps, 0, 3));
                                echo htmlspecialchars(substr($firstFewSteps, 0, 150));
                                if (strlen($firstFewSteps) > 150 || count($decodedSteps) > 3) {
                                    echo '...';
                                }
                            } else {
                                echo 'Langkah-langkah belum tersedia.';
                            }
                            ?>
                        </div>
                        <div class="rating-display">
                            <?php
                                $rating = floatval($recipe['avg_rating']);
                                $fullStars = floor($rating);
                                $halfStar = ($rating - $fullStars) >= 0.5;
                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

                                for ($i = 0; $i < $fullStars; $i++) {
                                    echo '<i class="fa-solid fa-star"></i>';
                                }
                                if ($halfStar) {
                                    echo '<i class="fa-solid fa-star-half-stroke"></i>';
                                }
                                for ($i = 0; $i < $emptyStars; $i++) {
                                    echo '<i class="fa-regular fa-star"></i>';
                                }
                                echo '<span class="avg-text">' . ($rating > 0 ? " ({$rating})" : " (Belum ada rating)") . '</span>';
                            ?>
                        </div>
                        <div class="user-rating">
                            <input type="hidden" name="user_rating" value="<?= htmlspecialchars($recipe['user_current_rating'] ?? 0) ?>">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" data-recipe-id="<?= $recipe['id'] ?>" data-rating="<?= $i ?>">
                                    <i class="fa-solid fa-star"></i>
                                </button>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-recipes-message">
                <p>Belum ada resep yang tersedia.</p>
                <p>Anda bisa <a href="add_recipe.php">menambahkan resep baru</a>!</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    &copy; 2025 ResepApp. Semua hak dilindungi.
</footer>
</body>
</html>