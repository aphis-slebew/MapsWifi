<?php
session_start();
if(!isset($_SESSION['login'])) { header("Location: login.php"); exit; }

// PROTEKSI: Cek apakah yang login adalah Admin
if($_SESSION['role'] !== 'admin') { 
    echo "<script>alert('Akses Ditolak! Halaman ini khusus Admin.'); window.location='index.php';</script>"; 
    exit; 
}

include 'config/koneksi.php';
$query = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id DESC"); 

include 'partials/header.php';
?>

<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>
    
    <main style="flex: 1; padding: 24px; overflow-y: auto;">
        <div class="table-card" style="background: var(--card-bg); padding: 24px; border-radius: 12px; border: 1px solid var(--border-color);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: var(--text-main); margin: 0;">Manajemen Pengguna</h3>
                <button onclick="document.getElementById('modalTambahUser').style.display='flex'" class="btn-primary" style="background: var(--primary-color); color: white; padding: 10px 15px; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                    <i class='bx bx-user-plus'></i> Tambah User
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; color: var(--text-main);">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color);">
                            <th style="padding: 12px;">No</th>
                            <th style="padding: 12px;">Nama Lengkap</th>
                            <th style="padding: 12px;">Username</th>
                            <th style="padding: 12px;">Role</th>
                            <th style="padding: 12px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while($row = mysqli_fetch_assoc($query)): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px;"><?= $no++ ?></td>
                            <td style="padding: 12px;"><?= $row['nama'] ?></td>
                            <td style="padding: 12px;"><b><?= $row['username'] ?></b></td>
                            <td style="padding: 12px; text-transform: uppercase;">
                                <span style="background: <?= $row['role'] == 'admin' ? '#ef4444' : '#3b82f6' ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    <?= $row['role'] ?>
                                </span>
                            </td>
                            <td style="padding: 12px; display: flex; gap: 5px; flex-wrap: wrap;">
                                <button onclick="bukaModalSandi(<?= $row['id'] ?>, '<?= $row['username'] ?>')" class="btn-primary" style="background: #eab308; color: white; padding: 6px 10px; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                    <i class='bx bx-key'></i> Sandi
                                </button>
                                
                                <a href="api/hapus_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus permanen user: <?= $row['username'] ?>?')" style="background: #ef4444; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 4px;">
                                    <i class='bx bx-trash'></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="modalTambahUser" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
    <div class="modal-content" style="background:var(--card-bg); padding:24px; border-radius:12px; width:100%; max-width:400px; color: var(--text-main);">
        <h3 style="margin-bottom: 20px;"><i class='bx bx-user-plus'></i> Tambah User Baru</h3>
        <form action="api/tambah_user.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Nama Lengkap</label>
                <input type="text" name="nama" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background: var(--input-bg); color: var(--text-main);">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Username (Untuk Login)</label>
                <input type="text" name="username" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background: var(--input-bg); color: var(--text-main);">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Password</label>
                <input type="password" name="password" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background: var(--input-bg); color: var(--text-main);">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:5px;">Hak Akses (Role)</label>
                <select name="role" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background: var(--input-bg); color: var(--text-main);">
                    <option value="teknisi">Teknisi</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalTambahUser').style.display='none'" style="flex:1; padding:10px; border-radius: 8px; cursor:pointer; background: var(--hover-bg); border: 1px solid var(--border-color); color: var(--text-main);">Batal</button>
                <button type="submit" name="tambah_user" style="flex:1; background:var(--primary-color); color:white; border:none; border-radius: 8px; padding:10px; cursor:pointer;">Simpan User</button>
            </div>
        </form>
    </div>
</div>

<div id="modalSandiAdmin" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
    <div class="modal-content" style="background:var(--card-bg); padding:24px; border-radius:12px; width:100%; max-width:400px; color: var(--text-main);">
        <h3 style="margin-bottom: 20px;">Ganti Sandi: <span id="namaUserSandi"></span></h3>
        <form action="api/admin_ganti_sandi.php" method="POST">
            <input type="hidden" name="id_user" id="idUserSandi">
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Password Baru</label>
                <input type="text" name="password_baru" placeholder="Masukkan sandi baru..." required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border-color); background: var(--input-bg); color: var(--text-main);">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalSandiAdmin').style.display='none'" style="flex:1; padding:10px; border-radius: 8px; cursor:pointer; background: var(--hover-bg); border: 1px solid var(--border-color); color: var(--text-main);">Batal</button>
                <button type="submit" name="ganti_sandi" style="flex:1; background:var(--primary-color); color:white; border:none; border-radius: 8px; padding:10px; cursor:pointer;">Simpan Sandi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalSandi(id, username) {
        document.getElementById('idUserSandi').value = id;
        document.getElementById('namaUserSandi').innerText = username;
        document.getElementById('modalSandiAdmin').style.display = 'flex';
    }
</script>
</body>
</html>