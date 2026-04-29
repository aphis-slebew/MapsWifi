<?php
include '../config/koneksi.php';

if(isset($_POST['import'])) {
    $file = $_FILES['file_csv']['tmp_name'];
    $handle = fopen($file, "r");
    
    $row_count = 0;
    $success_count = 0;

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row_count++;
        if($row_count == 1) continue; // Lewati baris header excel

        $nama         = mysqli_real_escape_string($koneksi, $data[0]);
        $pppoe        = mysqli_real_escape_string($koneksi, $data[1]);
        $no_wa        = mysqli_real_escape_string($koneksi, $data[2]);
        $alamat       = mysqli_real_escape_string($koneksi, $data[3]);
        $id_kecamatan = mysqli_real_escape_string($koneksi, $data[4]);
        $id_desa      = mysqli_real_escape_string($koneksi, $data[5]);
        $deskripsi    = mysqli_real_escape_string($koneksi, $data[6]);
        $lokasi_maps  = mysqli_real_escape_string($koneksi, $data[7]);

        // --- FITUR AUTO-PLOT OTOMATIS SAAT IMPORT ---
        $lat = "NULL";
        $lng = "NULL";

        if(!empty($lokasi_maps)) {
            $url = $lokasi_maps;
            // Cek link pendek
            if(strpos($url, 'goo.gl') !== false || strpos($url, 'googleusercontent') !== false) {
                $headers = @get_headers($url, 1);
                if($headers && (isset($headers['Location']) || isset($headers['location']))) {
                    $url = isset($headers['Location']) ? $headers['Location'] : $headers['location'];
                    if(is_array($url)) $url = end($url);
                }
            }
            // Ekstrak koordinat
            if(preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
                $lat = "'" . $matches[1] . "'";
                $lng = "'" . $matches[2] . "'";
            }
        }

        $sql = "INSERT INTO pelanggan (nama, pppoe, no_wa, alamat, id_kecamatan, id_desa, deskripsi, lokasi_maps, lat, lng) 
                VALUES ('$nama', '$pppoe', '$no_wa', '$alamat', '$id_kecamatan', '$id_desa', '$deskripsi', '$lokasi_maps', $lat, $lng)";
        
        if(mysqli_query($koneksi, $sql)) {
            $success_count++;
        }
    }

    fclose($handle);
    echo "<script>alert('Berhasil mengimport $success_count data pelanggan!'); window.location='../pelanggan.php';</script>";
}
?>