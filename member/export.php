<?php

require __DIR__ . '/../config/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=member.csv');

$out = fopen('php://output', 'w');
fwrite($out, "ï»¿");

fputcsv($out, [
    'id_member',
    'nama',
    'alamat',
    'tanggal_lahir',
    'jenis_kelamin',
    'jenis_member',
    'lokasi',
    'status',
]);

$sql = 'SELECT m.id_member, m.nama, m.alamat, m.tanggal_lahir,
               m.jenis_kelamin, m.jenis_member, l.nama_lokasi, m.status_keanggotaan
        FROM member m
        JOIN lokasi l USING (id_lokasi)
        ORDER BY 1';

foreach (db()->query($sql) as $r) {
    fputcsv($out, array_values($r));
}
