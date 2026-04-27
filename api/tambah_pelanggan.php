<?php
session_start();
include '../config/koneksi.php';

if(isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    
    $foto = NULL;
    // --- LOGIKA UPLOAD FOTO ---
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = 'user_' . time() . '_' . uniqid() . '.' . $ext;
        
        if(!is_dir('../uploads')) mkdir('../uploads', 0777, true);
        move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto);
    }

    $query = mysqli_query($koneksi, "INSERT INTO pelanggan (nama, no_hp, alamat, foto) VALUES ('$nama', '$no_hp', '$alamat', '$foto')");

    if($query) {
        header("Location: ../pelanggan.php");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}
?>