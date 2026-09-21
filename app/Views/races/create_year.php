<?= $this->extend('templates/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center"><div class="col-xl-8 col-lg-9">
<div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h2 fw-bold mb-1">Přidat ročník závodu</h1><p class="text-secondary mb-0">Vyplňte informace o novém ročníku.</p></div><a href="<?= site_url('pariz-nice') ?>" class="btn btn-outline-secondary">Zpět</a></div>
<?php if ($validation->getErrors()): ?><div class="alert alert-danger"><h5 class="alert-heading">Formulář obsahuje chyby</h5><ul class="mb-0"><?php foreach ($validation->getErrors() as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card border-0 shadow-sm"><div class="card-header bg-dark text-white py-3"><h2 class="h5 mb-0">Údaje ročníku</h2></div><div class="card-body p-4">
<form action="<?= site_url('race-years') ?>" method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="mb-4"><label class="form-label fw-semibold" for="real_name">Název ročníku</label><input class="form-control" id="real_name" name="real_name" value="<?= esc(old('real_name')) ?>" required></div>
<div class="mb-4"><label class="form-label fw-semibold" for="race_id">Závod</label><select class="form-select" id="race_id" name="race_id" required><option value="">-- Vyberte závod --</option><?php foreach ($races as $race): ?><option value="<?= (int)$race['id'] ?>" <?= old('race_id') == $race['id'] ? 'selected' : '' ?>><?= esc($race['default_name']) ?></option><?php endforeach; ?></select><div class="form-text">Pouze mužské závody kategorie E.</div></div>
<div class="mb-4"><label class="form-label fw-semibold" for="year">Rok</label><input type="number" class="form-control" id="year" name="year" min="1800" max="2200" value="<?= esc(old('year')) ?>" required></div>
<div class="row"><div class="col-md-6 mb-4"><label class="form-label fw-semibold" for="start_date">Datum od</label><input type="date" class="form-control" id="start_date" name="start_date" value="<?= esc(old('start_date')) ?>" required></div><div class="col-md-6 mb-4"><label class="form-label fw-semibold" for="end_date">Datum do</label><input type="date" class="form-control" id="end_date" name="end_date" value="<?= esc(old('end_date')) ?>" required></div></div>
<div class="mb-4"><label class="form-label fw-semibold" for="logo">Logo závodu</label><input type="file" class="form-control" id="logo" name="logo" accept=".png,.jpg,.jpeg,.webp,.gif" required><div class="form-text">Maximálně 2 MB.</div></div>
<div class="d-flex justify-content-end gap-2"><a href="<?= site_url('pariz-nice') ?>" class="btn btn-light">Zrušit</a><button class="btn btn-primary" type="submit">Přidat ročník</button></div>
</form></div></div></div></div>
<?= $this->endSection() ?>
