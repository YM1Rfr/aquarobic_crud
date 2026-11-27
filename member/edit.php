<?php

require __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? '';
$st = db()->prepare('SELECT * FROM member WHERE id_member = ?');
$st->execute([$id]);
$asli = $st->fetch();

if (!$asli) {
    flash('Member tidak ditemukan.', 'err');
    redirect(app_url('/member/index.php'));
}

$mode        = 'edit';
$m           = $asli;
$kunci_jenis = member_punya_iuran($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $m = $_POST + ['id_member' => $id];

    // Kunci jenis_member di sisi server apabila member sudah memiliki riwayat iuran
    if ($kunci_jenis) {
        $m['jenis_member'] = $asli['jenis_member'];
    }

    $err = validasi($m, $kunci_jenis);

    if (!in_array($m['status_keanggotaan'] ?? '', ['Aktif', 'Keluar'], true)) {
        $m['status_keanggotaan'] = 'Aktif';
    }

    if (!$err) {
        $sql = 'UPDATE member
                SET id_lokasi = ?,
                    nama = ?,
                    alamat = ?,
                    tanggal_lahir = ?,
                    jenis_kelamin = ?,
                    jenis_member = ?,
                    status_keanggotaan = ?
                WHERE id_member = ?';

        db()->prepare($sql)->execute([
            $m['id_lokasi'],
            trim($m['nama']),
            trim($m['alamat']),
            $m['tanggal_lahir'],
            $m['jenis_kelamin'],
            $m['jenis_member'],
            $m['status_keanggotaan'],
            $id,
        ]);

        flash("Data $id berhasil diperbarui.");
        redirect(app_url('/member/index.php'));
    }

    foreach ($err as $e) {
        flash($e, 'err');
    }
}

$judul = 'Edit Member';
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../layout/footer.php';
