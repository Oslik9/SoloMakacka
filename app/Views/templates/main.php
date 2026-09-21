<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Cyklistické závody') ?></title>
    <link rel="stylesheet" href="<?= base_url('node_modules/bootstrap/dist/css/bootstrap.min.css') ?>">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= site_url('/') ?>">Cyklistické závody</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto"><li class="nav-item"><a class="nav-link" href="<?= site_url('pariz-nice') ?>">Paříž–Nice</a></li></ul>
            <a class="btn btn-primary" href="<?= site_url('race-years/create') ?>">Přidat ročník</a>
        </div>
    </div>
</nav>
<main class="container py-4">
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success alert-dismissible fade show"><?= esc(session()->getFlashdata('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger alert-dismissible fade show"><?= esc(session()->getFlashdata('error')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?= $this->renderSection('content') ?>
</main>
<footer class="border-top bg-white mt-5"><div class="container py-4 text-center text-secondary">Cyklistické závody</div></footer>
<script src="<?= base_url('assets/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
