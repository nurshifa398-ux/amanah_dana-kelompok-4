<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Amanah Dana') ?> - Amanah Dana</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<header class="topbar">
    <div class="brand">Amanah Dana &middot; PSAK 109</div>
    <nav>
        <?php $pg = $_GET['page'] ?? 'jurnal';
        $menu = ['kategori' => 'Kategori', 'donatur' => 'Donatur', 'penerimaan' => 'Penerimaan',
                 'penyaluran' => 'Penyaluran', 'jurnal' => 'Jurnal Kas', 'lpd' => 'Laporan LPD'];
        foreach ($menu as $k => $label): ?>
            <a href="index.php?page=<?= e($k) ?>" class="<?= $pg === $k ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <form method="POST" action="index.php?page=auth&action=logout" class="logout">
        <?= csrf_field() ?>
        <span><?= e($_SESSION['user']['nama'] ?? '') ?></span>
        <button type="submit" class="btn btn-ghost">Keluar</button>
    </form>
</header>
<main class="container">
<?php if ($flashMsg = flash()): ?>
    <div class="alert alert-<?= e($flashMsg["type"]) ?>"><?= e($flashMsg["msg"]) ?></div>
<?php endif; ?>
