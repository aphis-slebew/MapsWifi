<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST['simpan_pelanggan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $pppoe = mysqli_real_escape_string($koneksi, $_POST['pppoe']);
    $no_wa = mysqli_real_escape_string($koneksi, $_POST['no_wa']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $id_kecamatan = mysqli_real_escape_string($koneksi, $_POST['id_kecamatan']);
    $id_desa = mysqli_real_escape_string($koneksi, $_POST['id_desa']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $lokasi_maps = mysqli_real_escape_string($koneksi, $_POST['lokasi_maps']); 
    
    // --- FITUR SMART AUTO-PLOT MAPS ---
    $lat = "NULL"; // Default jika kosong
    $lng = "NULL";
    
    if(!empty($lokasi_maps)) {
        $url_cek = $lokasi_maps;
        
        // Logika untuk menangani link pendek (goo.gl / googleusercontent)
        if(strpos($url_cek, 'goo.gl') !== false || strpos($url_cek, 'maps.app.goo.gl') !== false || strpos($url_cek, 'googleusercontent.com') !== false) {
            $headers = @get_headers($url_cek, 1);
            if($headers && (isset($headers['Location']) || isset($headers['location']))) {
                $location = isset($headers['Location']) ? $headers['Location'] : $headers['location'];
                $url_cek = is_array($location) ? end($location) : $location;
            }
        }

        // Ekstrak Latitude & Longitude menggunakan Regex
        if(preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches) || 
           preg_match('/place\/(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches) ||
           preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches)) {
            
            $val_lat = mysqli_real_escape_string($koneksi, $matches[1]);
            $val_lng = mysqli_real_escape_string($koneksi, $matches[2]);
            $lat = "'$val_lat'";
            $lng = "'$val_lng'";
        }
    }
    // ----------------------------------

    $nama_foto = "";
    if(isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != "") {
        $nama_foto = time() . '_' . $_FILES['foto']['name']; 
        move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/'.$nama_foto); 
    }

    // QUERY INSERT (Menambah data baru ke database)
    $query = mysqli_query($koneksi, "INSERT INTO pelanggan (nama, pppoe, no_wa, alamat, id_kecamatan, id_desa, deskripsi, lokasi_maps, foto, lat, lng) 
                                     VALUES ('$nama', '$pppoe', '$no_wa', '$alamat', '$id_kecamatan', '$id_desa', '$deskripsi', '$lokasi_maps', '$nama_foto', $lat, $lng)");

    if($query) {
        echo "<script>alert('Pelanggan baru berhasil ditambahkan dan otomatis di-plot!'); window.location='../pelanggan.php';</script>";
    } else {
        $error_db = mysqli_error($koneksi);
        echo "<script>alert('Gagal simpan! Error: $error_db'); window.history.back();</script>";
    }
} else {
    header("Location: ../pelanggan.php");
}
?>