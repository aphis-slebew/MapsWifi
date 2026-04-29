<?php
session_start();
if(!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'config/koneksi.php';

// ==========================================
// 1. TAMBAH DATA (DIJADIKAN 1)
// ==========================================
if(isset($_POST['simpan_wilayah'])) {
    $id_kec = mysqli_real_escape_string($koneksi, $_POST['id_kecamatan']);
    $nama_kec_baru = mysqli_real_escape_string($koneksi, $_POST['nama_kecamatan_baru']);
    $nama_desa = mysqli_real_escape_string($koneksi, $_POST['nama_desa']);
    
    // Jika user memilih "Buat Kecamatan Baru"
    if($id_kec == 'baru' && $nama_kec_baru != '') {
        mysqli_query($koneksi, "INSERT INTO kecamatan (nama_kecamatan) VALUES ('$nama_kec_baru')");
        $id_kec = mysqli_insert_id($koneksi); // Ambil ID yang barusan dibuat
    }
    
    // Jika user juga mengisi Nama Desa, masukkan ke dalam kecamatan tersebut
    if($nama_desa != '') {
        mysqli_query($koneksi, "INSERT INTO desa (id_kecamatan, nama_desa) VALUES ('$id_kec', '$nama_desa')");
    }
    echo "<script>window.location='wilayah.php';</script>"; exit;
}

// ==========================================
// 2. UPDATE / EDIT DATA (DIJADIKAN 1)
// ==========================================
if(isset($_POST['update_wilayah'])) {
    $id_kec = mysqli_real_escape_string($koneksi, $_POST['id_kecamatan_edit']);
    $nama_kec = mysqli_real_escape_string($koneksi, $_POST['nama_kecamatan_edit']);
    $id_desa = mysqli_real_escape_string($koneksi, $_POST['id_desa_edit']);
    $nama_desa = mysqli_real_escape_string($koneksi, $_POST['nama_desa_edit']);
    
    // Update Nama Kecamatan
    if(!empty($id_kec) && !empty($nama_kec)) {
        mysqli_query($koneksi, "UPDATE kecamatan SET nama_kecamatan='$nama_kec' WHERE id_kecamatan='$id_kec'");
    }
    // Update Nama Desa
    if(!empty($id_desa) && !empty($nama_desa)) {
        mysqli_query($koneksi, "UPDATE desa SET nama_desa='$nama_desa' WHERE id_desa='$id_desa'");
    }
    echo "<script>window.location='wilayah.php';</script>"; exit;
}

// ==========================================
// 3. HAPUS DATA (DIJADIKAN 1)
// ==========================================
if(isset($_GET['hapus'])) {
    $tipe = $_GET['tipe'];
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    if($tipe == 'desa') {
        mysqli_query($koneksi, "DELETE FROM desa WHERE id_desa='$id'");
    } elseif($tipe == 'kecamatan') {
        mysqli_query($koneksi, "DELETE FROM kecamatan WHERE id_kecamatan='$id'");
    }
    echo "<script>window.location='wilayah.php';</script>"; exit;
}

// --- LOGIKA PENCARIAN (SEARCH) ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$where = "";
if ($search != '') {
    $where = " WHERE k.nama_kecamatan LIKE '%$search%' OR d.nama_desa LIKE '%$search%' ";
}

// Tampil Data (LEFT JOIN agar kecamatan yg belum punya desa tetap muncul)
$query = mysqli_query($koneksi, "
    SELECT k.id_kecamatan, k.nama_kecamatan, d.id_desa, d.nama_desa 
    FROM kecamatan k 
    LEFT JOIN desa d ON k.id_kecamatan = d.id_kecamatan 
    $where 
    ORDER BY k.id_kecamatan DESC, d.nama_desa ASC
");

include 'partials/header.php';
?>

<style>
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; display: none; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px; }
    .modal-content { background: var(--card-bg, #fff); padding: 25px; border-radius: 12px; width: 100%; max-width: 450px; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1); animation: slideDown 0.3s ease-out; }
    .modal-close { position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 24px; color: var(--text-muted, #666); transition: 0.2s; }
    .modal-close:hover { color: #ef4444; }
    @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .input-group { margin-bottom: 15px; }
    .input-group label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px; }
    .input-group input, .input-group select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none; }
</style>

<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>
    
    <main style="flex: 1; padding: 24px; overflow-y: auto;">
        
        <div class="table-card" style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h3 style="margin: 0; color: var(--text-main); display: flex; align-items: center; gap: 8px;"><i class='bx bx-map-alt'></i> Data Wilayah Operasional</h3>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <form method="GET" action="" style="display: flex; gap: 10px;">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Kecamatan / Desa..." style="padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border-color); outline: none; width: 220px; background: var(--input-bg); color: var(--text-main);">
                        <button type="submit" class="btn-primary" style="padding: 10px 15px; border-radius: 8px; cursor: pointer; border: none; background: #3b82f6; color: white;"><i class='bx bx-search'></i></button>
                        <?php if($search): ?>
                            <a href="wilayah.php" style="padding: 10px 15px; background: #ef4444; color: white; text-decoration: none; border-radius: 8px; display: flex; align-items: center;"><i class='bx bx-x'></i></a>
                        <?php endif; ?>
                    </form>

                    <button onclick="bukaModalTambah()" class="btn-primary" style="padding: 10px 15px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 5px;">
                        <i class='bx bx-plus'></i> Tambah Wilayah
                    </button>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 800px; color: var(--text-main);">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); background: var(--hover-bg);">
                            <th style="padding: 12px; width: 50px;">No</th>
                            <th style="padding: 12px;">Kecamatan</th>
                            <th style="padding: 12px;">Desa</th>
                            <th style="padding: 12px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($query) > 0):
                            while($row = mysqli_fetch_assoc($query)): 
                                $id_kec = $row['id_kecamatan'];
                                $nama_kec = addslashes($row['nama_kecamatan']);
                                $id_desa = $row['id_desa'];
                                $nama_desa = addslashes($row['nama_desa']);
                        ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px;"><?= $no++ ?></td>
                            <td style="padding: 12px; font-weight: bold;">
                                <i class='bx bx-map' style="color: #3b82f6;"></i> <?= $row['nama_kecamatan'] ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if(!empty($row['id_desa'])): ?>
                                    <span style="color: var(--text-main);"><i class='bx bx-home-alt' style="color: #10b981;"></i> <?= $row['nama_desa'] ?></span>
                                <?php else: ?>
                                    <span style="color: #ef4444; font-size: 12px; font-style: italic;">Belum ada desa</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    
                                    <a href="javascript:void(0)" onclick="bukaModalEdit('<?= $id_kec ?>', '<?= $nama_kec ?>', '<?= $id_desa ?>', '<?= $nama_desa ?>')" style="background: #eab308; color: white; padding: 7px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 12px;" title="Edit Data">
                                        <i class='bx bx-edit'></i> Edit
                                    </a>
                                    
                                    <?php if(!empty($row['id_desa'])): ?>
                                        <a href="?hapus=<?= $row['id_desa'] ?>&tipe=desa" onclick="return confirm('Hapus desa <?= $nama_desa ?>?')" style="background: #ef4444; color: white; padding: 7px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 12px;" title="Hapus Desa">
                                            <i class='bx bx-trash'></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <a href="?hapus=<?= $row['id_kecamatan'] ?>&tipe=kecamatan" onclick="return confirm('Hapus kecamatan <?= $nama_kec ?>?')" style="background: #ef4444; color: white; padding: 7px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 12px;" title="Hapus Kecamatan">
                                            <i class='bx bx-trash'></i> Hapus
                                        </a>
                                    <?php endif; ?>
                                    
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                        <tr><td colspan="4" style="padding: 20px; text-align: center; color: var(--text-muted);">Data wilayah tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<div id="modalTambah" class="modal-overlay">
    <div class="modal-content">
        <i class='bx bx-x modal-close' onclick="document.getElementById('modalTambah').style.display='none'"></i>
        <h3 style="margin-bottom: 20px; color: var(--text-main); margin-top:0;"><i class='bx bx-layer-plus'></i> Tambah Wilayah</h3>
        
        <form action="" method="POST">
            <div class="input-group">
                <label>Pilih Kecamatan</label>
                <select name="id_kecamatan" onchange="cekKecamatanBaru(this.value)" required>
                    <option value="baru" style="font-weight:bold; color:#10b981;">+ Buat Kecamatan Baru</option>
                    <?php
                    $q_kec_dropdown = mysqli_query($koneksi, "SELECT * FROM kecamatan ORDER BY nama_kecamatan ASC");
                    while($kd = mysqli_fetch_assoc($q_kec_dropdown)) {
                        echo "<option value='{$kd['id_kecamatan']}'>{$kd['nama_kecamatan']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="input-group" id="input_kec_baru">
                <label>Nama Kecamatan Baru</label>
                <input type="text" name="nama_kecamatan_baru" id="field_kec_baru" placeholder="Ketik nama kecamatan baru..." required>
            </div>

            <div class="input-group">
                <label>Nama Desa (Opsional)</label>
                <input type="text" name="nama_desa" placeholder="Kosongkan jika hanya membuat kecamatan...">
            </div>
            
            <button type="submit" name="simpan_wilayah" class="btn-primary" style="width: 100%; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px;">
                <i class='bx bx-save'></i> Simpan Wilayah
            </button>
        </form>
    </div>
</div>

<div id="modalEdit" class="modal-overlay">
    <div class="modal-content">
        <i class='bx bx-x modal-close' onclick="document.getElementById('modalEdit').style.display='none'"></i>
        <h3 style="margin-bottom: 20px; color: var(--text-main); margin-top:0;"><i class='bx bx-edit'></i> Edit Wilayah</h3>
        
        <form action="" method="POST">
            <input type="hidden" name="id_kecamatan_edit" id="edit_id_kec">
            <input type="hidden" name="id_desa_edit" id="edit_id_desa">
            
            <div class="input-group">
                <label>Ubah Nama Kecamatan</label>
                <input type="text" name="nama_kecamatan_edit" id="edit_nama_kec" required>
                <small style="color: #f59e0b; font-size: 11px;">*Mengubah nama ini akan berimbas ke desa lain di kecamatan yang sama.</small>
            </div>

            <div class="input-group" id="group_edit_desa">
                <label>Ubah Nama Desa</label>
                <input type="text" name="nama_desa_edit" id="edit_nama_desa">
            </div>
            
            <button type="submit" name="update_wilayah" class="btn-primary" style="width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px;">
                <i class='bx bx-save'></i> Update Wilayah
            </button>
        </form>
    </div>
</div>

<script>
    // Logika Modal Tambah
    function bukaModalTambah() {
        document.getElementById('modalTambah').style.display = 'flex';
        cekKecamatanBaru('baru'); // Set default ke baru saat buka
    }

    function cekKecamatanBaru(val) {
        let inputArea = document.getElementById('input_kec_baru');
        let fieldKec = document.getElementById('field_kec_baru');
        if(val === 'baru') {
            inputArea.style.display = 'block';
            fieldKec.required = true;
        } else {
            inputArea.style.display = 'none';
            fieldKec.required = false;
        }
    }

    // Logika Modal Edit
    function bukaModalEdit(id_kec, nama_kec, id_desa, nama_desa) {
        document.getElementById('edit_id_kec').value = id_kec;
        document.getElementById('edit_nama_kec').value = nama_kec;
        
        if(id_desa !== '') {
            document.getElementById('edit_id_desa').value = id_desa;
            document.getElementById('edit_nama_desa').value = nama_desa;
            document.getElementById('group_edit_desa').style.display = 'block';
            document.getElementById('edit_nama_desa').required = true;
        } else {
            document.getElementById('edit_id_desa').value = '';
            document.getElementById('edit_nama_desa').value = '';
            document.getElementById('group_edit_desa').style.display = 'none';
            document.getElementById('edit_nama_desa').required = false;
        }
        document.getElementById('modalEdit').style.display = 'flex';
    }
</script>

</body>
</html>