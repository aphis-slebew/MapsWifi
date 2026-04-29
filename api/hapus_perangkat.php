<?php
session_start();
// Pastikan path ke koneksi benar, jika file ini di dalam folder 'api', maka naik satu folder ('../')
include '../config/koneksi.php'; 

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // 1. Cek apakah perangkat punya foto, jika ada hapus fotonya dari folder uploads
    $cek_foto = mysqli_query($koneksi, "SELECT foto FROM perangkat WHERE id = '$id'");
    if ($data_foto = mysqli_fetch_assoc($cek_foto)) {
        $foto_lama = $data_foto['foto'];
        if ($foto_lama != "" && file_exists('../uploads/' . $foto_lama)) {
            unlink('../uploads/' . $foto_lama); // Menghapus file foto
        }
    }

    // 2. Hapus data perangkat dari database
    $query_hapus = mysqli_query($koneksi, "DELETE FROM perangkat WHERE id = '$id'");

    if ($query_hapus) {
        echo "<script>
                alert('Data perangkat berhasil dihapus!');
                window.location.href = '../perangkat.php';
              </script>";
    } else {
        // Jika gagal (biasanya karena relasi foreign key, meski di kasus ini jarang terjadi)
        echo "<script>
                alert('Gagal menghapus data perangkat! Error: " . mysqli_error($koneksi) . "');
                window.location.href = '../perangkat.php';
              </script>";
    }
} else {
    // Jika tidak ada ID yang dikirim
    header("Location: ../perangkat.php");
}
?>