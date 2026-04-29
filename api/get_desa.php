<?php
include '../config/koneksi.php'; 

if (isset($_POST['id_kecamatan'])) {
    $id_kecamatan = mysqli_real_escape_string($koneksi, $_POST['id_kecamatan']);
    
    // Ambil data desa berdasarkan id_kecamatan
    $query = mysqli_query($koneksi, "SELECT * FROM desa WHERE id_kecamatan = '$id_kecamatan' ORDER BY nama_desa ASC");
    
    // Jika data tidak ada
    if(mysqli_num_rows($query) == 0){
        echo "<option value=''>-- Desa Tidak Ditemukan --</option>";
    }

    while ($row = mysqli_fetch_array($query)) {
        echo "<option value='" . $row['id_desa'] . "'>" . $row['nama_desa'] . "</option>";
    }
}
?>