<?php
session_start();
include '../config/koneksi.php'; // Pakai ../ karena file ini ada di dalam folder api/

if(isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    
    $foto = NULL;
    // --- LOGIKA UPLOAD FOTO ---
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = 'dev_' . time() . '_' . uniqid() . '.' . $ext;
        
        // Buat folder uploads jika belum ada
        if(!is_dir('../uploads')) {
            mkdir('../uploads', 0777, true);
        }
        // Pindahkan file ke folder uploads
        move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto);
    }

    $query = mysqli_query($koneksi, "INSERT INTO perangkat (nama, jenis, foto) VALUES ('$nama', '$jenis', '$foto')");

    if($query) {
        header("Location: ../perangkat.php");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}
?>