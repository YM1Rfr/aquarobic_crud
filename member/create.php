<?php

require __DIR__ . '/../config/db.php';

$mode        = 'tambah';
$m           = [];
$kunci_jenis = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $m   = $_POST;
    $err = validasi($m);

    if (!$err) {
        $id  = id_member_berikutnya();
        $sql = "INSERT INTO member
                    (id_member, id_lokasi, nama, alamat, tanggal_lahir, jenis_kelamin, jenis_member, status_keanggotaan)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, 'Aktif')";

        db()->prepare($sql)->execute([
            $id,
            $m['id_lokasi'],
            trim($m['nama']),
            trim($m['alamat']),
            $m['tanggal_lahir'],
            $m['jenis_kelamin'],
            $m['jenis_member'],
        ]);

        flash("Member $id berhasil ditambahkan.");
        redirect(app_url('/member/index.php'));
    }

    foreach ($err as $e) {
        flash($e, 'err');
    }
}

$judul = 'Tambah Member';
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../layout/footer.php';
