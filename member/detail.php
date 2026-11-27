<?php

require __DIR__ . '/../config/db.php';

$st = db()->prepare(
    'SELECT m.*, l.nama_lokasi
     FROM member m
     JOIN lokasi l USING (id_lokasi)
     WHERE id_member = ?'
);
$st->execute([$_GET['id'] ?? '']);
$m = $st->fetch();

if (!$m) {
    flash('Member tidak ditemukan.', 'err');
    redirect(app_url('/member/index.php'));
}

$st = db()->prepare('SELECT * FROM pembayaran WHERE id_member = ? ORDER BY periode_bulan DESC');
$st->execute([$m['id_member']]);
$bay = $st->fetchAll();

$judul = 'Detail Member';
require __DIR__ . '/../layout/header.php';
?>
<div class="card" style="width:640px">
    <h2>
        <?= h($m['nama']) ?>
        <span class="badge <?= $m['status_keanggotaan'] === 'Aktif' ? 'b-ok' : 'b-no' ?>">
            <?= h($m['status_keanggotaan']) ?>
        </span>
    </h2>

    <dl>
        <dt>ID Member</dt>
        <dd><?= h($m['id_member']) ?></dd>

        <dt>Alamat</dt>
        <dd><?= h($m['alamat']) ?></dd>

        <dt>Tanggal lahir / Jenis kelamin</dt>
        <dd><?= h($m['tanggal_lahir']) ?> / <?= h($m['jenis_kelamin']) ?></dd>

        <dt>Lokasi latihan</dt>
        <dd><?= h($m['nama_lokasi']) ?></dd>

        <dt>Jenis member</dt>
        <dd><?= h($m['jenis_member']) ?></dd>
    </dl>

    <h2 style="margin-top:22px">
        Riwayat Iuran
        <?php if ($m['status_keanggotaan'] === 'Aktif'): ?>
            <a class="btn green sm" style="float:right" href="<?= h(app_url('/iuran/create.php?id_member=' . rawurlencode($m['id_member']))) ?>">
                + Tambah Iuran
            </a>
        <?php endif; ?>
    </h2>

    <?php if ($m['status_keanggotaan'] !== 'Aktif'): ?>
        <p class="mute" style="margin:0">Member berstatus Keluar tidak dikenai iuran periode baru.</p>
    <?php endif; ?>

    <table class="t">
        <tr>
            <th>ID</th>
            <th>Periode</th>
            <th class="right">Nominal</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($bay as $b): ?>
            <tr>
                <td><?= h($b['id_pembayaran']) ?></td>
                <td><?= h($b['periode_bulan']) ?></td>
                <td class="right">Rp<?= number_format((float) $b['nominal_iuran'], 0, ',', '.') ?></td>
                <td>
                    <form class="inline" method="post" action="<?= h(app_url('/iuran/delete.php')) ?>" onsubmit="return confirm('Hapus iuran periode <?= h($b['periode_bulan']) ?>?')">
                        <input type="hidden" name="id" value="<?= h($b['id_pembayaran']) ?>">
                        <button class="btn sm red">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$bay): ?>
            <tr>
                <td colspan="4" class="mute">Belum ada data iuran.</td>
            </tr>
        <?php endif; ?>
    </table>

    <p>
        <a class="btn" href="<?= h(app_url('/member/edit.php?id=' . rawurlencode($m['id_member']))) ?>">Edit Member</a>
        <a class="btn gray" href="<?= h(app_url('/member/index.php')) ?>">Kembali</a>
    </p>
</div>
<?php
require __DIR__ . '/../layout/footer.php';
