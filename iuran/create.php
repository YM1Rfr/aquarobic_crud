<?php

require __DIR__ . '/../config/db.php';

$st = db()->prepare('SELECT * FROM member WHERE id_member = ?');
$st->execute([$_GET['id_member'] ?? '']);
$mem = $st->fetch();

if (!$mem) {
    flash('Member tidak ditemukan.', 'err');
    redirect(app_url('/member/index.php'));
}

if ($mem['status_keanggotaan'] !== 'Aktif') {
    flash('Member berstatus Keluar tidak dikenai iuran periode baru.', 'warn');
    redirect(app_url('/member/detail.php?id=' . rawurlencode($mem['id_member'])));
}

$p = [
    'periode_bulan' => date('Y-m'),
    'jenis_member'  => $mem['jenis_member'],
    'nominal_iuran' => nominal_acuan_paket($mem['jenis_member']),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p   = $_POST;
    $err = validasi_iuran($p);

    if (!$err) {
        $pdo = db();

        try {
            $pdo->beginTransaction();

            $id = id_pembayaran_berikutnya();
            $pdo->prepare(
                'INSERT INTO pembayaran (id_pembayaran, id_member, periode_bulan, nominal_iuran)
                 VALUES (?, ?, ?, ?)'
            )->execute([
                $id,
                $mem['id_member'],
                $p['periode_bulan'],
                $p['nominal_iuran'],
            ]);

            // Perbarui jenis_member hanya jika iuran yang dicatat adalah periode terbaru
            $stMax = $pdo->prepare('SELECT MAX(periode_bulan) FROM pembayaran WHERE id_member = ?');
            $stMax->execute([$mem['id_member']]);
            $periodeTerbaru = (string) $stMax->fetchColumn();

            $paketDiperbarui = false;
            if ($p['periode_bulan'] >= $periodeTerbaru && $p['jenis_member'] !== $mem['jenis_member']) {
                $pdo->prepare('UPDATE member SET jenis_member = ? WHERE id_member = ?')
                    ->execute([$p['jenis_member'], $mem['id_member']]);
                $paketDiperbarui = true;
            }

            $pdo->commit();

            $pesan = "Iuran $id periode {$p['periode_bulan']} berhasil ditambahkan.";
            if ($paketDiperbarui) {
                $pesan .= " Jenis member diperbarui menjadi {$p['jenis_member']}.";
            }

            flash($pesan);
            redirect(app_url('/member/detail.php?id=' . rawurlencode($mem['id_member'])));
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $sqlstate = (string) ($e->errorInfo[0] ?? $e->getCode());
            if ($sqlstate !== '23505') {
                throw $e;
            }

            $err[] = "Iuran periode {$p['periode_bulan']} untuk member ini sudah tercatat.";
        }
    }

    foreach ($err as $e) {
        flash($e, 'err');
    }
}

$judul = 'Tambah Iuran';
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../layout/footer.php';
