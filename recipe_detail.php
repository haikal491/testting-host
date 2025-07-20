<?php
include 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<p class='error-message'>Resep tidak valid.</p>";
    exit;
}

$stmt = $conn->prepare("
    SELECT recipes.*, users.username, users.profile_image 
    FROM recipes 
    JOIN users ON recipes.user_id = users.id 
    WHERE recipes.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "<p class='error-message'>Resep tidak ditemukan.</p>";
    exit;
}

$row = $result->fetch_assoc();
$steps = json_decode($row['steps'], true);
if (!is_array($steps)) {
    $steps = [];
}

// Main recipe image
$imagePath = ltrim($row['image'], '/');
$mainImagePathServer = $_SERVER['DOCUMENT_ROOT'] . '/' . $imagePath;
$mainImagePathUrl = '/' . $imagePath;

// User profile
$username = htmlspecialchars($row['username']);
$profileImageFile = $row['profile_image'];
$profileImage = $profileImageFile ? '/images/' . ltrim($profileImageFile, '/') : null;
$profileImageServer = $_SERVER['DOCUMENT_ROOT'] . $profileImage;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($row['name']) ?> - E-Katalog Cookies</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="images/logo.png">
    <style>
        :root {
            --primary-color: #e67e22;
            --primary-light: #f39c12;
            --text-color: #2c3e50;
            --light-gray: #f8f9fa;
            --medium-gray: #dee2e6;
            --dark-gray: #6c757d;
            --white: #ffffff;
            --shadow-light: rgba(0,0,0,0.05);
            --shadow-medium: rgba(0,0,0,0.1);
            --border-radius: 10px;
        }

        * { 
            box-sizing: border-box; 
            margin: 0;
            padding: 0;
        }
        body {
            background: var(--light-gray);
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            padding: 30px 20px;
            max-width: 900px; /* Slightly wider for better content display */
            margin: auto;
            line-height: 1.7; /* Slightly increased line-height for readability */
        }

        .container {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 25px var(--shadow-light);
            padding: 30px;
            margin-bottom: 30px;
        }

        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 0.5rem;
            font-size: 2.5rem; /* Larger heading */
            font-weight: 700;
            line-height: 1.2;
        }
        .author {
            text-align: center;
            color: var(--dark-gray);
            font-size: 1rem; /* Slightly larger font */
            margin-bottom: 35px; /* More space below author */
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-weight: 400;
        }
        .author-img {
            width: 45px; /* Slightly larger profile image */
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--medium-gray); /* Subtle border */
        }
        h3 {
            color: var(--primary-color);
            margin-top: 2.5rem;
            margin-bottom: 1rem; /* More space below subheadings */
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 8px;
            font-size: 1.5rem; /* Larger subheading */
            font-weight: 600;
        }
        .content-block {
            background: var(--light-gray); /* Light background for content blocks */
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px var(--shadow-light);
            margin-bottom: 25px;
            font-size: 1.05rem; /* Slightly larger text */
            color: var(--text-color);
            line-height: 1.8;
        }
        .error-message {
            text-align: center;
            color: #d9534f; /* A distinct error color */
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            border-radius: var(--border-radius);
            padding: 15px;
            margin: 20px auto;
            max-width: 400px;
            font-weight: 600;
        }
        .step {
            display: flex;
            align-items: flex-start;
            gap: 25px; /* Increased gap */
            margin-bottom: 30px; /* More space between steps */
            background: var(--white);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 12px var(--shadow-light);
            transition: transform 0.2s ease-in-out;
        }
        .step:hover {
            transform: translateY(-3px); /* Subtle hover effect */
        }
        .step img {
            width: 150px; /* Larger step images */
            height: 110px;
            border-radius: var(--border-radius);
            object-fit: cover;
            box-shadow: 0 4px 15px var(--shadow-medium);
            flex-shrink: 0;
            border: 1px solid var(--medium-gray);
        }
        .step p {
            margin: 0;
            font-size: 1rem;
            background: transparent;
            padding: 0;
            box-shadow: none;
            color: var(--text-color);
            line-height: 1.7;
        }
        .main-image {
            width: 100%;
            max-height: 480px; /* Taller main image */
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 30px var(--shadow-medium);
            margin-bottom: 2rem;
            display: block;
        }
        a.back-link {
            display: inline-block;
            margin-top: 40px;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 600;
            border: 2px solid var(--primary-color);
            padding: 12px 25px; /* Larger padding */
            border-radius: 30px;
            transition: all 0.3s ease;
            background-color: var(--white);
            box-shadow: 0 2px 8px var(--shadow-light);
        }
        a.back-link:hover {
            background-color: var(--primary-color);
            color: var(--white);
            box-shadow: 0 4px 15px var(--shadow-medium);
            transform: translateY(-2px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }
            .container {
                padding: 20px;
            }
            h2 {
                font-size: 2rem;
            }
            h3 {
                font-size: 1.3rem;
            }
            .step {
                flex-direction: column; /* Stack image and text on small screens */
                align-items: center;
                text-align: center;
                gap: 15px;
            }
            .step img {
                width: 100%; /* Full width for images on small screens */
                max-width: 250px;
                height: auto;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.8rem;
            }
            .author {
                font-size: 0.9rem;
            }
            h3 {
                font-size: 1.2rem;
            }
            .content-block, .step p {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($row['image']) && file_exists($mainImagePathServer)): ?>
            <img src="<?= htmlspecialchars($mainImagePathUrl) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="main-image">
        <?php endif; ?>

        <h2><?= htmlspecialchars($row['name']) ?></h2>

        <div class="author">
            <?php if ($profileImage && file_exists($profileImageServer)): ?>
                <img src="<?= htmlspecialchars($profileImage) ?>" alt="<?= $username ?>" class="author-img">
            <?php endif; ?>
            <span>Dibuat oleh <strong><?= $username ?></strong></span>
        </div>

        <h3>Bahan:</h3>
        <div class="content-block">
            <p><?= nl2br(htmlspecialchars($row['ingredients'])) ?></p>
        </div>

        <h3>Langkah:</h3>
        <?php foreach ($steps as $i => $step): ?>
            <?php if (!empty($step)): ?>
                <div class="step">
                    <?php
                        $stepImagePath = null;
                        $extensions = ['jpg', 'jpeg', 'png'];
                        foreach ($extensions as $ext) {
                            $checkPath = "images/uploads/steps/step-{$id}-{$i}.{$ext}";
                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $checkPath)) {
                                $stepImagePath = '/' . $checkPath;
                                break;
                            }
                        }
                    ?>
                    <?php if ($stepImagePath): ?>
                        <img src="<?= htmlspecialchars($stepImagePath) ?>" alt="Langkah <?= $i + 1 ?>">
                    <?php endif; ?>
                    <p><strong>Langkah <?= $i + 1 ?>:</strong> <?= htmlspecialchars($step) ?></p>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <a class="back-link" href="#" onclick="goBack()">← Kembali ke Resep Lain</a>
    </div>

<script>
function goBack() {
    if (document.referrer && document.referrer.includes(window.location.host)) { // Only go back if referrer is from the same site
        window.history.back();
    } else {
        window.location.href = "recipes.php"; // Fallback to recipes list
    }
}
</script>

</body>
</html>