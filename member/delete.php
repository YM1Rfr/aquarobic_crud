<?php

require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('/member/index.php'));
}

$id = trim($_POST['id'] ?? '');
if ($id === '') {
    flash('ID member tidak valid.', 'err');
    redirect(app_url('/member/index.php'));
}

$st = db()->prepare('SELECT id_member FROM member WHERE id_member = ?');
$st->execute([$id]);

if (!$st->fetch()) {
    flash('Member tidak ditemukan.', 'err');
    redirect(app_url('/member/index.php'));
}

try {
    $st = db()->prepare('DELETE FROM member WHERE id_member = ?');
    $st->execute([$id]);
    flash("Member $id dihapus.");
} catch (PDOException $e) {
    $sqlstate = (string) ($e->errorInfo[0] ?? $e->getCode());

    // 23001: restrict_violation (ON DELETE RESTRICT), 23503: foreign_key_violation
    if (in_array($sqlstate, ['23001', '23503'], true)) {
        db()->prepare("UPDATE member SET status_keanggotaan = 'Keluar' WHERE id_member = ?")
            ->execute([$id]);
        flash("$id masih punya data terkait (iuran/kehadiran), jadi tidak dihapus. Status diubah menjadi Keluar.", 'warn');
    } else {
        throw $e;
    }
}

redirect(app_url('/member/index.php'));
