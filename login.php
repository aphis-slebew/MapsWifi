<?php
session_start();

// Panggil koneksi database
include 'config/koneksi.php';

// Jika sudah login, langsung tendang ke peta
if(isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = false;

// Proses saat tombol login ditekan
if(isset($_POST['login'])) {
    // Ambil data dari form dan amankan dari karakter aneh
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    
    // Enkripsi password dengan MD5 (sesuai yang ada di database kamu)
    $password = md5($_POST['password']);

    // Cek kecocokan di database
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");

    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        
        // Deklarasi Session
        $_SESSION['login'] = true;
        $_SESSION['id_user'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        
        // Simpan Role (Admin/Teknisi)
        $_SESSION['role'] = $data['role']; 

        // Berhasil login, arahkan ke index
        header("Location: index.php");
        exit;
    } else {
        // Jika gagal, set variabel error menjadi true untuk memunculkan pesan merah
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MAPS-NET</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-color);
        }
        .login-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border-color);
            text-align: center;
        }
        .login-card i.logo-icon {
            font-size: 50px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <i class='bx bxs-network-chart logo-icon'></i>
        <h2 style="color: var(--text-main); margin-bottom: 5px;">MAPS-NET</h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">Silakan login untuk masuk ke sistem</p>

        <?php if($error): ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; text-align: left; border: 1px solid #f87171;">
                <i class='bx bx-error-circle'></i> Username atau Password salah!
            </div>
        <?php endif; ?>

        <form action="" method="POST" style="text-align: left;">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Contoh: admin" required>
            </div>
            <div class="input-group" style="margin-bottom: 25px;">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login" class="btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 16px;">
                Masuk <i class='bx bx-log-in-circle'></i>
            </button>
        </form>
    </div>

</body>
</html>