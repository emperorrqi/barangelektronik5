<?php
session_start();
include 'koneksi.php'; // Pastikan file ini koneksi ke DB

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek data dari database
    $stmt = $mysqli->prepare("SELECT username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Jika ditemukan user
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user, $hash);
        $stmt->fetch();

        // Verifikasi password
        if (password_verify($password, $hash)) {
            $_SESSION['username'] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Sistem Persediaan Barang</title>
  <style>
    body {
      background-color: #f4f4f4;
      font-family: Arial, sans-serif;
    }

    .login-container {
      max-width: 400px;
      margin: 80px auto;
      padding: 30px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #333;
    }

    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 12px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #007bff;
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }

    .error {
      color: red;
      text-align: center;
      margin-top: 10px;
    }

    .info {
      text-align: center;
      margin-top: 15px;
    }

    .info a {
      color: #007bff;
      text-decoration: none;
    }

    .info a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login Sistem Persediaan Barang PT Icon Plus</h2>
    <?php if ($error): ?>
      <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
    </form>
    <div class="info">
      Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>
  </div>
</body>
</html>
