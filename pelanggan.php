<?php
session_start();
if(!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'config/koneksi.php';

// --- LOGIKA PENCARIAN ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$where = "";
if ($search != '') {
    $where = " WHERE nama LIKE '%$search%' OR alamat LIKE '%$search%' ";
}

$query = mysqli_query($koneksi, "SELECT * FROM pelanggan $where ORDER BY id DESC");

include 'partials/header.php';
?>

<style>
    /* CSS Khusus untuk Pop-up (Modal) */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 9999;
        display: none; align-items: center; justify-content: center;
        backdrop-filter: blur(3px);
    }
    .modal-content {
        background: var(--card-bg, #fff); padding: 24px; border-radius: 12px;
        width: 100%; max-width: 400px; position: relative;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        animation: slideDown 0.3s ease-out;
    }
    .modal-close {
        position: absolute; top: 15px; right: 15px; cursor: pointer;
        font-size: 24px; color: var(--text-muted, #666); transition: 0.2s;
    }
    .modal-close:hover { color: #ef4444; }
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>
    
    <main style="flex: 1; padding: 24px; overflow-y: auto;">
        
        <div class="table-card" style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h3 style="margin: 0;">Data Pelanggan</h3>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <form method="GET" action="" style="display: flex; gap: 10px;">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Nama / Alamat..." style="padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border-color); outline: none; width: 220px; background: var(--input-bg); color: var(--text-main);">
                        <button type="submit" class="btn-primary" style="width: auto; padding: 10px 15px; cursor: pointer;"><i class='bx bx-search'></i></button>
                        <?php if($search): ?>
                            <a href="pelanggan.php" class="btn-primary" style="width: auto; padding: 10px 15px; background: #ef4444; text-decoration: none; display: flex; align-items: center;"><i class='bx bx-x'></i></a>
                        <?php endif; ?>
                    </form>

                    <button onclick="bukaModal()" class="btn-primary" style="width: auto; padding: 10px 20px; background: #10b981; cursor: pointer;">
                        <i class='bx bx-plus'></i> Tambah Data
                    </button>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color);">
                            <th style="padding: 12px;">No</th>
                            <th style="padding: 12px;">Foto</th>
                            <th style="padding: 12px;">Nama</th>
                            <th style="padding: 12px;">Alamat</th>
                            <th style="padding: 12px;">Status</th>
                            <th style="padding: 12px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($query) > 0):
                            while($row = mysqli_fetch_assoc($query)): 
                        ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px;"><?= $no++ ?></td>
                            <td style="padding: 12px;">
                                <?php if(!empty($row['foto'])): ?>
                                    <img src="uploads/<?= $row['foto'] ?>" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: var(--hover-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);"><i class='bx bx-image-alt'></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;"><b><?= $row['nama'] ?></b></td>
                            <td style="padding: 12px;"><?= $row['alamat'] ?></td>
                            <td style="padding: 12px;">
                                <?php if($row['lat']): ?>
                                    <span style="background: #dcfce7; color: #16a34a; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Di-Plot</span>
                                <?php else: ?>
                                    <span style="background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Belum</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; display: flex; gap: 5px; flex-wrap: wrap;">
                                <a href="index.php?id_target=<?= $row['id'] ?>&tipe=pelanggan" title="Plot ke Peta" style="background: #3b82f6; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;"><i class='bx bx-map-pin'></i></a>
                                <a href="edit_pelanggan.php?id=<?= $row['id'] ?>" title="Edit Data" style="background: #eab308; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;"><i class='bx bx-edit'></i></a>
                                <a href="api/hapus_data.php?id=<?= $row['id'] ?>&tipe=pelanggan" onclick="return confirm('Hapus permanen?')" title="Hapus Data" style="background: #ef4444; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;"><i class='bx bx-trash'></i></a>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                        <tr>
                            <td colspan="6" style="padding: 20px; text-align: center; color: var(--text-muted);">Tidak ada data ditemukan.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<div id="modalTambah" class="modal-overlay">
    <div class="modal-content">
        <i class='bx bx-x modal-close' onclick="tutupModal()"></i>
        <h3 style="margin-bottom: 20px; color: var(--text-main);">Tambah Pelanggan</h3>
        
        <form action="api/tambah_pelanggan.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Nama Pelanggan</label>
                <input type="text" name="nama" placeholder="Masukkan nama..." required>
            </div>
            <div class="input-group">
                <label>Alamat</label>
                <input type="text" name="alamat" placeholder="Masukkan alamat..." required>
            </div>
            <div class="input-group">
                <label>Foto Lokasi/Rumah</label>
                <input type="file" name="foto" accept="image/*">
            </div>
            <button type="submit" name="simpan" class="btn-primary" style="margin-top: 15px;">
                <i class='bx bx-save'></i> Simpan Data
            </button>
        </form>
    </div>
</div>

<script>
    // Fungsi untuk membuka dan menutup Pop-up
    function bukaModal() { document.getElementById('modalTambah').style.display = 'flex'; }
    function tutupModal() { document.getElementById('modalTambah').style.display = 'none'; }
    
    // Tutup modal kalau klik di luar kotak
    window.onclick = function(event) {
        let modal = document.getElementById('modalTambah');
        if (event.target == modal) { modal.style.display = "none"; }
    }
</script>

</body>
</html>