<?php // Společný layout obsahuje navigaci, zprávy a místo pro obsah jednotlivých views. ?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Cyklistické závody') ?> | Cyklistické závody</title>
<?php // Bootstrap načítáme přímo z node_modules; base_url() přidá základní adresu projektu. ?>
    <link rel="stylesheet" href="<?= base_url('node_modules/bootstrap/dist/css/bootstrap.min.css') ?>">
<?php // Vlajky i jejich CSS jsou místní soubory z npm; stránka nepoužívá CDN. ?>
    <link rel="stylesheet" href="<?= base_url('node_modules/flag-icons/css/flag-icons.min.css') ?>">
</head>
<?php // Světle modré pozadí a modrý gradient používají hotové třídy Bootstrapu. ?>
<body class="bg-primary-subtle d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-md navbar-dark bg-primary bg-gradient shadow-sm" aria-label="Hlavní navigace">
    <div class="container py-3">
<?php // Název webu vede na úvodní adresu, která přesměruje na hlavní přehled Paříž–Nice. ?>
        <a class="navbar-brand fw-bold" href="<?= base_url() ?>" title="Úvodní stránka">Cyklistické závody</a>
<?php // Bootstrap collapse propojí tlačítko s #mainNavbar a na mobilu rozbalí navigaci. ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Otevřít navigaci">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-md-end" id="mainNavbar">
<?php // Hvězdička zahrne zobrazení formuláře i jeho opětovný výpis po chybě POST požadavku. ?>
            <a class="btn <?= url_is('race-years*') ? 'btn-light' : 'btn-outline-light' ?> rounded-pill px-4 fw-semibold mt-3 mt-md-0" href="<?= base_url('race-years/create') ?>" <?= url_is('race-years*') ? 'aria-current="page"' : '' ?>>+ Přidat ročník</a>
        </div>
    </div>
</nav>
<main class="container py-4 py-lg-5 flex-grow-1">
<?php // Načteme zprávu uloženou controllerem přes setFlashdata(). ?>
    <?php if ($message = session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zavřít"></button>
        </div>
    <?php endif; ?>
<?php // Chyby dostává view přímo v poli errors předaném controllerem. ?>
<?php if (!empty($errors)): ?>
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
