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

        // Redirect sesuai peran
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
