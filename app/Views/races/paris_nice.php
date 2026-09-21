<?= $this->extend('templates/main') ?>
<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-md"><h1 class="display-6 fw-bold mb-1">Paříž–Nice</h1><p class="text-secondary mb-0">Přehled všech ročníků závodu</p></div>
    <div class="col-md-auto mt-3 mt-md-0"><a href="<?= site_url('race-years/create') ?>" class="btn btn-primary">Přidat ročník</a></div>
</div>
<?php if (empty($raceYears)): ?><div class="alert alert-info">Pro závod Paříž–Nice nebyly nalezeny žádné ročníky.</div><?php endif; ?>
<?php foreach ($raceYears as $raceYear): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3"><div class="row align-items-center">
        <?php if (!empty($raceYear['logo'])): ?><div class="col-auto"><img src="<?= base_url($raceYear['logo']) ?>" alt="Logo" class="img-fluid" width="100"></div><?php endif; ?>
        <div class="col"><h2 class="h4 fw-bold mb-1"><?= esc($raceYear['real_name']) ?></h2><div class="d-flex flex-wrap gap-2">
            <?php if (!empty($raceYear['year'])): ?><span class="badge text-bg-dark"><?= esc($raceYear['year']) ?></span><?php endif; ?>
            <span class="badge text-bg-light"><?= !empty($raceYear['start_date']) ? date('d. m. Y', strtotime($raceYear['start_date'])) : '–' ?> – <?= !empty($raceYear['end_date']) ? date('d. m. Y', strtotime($raceYear['end_date'])) : '–' ?></span>
            <span class="badge text-bg-primary"><?= number_format((float)$raceYear['total_distance'], 0, ',', ' ') ?> km</span>
        </div></div>
    </div></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-dark"><tr><th>Etapa</th><th>Datum</th><th>Trasa</th><th>Délka</th><th>Převýšení</th><th>Typ etapy</th><th>Vítěz</th><th>Výsledky</th></tr></thead>
        <tbody>
        <?php foreach ($raceYear['stages'] as $stage): ?><tr>
            <td><span class="badge text-bg-dark"><?= esc($stage['number'] ?? '–') ?></span></td>
            <td class="text-nowrap"><?= !empty($stage['date']) ? date('d. m. Y', strtotime($stage['date'])) : '–' ?></td>
            <td><?= esc($stage['departure'] ?? '') ?><?= (!empty($stage['departure']) && !empty($stage['arrival'])) ? ' → ' : '' ?><?= esc($stage['arrival'] ?? '') ?></td>
            <td class="text-nowrap fw-semibold"><?= $stage['distance'] !== null ? number_format((float)$stage['distance'], 1, ',', ' ') . ' km' : '–' ?></td>
            <td class="text-nowrap"><?= !empty($stage['vertical_meters']) ? number_format((int)$stage['vertical_meters'], 0, ',', ' ') . ' m' : '–' ?></td>
            <td><span class="badge text-bg-secondary"><?= esc($stage['stage_type'] ?? 'Neznámý') ?></span></td>
            <td class="fw-semibold"><?= esc($stage['winner'] ?: '–') ?></td>
            <td><div class="btn-group btn-group-sm"><a href="<?= site_url('pariz-nice/stage/'.$stage['id'].'/results/1') ?>" class="btn btn-outline-primary">Etapa</a><a href="<?= site_url('pariz-nice/stage/'.$stage['id'].'/results/4') ?>" class="btn btn-outline-dark">Po etapě</a></div></td>
        </tr><?php endforeach; ?>
        <?php if (empty($raceYear['stages'])): ?><tr><td colspan="8" class="text-center text-secondary py-5">Tento ročník nemá žádné etapy.</td></tr><?php endif; ?>
        </tbody>
    </table></div></div>
</div>
<?php endforeach; ?>
<?= $this->endSection() ?>
