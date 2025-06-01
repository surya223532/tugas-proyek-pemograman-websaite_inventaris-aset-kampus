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
</head>
<body>
    <div class="login-container">
        <img src="/SIMAN/assets/img/logo.png" alt="Logo Institusi" class="logo">
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
