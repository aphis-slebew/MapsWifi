<?php
session_start();
if(!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'config/koneksi.php';

// --- LOGIKA FILTER PENCARIAN & WILAYAH ---
$search       = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$tipe_data    = isset($_GET['tipe_data']) ? $_GET['tipe_data'] : 'semua';
$id_kecamatan = isset($_GET['kecamatan']) ? mysqli_real_escape_string($koneksi, $_GET['kecamatan']) : '';
$id_desa      = isset($_GET['desa']) ? mysqli_real_escape_string($koneksi, $_GET['desa']) : '';

// 1. Filter Dasar Wilayah (berlaku untuk pelanggan & perangkat)
$wilayah_sql = "";
if ($id_kecamatan != '') {
    $wilayah_sql .= " AND p.id_kecamatan = '$id_kecamatan' ";
}
if ($id_desa != '') {
    $wilayah_sql .= " AND p.id_desa = '$id_desa' ";
}

// 2. Filter Spesifik Pelanggan (Cari Nama + PPPOE)
$where_pelanggan = " WHERE p.lat IS NOT NULL AND p.lat != '' " . $wilayah_sql;
if ($search != '') {
    $where_pelanggan .= " AND (p.nama LIKE '%$search%' OR p.pppoe LIKE '%$search%') ";
}

// 3. Filter Spesifik Perangkat (Cari Nama SAJA - Menghindari Error pppoe)
$where_perangkat = " WHERE p.lat IS NOT NULL AND p.lat != '' " . $wilayah_sql;
if ($search != '') {
    $where_perangkat .= " AND p.nama LIKE '%$search%' ";
}

// --- EKSEKUSI PENGAMBILAN DATA ---
$list_pelanggan = [];
$list_perangkat = [];

// Ambil data pelanggan jika tipe 'semua' atau 'pelanggan'
if ($tipe_data == 'semua' || $tipe_data == 'pelanggan') {
    $q_pel = "SELECT p.*, k.nama_kecamatan, d.nama_desa FROM pelanggan p 
              LEFT JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan 
              LEFT JOIN desa d ON p.id_desa = d.id_desa $where_pelanggan";
    $res_pel = mysqli_query($koneksi, $q_pel);
    $list_pelanggan = mysqli_fetch_all($res_pel, MYSQLI_ASSOC);
}

// Ambil data perangkat jika tipe 'semua' atau 'perangkat'
if ($tipe_data == 'semua' || $tipe_data == 'perangkat') {
    $q_per = "SELECT p.*, k.nama_kecamatan, d.nama_desa FROM perangkat p 
              LEFT JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan 
              LEFT JOIN desa d ON p.id_desa = d.id_desa $where_perangkat";
    $res_per = mysqli_query($koneksi, $q_per);
    $list_perangkat = mysqli_fetch_all($res_per, MYSQLI_ASSOC);
}

$list_kecamatan = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM kecamatan"), MYSQLI_ASSOC);
$list_kabel = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM jalur_kabel"), MYSQLI_ASSOC);

// Ambil Nama Target Plotting (Jika ada)
$id_target = $_GET['id_target'] ?? null;
$tipe_target = $_GET['tipe'] ?? 'pelanggan';
$nama_target = "";
if($id_target) {
    $tabel = ($tipe_target == 'perangkat') ? 'perangkat' : 'pelanggan';
    $cek = mysqli_query($koneksi, "SELECT nama FROM $tabel WHERE id='$id_target'");
    if($data = mysqli_fetch_assoc($cek)) $nama_target = $data['nama'];
}

include 'partials/header.php';
?>

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .main-map-content { flex: 1; position: relative; padding: 0 !important; overflow: hidden; }
    #map { width: 100%; height: 100vh; z-index: 1; }
    .floating-ui { position: absolute; z-index: 9999; }
    
    /* Bar Filter Atas */
    .filter-container { top: 20px; left: 50%; transform: translateX(-50%); width: auto; max-width: 95%; }
    .filter-bar { 
        background: var(--card-bg); padding: 10px 15px; border-radius: 12px; 
        display: flex; gap: 8px; border: 1px solid var(--border-color); 
        box-shadow: 0 4px 15px rgba(0,0,0,0.15); align-items: center; 
    }
    .filter-bar input, .filter-bar select { 
        border: 1px solid var(--border-color); border-radius: 6px; 
        padding: 8px 12px; background: var(--body-bg); color: var(--text-main); font-size: 13px; 
    }

    /* 3 Tombol Utama Kiri Bawah */
    .action-buttons { bottom: 30px; left: 80px; display: flex; flex-direction: column; gap: 12px; }
    .btn-action {
        display: flex; align-items: center; gap: 12px; background: var(--card-bg); 
        border: 1px solid var(--border-color); color: var(--text-main); 
        padding: 12px 18px; border-radius: 10px; cursor: pointer; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-size: 14px; font-weight: bold; transition: 0.2s;
    }
    .btn-action:hover { background: var(--hover-bg); transform: translateX(5px); }
    .btn-draw { background: var(--primary-color); color: white; border: none; }

    /* Marker Styling */
    .marker-pin { display: flex; justify-content: center; align-items: center; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 2px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.3); }
    .marker-pin i { transform: rotate(45deg); color: white; font-size: 16px; }
    .bg-server { background: #ef4444; } .bg-odp { background: #3b82f6; } .bg-splitter { background: #eab308; } .bg-htb { background: #f97316; } .bg-pelanggan { background: #22c55e; }
    
    /* Modal Input */
    .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10001; justify-content:center; align-items:center; }
    .modal-content { background:var(--card-bg); padding:25px; border-radius:12px; width:90%; max-width: 400px; color:var(--text-main); }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-size: 12px; font-weight: bold; color: var(--text-muted); }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--body-bg); color: var(--text-main); }
</style>

<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>
    <main class="main-map-content">
        
        <div class="floating-ui filter-container">
            <form action="" method="GET" class="filter-bar">
                <input type="text" name="search" placeholder="Cari Nama / PPPOE..." value="<?= $search ?>">
                
                <select name="tipe_data">
                    <option value="semua" <?= $tipe_data == 'semua' ? 'selected' : '' ?>>Semua Tipe</option>
                    <option value="pelanggan" <?= $tipe_data == 'pelanggan' ? 'selected' : '' ?>>Pelanggan</option>
                    <option value="perangkat" <?= $tipe_data == 'perangkat' ? 'selected' : '' ?>>Perangkat</option>
                </select>

                <select name="kecamatan" id="filter_kecamatan">
                    <option value="">Kecamatan</option>
                    <?php foreach($list_kecamatan as $k): ?>
                        <option value="<?= $k['id_kecamatan'] ?>" <?= $id_kecamatan == $k['id_kecamatan'] ? 'selected' : '' ?>><?= $k['nama_kecamatan'] ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="desa" id="filter_desa">
                    <option value="">Desa</option>
                    </select>

                <button type="submit" style="background:var(--primary-color); color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer;">
                    <i class='bx bx-search'></i>
                </button>
            </form>
        </div>

        <?php if($id_target): ?>
        <div class="floating-ui" style="top: 85px; left: 50%; transform: translateX(-50%); background: #ef4444; color: white; padding: 12px 25px; border-radius: 8px; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <i class='bx bx-target-lock bx-tada'></i> Klik Peta untuk Lokasi: <?= $nama_target ?>
            <button onclick="location.href='index.php'" style="background:white; color:#ef4444; border:none; margin-left:15px; padding:5px 12px; border-radius:6px; cursor:pointer; font-weight:bold;">Batal</button>
        </div>
        <?php endif; ?>

        <div id="drawStatus" class="floating-ui" style="display:none; top: 85px; left: 50%; transform: translateX(-50%); background: #f59e0b; color: white; padding: 12px 25px; border-radius: 8px; font-weight: bold; text-align:center;">
            <i class='bx bx-edit-alt bx-flashing'></i> SEDANG MENGGAMBAR JALUR KABEL...<br>
            <small>Tekan <b>'S'</b> di Keyboard untuk Simpan Jalur.</small>
        </div>

        <div id="map"></div>

        <div class="floating-ui action-buttons">
            <button type="button" class="btn-action" onclick="window.location.href='pelanggan.php'">
                <i class='bx bx-user-pin' style="color: #22c55e; font-size: 22px;"></i> Plot Pelanggan Baru
            </button>
            <button type="button" class="btn-action" onclick="window.location.href='perangkat.php'">
                <i class='bx bx-hdd' style="color: #3b82f6; font-size: 22px;"></i> Plot Perangkat Baru
            </button>
            <button type="button" class="btn-action btn-draw" onclick="document.getElementById('modalKabel').style.display='flex'">
                <i class='bx bx-git-branch' style="font-size: 22px;"></i> Tarik Jalur Kabel
            </button>
        </div>
    </main>
</div>

<div id="modalKabel" class="modal-overlay">
    <div class="modal-content">
        <h3 style="margin-bottom: 20px;"><i class='bx bx-git-branch'></i> Persiapan Jalur Kabel</h3>
        <div class="form-group">
            <label>Nama Jalur</label>
            <input type="text" id="kab_nama" placeholder="Misal: Jalur Utama ke ODP-A">
        </div>
        <div class="form-group">
            <label>Jenis Jalur</label>
            <select id="kab_tipe">
                <option value="biasa">Garis Biasa (Standard)</option>
                <option value="tebal">Garis Tebal (Backbone)</option>
                <option value="patah">Garis Putus-putus (Rencana)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Warna Kabel</label>
            <input type="color" id="kab_warna" value="#f59e0b" style="height: 45px; padding: 2px;">
        </div>
        <div class="form-group">
            <label>Deskripsi / Catatan</label>
            <textarea id="kab_desc" rows="3" placeholder="Keterangan tambahan..."></textarea>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button onclick="document.getElementById('modalKabel').style.display='none'" style="flex:1; background:#ccc; border:none; padding:12px; border-radius:8px; cursor:pointer; font-weight:bold;">Batal</button>
            <button onclick="siapkanDrawing()" style="flex:1; background:var(--primary-color); color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; font-weight:bold;">Mulai Gambar</button>
        </div>
    </div>
</div>

<script>
    // Inisialisasi Map
    const satelit = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{ maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3'] });
    const map = L.map('map', { center: [-7.6469, 112.9078], zoom: 14, layers: [satelit], zoomControl: false });
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    const layerKabel = L.layerGroup().addTo(map);
    const layerPoint = L.layerGroup().addTo(map);
    let allMarkersCoords = []; // Untuk Auto-Zoom

    function createIcon(color, icon) { return L.divIcon({ className: '', html: `<div class='marker-pin ${color}'><i class='bx ${icon}'></i></div>`, iconSize: [30, 42], iconAnchor: [15, 42] }); }
    const icons = { pelanggan: createIcon('bg-pelanggan', 'bx-home-wifi'), server: createIcon('bg-server', 'bx-server'), odp: createIcon('bg-odp', 'bx-hdd'), splitter: createIcon('bg-splitter', 'bx-git-branch'), htb: createIcon('bg-htb', 'bx-transfer') };

    // Render Data Pelanggan
    const dataPel = <?= json_encode($list_pelanggan) ?>;
    dataPel.forEach(p => { 
        L.marker([p.lat, p.lng], {icon: icons.pelanggan}).addTo(layerPoint).bindPopup(`<b>${p.nama}</b><br>${p.pppoe}`); 
        allMarkersCoords.push([p.lat, p.lng]);
    });

    // Render Data Perangkat
    const dataDev = <?= json_encode($list_perangkat) ?>;
    dataDev.forEach(d => { 
        L.marker([d.lat, d.lng], {icon: icons[d.jenis] || icons.server}).addTo(layerPoint).bindPopup(`<b>${d.nama}</b><br>${d.jenis.toUpperCase()}`); 
        allMarkersCoords.push([d.lat, d.lng]);
    });

    // Render Jalur Kabel
    const dataKab = <?= json_encode($list_kabel) ?>;
    dataKab.forEach(k => { 
        L.polyline(JSON.parse(k.koordinat), {
            color: k.warna, 
            weight: (k.tipe_garis === 'tebal' ? 6 : 3), 
            dashArray: (k.tipe_garis === 'patah' ? '10,10' : null)
        }).addTo(layerKabel).bindPopup(`<b>${k.nama}</b><br>${k.deskripsi}`); 
    });

    // --- LOGIKA AUTO ZOOM ---
    // Jika ada pencarian atau filter wilayah, otomatis arahkan peta ke lokasi tersebut
    <?php if($search != '' || $id_kecamatan != '' || $id_desa != ''): ?>
    if (allMarkersCoords.length > 0) {
        let bounds = L.latLngBounds(allMarkersCoords);
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 18 });
    }
    <?php endif; ?>

    // --- LOGIKA DRAWING KABEL ---
    let isDrawing = false; let path = []; let tempLine = null; let configKabel = {};

    function siapkanDrawing() {
        configKabel = {
            nama: document.getElementById('kab_nama').value,
            tipe: document.getElementById('kab_tipe').value,
            warna: document.getElementById('kab_warna').value,
            deskripsi: document.getElementById('kab_desc').value
        };
        if(!configKabel.nama) return alert("Isi Nama Jalur terlebih dahulu!");

        document.getElementById('modalKabel').style.display = 'none';
        document.getElementById('drawStatus').style.display = 'block';
        isDrawing = true; path = [];
        
        let weight = (configKabel.tipe === 'tebal') ? 6 : 3;
        let dash = (configKabel.tipe === 'patah') ? '10, 10' : null;
        tempLine = L.polyline([], {color: configKabel.warna, weight: weight, dashArray: dash}).addTo(map);
    }

    map.on('click', function(e) {
        if(isDrawing) {
            path.push([e.latlng.lat, e.latlng.lng]);
            tempLine.setLatLngs(path);
        } else {
            <?php if($id_target): ?>
            if(confirm("Simpan koordinat di sini untuk <?= $nama_target ?>?")) {
                window.location.href = `api/simpan_lokasi.php?id=<?= $id_target ?>&lat=${e.latlng.lat}&lng=${e.latlng.lng}&tipe=<?= $tipe_target ?>`;
            }
            <?php endif; ?>
        }
    });

    window.addEventListener('keydown', (e) => {
        if((e.key === 's' || e.key === 'S') && isDrawing) {
            if(path.length < 2) return alert("Tambahkan minimal 2 titik!");
            let formData = new FormData();
            formData.append('nama', configKabel.nama);
            formData.append('tipe_garis', configKabel.tipe);
            formData.append('warna', configKabel.warna);
            formData.append('deskripsi', configKabel.deskripsi);
            formData.append('koordinat', JSON.stringify(path));
            fetch('api/simpan_kabel.php', { method: 'POST', body: formData }).then(() => location.reload());
        }
    });

    // --- AJAX LOAD DESA UNTUK FILTER ---
    const filterKec = document.getElementById('filter_kecamatan');
    const filterDesa = document.getElementById('filter_desa');

    function loadDesa(idKec, idDesaSelected = '') {
        if(!idKec) { filterDesa.innerHTML = '<option value="">Desa</option>'; return; }
        let fd = new FormData();
        fd.append('id_kecamatan', idKec);
        fetch('api/get_desa.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(html => {
            filterDesa.innerHTML = '<option value="">Desa</option>' + html;
            if(idDesaSelected) filterDesa.value = idDesaSelected;
        });
    }

    filterKec.addEventListener('change', function() { loadDesa(this.value); });
    
    // Jalankan saat halaman pertama load jika filter sudah terpilih
    <?php if($id_kecamatan != ''): ?>
    loadDesa('<?= $id_kecamatan ?>', '<?= $id_desa ?>');
    <?php endif; ?>
</script>