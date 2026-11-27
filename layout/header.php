<?php $judul = $judul ?? 'Data Member'; ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aquarobic Health Club - <?= h($judul) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(app_url('/assets/style.css')) ?>">
</head>
<body>
<div class="top">
    <a href="<?= h(app_url('/member/import.php')) ?>">Import CSV</a>
    <a href="<?= h(app_url('/member/export.php')) ?>">Export CSV</a>
    <a href="<?= h(app_url('/member/index.php')) ?>">Daftar Member</a>
    <b>Aquarobic Health Club</b>
    <span>Sistem Data Member</span>
</div>
<div class="wrap">
    <?php foreach ($_SESSION['flash'] ?? [] as [$flashType, $flashMsg]): ?>
        <div class="flash <?= h($flashType) ?>"><?= h($flashMsg) ?></div>
    <?php endforeach; ?>
    <?php unset($_SESSION['flash'], $flashType, $flashMsg); ?>
