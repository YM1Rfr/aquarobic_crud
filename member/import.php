<?php

require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['file']['tmp_name'])) {
        flash('Pilih file CSV terlebih dahulu.', 'err');
        redirect(app_url('/member/import.php'));
    }

    $fh     = fopen($_FILES['file']['tmp_name'], 'r');
    $baris1 = fgets($fh);
    rewind($fh);

    // Excel Indonesia sering memakai titik koma
    $delim = substr_count((string) $baris1, ';') > substr_count((string) $baris1, ',') ? ';' : ',';

    $lok = [];
    foreach (lokasi_semua() as $l) {
        $lok[strtolower($l['nama_lokasi'])] = $l['id_lokasi'];
    }

    $head = fgetcsv($fh, 0, $delim);
    $head = array_map(
        fn($x) => strtolower(trim((string) preg_replace('/^ï»¿/', '', (string) $x))),
        $head ?: []
    );

    $ok  = 0;
    $bad = [];
    $no  = 1;

    $stmtInsert = db()->prepare(
        "INSERT INTO member
            (id_member, id_lokasi, nama, alamat, tanggal_lahir, jenis_kelamin, jenis_member, status_keanggotaan)
         VALUES
            (?, ?, ?, ?, ?, ?, ?, 'Aktif')"
    );

    while (($row = fgetcsv($fh, 0, $delim)) !== false) {
        $no++;
        if (count($row) < 2) {
            continue;
        }

        $r = array_map('trim', array_combine($head, array_pad($row, count($head), '')));
        $r['id_lokasi'] = $lok[strtolower($r['lokasi'] ?? '')] ?? '';

        $e = validasi($r);
        if ($e) {
            $bad[] = "Baris $no: " . implode(' ', $e);
            continue;
        }

        $stmtInsert->execute([
            id_member_berikutnya(),
            $r['id_lokasi'],
            $r['nama'],
            $r['alamat'],
            $r['tanggal_lahir'],
            $r['jenis_kelamin'],
            $r['jenis_member'],
        ]);
        $ok++;
    }

    fclose($fh);

    flash("$ok member berhasil diimpor.");
    foreach (array_slice($bad, 0, 5) as $b) {
        flash($b, 'err');
    }

    redirect(app_url('/member/index.php'));
}

$judul = 'Import CSV';
require __DIR__ . '/../layout/header.php';
?>
<div class="card" style="width:640px">
    <h2>Import Data Member dari Excel (CSV)</h2>
    <p>
        Simpan sheet Excel sebagai <b>CSV</b> dengan kolom:
        <code>nama, alamat, tanggal_lahir, jenis_kelamin, jenis_member, lokasi</code>.<br>
        Format tanggal YYYY-MM-DD. Pemisah koma atau titik koma dikenali otomatis. Baris yang tidak valid dilewati dan dilaporkan.
    </p>
    <form method="post" action="<?= h(app_url('/member/import.php')) ?>" enctype="multipart/form-data">
        <input type="file" name="file" accept=".csv">
        <p>
            <button class="btn">Impor</button>
            <a class="btn gray" href="<?= h(app_url('/member/index.php')) ?>">Batal</a>
        </p>
    </form>
</div>
<?php
require __DIR__ . '/../layout/footer.php';
