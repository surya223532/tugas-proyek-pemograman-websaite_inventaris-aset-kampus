<?php
session_start();
include('include/koneksi.php'); // Pastikan koneksi ke database benar

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Query langsung mencocokkan email dan password plaintext
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username']; // Tambahkan baris ini

        // Redirect sesuai role
        switch ($user['role']) {
            case 'admin': header("Location: adm/admin.php"); break;
            case 'mahasiswa': header("Location: mhs/mahasiswa.php"); break;
            case 'dosen': header("Location: dos/dosen.php"); break;
            case 'staf': header("Location: staf/staf.php"); break;
            case 'pimpinan': header("Location: pimpinan/pimpinan.php"); break;
        }
        exit();
    } else {
        header("Location: login.php?error=Email atau password salah");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login SIMAN</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-image: url('gambar/kampus.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }
        
        .logo {
            width: 100px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #003366;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        button {
            background-color: #003366;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #004080;
        }
        
        .error-message {
            color: red;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="gambar/logo.png" alt="Logo Institusi" class="logo">
        <h1>SISTEM MANAJEMEN INVENTARIS KAMPUS ITH</h1>
        
        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="email">Username</label>
                <input type="text" id="email" name="email" required>
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login">Masuk</button>
        </form>

        <?php if (isset($_GET['error'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
    </div>
</body>

</html>