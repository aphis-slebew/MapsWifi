<?php
include '../config/koneksi.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data_pelanggan_'.date('Ymd').'.csv');

$output = fopen('php://output', 'w');

// Header kolom di Excel
fputcsv($output, array('Nama', 'PPPOE', 'No WA', 'Alamat', 'ID Kec', 'ID Desa', 'Deskripsi', 'Link Maps', 'Lat', 'Lng'));

$query = mysqli_query($koneksi, "SELECT nama, pppoe, no_wa, alamat, id_kecamatan, id_desa, deskripsi, lokasi_maps, lat, lng FROM pelanggan");

while($row = mysqli_fetch_assoc($query)) {
    fputcsv($output, $row);
}

fclose($output);
?>