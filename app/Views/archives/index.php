<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Arsip - Alih Media Supabase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= site_url('/archives/dashboard'); ?>">
            <i class="bi bi-archive-fill me-2"></i>Alih Media Arsip Dinamis
        </a>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-secondary mb-0">Daftar Arsip Terintegrasi Supabase</h4>
        <div>
            <a href="<?= site_url('/archives/import'); ?>" class="btn btn-success me-2">
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

    <div class="card border-0 shadow-sm">
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
                                <td colspan="10" class="text-center py-4 text-muted">Belum ada data arsip tersimpan di Supabase.</td>
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