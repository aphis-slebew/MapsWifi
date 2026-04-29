<?php
session_start();
if(!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'config/koneksi.php';

// --- LOGIKA PENCARIAN & FILTER WILAYAH ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$id_kecamatan = isset($_GET['kecamatan']) ? mysqli_real_escape_string($koneksi, $_GET['kecamatan']) : '';
$id_desa = isset($_GET['desa']) ? mysqli_real_escape_string($koneksi, $_GET['desa']) : '';
$status_plot = isset($_GET['status_plot']) ? $_GET['status_plot'] : '';

$where = " WHERE 1=1 "; 

// 1. Filter Text
if ($search != '') {
    $where .= " AND (p.nama LIKE '%$search%' OR p.pppoe LIKE '%$search%' OR p.alamat LIKE '%$search%') ";
}
// 2. Filter Kecamatan dan Desa
if ($id_kecamatan != '') { 
    $where .= " AND p.id_kecamatan = '$id_kecamatan' "; 
}

if ($id_desa != '') { 
    $where .= " AND p.id_desa = '$id_desa' "; 
}

// 3. Filter Status Plot
if ($status_plot == 'sudah') {
    $where .= " AND p.lat IS NOT NULL AND p.lat != '' ";
} elseif ($status_plot == 'belum') {
    $where .= " AND (p.lat IS NULL OR p.lat = '') ";
}

$query = mysqli_query($koneksi, "
    SELECT p.*, k.nama_kecamatan, d.nama_desa 
    FROM pelanggan p
    LEFT JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan 
    LEFT JOIN desa d ON p.id_desa = d.id_desa 
    $where 
    ORDER BY p.id DESC
");

include 'partials/header.php';
?>

<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>
    
    <main style="flex: 1; padding: 24px; overflow-y: auto;">
        
        <div class="table-card" style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h3 style="margin: 0; color: var(--text-main); display: flex; align-items: center; gap: 8px;"><i class='bx bx-group'></i> Data Pelanggan</h3>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="api/export_pelanggan.php" style="padding: 10px 15px; background: #3b82f6; color: white; border-radius: 8px; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 5px; font-size: 13px;">
                        <i class='bx bxs-file-export'></i> Export
                    </a>
                    
                    <button onclick="bukaModal('modalImport')" style="padding: 10px 15px; background: #f59e0b; color: white; border-radius: 8px; border: none; font-weight: bold; display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
                        <i class='bx bxs-file-import'></i> Import
                    </button>

                    <button onclick="bukaModal('modalTambah')" class="btn-primary" style="padding: 10px 15px; background: #10b981; color: white; border-radius: 8px; border: none; font-weight: bold; display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
                        <i class='bx bx-plus'></i> Tambah Data
                    </button>
                </div>
            </div>

            <div style="background: var(--hover-bg); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                <form method="GET" action="" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <span style="color: var(--text-main); font-weight: bold; margin-right: 10px;"><i class='bx bx-filter-alt'></i> Filter:</span>
                    
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Nama / PPPOE..." style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none; flex: 1; min-width: 150px;">
                    
                    <select name="status_plot" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none; min-width: 140px;">
                        <option value="">-- Semua Status --</option>
                        <option value="sudah" <?= ($status_plot == 'sudah') ? 'selected' : '' ?>>✅ Sudah Plot</option>
                        <option value="belum" <?= ($status_plot == 'belum') ? 'selected' : '' ?>>❌ Belum Plot</option>
                    </select>

                    <select name="kecamatan" id="filterKecamatan" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none; min-width: 140px;">
                        <option value="">-- Semua Kecamatan --</option>
                        <?php
                        $q_kec = mysqli_query($koneksi, "SELECT * FROM kecamatan ORDER BY nama_kecamatan ASC");
                        while($k = mysqli_fetch_assoc($q_kec)) {
                            $sel_k = ($id_kecamatan == $k['id_kecamatan']) ? 'selected' : '';
                            echo "<option value='{$k['id_kecamatan']}' $sel_k>{$k['nama_kecamatan']}</option>";
                        }
                        ?>
                    </select>

                    <select name="desa" id="filterDesa" style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none; min-width: 140px;">
                        <option value="">-- Semua Desa --</option>
                        <?php
                        if($id_kecamatan != '') {
                            $q_desa_filter = mysqli_query($koneksi, "SELECT * FROM desa WHERE id_kecamatan='$id_kecamatan' ORDER BY nama_desa ASC");
                            while($df = mysqli_fetch_assoc($q_desa_filter)) {
                                $sel_d = ($id_desa == $df['id_desa']) ? 'selected' : '';
                                echo "<option value='{$df['id_desa']}' $sel_d>{$df['nama_desa']}</option>";
                            }
                        }
                        ?>
                    </select>

                    <button type="submit" class="btn-primary" style="padding: 10px 15px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer;"><i class='bx bx-search'></i> Terapkan</button>
                    
                    <?php if($search || $id_kecamatan || $id_desa || $status_plot): ?>
                        <a href="pelanggan.php" style="padding: 10px 15px; background: #ef4444; color: white; text-decoration: none; border-radius: 8px;" title="Reset Filter"><i class='bx bx-x'></i></a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 1200px; color: var(--text-main);">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); background: var(--hover-bg);">
                            <th style="padding: 12px;">No</th>
                            <th style="padding: 12px;">Foto</th>
                            <th style="padding: 12px;">Nama & Status</th>
                            <th style="padding: 12px;">No WA</th>
                            <th style="padding: 12px;">Wilayah (Kec/Desa)</th>
                            <th style="padding: 12px; width: 250px;">Alamat & Deskripsi</th>
                            <th style="padding: 12px; text-align: center;">Aksi</th>
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
                                    <div style="width: 50px; height: 50px; background: var(--hover-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 10px;">Kosong</div>
                                <?php endif; ?>
                            </td>
                            
                            <td style="padding: 12px;">
                                <b style="display: block; font-size: 15px;"><?= $row['nama'] ?></b>
                                <code style="background: #334155; color: #38bdf8; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-bottom: 5px; display: inline-block;"><?= $row['pppoe'] ?></code>
                                
                                <div style="margin-top: 5px;">
                                    <?php if(!empty($row['lat'])): ?>
                                        <span style="background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; border: 1px solid #bbf7d0;">
                                            <i class='bx bx-check-circle'></i> Sudah Plot
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; border: 1px solid #fecaca;">
                                            <i class='bx bx-x-circle'></i> Belum Plot
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td style="padding: 12px;"><?= $row['no_wa'] ?></td>
                            <td style="padding: 12px;">
                                <span style="display: block; font-weight: bold;"><i class='bx bx-map' style="color: #3b82f6;"></i> <?= $row['nama_kecamatan'] ?></span>
                                <span style="font-size: 12px; color: var(--text-muted);"><i class='bx bx-home-alt' style="color: #10b981;"></i> <?= $row['nama_desa'] ?></span>
                            </td>
                            <td style="padding: 12px; font-size: 13px;">
                                <b>Alamat:</b> <?= $row['alamat'] ?><br>
                                <?php if(!empty($row['deskripsi'])): ?>
                                    <span style="color: #eab308;"><b>Ket:</b> <?= $row['deskripsi'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: nowrap;">
                                    <?php 
                                        if (!empty($row['lat']) && !empty($row['lng'])) {
                                            $link_lokasi = "https://maps.google.com/?q=" . $row['lat'] . "," . $row['lng'];
                                        } elseif (!empty($row['lokasi_maps'])) {
                                            $link_lokasi = $row['lokasi_maps']; 
                                        } else {
                                            $link_lokasi = "Belum di-plot";
                                        }

                                        $pesan_wa = "*SCRIPT PHZ - DATA PELANGGAN*\n\n"
                                                  . "👤 *Nama:* " . $row['nama'] . "\n"
                                                  . "🌐 *PPPOE:* " . $row['pppoe'] . "\n"
                                                  . "📞 *No WA:* " . $row['no_wa'] . "\n"
                                                  . "🏠 *Alamat:* " . $row['alamat'] . " (" . $row['nama_desa'] . ", " . $row['nama_kecamatan'] . ")\n"
                                                  . "📝 *Deskripsi:* " . $row['deskripsi'] . "\n"
                                                  . "📍 *Link Peta:* " . $link_lokasi; 
                                        
                                        $link_wa = "https://api.whatsapp.com/send?text=" . urlencode($pesan_wa);
                                    ?>
                                    
                                    <a href="<?= $link_wa ?>" target="_blank" title="Kirim ke WA Teknisi" style="background: #22c55e; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                                        <i class='bx bxl-whatsapp'></i>
                                    </a>
                                    
                                    <a href="index.php?id_target=<?= $row['id'] ?>&tipe=pelanggan" title="Plot ke Peta" style="background: #3b82f6; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                                        <i class='bx bx-map-pin'></i>
                                    </a>

                                    <button class="btn-edit" 
                                        data-id="<?= $row['id'] ?>"
                                        data-nama="<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>"
                                        data-pppoe="<?= htmlspecialchars($row['pppoe'], ENT_QUOTES) ?>"
                                        data-nowa="<?= htmlspecialchars($row['no_wa'], ENT_QUOTES) ?>"
                                        data-idkec="<?= $row['id_kecamatan'] ?>"
                                        data-iddesa="<?= $row['id_desa'] ?>"
                                        data-alamat="<?= htmlspecialchars($row['alamat'], ENT_QUOTES) ?>"
                                        data-deskripsi="<?= htmlspecialchars($row['deskripsi'], ENT_QUOTES) ?>"
                                        data-maps="<?= htmlspecialchars($row['lokasi_maps'], ENT_QUOTES) ?>"
                                        data-foto="<?= $row['foto'] ?>"
                                        style="background: #eab308; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer;" title="Edit Data">
                                        <i class='bx bx-edit'></i>
                                    </button>

                                    <a href="api/hapus_data.php?id=<?= $row['id'] ?>&tipe=pelanggan" onclick="return confirm('Yakin hapus data permanen?')" title="Hapus Data" style="background: #ef4444; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="7" style="padding: 20px; text-align: center; color: var(--text-muted);">Tidak ada data pelanggan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="modalTambah" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px;">
    <div style="background: var(--card-bg, #fff); padding: 25px; border-radius: 12px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <i class='bx bx-x' onclick="tutupModal('modalTambah')" style="position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 24px; color: var(--text-muted);"></i>
        
        <h3 style="margin-bottom: 20px; margin-top: 0; color: var(--text-main);"><i class='bx bx-user-plus'></i> Tambah Pelanggan</h3>
        
        <form action="api/tambah_pelanggan.php" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Nama Lengkap</label>
                    <input type="text" name="nama" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">PPPOE</label>
                    <input type="text" name="pppoe" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">No. WhatsApp</label>
                    <input type="number" name="no_wa" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Foto Lokasi/Rumah</label>
                    <input type="file" name="foto" accept="image/*" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Kecamatan</label>
                    <select name="id_kecamatan" id="inputModalKecamatan" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                        <option value="">-- Pilih Kecamatan --</option>
                        <?php
                        $q_kec_modal = mysqli_query($koneksi, "SELECT * FROM kecamatan ORDER BY nama_kecamatan ASC");
                        while($km = mysqli_fetch_assoc($q_kec_modal)) {
                            echo "<option value='{$km['id_kecamatan']}'>{$km['nama_kecamatan']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Desa</label>
                    <select name="id_desa" id="inputModalDesa" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                        <option value="">-- Pilih Desa --</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Alamat Lengkap</label>
                <input type="text" name="alamat" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Deskripsi Tambahan</label>
                <textarea name="deskripsi" rows="2" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;"></textarea>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Link Peta Manual (Opsional)</label>
                <input type="text" name="lokasi_maps" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
            </div>

            <button type="submit" name="simpan_pelanggan" style="width: 100%; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                <i class='bx bx-save'></i> Simpan Data Pelanggan
            </button>
        </form>
    </div>
</div>

<div id="modalEdit" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px;">
    <div style="background: var(--card-bg, #fff); padding: 25px; border-radius: 12px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <i class='bx bx-x' onclick="tutupModal('modalEdit')" style="position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 24px; color: var(--text-muted);"></i>
        
        <h3 style="margin-bottom: 20px; margin-top: 0; color: var(--text-main);"><i class='bx bx-edit'></i> Edit Pelanggan</h3>
        
        <form action="api/edit_pelanggan.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="foto_lama" id="edit_foto_lama">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">PPPOE</label>
                    <input type="text" name="pppoe" id="edit_pppoe" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">No. WhatsApp</label>
                    <input type="number" name="no_wa" id="edit_nowa" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Foto Baru (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                    <small id="teks_foto_lama" style="color: #10b981; font-size: 11px; display: block; margin-top: 5px;"></small>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Kecamatan</label>
                    <select name="id_kecamatan" id="edit_kecamatan" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                        <option value="">-- Pilih Kecamatan --</option>
                        <?php
                        $q_kec_edit = mysqli_query($koneksi, "SELECT * FROM kecamatan ORDER BY nama_kecamatan ASC");
                        while($ke = mysqli_fetch_assoc($q_kec_edit)) {
                            echo "<option value='{$ke['id_kecamatan']}'>{$ke['nama_kecamatan']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Desa</label>
                    <select name="id_desa" id="edit_desa" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                        <option value="">-- Pilih Desa --</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Alamat Lengkap</label>
                <input type="text" name="alamat" id="edit_alamat" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Deskripsi Tambahan</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;"></textarea>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Link Peta Manual (Opsional)</label>
                <input type="text" name="lokasi_maps" id="edit_maps" placeholder="Link Peta Manual (Opsional)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
            </div>

            <button type="submit" name="update_pelanggan" style="width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                <i class='bx bx-save'></i> Perbarui Data Pelanggan
            </button>
        </form>
    </div>
</div>

<div id="modalImport" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px;">
    <div style="background: var(--card-bg, #fff); padding: 25px; border-radius: 12px; width: 100%; max-width: 450px; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <i class='bx bx-x' onclick="tutupModal('modalImport')" style="position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 24px; color: var(--text-muted);"></i>
        
        <h3 style="margin-bottom: 15px; margin-top: 0; color: var(--text-main);"><i class='bx bxs-file-import'></i> Import Data Pelanggan</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">Gunakan fitur <b>Export</b> terlebih dahulu untuk mendapatkan format file CSV yang benar sebelum melakukan proses import.</p>
        
        <form action="api/import_pelanggan.php" method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-main); font-size: 13px;">Pilih File (Hanya .csv)</label>
                <input type="file" name="file_csv" accept=".csv" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="tutupModal('modalImport')" style="flex: 1; padding: 10px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Batal</button>
                <button type="submit" name="import" style="flex: 2; padding: 10px; background: #f59e0b; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Upload & Import Data</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function bukaModal(id) { document.getElementById(id).style.display = 'flex'; }
    function tutupModal(id) { document.getElementById(id).style.display = 'none'; }
    window.onclick = function(event) {
        if (event.target == document.getElementById('modalTambah')) { tutupModal('modalTambah'); }
        if (event.target == document.getElementById('modalEdit')) { tutupModal('modalEdit'); }
        if (event.target == document.getElementById('modalImport')) { tutupModal('modalImport'); }
    }

    $(document).ready(function(){
        
        $('#filterKecamatan, #inputModalKecamatan').change(function(){
            var id_kecamatan = $(this).val();
            var target = $(this).attr('id') == 'filterKecamatan' ? '#filterDesa' : '#inputModalDesa';
            $.ajax({
                type: "POST",
                url: "api/get_desa.php",
                data: {id_kecamatan: id_kecamatan},
                success: function(response){
                    $(target).html('<option value="">-- Pilih Desa --</option>' + response);
                }
            });
        });

        $('.btn-edit').click(function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            var pppoe = $(this).data('pppoe');
            var nowa = $(this).data('nowa');
            var idkec = $(this).data('idkec');
            var iddesa = $(this).data('iddesa');
            var alamat = $(this).data('alamat');
            var deskripsi = $(this).data('deskripsi');
            var foto = $(this).data('foto');
            var maps = $(this).data('maps');

            $('#edit_id').val(id);
            $('#edit_nama').val(nama);
            $('#edit_pppoe').val(pppoe);
            $('#edit_nowa').val(nowa);
            $('#edit_kecamatan').val(idkec);
            $('#edit_alamat').val(alamat);
            $('#edit_deskripsi').val(deskripsi);
            $('#edit_maps').val(maps);
            $('#edit_foto_lama').val(foto);

            if(foto != "") { $('#teks_foto_lama').text("Foto tersimpan: " + foto); } 
            else { $('#teks_foto_lama').text("Belum ada foto"); }

            $.ajax({
                type: "POST",
                url: "api/get_desa.php",
                data: {id_kecamatan: idkec},
                success: function(response){
                    $('#edit_desa').html('<option value="">-- Pilih Desa --</option>' + response);
                    $('#edit_desa').val(iddesa);
                }
            });

            bukaModal('modalEdit');
        });

        $('#edit_kecamatan').change(function(){
            var id_kecamatan = $(this).val();
            $.ajax({
                type: "POST",
                url: "api/get_desa.php",
                data: {id_kecamatan: id_kecamatan},
                success: function(response){
                    $('#edit_desa').html('<option value="">-- Pilih Desa --</option>' + response);
                }
            });
        });

    });
</script>

</body>
</html>