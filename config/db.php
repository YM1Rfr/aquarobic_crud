<?php

session_start();

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $c   = require __DIR__ . '/config.php';
        $dsn = "pgsql:host={$c['host']};port={$c['port']};dbname={$c['dbname']}";

        try {
            $pdo = new PDO($dsn, $c['user'], $c['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(
                '<h3>Gagal terhubung ke database</h3>'
                . '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>'
                . '<p>Periksa config/config.php dan pastikan PostgreSQL aktif serta ekstensi pdo_pgsql diaktifkan.</p>'
            );
        }
    }

    return $pdo;
}

function h($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function flash(string $msg, string $type = 'ok'): void
{
    $_SESSION['flash'][] = [$type, $msg];
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function app_url(string $path = ''): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base   = rtrim(dirname($script), '/');

    if (in_array(basename($base), ['member', 'iuran', 'config', 'layout'], true)) {
        $base = rtrim(dirname($base), '/');
    }

    return $base . '/' . ltrim($path, '/');
}

function lokasi_semua(): array
{
    return db()->query('SELECT * FROM lokasi ORDER BY id_lokasi')->fetchAll();
}

function id_member_berikutnya(): string
{
    $sql = "SELECT COALESCE(MAX(CAST(SUBSTRING(id_member FROM 4) AS INTEGER)), 0)
            FROM member
            WHERE id_member ~ '^MBR[0-9]+$'";
    $max = (int) db()->query($sql)->fetchColumn();

    return sprintf('MBR%03d', $max + 1);
}

function id_pembayaran_berikutnya(): string
{
    $sql = "SELECT COALESCE(MAX(CAST(SUBSTRING(id_pembayaran FROM 4) AS INTEGER)), 0)
            FROM pembayaran
            WHERE id_pembayaran ~ '^PEM[0-9]+$'";
    $max = (int) db()->query($sql)->fetchColumn();

    return sprintf('PEM%03d', $max + 1);
}

function member_punya_iuran(string $id_member): bool
{
    $st = db()->prepare('SELECT 1 FROM pembayaran WHERE id_member = ? LIMIT 1');
    $st->execute([$id_member]);

    return (bool) $st->fetchColumn();
}

function nominal_acuan_paket(string $jenis_member): int
{
    return $jenis_member === 'Full' ? 500000 : 250000;
}

function validasi(array $f, bool $kunci_jenis = false): array
{
    $err   = [];
    $wajib = [
        'nama'          => 'Nama',
        'alamat'        => 'Alamat',
        'tanggal_lahir' => 'Tanggal lahir',
        'id_lokasi'     => 'Lokasi',
        'jenis_kelamin' => 'Jenis kelamin',
    ];

    if (!$kunci_jenis) {
        $wajib['jenis_member'] = 'Jenis member';
    }

    foreach ($wajib as $k => $label) {
        if (trim($f[$k] ?? '') === '') {
            $err[] = "$label wajib diisi.";
        }
    }

    $tgl = $f['tanggal_lahir'] ?? '';
    if ($tgl !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $tgl);
        if (!$d || $d->format('Y-m-d') !== $tgl) {
            $err[] = 'Format tanggal lahir harus YYYY-MM-DD.';
        } elseif ($tgl > date('Y-m-d')) {
            $err[] = 'Tanggal lahir tidak boleh lebih dari hari ini.';
        }
    }

    if (($f['jenis_kelamin'] ?? '') !== '' && !in_array($f['jenis_kelamin'], ['Laki-laki', 'Perempuan'], true)) {
        $err[] = 'Jenis kelamin tidak valid.';
    }

    if (!$kunci_jenis && ($f['jenis_member'] ?? '') !== '' && !in_array($f['jenis_member'], ['Full', 'Reguler'], true)) {
        $err[] = 'Jenis member tidak valid.';
    }

    return $err;
}

function validasi_iuran(array $f): array
{
    $err = [];

    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $f['periode_bulan'] ?? '')) {
        $err[] = 'Periode bulan wajib diisi dengan format YYYY-MM.';
    }

    if (!in_array($f['jenis_member'] ?? '', ['Full', 'Reguler'], true)) {
        $err[] = 'Paket iuran (jenis member) wajib dipilih.';
    }

    $n = $f['nominal_iuran'] ?? '';
    if ($n === '' || !is_numeric($n)) {
        $err[] = 'Nominal iuran wajib diisi dengan angka.';
    } elseif ((float) $n <= 0) {
        $err[] = 'Nominal iuran harus lebih dari 0.';
    }

    return $err;
}

