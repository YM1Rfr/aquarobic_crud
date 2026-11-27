<?php

require __DIR__ . '/../config/db.php';

$q   = trim($_GET['q'] ?? '');
$lok = $_GET['lokasi'] ?? '';
$jen = $_GET['jenis'] ?? '';
$sta = $_GET['status'] ?? '';

$sql = 'SELECT m.*, l.nama_lokasi
        FROM member m
        JOIN lokasi l USING (id_lokasi)
        WHERE 1=1';
$p   = [];

if ($q !== '') {
    $sql .= ' AND (m.nama ILIKE :q OR m.id_member ILIKE :q)';
    $p[':q'] = "%$q%";
}
if ($lok !== '') {
    $sql .= ' AND m.id_lokasi = :lok';
    $p[':lok'] = $lok;
}
if ($jen !== '') {
    $sql .= ' AND m.jenis_member = :jen';
    $p[':jen'] = $jen;
}
if ($sta !== '') {
    $sql .= ' AND m.status_keanggotaan = :sta';
    $p[':sta'] = $sta;
}

$st = db()->prepare($sql . ' ORDER BY m.id_member');
$st->execute($p);
$rows = $st->fetchAll();

$stat = db()->query(
    "SELECT COUNT(*) total,
            COUNT(*) FILTER (WHERE status_keanggotaan = 'Aktif') aktif,
            COUNT(*) FILTER (WHERE jenis_member = 'Full') full_,
            COUNT(*) FILTER (WHERE jenis_member = 'Reguler') reguler
     FROM member"
)->fetch();

$judul = 'Daftar Member';
require __DIR__ . '/../layout/header.php';
?>
<table class="stats">
    <tr>
        <td>
            <div class="n"><?= $stat['total'] ?></div>
            <div class="l">Total member</div>
        </td>
        <td>
            <div class="n"><?= $stat['aktif'] ?></div>
            <div class="l">Member aktif</div>
        </td>
        <td>
            <div class="n"><?= $stat['full_'] ?></div>
            <div class="l">Full member</div>
        </td>
        <td>
            <div class="n"><?= $stat['reguler'] ?></div>
            <div class="l">Reguler member</div>
        </td>
    </tr>
</table>

<div class="card">
    <h2>Daftar Member</h2>
    <form class="filter" method="get" action="<?= h(app_url('/member/index.php')) ?>">
        <input name="q" placeholder="Cari nama / ID member" value="<?= h($q) ?>" style="width:230px">

        <select name="lokasi">
            <option value="">Semua lokasi</option>
            <?php foreach (lokasi_semua() as $l): ?>
                <option value="<?= h($l['id_lokasi']) ?>" <?= $lok === $l['id_lokasi'] ? 'selected' : '' ?>>
                    <?= h($l['nama_lokasi']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="jenis">
            <option value="">Semua jenis</option>
            <?php foreach (['Full', 'Reguler'] as $j): ?>
                <option <?= $jen === $j ? 'selected' : '' ?>><?= $j ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status">
            <option value="">Semua status</option>
            <?php foreach (['Aktif', 'Keluar'] as $s): ?>
                <option <?= $sta === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>

        <button class="btn">Cari</button>
        <a class="btn green" href="<?= h(app_url('/member/create.php')) ?>" style="float:right">+ Tambah Member</a>
    </form>

    <table class="t">
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Lokasi</th>
            <th>Jenis</th>
            <th>Tgl Lahir</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= h($r['id_member']) ?></td>
                <td>
                    <a href="<?= h(app_url('/member/detail.php?id=' . rawurlencode($r['id_member']))) ?>" style="color:#0e5a8a;text-decoration:none">
                        <?= h($r['nama']) ?>
                    </a>
                </td>
                <td><?= h($r['nama_lokasi']) ?></td>
                <td>
                    <span class="badge <?= $r['jenis_member'] === 'Full' ? 'b-full' : 'b-reg' ?>">
                        <?= h($r['jenis_member']) ?>
                    </span>
                </td>
                <td><?= h($r['tanggal_lahir']) ?></td>
                <td>
                    <span class="badge <?= $r['status_keanggotaan'] === 'Aktif' ? 'b-ok' : 'b-no' ?>">
                        <?= h($r['status_keanggotaan']) ?>
                    </span>
                </td>
                <td>
                    <a class="btn sm" href="<?= h(app_url('/member/edit.php?id=' . rawurlencode($r['id_member']))) ?>">Edit</a>
                    <form class="inline" method="post" action="<?= h(app_url('/member/delete.php')) ?>" onsubmit="return confirm('Hapus member <?= h(addslashes($r['nama'])) ?>?')">
                        <input type="hidden" name="id" value="<?= h($r['id_member']) ?>">
                        <button class="btn sm red">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr>
                <td colspan="7" style="text-align:center;color:#64748b;padding:24px">Tidak ada data member.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
<?php
require __DIR__ . '/../layout/footer.php';
