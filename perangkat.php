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
    $where .= " AND (p.nama LIKE '%$search%' OR p.jenis LIKE '%$search%') ";
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

$query_str = "
    SELECT p.*, k.nama_kecamatan, d.nama_desa 
    FROM perangkat p
    LEFT JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan 
    LEFT JOIN desa d ON p.id_desa = d.id_desa 
    $where 
    ORDER BY p.id DESC
";
$query = mysqli_query($koneksi, $query_str);

include 'partials/header.php';
?>

<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>
    
    <main style="flex: 1; padding: 24px; overflow-y: auto;">
        
        <div class="table-card" style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <h3 style="margin: 0; color: var(--text-main); display: flex; align-items: center; gap: 8px;"><i class='bx bx-server'></i> Data Perangkat / ODP</h3>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button onclick="bukaModal('modalTambah')" class="btn-primary" style="padding: 10px 15px; background: #10b981; color: white; border-radius: 8px; border: none; font-weight: bold; display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 13px;">
                        <i class='bx bx-plus'></i> Tambah Perangkat
                    </button>
                </div>
            </div>

            <div style="background: var(--hover-bg); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                <form method="GET" action="" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <span style="color: var(--text-main); font-weight: bold; margin-right: 10px;"><i class='bx bx-filter-alt'></i> Filter:</span>
                    
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Nama / Jenis..." style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none; flex: 1; min-width: 150px;">
                    
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
                        <a href="perangkat.php" style="padding: 10px 15px; background: #ef4444; color: white; text-decoration: none; border-radius: 8px;" title="Reset Filter"><i class='bx bx-x'></i></a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 1100px; color: var(--text-main);">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); background: var(--hover-bg);">
                            <th style="padding: 12px;">No</th>
                            <th style="padding: 12px;">Foto</th>
                            <th style="padding: 12px;">Nama & Status</th>
                            <th style="padding: 12px;">Wilayah</th>
                            <th style="padding: 12px; width: 250px;">Deskripsi & Maps</th>
                            <th style="padding: 12px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($query) > 0):
                        while($row = mysqli_fetch_assoc($query)): 
                            
                            // LOGIKA AUTO-GENERATE LINK MAPS
                            $link_maps = $row['maps'] ?? '';
                            if (empty($link_maps) && !empty($row['lat']) && !empty($row['lng'])) {
                                $link_maps = "https://maps.google.com/?q=" . $row['lat'] . "," . $row['lng'];
                            } elseif(empty($link_maps)) {
                                $link_maps = "Belum di-plot";
                            }

                            // STYLE PESAN WHATSAPP
                            $nama_p = strtoupper($row['nama'] ?? '-');
                            $jenis_p = strtoupper($row['jenis'] ?? '-');
                            $kec_p = $row['nama_kecamatan'] ?? '-';
                            $desa_p = $row['nama_desa'] ?? '-';
                            $desk_p = $row['deskripsi'] ?? '-';
                            $loc_p = ($link_maps != 'Belum di-plot') ? $link_maps : 'Belum ada lokasi';

                            $pesan_wa = "*SCRIPT PHZ-DATA PERANGKAT* 🛰️\n";
                            $pesan_wa .= "------------------------------------------\n";
                            $pesan_wa .= "📌 *Nama : * " . $nama_p . "\n";
                            $pesan_wa .= "⚙️ *Jenis : * " . $jenis_p . "\n";
                            $pesan_wa .= "🏘️ *Kecamatan:* " . $kec_p . "\n";
                            $pesan_wa .= "🏡 *Desa : * " . $desa_p . "\n";
                            $pesan_wa .= "📝 *Keterangan : * " . $desk_p . "\n";
                            $pesan_wa .= "------------------------------------------\n\n";
                            $pesan_wa .= "📍 *Link Lokasi : * \n" . $loc_p; 

                            $url_wa = "https://api.whatsapp.com/send?text=" . urlencode($pesan_wa);
                        ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px;"><?= $no++ ?></td>
                            <td style="padding: 12px;">
                                <?php if(!empty($row['foto'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: var(--hover-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 10px;">Kosong</div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <b style="display: block; font-size: 15px;"><?= htmlspecialchars($row['nama'] ?? '') ?></b>
                                <code style="background: #334155; color: #38bdf8; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-bottom: 5px; display: inline-block; text-transform: uppercase;"><?= htmlspecialchars($row['jenis'] ?? '') ?></code>
                                
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
                            <td style="padding: 12px;">
                                <small>Kec. <?= htmlspecialchars($kec_p) ?><br>Desa <?= htmlspecialchars($desa_p) ?></small>
                            </td>
                            <td style="padding: 12px; font-size: 13px;">
                                <div style="margin-bottom: 5px;"><?= htmlspecialchars($desk_p) ?></div>
                                <?php if($link_maps != 'Belum di-plot'): ?>
                                    <a href="<?= htmlspecialchars($link_maps) ?>" target="_blank" style="color: #3b82f6; text-decoration: none; font-size: 11px; font-weight: bold;">
                                        <i class='bx bx-link-external'></i> Buka Maps
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 11px; font-style: italic;"><i class='bx bx-map-alt'></i> Belum ada link</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <div style="display: flex; gap: 5px; justify-content: center; flex-wrap: nowrap;">
                                    <a href="<?= $url_wa ?>" target="_blank" style="background: #25D366; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;" title="Share WhatsApp">
                                        <i class='bx bxl-whatsapp'></i>
                                    </a>
                                    <a href="index.php?id_target=<?= $row['id'] ?>&tipe=perangkat" style="background: #3b82f6; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;" title="Plot Peta">
                                        <i class='bx bx-map-pin'></i>
                                    </a>
                                    <button class="btn-edit-perangkat" 
                                        data-id="<?= $row['id'] ?>"
                                        data-nama="<?= htmlspecialchars($row['nama'] ?? '', ENT_QUOTES) ?>"
                                        data-jenis="<?= htmlspecialchars($row['jenis'] ?? '', ENT_QUOTES) ?>"
                                        data-idkec="<?= $row['id_kecamatan'] ?? '' ?>"
                                        data-iddesa="<?= $row['id_desa'] ?? '' ?>"
                                        data-deskripsi="<?= htmlspecialchars($row['deskripsi'] ?? '', ENT_QUOTES) ?>"
                                        data-maps="<?= htmlspecialchars($row['maps'] ?? '', ENT_QUOTES) ?>"
                                        data-foto="<?= htmlspecialchars($row['foto'] ?? '', ENT_QUOTES) ?>"
                                        style="background: #eab308; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer;" title="Edit Data">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <a href="api/hapus_perangkat.php?id=<?= $row['id'] ?>&tipe=perangkat" onclick="return confirm('Yakin hapus data permanen?')" title="Hapus Data" style="background: #ef4444; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none;">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr><td colspan="6" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada data perangkat.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="modalTambah" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px;">
    <div style="background: var(--card-bg, #fff); padding: 25px; border-radius: 12px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; position: relative;">
        <i class='bx bx-x' onclick="tutupModal('modalTambah')" style="position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 24px; color: var(--text-muted);"></i>
        
        <h3 style="margin-bottom: 20px; margin-top: 0; color: var(--text-main);"><i class='bx bx-plus'></i> Tambah Perangkat Baru</h3>
        
        <form action="api/tambah_perangkat.php" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Nama Perangkat</label>
                    <input type="text" name="nama" required placeholder="Contoh: ODP 01..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Jenis Perangkat</label>
                    <select name="jenis" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                        <option value="server">Server Utama</option>
                        <option value="odp">ODP (Optical Distribution Point)</option>
                        <option value="splitter">Splitter</option>
                        <option value="htb">HTB / Media Converter</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Kecamatan</label>
                    <select name="id_kecamatan" id="inputModalKecamatan" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
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
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Deskripsi / Lokasi Detail</label>
                <textarea name="deskripsi" rows="2" placeholder="Contoh: Di tiang depan rumah Pak RT..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;"><i class='bx bx-map-alt'></i> Link Google Maps</label>
                    <input type="text" name="maps" placeholder="Paste link untuk auto-plot..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Upload Foto</label>
                    <input type="file" name="foto" accept="image/*" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
            </div>

            <button type="submit" name="simpan" style="width: 100%; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px;">
                <i class='bx bx-save'></i> Simpan Data Perangkat
            </button>
        </form>
    </div>
</div>

<div id="modalEdit" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px;">
    <div style="background: var(--card-bg, #fff); padding: 25px; border-radius: 12px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; position: relative;">
        <i class='bx bx-x' onclick="tutupModal('modalEdit')" style="position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 24px; color: var(--text-muted);"></i>
        
        <h3 style="margin-bottom: 20px; margin-top: 0; color: var(--text-main);"><i class='bx bx-edit'></i> Edit Data Perangkat</h3>
        
        <form action="api/edit_perangkat.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="foto_lama" id="edit_foto_lama">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Nama Perangkat</label>
                    <input type="text" name="nama" id="edit_nama" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Jenis Perangkat</label>
                    <select name="jenis" id="edit_jenis" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                        <option value="server">Server Utama</option>
                        <option value="odp">ODP (Optical Distribution Point)</option>
                        <option value="splitter">Splitter</option>
                        <option value="htb">HTB / Media Converter</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Kecamatan</label>
                    <select name="id_kecamatan" id="edit_kecamatan" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
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
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Deskripsi / Lokasi Detail</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;"><i class='bx bx-map-alt'></i> Link Google Maps Baru</label>
                    <input type="text" name="maps" id="edit_maps" placeholder="Isi untuk perbarui kordinat..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-main); font-size: 13px;">Foto Baru (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); outline: none;">
                    <small id="teks_foto_lama" style="color: var(--text-muted); font-size: 11px; display: block; margin-top: 5px;"></small>
                </div>
            </div>

            <button type="submit" name="update" style="width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px;">
                <i class='bx bx-save'></i> Perbarui Data Perangkat
            </button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function bukaModal(id) { document.getElementById(id).style.display = 'flex'; }
    function tutupModal(id) { document.getElementById(id).style.display = 'none'; }
    
    // Tutup modal jika klik di luar box form
    window.onclick = function(event) {
        if (event.target == document.getElementById('modalTambah')) { tutupModal('modalTambah'); }
        if (event.target == document.getElementById('modalEdit')) { tutupModal('modalEdit'); }
    }

    $(document).ready(function(){
        
        // Load Desa saat Kecamatan diubah (Filter & Modal Tambah)
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

        // Trigger Modal Edit & Load Data Form
        $('.btn-edit-perangkat').click(function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            var jenis = $(this).data('jenis');
            var idkec = $(this).data('idkec');
            var iddesa = $(this).data('iddesa');
            var deskripsi = $(this).data('deskripsi');
            var maps = $(this).data('maps');
            var foto = $(this).data('foto');

            $('#edit_id').val(id);
            $('#edit_nama').val(nama);
            $('#edit_jenis').val(jenis);
            $('#edit_kecamatan').val(idkec);
            $('#edit_deskripsi').val(deskripsi);
            $('#edit_maps').val(maps);
            $('#edit_foto_lama').val(foto);

            if(foto != "") { $('#teks_foto_lama').text("Foto tersimpan: " + foto); } 
            else { $('#teks_foto_lama').text("Belum ada foto"); }

            // Load Desa untuk Modal Edit
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

        // Load Desa saat Kecamatan diubah (Di dalam Modal Edit)
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