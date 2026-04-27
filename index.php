<?php
session_start();
if(!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'config/koneksi.php';

// Ambil status plotting
$id_target = $_GET['id_target'] ?? null;
$tipe_target = $_GET['tipe'] ?? 'pelanggan';
$nama_target = "";

if($id_target) {
    $tabel = ($tipe_target == 'perangkat') ? 'perangkat' : 'pelanggan';
    $cek = mysqli_query($koneksi, "SELECT nama FROM $tabel WHERE id='$id_target'");
    if($data = mysqli_fetch_assoc($cek)) $nama_target = $data['nama'];
}

$list_pelanggan = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE lat IS NOT NULL"), MYSQLI_ASSOC);
$list_perangkat = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM perangkat WHERE lat IS NOT NULL"), MYSQLI_ASSOC);
$list_kabel = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM jalur_kabel"), MYSQLI_ASSOC);

include 'partials/header.php';
?>

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .main-map-content { flex: 1; position: relative; padding: 0 !important; overflow: hidden; }
    #map { width: 100%; height: 100vh; z-index: 1; }
    [data-theme="dark"] .leaflet-layer { filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%); }
    .floating-ui { position: absolute; z-index: 1000; }
    .search-bar-container { top: 15px; left: 60px; display: flex; gap: 10px; }
    .search-box { background: var(--card-bg); padding: 10px 15px; border-radius: 8px; display: flex; align-items: center; gap: 10px; border: 1px solid var(--border-color); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .search-box input { border: none; background: transparent; outline: none; color: var(--text-main); width: 220px; font-size: 14px; }
    .btn-circle { width: 45px; height: 45px; border-radius: 50%; background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items:center; justify-content:center; cursor:pointer; font-size: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); transition: 0.2s; }
    .btn-circle:hover { filter: brightness(0.9); transform: scale(1.05); }
    .btn-theme { border-radius: 8px !important; }
    
    /* CSS Pin Peta */
    .marker-pin { display: flex; justify-content: center; align-items: center; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 2px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.3); }
    .marker-pin i { transform: rotate(45deg); color: white; font-size: 16px; }
    .bg-server { background: #ef4444; } .bg-odp { background: #3b82f6; } .bg-splitter { background: #eab308; } .bg-htb { background: #f97316; } .bg-pelanggan { background: #22c55e; }
    
    /* Modifikasi Pop-up Leaflet agar support Dark Mode */
    .leaflet-popup-content-wrapper { background: var(--card-bg); color: var(--text-main); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
    .leaflet-popup-tip { background: var(--card-bg); }
    .leaflet-popup-close-button { color: var(--text-muted) !important; margin-top: 5px; margin-right: 5px; }
</style>

<div class="app-container">
    <?php include 'partials/sidebar.php'; ?>
    <main class="main-map-content">
        
        <div class="floating-ui search-bar-container">
            <div class="search-box">
                <i class='bx bx-search' style="color: var(--text-muted); font-size: 20px;"></i>
                <input type="text" placeholder="Cari Pelanggan / Alat...">
            </div>
        </div>

        <div class="floating-ui" style="top: 90px; right: 10px;">
            <button class="btn-circle btn-theme" onclick="toggleTheme()" id="themeBtn" title="Ganti Tema"><i class='bx bx-moon'></i></button>
        </div>

        <?php if($id_target): ?>
        <div class="floating-ui" style="top: 20px; left: 50%; transform: translateX(-50%); background: var(--primary-color); color: white; padding: 10px 25px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <i class='bx bx-target-lock bx-tada'></i> Plotting: <strong><?= $nama_target ?></strong>
            <button onclick="location.href='index.php'" style="background: white; color: var(--primary-color); border:none; padding:4px 12px; border-radius:20px; cursor:pointer; font-weight:bold; margin-left:10px;">Batal</button>
        </div>
        <?php endif; ?>

        <div id="map"></div>

        <div class="floating-ui" style="bottom: 30px; right: 20px; display: flex; flex-direction: column; gap: 15px;">
            <button class="btn-circle" onclick="document.getElementById('modalPersiapanKabel').style.display='flex'" title="Tarik Kabel Jalur" style="background:#f59e0b; color:white; border:none;"><i class='bx bx-share-alt'></i></button>
            <button class="btn-circle" onclick="location.href='perangkat.php'" title="Data Perangkat" style="background:#3b82f6; color:white; border:none;"><i class='bx bx-hdd'></i></button>
            <button class="btn-circle" onclick="location.href='pelanggan.php'" title="Data Pelanggan" style="background:#22c55e; color:white; border:none;"><i class='bx bx-user-plus'></i></button>
        </div>
    </main>
</div>

<div id="modalPersiapanKabel" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px; padding: 25px;">
        <h3 style="margin-bottom: 20px; text-align: center;"><i class='bx bx-git-branch' style="color:var(--primary-color);"></i> Persiapan Tarik Kabel</h3>
        <div class="input-group">
            <label>Pilih Jenis / Ketebalan Kabel</label>
            <select id="pilihanJenisKabel" style="padding: 12px;">
                <option value="biasa">1. Garis Biasa (Normal, 3px)</option>
                <option value="tebal">2. Garis Tebal (Backbone/Feeder, 6px)</option>
                <option value="patah">3. Garis Patah-patah (Dropcore, 3px)</option>
            </select>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button onclick="document.getElementById('modalPersiapanKabel').style.display='none'" class="btn-primary" style="flex:1; background:var(--hover-bg); color:var(--text-main); border:1px solid var(--border-color);">Batal</button>
            <button onclick="mulaiTarikKabel()" class="btn-primary" style="flex:1; background:var(--primary-color);">Mulai Tarik</button>
        </div>
    </div>
</div>

<div id="modalKabel" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <h3 style="margin-bottom: 20px;">Simpan Jalur Kabel</h3>
        <form action="api/simpan_kabel.php" method="POST">
            <input type="hidden" name="koordinat" id="inputKoordinat">
            <input type="hidden" name="tipe_garis" id="inputTipeGaris"> 
            <div class="input-group">
                <label>Nama Jalur Kabel</label>
                <input type="text" name="nama" placeholder="Contoh: Feeder ODP 1" required>
            </div>
            <div class="input-group" style="margin-bottom:20px;">
                <label>Warna Garis</label>
                <input type="color" name="warna" value="#3b82f6" style="width:100%; height:45px; border:none; cursor: pointer; background: transparent; padding: 0;">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalKabel').style.display='none'" style="flex:1; background:var(--hover-bg); color:var(--text-main); border:1px solid var(--border-color); padding:10px; border-radius:8px; cursor:pointer; font-weight:bold;">Batal</button>
                <button type="submit" name="simpan_kabel" style="flex:1; background:var(--success-color); color:white; border:none; padding:10px; border-radius:8px; cursor:pointer; font-weight:bold;">Simpan ke DB</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditKabel" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <h3 style="margin-bottom: 20px;">Edit Data Kabel</h3>
        <form action="api/edit_kabel.php" method="POST">
            <input type="hidden" name="id" id="editKabelId">
            <div class="input-group">
                <label>Nama Jalur</label>
                <input type="text" name="nama" id="editKabelNama" required>
            </div>
            <div class="input-group">
                <label>Ubah Jenis Kabel</label>
                <select name="tipe_garis" id="editKabelTipe">
                    <option value="biasa">Garis Biasa</option>
                    <option value="tebal">Garis Tebal</option>
                    <option value="patah">Garis Patah-patah</option>
                </select>
            </div>
            <div class="input-group" style="margin-bottom:20px;">
                <label>Warna Garis</label>
                <input type="color" name="warna" id="editKabelWarna" style="width:100%; height:45px; border:none; cursor:pointer; background:transparent; padding:0;">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalEditKabel').style.display='none'" style="flex:1; background:var(--hover-bg); color:var(--text-main); border:1px solid var(--border-color); padding:10px; border-radius:8px; cursor:pointer; font-weight:bold;">Batal</button>
                <button type="submit" name="edit_kabel" style="flex:1; background:var(--primary-color); color:white; border:none; padding:10px; border-radius:8px; cursor:pointer; font-weight:bold;">Update Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Inisialisasi Peta ---
    const satelit = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{ maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3'] });
    const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    const map = L.map('map', { center: [-7.6469, 112.9078], zoom: 14, layers: [satelit], zoomControl: false });
    
    L.control.zoom({ position: 'bottomleft' }).addTo(map);
    L.control.layers({ "Peta Jalan": osm, "Satelit": satelit }, null, {position: 'topleft'}).addTo(map);

    function createIcon(color, icon) { return L.divIcon({ className: '', html: `<div class='marker-pin ${color}'><i class='bx ${icon}'></i></div>`, iconSize: [30, 42], iconAnchor: [15, 42] }); }
    const icons = { pelanggan: createIcon('bg-pelanggan', 'bx-home-wifi'), server: createIcon('bg-server', 'bx-server'), odp: createIcon('bg-odp', 'bx-hdd'), splitter: createIcon('bg-splitter', 'bx-git-branch'), htb: createIcon('bg-htb', 'bx-transfer') };

    // ==========================================
    // 1. RENDER DATA PERANGKAT (ALAT JARINGAN)
    // ==========================================
    const dataPerangkat = <?= json_encode($list_perangkat) ?>;
    dataPerangkat.forEach(d => { 
        // Logika Gambar Perangkat
        let imgHtmlDev = d.foto 
            ? `<img src="uploads/${d.foto}" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:10px; border: 1px solid var(--border-color);">` 
            : `<div style="width:100%; height:120px; background:var(--hover-bg); border-radius:8px; margin-bottom:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--text-muted); border: 1px solid var(--border-color);"><i class='bx bx-hdd' style='font-size:36px; margin-bottom:5px;'></i><span style="font-size:11px;">Belum ada foto</span></div>`;

        let popupHtml = `
            <div style="min-width: 170px; text-align: center;">
                ${imgHtmlDev}
                <b style="font-size:15px; color:var(--text-main); display:block;">${d.nama}</b>
                <span style="font-size:11px; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:10px;">Jenis: ${d.jenis}</span>
                <hr style="margin: 0 0 10px 0; border:0; border-top: 1px solid var(--border-color);">
                <a href="api/hapus_objek.php?id=${d.id}&tipe=perangkat" onclick="return confirm('Hapus perangkat ini dari peta? (Data di menu perangkat tetap aman)')" style="display:flex; align-items:center; justify-content:center; gap:5px; background:var(--danger-color); color:white; padding:8px; border-radius:6px; text-decoration:none; font-size:12px; font-weight:bold;">
                    <i class='bx bx-map-pin' style="font-size:16px;"></i> Hapus dari Peta
                </a>
            </div>
        `;
        L.marker([d.lat, d.lng], {icon: icons[d.jenis] || icons.server}).addTo(map).bindPopup(popupHtml); 
    });

    // ==========================================
    // 2. RENDER DATA PELANGGAN
    // ==========================================
    const dataPelanggan = <?= json_encode($list_pelanggan) ?>;
    dataPelanggan.forEach(p => { 
        // Logika Gambar Pelanggan
        let imgHtml = p.foto 
            ? `<img src="uploads/${p.foto}" style="width:100%; height:130px; object-fit:cover; border-radius:8px; margin-bottom:10px; border: 1px solid var(--border-color);">` 
            : `<div style="width:100%; height:130px; background:var(--hover-bg); border-radius:8px; margin-bottom:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--text-muted); border: 1px solid var(--border-color);"><i class='bx bx-image-alt' style='font-size:40px; margin-bottom:5px;'></i><span style="font-size:11px;">Belum ada foto</span></div>`;
            
        let popupContent = `
            <div style="min-width: 180px;">
                ${imgHtml}
                <b style="font-size: 15px; color: var(--text-main); display:block; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin-bottom: 8px;">${p.nama}</b>
                <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); display:flex; align-items:center; gap:6px; font-size:12px;">
                        <i class='bx bxl-whatsapp' style="font-size:16px; color:#22c55e;"></i> 
                        ${p.no_hp ? p.no_hp : '-'}
                    </span>
                    <span style="color: var(--text-muted); display:flex; align-items:flex-start; gap:6px; font-size:12px; line-height: 1.4;">
                        <i class='bx bx-map' style="font-size:16px; color:#ef4444; margin-top:2px;"></i> 
                        ${p.alamat}
                    </span>
                </div>
                <a href="api/hapus_objek.php?id=${p.id}&tipe=pelanggan" onclick="return confirm('Hapus pelanggan ini dari peta? (Data di menu pelanggan tetap aman)')" style="display:flex; align-items:center; justify-content:center; gap:5px; background:var(--danger-color); color:white; padding:8px; border-radius:6px; text-decoration:none; font-size:12px; font-weight:bold;">
                    <i class='bx bx-map-pin' style="font-size:16px;"></i> Hapus dari Peta
                </a>
            </div>
        `;

        L.marker([p.lat, p.lng], {icon: icons.pelanggan}).addTo(map).bindPopup(popupContent); 
    });

    // ==========================================
    // 3. RENDER JALUR KABEL
    // ==========================================
    const dataKabel = <?= json_encode($list_kabel) ?>;
    dataKabel.forEach(k => { 
        let weight = (k.tipe_garis === 'tebal') ? 6 : 3;
        let dash = (k.tipe_garis === 'patah' || k.tipe_garis === 'putus') ? '5, 10' : null;

        L.polyline(JSON.parse(k.koordinat), {color: k.warna, weight: weight, dashArray: dash}).addTo(map).bindPopup(`
            <div style="min-width: 150px; text-align: center;">
                <b style="font-size:15px; color:var(--text-main);">${k.nama}</b><br>
                <span style="font-size:11px; color:var(--text-muted); text-transform:uppercase;">Tipe: ${k.tipe_garis}</span>
                <hr style="margin: 10px 0; border:0; border-top: 1px solid var(--border-color);">
                <div style="display: flex; gap: 5px; justify-content: center;">
                    <button onclick="bukaEditKabel(${k.id}, '${k.nama}', '${k.tipe_garis}', '${k.warna}')" style="flex:1; background:var(--primary-color); color:white; border:none; padding:6px; border-radius:6px; cursor:pointer; font-size:12px;"><i class='bx bx-edit'></i> Edit</button>
                    <a href="api/hapus_objek.php?id=${k.id}&tipe=kabel" onclick="return confirm('Hapus kabel ini secara permanen?')" style="flex:1; background:var(--danger-color); color:white; padding:6px; border-radius:6px; text-decoration:none; font-size:12px;"><i class='bx bx-trash'></i> Hapus</a>
                </div>
            </div>
        `); 
    });

    // Fungsi Trigger Pop-up Edit Kabel
    function bukaEditKabel(id, nama, tipe, warna) {
        document.getElementById('editKabelId').value = id;
        document.getElementById('editKabelNama').value = nama;
        document.getElementById('editKabelTipe').value = tipe;
        document.getElementById('editKabelWarna').value = warna;
        document.getElementById('modalEditKabel').style.display = 'flex';
    }

    // --- Logika Plotting Titik Pelanggan ---
    map.on('click', function(e) {
        <?php if($id_target): ?>
            if(confirm("Simpan lokasi pin di sini?")) window.location.href = `api/simpan_lokasi.php?id=<?= $id_target ?>&lat=${e.latlng.lat}&lng=${e.latlng.lng}&tipe=<?= $tipe_target ?>`;
        <?php endif; ?>
    });

    // --- Logika Menarik Kabel ---
    let isDrawing = false, path = [], tempLine = null, currentStyle = { weight: 3, dashArray: null, tipe: 'biasa' };
    
    function mulaiTarikKabel() { 
        document.getElementById('modalPersiapanKabel').style.display = 'none';
        
        let jenis = document.getElementById('pilihanJenisKabel').value;
        if(jenis === 'biasa') currentStyle = { weight: 3, dashArray: null, tipe: 'biasa' };
        else if(jenis === 'tebal') currentStyle = { weight: 6, dashArray: null, tipe: 'tebal' };
        else if(jenis === 'patah') currentStyle = { weight: 3, dashArray: '5, 10', tipe: 'patah' };

        isDrawing = true; 
        path = []; 
        if(tempLine) map.removeLayer(tempLine);
        
        tempLine = L.polyline([], {color: '#f59e0b', weight: currentStyle.weight, dashArray: currentStyle.dashArray}).addTo(map);
        
        alert("Mode Tarik Kabel Diaktifkan!\nSilakan klik di peta untuk menggambar jalur.\n\nJika sudah selesai, tekan tombol 'S' di keyboard.");
        
        map.on('click', function(e) {
            if(!isDrawing) return;
            path.push([e.latlng.lat, e.latlng.lng]);
            tempLine.setLatLngs(path);
        });
    }

    window.addEventListener('keydown', (e) => {
        if((e.key === 's' || e.key === 'S') && isDrawing) {
            isDrawing = false;
            document.getElementById('inputKoordinat').value = JSON.stringify(path);
            document.getElementById('inputTipeGaris').value = currentStyle.tipe;
            document.getElementById('modalKabel').style.display = 'flex';
        }
    });
</script>