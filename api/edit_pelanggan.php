<?php
session_start();
include '../config/koneksi.php'; // Pastikan path ke koneksi benar

if(isset($_POST['update_pelanggan'])) {
    $id           = mysqli_real_escape_string($koneksi, $_POST['id']);
    $nama         = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $pppoe        = mysqli_real_escape_string($koneksi, $_POST['pppoe']);
    $no_wa        = mysqli_real_escape_string($koneksi, $_POST['no_wa']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $id_kecamatan = mysqli_real_escape_string($koneksi, $_POST['id_kecamatan']);
    $id_desa      = mysqli_real_escape_string($koneksi, $_POST['id_desa']);
    $deskripsi    = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $lokasi_maps  = mysqli_real_escape_string($koneksi, $_POST['lokasi_maps']); 
    
    // --- FITUR AUTO-PLOT (UPDATE KOORDINAT) ---
    $query_update_koordinat = ""; 
    
    if(!empty($lokasi_maps)) {
        $url_cek = $lokasi_maps;
        
        // 1. Tangani link pendek (goo.gl, maps.app.goo.gl, atau googleusercontent dari mobile)
        if(strpos($url_cek, 'goo.gl') !== false || strpos($url_cek, 'googleusercontent.com') !== false) {
            $headers = @get_headers($url_cek, 1);
            if($headers && (isset($headers['Location']) || isset($headers['location']))) {
                $location = isset($headers['Location']) ? $headers['Location'] : $headers['location'];
                $url_cek = is_array($location) ? end($location) : $location;
            }
        }

        // 2. Ekstrak Latitude & Longitude menggunakan Regex yang lebih luas
        if(preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches) || 
           preg_match('/place\/(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches) ||
           preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches) ||
           preg_match('/ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches)) {
            
            $val_lat = mysqli_real_escape_string($koneksi, $matches[1]);
            $val_lng = mysqli_real_escape_string($koneksi, $matches[2]);
            
            // Masukkan ke dalam string query update jika koordinat ditemukan
            $query_update_koordinat = ", lat='$val_lat', lng='$val_lng'";
        }
    }
    // ------------------------------------------

    // Proses Foto
    $foto_lama = mysqli_real_escape_string($koneksi, $_POST['foto_lama']);
    $nama_foto = $foto_lama; 
    
    if(isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != "") {
        // Hapus foto lama jika ada
        if($foto_lama != "" && file_exists('../uploads/'.$foto_lama)) {
            unlink('../uploads/'.$foto_lama); 
        }
        $nama_file = $_FILES['foto']['name'];
        $nama_foto = time() . '_' . $nama_file; 
        move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/'.$nama_foto);
    }

    // Eksekusi Update
    $query = "UPDATE pelanggan SET 
                nama='$nama', 
                pppoe='$pppoe', 
                no_wa='$no_wa', 
                alamat='$alamat', 
                id_kecamatan='$id_kecamatan', 
                id_desa='$id_desa', 
                deskripsi='$deskripsi', 
                lokasi_maps='$lokasi_maps', 
                foto='$nama_foto' 
                $query_update_koordinat 
              WHERE id='$id'";

    $update = mysqli_query($koneksi, $query);

    if($update) {
        echo "<script>alert('Data pelanggan berhasil diperbarui!'); window.location='../pelanggan.php';</script>";
    } else {
        $error_db = mysqli_error($koneksi);
        echo "<script>alert('Gagal memperbarui data! Error: $error_db'); window.history.back();</script>";
    }
} else {
    header("Location: ../pelanggan.php");
}
?>