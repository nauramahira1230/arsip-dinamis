<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Arsip - Alih Media Supabase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#102a43; --muted:#6b7c93; --teal:#0f766e; --line:#dce7ee; --paper:#f5f8fa; }
        body { background:var(--paper) !important; color:var(--ink); font-family:'Manrope',sans-serif; }
        .page-title { font:700 clamp(1.6rem,3vw,2.25rem) 'Space Grotesk',sans-serif; letter-spacing:-.05em; }.eyebrow { color:var(--teal); font-size:.7rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }.muted { color:var(--muted); }
        .btn { border-radius:9px; font-weight:800; }.btn-primary { background:var(--teal); border-color:var(--teal); }.btn-primary:hover { background:#0b5f59; border-color:#0b5f59; }.btn-success { background:#f97316; border-color:#f97316; }.btn-success:hover { background:#ea580c; border-color:#ea580c; }
        .table-shell { border:1px solid var(--line); border-radius:16px; overflow:hidden; background:#fff; box-shadow:0 14px 30px rgba(16,42,67,.06); }.table { margin:0; }.table thead th { background:var(--ink); color:#fff; border:0; font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; white-space:nowrap; padding:1rem .85rem; }.table td { padding:1rem .85rem; border-color:#edf1f3; vertical-align:middle; }.table tbody tr:hover { background:#f5fbfa; }.empty-state { color:var(--muted); padding:3rem !important; }.empty-state i { color:#9ab8bf; font-size:2rem; display:block; margin-bottom:.6rem; }
    </style>
</head>
<body>

<?php $navbarFluid = 'container-fluid'; ?>
<?= view('partials/app_navbar', ['navbarFluid' => $navbarFluid]) ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4 flex-wrap">
        <div><div class="eyebrow mb-2">Repositori arsip</div><h1 class="page-title mb-2">Daftar Arsip</h1><p class="muted mb-0">Data arsip yang sudah terintegrasi dan tersimpan di Supabase.</p></div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('/archives/import'); ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Baru
            </a>
            <a href="<?= site_url('/archives/export-pdf'); ?>" class="btn btn-danger" target="_blank">
                <i class="bi bi-file-pdf me-1"></i> Cetak Laporan PDF
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-shell">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px;">
                <table class="table table-striped table-hover table-bordered small text-nowrap align-middle mb-0">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th>ID</th>
                            <th>Kode Klasifikasi</th>
                            <th>Uraian Informasi</th>
                            <th>Kurun Waktu</th>
                            <th>Media Semula</th>
                            <th>Media Menjadi</th>
                            <th>Kategori</th>
                            <th>Hak Akses</th>
                            <th>Status Auth</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($archives)): ?>
                            <?php foreach ($archives as $item): ?>
                                <tr>
                                    <td><?= $item['id']; ?></td>
                                    <td><strong><?= esc($item['kode_klasifikasi']); ?></strong></td>
                                    <td><?= esc($item['uraian_informasi']); ?></td>
                                    <td><?= esc($item['kurun_waktu']); ?></td>
                                    <td><?= esc($item['media_semula']); ?></td>
                                    <td><span class="badge bg-info text-dark"><?= esc($item['media_menjadi']); ?></span></td>
                                    <td><?= esc($item['kategori_arsip']); ?></td>
                                    <td><?= esc($item['hak_akses']); ?></td>
                                    <td><span class="badge bg-success"><?= esc($item['status_authentication']); ?></span></td>
                                    <td>
                                        <a href="<?= site_url('/archives/edit/' . $item['id']); ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= site_url('/archives/delete/' . $item['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center empty-state"><i class="bi bi-archive"></i>Belum ada data arsip tersimpan di Supabase.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.bundle.min.js"></script>
</body>
</html>