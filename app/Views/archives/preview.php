<?php
/** @var array $previewData */
$kegiatan = $kegiatan ?? [];
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

        .preview-table { min-width: 2750px; border-color: var(--line); table-layout: auto; }

        .preview-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: .55rem .5rem;
            color: #111827;
            background: var(--header);
            border: 1px solid #ccebe5;
            box-shadow: 0 2px 4px rgba(16,42,67,.05);
            white-space: nowrap;
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

        .preview-table th:first-child, .preview-table td:first-child { min-width: 48px; width: 48px; }
        .preview-table th:nth-child(2), .preview-table td:nth-child(2) { min-width: 150px; }
        .preview-table th:nth-child(3), .preview-table td:nth-child(3) { min-width: 150px; }
        .preview-table th:nth-child(4), .preview-table td:nth-child(4) { min-width: 180px; }
        .preview-table th:nth-child(5), .preview-table td:nth-child(5) { min-width: 130px; }
        .preview-table th:nth-child(6), .preview-table td:nth-child(6) { min-width: 300px; }
        .preview-table th:nth-child(7), .preview-table td:nth-child(7) { min-width: 520px; }
        .preview-table th:nth-child(8), .preview-table td:nth-child(8) { min-width: 120px; }
        .preview-table th:nth-child(9), .preview-table td:nth-child(9) { min-width: 120px; }
        .preview-table th:nth-child(10), .preview-table td:nth-child(10),
        .preview-table th:nth-child(11), .preview-table td:nth-child(11) { min-width: 120px; }
        .preview-table th:nth-child(12), .preview-table td:nth-child(12) { min-width: 180px; }
        .preview-table th:nth-child(13), .preview-table td:nth-child(13) { min-width: 175px; }
        .preview-table th:nth-child(14), .preview-table td:nth-child(14) { min-width: 180px; }
        .preview-table th:nth-child(15), .preview-table td:nth-child(15),
        .preview-table th:nth-child(16), .preview-table td:nth-child(16),
        .preview-table th:nth-child(17), .preview-table td:nth-child(17) { min-width: 100px; }
        .preview-table th:nth-child(18), .preview-table td:nth-child(18),
        .preview-table th:nth-child(19), .preview-table td:nth-child(19) { min-width: 90px; }
        .preview-table th:nth-child(20), .preview-table td:nth-child(20) { min-width: 220px; }
        .preview-table th:nth-child(21), .preview-table td:nth-child(21) { min-width: 180px; }
        .preview-table th:last-child, .preview-table td:last-child { min-width: 60px; width: 60px; }

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

        .preview-table textarea { min-height: 64px; resize: vertical; }
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
            <h3 class="fw-bold text-dark mb-1">Preview Data Hasil Import & Analisis AI</h3>
            <p class="text-muted mb-0">Periksa dan edit hasil AI langsung seperti lembar Excel sebelum disimpan.</p>
            <div class="mt-2"><span class="badge text-bg-success">Kegiatan: <?= esc($kegiatan['nama_kegiatan']) ?></span> <span class="badge text-bg-light border">Target: <?= number_format($kegiatan['target_lembar']) ?> lembar</span></div>
        </div>
        <div class="d-flex gap-2 preview-actions">
            <a href="<?= base_url('archives/import') ?>" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Upload Ulang
            </a>
            <button type="button" class="btn btn-outline-success" onclick="window.print()">
                <i class="fa fa-file-excel me-1"></i> Export / Cetak
            </button>
        </div>
    </div>

    <form action="<?= base_url('archives/saveBulk') ?>" method="post">
        <?= csrf_field() ?>

        <div class="table-responsive preview-table-wrap" style="max-height: 75vh;">
            <table class="table table-bordered table-hover align-middle mb-0 text-nowrap preview-table">
                <thead>
                    <tr>
                        <th>No</th>
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
                            <tr>
                                <td class="text-center fw-bold"><?= $i + 1 ?></td>
                                <?php foreach (['unit_pencipta', 'unit_pengolah'] as $field): ?>
                                    <td>
                                        <?= esc($row[$field]) ?>
                                        <input type="hidden" name="archives[<?= $i ?>][<?= $field ?>]" value="<?= esc($row[$field]) ?>">
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][jenis_naskah]" value="<?= esc($row['jenis_naskah']) ?>" class="form-control table-input">
                                </td>
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][kategori_arsip]" value="<?= esc($row['kategori_arsip']) ?>" class="form-control table-input">
                                </td>
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][nama_berkas]" value="<?= esc($row['nama_berkas']) ?>" class="form-control table-input-lg">
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
                            <td colspan="22" class="text-center py-4 text-muted">Tidak ada data untuk ditampilkan.</td>
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