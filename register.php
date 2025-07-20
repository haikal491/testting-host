<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - ResepApp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="images/logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff8f0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-container {
            background-color: #ffffff;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #d35400;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-size: 14px;
            color: #444;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #e67e22;
            box-shadow: 0 0 6px rgba(230, 126, 34, 0.3);
        }

        .register-btn {
            width: 100%;
            padding: 14px;
            background-color: #e67e22;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .register-btn:hover {
            background-color: #cf711f;
        }

        .link-login {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }

        .link-login a {
            color: #d35400;
            text-decoration: none;
            font-weight: 500;
        }

        .link-login a:hover {
            text-decoration: underline;
        }

        .logo {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo img {
            width: 80px;
        }

        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: #e67e22;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="logo">
        <img src="images/logo.png" alt="ResepApp Logo">
        <div class="logo-title">ResepApp</div>
    </div>
    <h2>Buat Akun Baru</h2>
    <form action="register_process.php" method="POST">
        <div class="form-group">
            <label for="username">Nama Pengguna</label>
            <input type="text" name="username" id="username" placeholder="Masukkan username" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="email@example.com" required>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" name="password" id="password" placeholder="********" required>
        </div>

        <input type="submit" value="Daftar" class="register-btn">
    </form>

    <div class="link-login">
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</div>

</body>
</html>
