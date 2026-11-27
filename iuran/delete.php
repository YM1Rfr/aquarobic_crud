<?php

require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('/member/index.php'));
}

$id = trim($_POST['id'] ?? '');
$st = db()->prepare('SELECT id_member FROM pembayaran WHERE id_pembayaran = ?');
$st->execute([$id]);
$r = $st->fetch();

$kembali = $r
    ? app_url('/member/detail.php?id=' . rawurlencode($r['id_member']))
    : app_url('/member/index.php');

try {
    db()->prepare('DELETE FROM pembayaran WHERE id_pembayaran = ?')->execute([$id]);
    flash("Iuran $id dihapus.");
} catch (PDOException $e) {
    $sqlstate = (string) ($e->errorInfo[0] ?? $e->getCode());

    // Sudah tercatat di transaksi_kas (ON DELETE RESTRICT)
    if (!in_array($sqlstate, ['23001', '23503'], true)) {
        throw $e;
    }

    flash("Iuran $id sudah tercatat di transaksi kas sehingga tidak dapat dihapus. Lakukan koreksi melalui pencatatan baru.", 'warn');
}

redirect($kembali);
