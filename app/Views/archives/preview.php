<?php
/** @var array $previewData */
$kegiatan = $kegiatan ?? [];
$previewSummary = $previewSummary ?? ['total' => 0, 'AUTO' => 0, 'PERLU_VERIFIKASI' => 0, 'GAGAL_DIPROSES' => 0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Preview Data Arsip</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #102a43; --muted: #6b7c93; --line: #d5e0e6; --header: #e8f7f4; --accent: #0f766e; }

        body { color: var(--ink); background: #f5f8fa !important; font-family: 'Manrope', sans-serif; }


        .page-heading { border-left: 5px solid var(--accent); padding-left: 1rem; }

        .page-heading h3 { font-family: 'Space Grotesk', sans-serif; letter-spacing: -.04em; }
        .page-heading p { color: var(--muted) !important; }

        .preview-table-wrap { border: 1px solid var(--line); border-radius: 14px; overflow: auto; background: #fff; box-shadow: 0 12px 26px rgba(16,42,67,.06); }

        .preview-table { width: 2750px; min-width: 2750px; border-color: var(--line); table-layout: fixed; }

        .preview-table th,
        .preview-table td {
            white-space: normal;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }

        .preview-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: .55rem .5rem;
            color: #111827;
            background: var(--header);
            border: 1px solid #ccebe5;
            box-shadow: 0 2px 4px rgba(16,42,67,.05);
            text-align: center;
            vertical-align: middle;
            font-size: .75rem;
            letter-spacing: 0;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .preview-table tbody td {
            padding: .25rem;
            background: #fff;
            border: 1px solid #edf1f3;
            vertical-align: top;
        }

        .preview-table tbody tr:hover td { background: #f4fbfa; }

        .preview-table th:nth-child(1), .preview-table td:nth-child(1) { width: 48px; }
        .preview-table th:nth-child(2), .preview-table td:nth-child(2) { width: 130px; }
        .preview-table th:nth-child(3), .preview-table td:nth-child(3) { width: 150px; }
        .preview-table th:nth-child(4), .preview-table td:nth-child(4) { width: 160px; }
        .preview-table th:nth-child(5), .preview-table td:nth-child(5) { width: 130px; }
        .preview-table th:nth-child(6), .preview-table td:nth-child(6) { width: 210px; }
        .preview-table th:nth-child(7), .preview-table td:nth-child(7) { width: 360px; }
        .preview-table th:nth-child(8), .preview-table td:nth-child(8) { width: 240px; }
        .preview-table th:nth-child(9), .preview-table td:nth-child(9) { width: 105px; }
        .preview-table th:nth-child(10), .preview-table td:nth-child(10) { width: 115px; }
        .preview-table th:nth-child(11), .preview-table td:nth-child(11) { width: 115px; }
        .preview-table th:nth-child(12), .preview-table td:nth-child(12) { width: 115px; }
        .preview-table th:nth-child(13), .preview-table td:nth-child(13) { width: 130px; }
        .preview-table th:nth-child(14), .preview-table td:nth-child(14) { width: 130px; }
        .preview-table th:nth-child(15), .preview-table td:nth-child(15) { width: 110px; }
        .preview-table th:nth-child(16), .preview-table td:nth-child(16) { width: 95px; }
        .preview-table th:nth-child(17), .preview-table td:nth-child(17) { width: 95px; }
        .preview-table th:nth-child(18), .preview-table td:nth-child(18) { width: 85px; }
        .preview-table th:nth-child(19), .preview-table td:nth-child(19) { width: 85px; }
        .preview-table th:nth-child(20), .preview-table td:nth-child(20) { width: 100px; }
        .preview-table th:nth-child(21), .preview-table td:nth-child(21) { width: 170px; }
        .preview-table th:nth-child(22), .preview-table td:nth-child(22) { width: 170px; }
        .preview-table th:nth-child(23), .preview-table td:nth-child(23) { width: 60px; }

        .table-input, .table-input-sm, .table-input-lg { min-width: 0; width: 100%; }

        .preview-table .form-control {
            min-height: 38px;
            border-color: transparent;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .preview-table .form-control:focus {
            border-color: var(--accent);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, .14);
        }

        .preview-table textarea {
            min-height: 64px;
            resize: none;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .action-cell { min-width: 72px; }

        @media (max-width: 768px) {
            .container-fluid { padding: 1rem !important; }
            .page-heading h3 { font-size: 1.25rem; }
            .page-heading p { font-size: .85rem; }
            .preview-actions { margin-top: 1rem; width: 100%; }
            .preview-actions .btn { flex: 1; }
        }

        @media print {
            .preview-actions, .save-action, .action-cell { display: none !important; }
            .preview-table-wrap { max-height: none !important; overflow: visible; box-shadow: none !important; }
            .preview-table { min-width: 0; font-size: 8px; }
            .preview-table .form-control { border: 0; padding: 0; background: transparent; }
        }
    </style>
</head>
<body>
<?php $navbarFluid = 'container-fluid px-4'; ?>
<?= view('partials/app_navbar', ['navbarFluid' => $navbarFluid]) ?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div class="page-heading">
            <h3 class="fw-bold text-dark mb-1">Preview 22 Kolom Arsip</h3>
            <p class="text-muted mb-0">Data faktual dari Excel dipertahankan. Periksa hasil klasifikasi AI sebelum disimpan.</p>
            <p class="text-muted mb-0"><strong><?= count(is_array($previewData ?? null) ? $previewData : []) ?></strong> data berhasil diproses.</p>
            <div class="mt-2"><span class="badge text-bg-success">Kegiatan: <?= esc($kegiatan['nama_kegiatan']) ?></span> <span class="badge text-bg-light border">Target: <?= number_format($kegiatan['target_lembar']) ?> lembar</span></div>
        </div>
        <div class="d-flex gap-2 preview-actions">
            <a href="<?= base_url('archives/import') ?>" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Upload Ulang
            </a>
            <a href="<?= base_url('archives/export') ?>" class="btn btn-outline-success"><i class="fa fa-file-excel me-1"></i> Export Excel</a>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="badge text-bg-light border">Total Data: <?= esc($previewSummary['total']) ?></span>
        <span class="badge text-bg-success">AUTO: <?= esc($previewSummary['AUTO']) ?></span>
        <span class="badge text-bg-warning">PERLU_VERIFIKASI: <?= esc($previewSummary['PERLU_VERIFIKASI']) ?></span>
        <span class="badge text-bg-danger">GAGAL_DIPROSES: <?= esc($previewSummary['GAGAL_DIPROSES']) ?></span>
    </div>

    <form action="<?= base_url('archives/saveBulk') ?>" method="post">
        <?= csrf_field() ?>

        <div class="table-responsive preview-table-wrap" style="max-height: 75vh;">
            <table class="table table-bordered table-hover align-middle mb-0 preview-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Klasifikasi</th>
                        <th>Unit Pencipta</th>
                        <th>Unit Pengolah</th>
                        <th>Jenis Naskah (AI)</th>
                        <th>Kategori Arsip (AI)</th>
                        <th>Nama Berkas (AI)</th>
                        <th>Uraian Arsip</th>
                        <th>Jumlah Lembar</th>
                        <th>Kurun Waktu</th>
                        <th>Semula</th>
                        <th>Menjadi</th>
                        <th>Alat Scan</th>
                        <th>Waktu Scan</th>
                        <th>Tingkat Perkembangan</th>
                        <th>No Sampul</th>
                        <th>No Item</th>
                        <th>Boks</th>
                        <th>Rak</th>
                        <th>RO</th>
                        <th>Lokasi</th>
                        <th>Status Autentikasi</th>
                        <th style="width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($previewData) && is_array($previewData)): ?>
                        <?php foreach ($previewData as $i => $row): ?>
                            <tr class="<?= ($row['ai_status'] ?? '') === 'PERLU_VERIFIKASI' ? 'table-warning' : '' ?>">
                                <td class="text-center fw-bold"><?= esc($row['no'] ?? '') ?><input type="hidden" name="archives[<?= $i ?>][no]" value="<?= esc($row['no'] ?? '') ?>"></td>
                                <td><?= esc($row['kode_klasifikasi'] ?? '') ?><input type="hidden" name="archives[<?= $i ?>][kode_klasifikasi]" value="<?= esc($row['kode_klasifikasi'] ?? '') ?>"></td>
                                <?php foreach (['unit_pencipta', 'unit_pengolah'] as $field): ?>
                                    <td>
                                        <?= esc($row[$field]) ?>
                                        <input type="hidden" name="archives[<?= $i ?>][<?= $field ?>]" value="<?= esc($row[$field]) ?>">
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <textarea name="archives[<?= $i ?>][jenis_naskah]" class="form-control table-input" rows="2"><?= esc($row['jenis_naskah']) ?></textarea>
                                    <input type="hidden" name="archives[<?= $i ?>][ai_status]" value="<?= esc($row['ai_status'] ?? 'GAGAL_DIPROSES') ?>">
                                    <input type="hidden" name="archives[<?= $i ?>][ai_confidence]" value="<?= esc($row['ai_confidence'] ?? 0) ?>">
                                    <input type="hidden" name="archives[<?= $i ?>][ai_metadata]" value="<?= esc($row['ai_metadata'] ?? '{}') ?>">
                                    <?php if (($row['ai_status'] ?? '') === 'PERLU_VERIFIKASI'): ?><span class="badge text-bg-warning">Perlu diperiksa</span><?php endif; ?>
                                </td>
                                <td>
                                    <textarea name="archives[<?= $i ?>][kategori_arsip]" class="form-control table-input" rows="2"><?= esc($row['kategori_arsip']) ?></textarea>
                                </td>
                                <td>
                                    <textarea name="archives[<?= $i ?>][nama_berkas]" class="form-control table-input-lg" rows="2"><?= esc($row['nama_berkas']) ?></textarea>
                                </td>
                                <td>
                                    <textarea name="archives[<?= $i ?>][uraian_arsip]" class="form-control table-input-lg" rows="2"><?= esc($row['uraian_arsip']) ?></textarea>
                                </td>
                                <?php foreach (['jumlah_lembar', 'kurun_waktu', 'semula', 'menjadi', 'alat_scan'] as $field): ?>
                                    <td>
                                        <?= esc($row[$field]) ?>
                                        <input type="hidden" name="archives[<?= $i ?>][<?= $field ?>]" value="<?= esc($row[$field]) ?>">
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <?= esc($row['waktu_scan']) ?>
                                    <input type="hidden" name="archives[<?= $i ?>][waktu_scan]" value="<?= esc($row['waktu_scan']) ?>">
                                </td>
                                <?php foreach (['tingkat_perkembangan', 'no_sampul', 'no_item', 'boks', 'rak', 'ro', 'lokasi', 'status_authentication'] as $field): ?>
                                    <td>
                                        <?= esc($row[$field]) ?>
                                        <input type="hidden" name="archives[<?= $i ?>][<?= $field ?>]" value="<?= esc($row[$field]) ?>">
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-center action-cell">
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="Hapus Baris Ini" onclick="this.closest('tr').remove();">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="24" class="text-center py-4 text-muted">Tidak ada data untuk ditampilkan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-end save-action">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fa fa-save me-1"></i> Simpan Semua Data ke Database
            </button>
        </div>
    </form>

</div>
</body>
</html>