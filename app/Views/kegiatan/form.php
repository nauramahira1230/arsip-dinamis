<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($kegiatan) ? 'Edit' : 'Tambah' ?> Kegiatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#102a43; --muted:#6b7c93; --teal:#0f766e; --line:#dce7ee; }
        body { background:#f5f8fa !important; color:var(--ink); font-family:'Manrope',sans-serif; }.topbar { background:var(--ink); padding:1rem 0; }.brand { color:#fff; text-decoration:none; font:700 1.1rem 'Space Grotesk',sans-serif; }.brand i { color:#6ee7d8; }
        h1 { font:700 clamp(1.7rem,4vw,2.4rem) 'Space Grotesk',sans-serif; letter-spacing:-.05em; }.kicker { color:var(--teal); text-transform:uppercase; letter-spacing:.14em; font-size:.7rem; font-weight:800; }.muted { color:var(--muted); }.form-card { max-width:720px; border:1px solid var(--line); border-radius:16px; background:#fff; box-shadow:0 14px 30px rgba(16,42,67,.06); }.form-label { font-weight:800; font-size:.84rem; }.form-control { border-color:#cbd8e1; border-radius:9px; padding:.72rem .85rem; }.form-control:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }.btn { border-radius:9px; font-weight:800; }.btn-primary { background:var(--teal); border-color:var(--teal); }.btn-primary:hover { background:#0b5f59; border-color:#0b5f59; }
    </style>
</head>
<body>
<div class="topbar"><div class="container"><a class="brand" href="<?= site_url('/archives/dashboard') ?>"><i class="bi bi-archive-fill me-2"></i>ArsipDinamis</a></div></div>
<main class="container py-5">
    <div class="mb-4"><div class="kicker mb-2">Master kegiatan</div><h1 class="mb-2"><?= isset($kegiatan) ? 'Edit' : 'Tambah' ?> kegiatan</h1><p class="muted mb-0">Satu kegiatan menjadi konteks target untuk setiap batch arsip yang diunggah.</p></div>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <form method="post" action="<?= isset($kegiatan) ? site_url('kegiatan/update/' . $kegiatan['id']) : site_url('kegiatan/store') ?>" class="form-card card-body p-4 p-md-5">
        <?= csrf_field() ?>
        <label class="form-label">Nama Kegiatan</label>
        <input class="form-control mb-3" name="nama_kegiatan" required value="<?= old('nama_kegiatan', $kegiatan['nama_kegiatan'] ?? '') ?>">
        <label class="form-label">Tahun</label>
        <input class="form-control mb-3" type="number" name="tahun" min="1" required value="<?= old('tahun', $kegiatan['tahun'] ?? date('Y')) ?>">
        <label class="form-label">Jenis Naskah</label>
        <input class="form-control mb-3" name="jenis_naskah" required value="<?= old('jenis_naskah', $kegiatan['jenis_naskah'] ?? '') ?>">
        <label class="form-label">Target Lembar</label>
        <input class="form-control mb-3" type="number" name="target_lembar" min="1" required value="<?= old('target_lembar', $kegiatan['target_lembar'] ?? '') ?>">
        <div class="pt-2"><button class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Simpan Kegiatan</button> <a class="btn btn-outline-secondary" href="<?= site_url('kegiatan') ?>">Batal</a></div>
    </form>
</main>
</body>
</html>