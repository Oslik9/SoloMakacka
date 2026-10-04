<?php // Výsledky zobrazíme ve stejné šabloně jako ostatní stránky. ?>
<?= $this->extend('templates/main') ?>
<?= $this->section('content') ?>
<?php // Kotva vrátí uživatele přímo na ročník, do kterého vybraná etapa patří. ?>
<a class="btn btn-outline-secondary btn-sm mb-4" href="<?= base_url('pariz-nice') ?>#rocnik-<?= (int) $stage['id_race_year'] ?>">← Zpět na ročník</a>
<header class="mb-4">
    <h1 class="display-6 fw-bold mb-2"><?= esc($title) ?></h1>
    <p class="text-secondary mb-0">
        <?= esc($stage['real_name']) ?> · <?= esc($stage['year']) ?> · Etapa <?= esc($stage['number'] ?? '') ?> ·
        <?= $stage['date'] !== '0000-00-00' ? date('d. m. Y', strtotime($stage['date'])) : '' ?>
    </p>
</header>
<?php // Podle typeResult zvýrazníme aktuální pořadí; tlačítka přepínají mezi typy 1 a 4. ?>
<div class="btn-group btn-group-sm mb-4" role="group" aria-label="Pořadí etapy">
    <a class="btn <?= $typeResult === 1 ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= base_url('pariz-nice/stage/' . (int) $stage['id'] . '/results/1') ?>" <?= $typeResult === 1 ? 'aria-current="page"' : '' ?>>V etapě</a>
    <a class="btn <?= $typeResult === 4 ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= base_url('pariz-nice/stage/' . (int) $stage['id'] . '/results/4') ?>" <?= $typeResult === 4 ? 'aria-current="page"' : '' ?>>Po etapě</a>
</div>
<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="ps-4">Pořadí</th>
                    <th scope="col">Jezdec</th>
                    <th scope="col" class="pe-4">Čas</th>
                </tr>
            </thead>
            <tbody>
<?php // Výsledky jsou z modelu seřazené podle umístění; každý záznam tvoří jeden řádek. ?>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <th scope="row" class="ps-4"><?= esc($result['rank']) ?>.</th>
                        <td class="fw-semibold"><?= esc(trim($result['first_name'] . ' ' . $result['last_name'])) ?></td>
<?php // Chybějící čas (null) převedeme na prázdný text, esc() zajistí bezpečný výpis. ?>
                        <td class="text-nowrap pe-4"><?= esc($result['time'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
<?php // Chybějící výsledky nenahrazujeme odhadem, pouze zobrazíme informaci. ?>
                <?php if (empty($results)): ?>
                    <tr><td colspan="3" class="text-center text-secondary py-5">Pro toto pořadí nejsou v databázi dostupné výsledky.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
