<?= $this->extend('templates/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <a href="<?= site_url('pariz-nice') ?>" class="btn btn-outline-secondary btn-sm mb-3">← Zpět</a>
    
    <div class="row align-items-end">
        <div class="col">
            <h1 class="h2 fw-bold mb-1"><?= esc($title) ?></h1>
            <p class="text-secondary mb-0">
                <?= esc($stage['real_name']) ?>
                <?= !empty($stage['year']) ? ' · ' . esc($stage['year']) : '' ?>
                <?= !empty($stage['number']) ? ' · Etapa ' . esc($stage['number']) : '' ?>
                <?= !empty($stage['date']) ? ' · ' . date('d. m. Y', strtotime($stage['date'])) : '' ?>
            </p>
        </div>
        <div class="col-auto">
            <span class="badge <?= $typeResult === 1 ? 'text-bg-primary' : 'text-bg-dark' ?> fs-6">
                <?= $typeResult === 1 ? 'Pořadí v etapě' : 'Pořadí po etapě' ?>
            </span>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white py-3">
        <h2 class="h5 mb-0">
            <?= $typeResult === 1 ? 'Výsledky etapy' : 'Celkové pořadí po etapě' ?>
        </h2>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Jezdec</th>
                    <th>Země</th>
                    <th>Čas</th>
                    <th>Poznámka</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td>
                            <span class="badge text-bg-dark"><?= esc($result['rank']) ?></span>
                        </td>
                        <td class="fw-semibold">
                            <?= esc($result['rider_name'] ?: $result['name_link'] ?: '–') ?>
                        </td>
                        <td><?= esc($result['country'] ?? '–') ?></td>
                        <td><?= esc($result['time'] ?? '–') ?></td>
                        <td class="text-secondary"><?= esc($result['note'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($results)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-5">
                            Pro tuto etapu nejsou dostupné výsledky.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>