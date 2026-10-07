<?php // Formulář pro přidání ročníku závodu. ?>
<?= $this->extend('templates/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <header class="bg-light border border-primary-subtle rounded-4 shadow-sm p-4 mb-4">
            <h1 class="display-6 fw-bold text-primary-emphasis mb-2">Přidat ročník závodu</h1>
            <p class="text-secondary mb-0">Vyberte mužský závod kategorie E. Logo je nepovinné.</p>
        </header>
        <div class="card border border-primary-subtle rounded-4 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <form action="<?= base_url('race-years') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="real_name">Název ročníku</label>
                        <input class="form-control bg-light" id="real_name" name="real_name" maxlength="255" value="<?= esc($values['real_name'] ?? '', 'attr') ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="race_id">Závod</label>
                        <?php // Výběr mužského závodu kategorie E. ?>
                        <select class="form-select bg-light" id="race_id" name="race_id" aria-describedby="race-help" required>
                            <option value="">Vyberte závod</option>
                            <?php foreach ($races as $race): ?>
                                <option value="<?= (int) $race['id'] ?>" <?= ($values['race_id'] ?? '') == $race['id'] ? 'selected' : '' ?>><?= esc($race['default_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="race-help">Pouze mužské závody kategorie E (Elite).</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="year">Rok ročníku</label>
                        <input type="number" class="form-control bg-light" id="year" name="year" value="<?= esc($values['year'] ?? '', 'attr') ?>" required>
                    </div>
                    <?php // Termín konání závodu. ?>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold" for="start_date">Datum od</label>
                            <input type="date" class="form-control bg-light" id="start_date" name="start_date" value="<?= esc($values['start_date'] ?? '', 'attr') ?>" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold" for="end_date">Datum do</label>
                            <input type="date" class="form-control bg-light" id="end_date" name="end_date" value="<?= esc($values['end_date'] ?? '', 'attr') ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <?php // Nepovinné logo závodu. ?>
                        <label class="form-label fw-semibold" for="logo">Logo závodu (nepovinné)</label>
                        <input type="file" class="form-control bg-light" id="logo" name="logo" accept=".png,.jpg,.jpeg,.webp,.gif" aria-describedby="logo-help">
                        <div class="form-text" id="logo-help">PNG, JPG, WebP nebo GIF, nejvýše 2 MB. Ročník můžete přidat i bez loga.</div>
                    </div>
                    <?php if (empty($races)): ?>
                        <div class="alert alert-info">V databázi nejsou žádné mužské závody kategorie E.</div>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap justify-content-end gap-2 border-top pt-4">
                        <a href="<?= base_url('pariz-nice') ?>" class="btn btn-outline-secondary rounded-pill px-4">Zrušit</a>
                        <button type="submit" class="btn btn-primary bg-gradient rounded-pill px-4" <?= empty($races) ? 'disabled' : '' ?>>Uložit ročník</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
