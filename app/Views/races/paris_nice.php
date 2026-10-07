<?php // Použijeme společnou šablonu; obsah stránky patří do její sekce content. ?>
<?= $this->extend('templates/main') ?>
<?= $this->section('content') ?>
<header class="bg-light border border-primary-subtle rounded-4 shadow-sm p-4 p-lg-5 mb-4">
    <h1 class="display-6 fw-bold text-primary-emphasis mb-2">Paříž–Nice</h1>
    <p class="text-secondary mb-0">Všechny ročníky od nejnovějšího. Etapy, jejich vítězové a průběžné pořadí na jednom místě.</p>
</header>
<?php if (empty($raceYears)): ?>
    <div class="alert alert-info">Pro závod Paříž–Nice zatím nejsou uložené žádné ročníky.</div>
<?php endif; ?>

<?php // Každý ročník má vlastní kartu a ID pro přímý odkaz přes kotvu #rocnik-ID. ?>
<?php foreach ($raceYears as $raceYear): ?>
    <section class="card border border-primary-subtle rounded-4 shadow-sm overflow-hidden mb-4" id="rocnik-<?= (int) $raceYear['id'] ?>" aria-labelledby="nazev-<?= (int) $raceYear['id'] ?>">
        <div class="card-header bg-light border-primary-subtle p-4">
            <div class="d-flex flex-wrap align-items-center gap-3">
<?php // Controller připraví logo_url pouze pro soubor, který opravdu existuje. ?>
                <?php if ($raceYear['logo_url']): ?>
                    <img src="<?= esc($raceYear['logo_url'], 'attr') ?>" alt="Logo <?= esc($raceYear['real_name'], 'attr') ?>" class="img-fluid object-fit-contain" width="72" height="72" loading="lazy">
                <?php endif; ?>
                <div class="flex-grow-1">
                    <div class="badge text-bg-primary rounded-pill mb-2">ROČNÍK <?= esc($raceYear['year']) ?></div>
<?php // esc() bezpečně vypíše text; varianta 'attr' je určená pro hodnoty HTML atributů. ?>
                    <h2 class="h4 fw-bold mb-2" id="nazev-<?= (int) $raceYear['id'] ?>"><?= esc($raceYear['real_name']) ?></h2>
<?php // date() zobrazí český formát data; nulové datum z databáze necháme prázdné. ?>
                    <div class="text-secondary small">
                        <?= $raceYear['start_date'] !== '0000-00-00' ? date('d. m. Y', strtotime($raceYear['start_date'])) : '' ?> –
                        <?= $raceYear['end_date'] !== '0000-00-00' ? date('d. m. Y', strtotime($raceYear['end_date'])) : '' ?>
                    </div>
                </div>
                <div class="bg-primary-subtle rounded-3 p-3 text-md-end">
<?php // null znamená ročník bez etap; jinak zobrazíme připravený součet v celých km. ?>
                    <?php if ($raceYear['total_distance'] !== null): ?>
                        <div class="h4 fw-bold text-primary-emphasis mb-1"><?= number_format($raceYear['total_distance'], 0, ',', ' ') ?> <span class="fs-6 fw-normal text-secondary">km</span></div>
                    <?php endif; ?>
                    <div class="small text-secondary">Celková délka</div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" class="ps-4">Etapa</th>
                        <th scope="col">Datum</th>
                        <th scope="col">Délka</th>
                        <th scope="col">Převýšení</th>
                        <th scope="col">Typ etapy</th>
                        <th scope="col">Vítěz</th>
                        <th scope="col" class="pe-4">Pořadí</th>
                    </tr>
                </thead>
                <tbody>
<?php // Model už etapy seřadil podle čísla, zde jen vypíšeme jeden řádek pro každou etapu. ?>
                    <?php foreach ($raceYear['stages'] as $stage): ?>
                        <tr>
                            <th scope="row" class="ps-4"><span class="badge text-bg-primary rounded-pill"><?= esc($stage['number'] ?? '') ?></span></th>
                            <td class="text-nowrap small"><?= $stage['date'] !== '0000-00-00' ? date('d. m. Y', strtotime($stage['date'])) : '' ?></td>
<?php // number_format() určuje počet desetinných míst a odděluje tisíce mezerou. ?>
                            <td class="text-nowrap fw-semibold"><?= number_format((float) $stage['distance'], 1, ',', ' ') ?> km</td>
                            <td class="text-nowrap"><?= number_format((int) $stage['vertical_meters'], 0, ',', ' ') ?> m</td>
                            <td class="small">
                                <?= esc($stage['stage_type'] ?? '') ?>
<?php // ITT je individuální časovka, TTT týmová; označení vypíšeme jen z uložené poznámky. ?>
                                <?php if (in_array($stage['note'], ['ITT', 'TTT'], true)): ?>
                                    <span class="badge text-bg-secondary"><?= esc($stage['note']) ?></span>
                                <?php endif; ?>
                            </td>
<?php // Spojíme jméno a příjmení vítěze; trim() odstraní krajní mezery při chybějícím údaji. ?>
                            <td class="fw-semibold small"><?= esc(trim($stage['winner_first_name'] . ' ' . $stage['winner_last_name'])) ?></td>
                            <td class="pe-4">
<?php // URL obsahují ID etapy a typ pořadí: 1 pro etapu, 4 pro celkové pořadí po etapě. ?>
                                <div class="btn-group btn-group-sm text-nowrap" role="group" aria-label="Pořadí etapy">
                                    <a class="btn btn-outline-primary" href="<?= base_url('pariz-nice/stage/' . (int) $stage['id'] . '/results/1') ?>">V etapě</a>
                                    <a class="btn btn-outline-primary" href="<?= base_url('pariz-nice/stage/' . (int) $stage['id'] . '/results/4') ?>">Po etapě</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
<?php // Ročník bez etap zůstane ve výpisu, jen místo řádků zobrazíme tuto zprávu. ?>
                    <?php if (empty($raceYear['stages'])): ?>
                        <tr><td colspan="7" class="text-center text-secondary py-5">Tento ročník zatím nemá žádné etapy.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endforeach; ?>
<?= $this->endSection() ?>
