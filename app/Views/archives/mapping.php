<?php
$mapping = $mapping ?? [];
$headers = $headers ?? [];
$fields = $fields ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mapping Kolom Excel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{background:#f5f8fa;color:#102a43;font-family:Manrope,sans-serif}.mapping-shell{max-width:920px}.mapping-card{background:#fff;border:1px solid #dce7ee;border-radius:14px;box-shadow:0 12px 28px rgba(16,42,67,.06)}.form-label{font-weight:700}.required{color:#b42318}</style>
</head>
<body>
<?= view('partials/app_navbar') ?>
<div class="container mapping-shell py-5">
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <div class="mb-4"><div class="text-uppercase small fw-bold text-success">Langkah 2 dari 3</div><h1 class="h2 fw-bold">Periksa mapping kolom</h1><p class="text-secondary mb-0">Sheet <strong><?= esc($sheetName) ?></strong> sudah dibaca. Koreksi mapping sebelum data ditransformasi.</p></div>
    <form action="<?= base_url('archives/processMapping') ?>" method="post" class="mapping-card p-4 p-md-5">
        <?= csrf_field() ?>
        <?php foreach ($fields as $field): ?>
            <div class="row align-items-center mb-3">
                <label class="col-md-5 col-form-label form-label" for="mapping_<?= esc($field) ?>"><?= esc(ucwords(str_replace('_', ' ', $field))) ?><?php if ($field === 'uraian'): ?> <span class="required">*</span><?php endif; ?></label>
                <div class="col-md-7"><select class="form-select" id="mapping_<?= esc($field) ?>" name="mapping[<?= esc($field) ?>]"><option value="">-- Tidak tersedia --</option><?php foreach ($headers as $index => $header): ?><option value="<?= $index ?>" <?= (string) ($mapping[$field] ?? '') === (string) $index ? 'selected' : '' ?>><?= esc($header) ?></option><?php endforeach; ?></select></div>
            </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between gap-2 mt-4"><a href="<?= base_url('archives/import') ?>" class="btn btn-outline-secondary">Kembali</a><button class="btn btn-primary" type="submit">Lanjut ke Preview</button></div>
    </form>
</div>
</body>
</html>
