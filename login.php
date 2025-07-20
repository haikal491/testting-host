<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - ResepApp</title>
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

        .login-container {
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

        .login-btn {
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

        .login-btn:hover {
            background-color: #cf711f;
        }

        .link-register {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }

        .link-register a {
            color: #d35400;
            text-decoration: none;
            font-weight: 500;
        }

        .link-register a:hover {
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

<div class="login-container">
    <div class="logo">
        <img src="images/logo.png" alt="ResepApp Logo">
        <div class="logo-title">ResepApp</div>
    </div>
    <h2>Login ke Akun Anda</h2>
    <form action="login_process.php" method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="email@example.com" required>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" name="password" id="password" placeholder="********" required>
        </div>

        <input type="submit" value="Login" class="login-btn">
    </form>

    <div class="link-register">
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</div>

</body>
</html>
