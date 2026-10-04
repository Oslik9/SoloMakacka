<?php // Společný layout obsahuje navigaci, zprávy a místo pro obsah jednotlivých views. ?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Cyklistické závody') ?> | Cyklistické závody</title>
<?php // Bootstrap načítáme přímo z node_modules; base_url() přidá základní adresu projektu. ?>
    <link rel="stylesheet" href="<?= base_url('node_modules/bootstrap/dist/css/bootstrap.min.css') ?>">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm" aria-label="Hlavní navigace">
    <div class="container py-2">
        <span class="navbar-brand fw-semibold">Cyklistické závody</span>
<?php // Bootstrap collapse propojí tlačítko s #mainNavbar a na mobilu rozbalí navigaci. ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Otevřít navigaci">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
<?php // url_is() pozná aktuální stránku; hvězdička zahrne i stránky výsledků Paříž–Nice. ?>
                    <a class="nav-link <?= url_is('pariz-nice*') ? 'active' : '' ?>" href="<?= base_url('pariz-nice') ?>" <?= url_is('pariz-nice*') ? 'aria-current="page"' : '' ?>>Paříž–Nice</a>
                </li>
            </ul>
            <a class="btn <?= url_is('race-years/create') ? 'btn-light' : 'btn-outline-light' ?> mt-3 mt-md-0" href="<?= base_url('race-years/create') ?>" <?= url_is('race-years/create') ? 'aria-current="page"' : '' ?>>+ Přidat ročník</a>
        </div>
    </div>
</nav>
<main class="container py-4 py-lg-5 flex-grow-1">
<?php // Flash zprávy z controlleru zobrazíme zeleně (success) nebo červeně (error). ?>
<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $color): ?>
    <?php if ($message = session()->getFlashdata($key)): ?>
        <div class="alert alert-<?= $color ?> alert-dismissible fade show" role="alert">
            <?= esc($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zavřít"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
<?php // validation_errors() načte chyby kontroly formuláře uložené pomocí withInput(). ?>
<?php if ($errors = validation_errors()): ?>
    <div class="alert alert-danger" role="alert">
        <p class="fw-semibold mb-2">Zkontrolujte vyplněné údaje:</p>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php // Sem CI4 vloží obsah vymezený section('content') a endSection() v konkrétním view. ?>
    <?= $this->renderSection('content') ?>
</main>
<?php // Bundle obsahuje Bootstrap JavaScript potřebný pro rozbalení menu a zavření zpráv. ?>
<script src="<?= base_url('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
