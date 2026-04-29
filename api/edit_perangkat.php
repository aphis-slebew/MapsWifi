<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['update'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    $id_kecamatan = mysqli_real_escape_string($koneksi, $_POST['id_kecamatan']);
    $id_desa = mysqli_real_escape_string($koneksi, $_POST['id_desa']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $maps = mysqli_real_escape_string($koneksi, $_POST['maps']);
    $foto_lama = $_POST['foto_lama'];

    // --- FITUR SMART AUTO-PLOT MAPS (Untuk Edit) ---
    $query_update_koordinat = ""; 
    
    if(!empty($maps)) {
        $url_cek = $maps;
        
        // Cek link pendek
        if(strpos($url_cek, 'goo.gl') !== false || strpos($url_cek, 'googleusercontent.com') !== false) {
            $headers = @get_headers($url_cek, 1);
            if($headers && (isset($headers['Location']) || isset($headers['location']))) {
                $location = isset($headers['Location']) ? $headers['Location'] : $headers['location'];
                $url_cek = is_array($location) ? end($location) : $location;
            }
        }

        // Ekstrak kordinat
        if(preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches) || 
           preg_match('/place\/(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches) ||
           preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url_cek, $matches)) {
            
            $val_lat = mysqli_real_escape_string($koneksi, $matches[1]);
            $val_lng = mysqli_real_escape_string($koneksi, $matches[2]);
            $query_update_koordinat = ", lat='$val_lat', lng='$val_lng'";
        }
    }
    // ----------------------------------

    // --- LOGIKA UPDATE FOTO ---
    $nama_file = $foto_lama;
    if ($_FILES['foto']['name'] != '') {
        $ekstensi_diperbolehkan = array('png','jpg','jpeg','webp');
        $nama_file_asli = $_FILES['foto']['name'];
        $x = explode('.', $nama_file_asli);
        $ekstensi = strtolower(end($x));
        
        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            $nama_file = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $nama_file_asli);
            move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $nama_file);
            
            if (!empty($foto_lama) && file_exists('../uploads/' . $foto_lama)) {
                unlink('../uploads/' . $foto_lama);
            }
        }
    }

    $val_kecamatan = empty($id_kecamatan) ? "NULL" : "'$id_kecamatan'";
    $val_desa = empty($id_desa) ? "NULL" : "'$id_desa'";

    // UPDATE DATABASE
    $query = "UPDATE perangkat SET 
                nama = '$nama', 
                jenis = '$jenis', 
                id_kecamatan = $val_kecamatan, 
                id_desa = $val_desa, 
                deskripsi = '$deskripsi', 
                maps = '$maps', 
                foto = '$nama_file'
                $query_update_koordinat
              WHERE id = '$id'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../perangkat.php?status=updated");
    } else {
        echo "Error updating record: " . mysqli_error($koneksi);
    }
}
?>