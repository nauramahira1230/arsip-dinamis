<!DOCTYPE html>
<html lang="id">
<?php $kegiatan = $kegiatan ?? []; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Kegiatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#102a43; --muted:#6b7c93; --teal:#0f766e; --line:#dce7ee; }
        body { background:#f5f8fa !important; color:var(--ink); font-family:'Manrope',sans-serif; }
        h1 { font:700 clamp(1.7rem,4vw,2.4rem) 'Space Grotesk',sans-serif; letter-spacing:-.05em; }.kicker { color:var(--teal); text-transform:uppercase; letter-spacing:.14em; font-size:.7rem; font-weight:800; }.muted { color:var(--muted); }
        .btn { border-radius:9px; font-weight:800; }.btn-primary { background:var(--teal); border-color:var(--teal); }.btn-primary:hover { background:#0b5f59; border-color:#0b5f59; }
        .table-shell { border:1px solid var(--line); border-radius:16px; overflow:hidden; background:#fff; box-shadow:0 14px 30px rgba(16,42,67,.06); }.table { margin:0; }.table thead th { background:#edf7f5; color:#24545a; border-bottom:1px solid #ccebe5; font-size:.73rem; letter-spacing:.06em; text-transform:uppercase; white-space:nowrap; }.table td { padding:1rem .85rem; border-color:#edf1f3; vertical-align:middle; }.table tbody tr:hover { background:#f8fcfb; }.year-pill { background:#e4efff; color:#2563eb; border-radius:99px; padding:.3rem .6rem; font-size:.8rem; font-weight:800; }.target { font-weight:800; color:var(--teal); }
    </style>
</head>
<body>
<?= view('partials/app_navbar') ?>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4 flex-wrap"><div><div class="kicker mb-2">Perencanaan pekerjaan</div><h1 class="mb-2">Master Kegiatan</h1><p class="muted mb-0">Tentukan target lembar sebelum operator mulai mengunggah arsip.</p></div><a class="btn btn-primary" href="<?= site_url('kegiatan/create') ?>"><i class="bi bi-plus-lg me-1"></i> Tambah Kegiatan</a></div>
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <div class="table-shell"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Nama Kegiatan</th><th>Tahun</th><th>Jenis Naskah</th><th>Target</th><th class="text-end">Aksi</th></tr></thead><tbody>
    <?php if (empty($kegiatan)): ?><tr><td colspan="5" class="text-center py-5 muted"><i class="bi bi-calendar2-x d-block fs-2 mb-2"></i>Belum ada master kegiatan.</td></tr><?php endif; ?>
    <?php foreach ($kegiatan as $item): ?><tr><td><strong><?= esc($item['nama_kegiatan']) ?></strong></td><td><span class="year-pill"><?= esc($item['tahun']) ?></span></td><td><?= esc($item['jenis_naskah']) ?></td><td class="target"><?= number_format($item['target_lembar']) ?> <span class="muted fw-normal">lembar</span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('kegiatan/edit/' . $item['id']) ?>"><i class="bi bi-pencil"></i> Edit</a> <form class="d-inline" method="post" action="<?= site_url('kegiatan/delete/' . $item['id']) ?>" onsubmit="return confirm('Hapus kegiatan ini?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button></form></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
</main>
</body>
</html>