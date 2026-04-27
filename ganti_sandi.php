<?php
session_start();
if(!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'config/koneksi.php';
include 'partials/header.php';
?>

<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>
    
    <main style="flex: 1; padding: 24px; overflow-y: auto;">
        <div class="table-card" style="background: var(--card-bg); padding: 24px; border-radius: 12px; border: 1px solid var(--border-color); max-width: 500px; margin: 0 auto; margin-top: 50px;">
            <h3 style="margin-bottom: 20px; text-align: center; color: var(--text-main);"><i class='bx bx-lock-alt'></i> Ganti Sandi Akun Saya</h3>
            
            <form action="api/user_ganti_sandi.php" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; color: var(--text-main);">Sandi Lama</label>
                    <input type="password" name="sandi_lama" required style="width:100%; padding:12px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:var(--text-main);">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:5px; color: var(--text-main);">Sandi Baru</label>
                    <input type="password" name="sandi_baru" required style="width:100%; padding:12px; border-radius:8px; border:1px solid var(--border-color); background:var(--input-bg); color:var(--text-main);">
                </div>
                
                <button type="submit" name="simpan_sandi_saya" class="btn-primary" style="width: 100%; padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold;">
                    Simpan Sandi Baru
                </button>
            </form>
        </div>
    </main>
</div>
</body>
</html>