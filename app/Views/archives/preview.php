<?php
/** @var array $previewData */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Preview Data Arsip (22 Kolom)</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --ink: #183153;
            --muted: #667085;
            --line: #dce4ec;
            --soft-blue: #f4f8fc;
            --accent: #0f766e;
        }

        body {
            color: var(--ink);
            background: #eef3f7 !important;
        }

        .page-heading {
            border-left: 5px solid var(--accent);
            padding-left: 1rem;
        }

        .page-heading h3 { letter-spacing: -.02em; }
        .page-heading p { color: var(--muted) !important; }

        .preview-table-wrap {
            border: 1px solid var(--line);
            border-radius: 12px !important;
            box-shadow: 0 12px 30px rgba(24, 49, 83, .08) !important;
            overflow: auto;
        }

        .preview-table {
            min-width: 2500px;
            border-color: var(--line);
        }

        .preview-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: .85rem .75rem;
            color: #fff;
            background: var(--ink);
            border-color: rgba(255, 255, 255, .16);
            box-shadow: inset 0 -3px 0 var(--accent);
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
            font-size: .76rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .preview-table tbody td {
            padding: .6rem;
            background: #fff;
            border-color: var(--line);
        }

        .preview-table tbody tr:nth-child(even) td { background: var(--soft-blue); }
        .preview-table tbody tr:hover td { background: #e8f5f3; }

        .preview-table tbody td:first-child {
            position: sticky;
            left: 0;
            z-index: 1;
            color: var(--accent);
            background: inherit;
            box-shadow: 5px 0 10px rgba(24, 49, 83, .06);
        }

        .table-input { min-width: 130px; }
        .table-input-sm { min-width: 90px; }
        .table-input-lg { min-width: 250px; }

        .preview-table .form-control {
            min-height: 38px;
            border-color: #d5dee8;
            background: rgba(255, 255, 255, .86);
            border-radius: 7px;
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
<body class="bg-light">
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div class="page-heading">
            <h3 class="fw-bold text-dark mb-1">Preview Data Hasil Import & Analisis AI</h3>
            <p class="text-muted mb-0">Semua 22 kolom ditampilkan utuh. Kamu bisa edit langsung atau hapus baris sebelum disimpan.</p>
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
                        <th style="width: 40px;">No</th>
                        <th>Unit Pencipta</th>
                        <th>Unit Pengolah</th>
                        <th>Jenis Naskah (AI)</th>
                        <th>Kategori Arsip (AI)</th>
                        <th>Nama Berkas (AI)</th>
                        <th>Uraian Arsip</th>
                        <th>Jumlah Lembar</th>
                        <th>Kurun Waktu (Tahun)</th>
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
                                <!-- 1. No Index -->
                                <td class="text-center fw-bold"><?= $i + 1 ?></td>

                                <!-- 2. Unit Pencipta -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][unit_pencipta]" value="<?= esc($row['unit_pencipta']) ?>" class="form-control table-input">
                                </td>

                                <!-- 3. Unit Pengolah -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][unit_pengolah]" value="<?= esc($row['unit_pengolah']) ?>" class="form-control table-input">
                                </td>

                                <!-- 4. Jenis Naskah -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][jenis_naskah]" value="<?= esc($row['jenis_naskah']) ?>" class="form-control table-input">
                                </td>

                                <!-- 5. Kategori Arsip -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][kategori_arsip]" value="<?= esc($row['kategori_arsip']) ?>" class="form-control table-input">
                                </td>

                                <!-- 6. Nama Berkas -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][nama_berkas]" value="<?= esc($row['nama_berkas']) ?>" class="form-control table-input-lg">
                                </td>

                                <!-- 7. Uraian Arsip -->
                                <td>
                                    <textarea name="archives[<?= $i ?>][uraian_arsip]" class="form-control table-input-lg" rows="2"><?= esc($row['uraian_arsip']) ?></textarea>
                                </td>

                                <!-- 8. Jumlah Lembar -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][jumlah_lembar]" value="<?= esc($row['jumlah_lembar']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 9. Kurun Waktu -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][kurun_waktu]" value="<?= esc($row['kurun_waktu']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 10. Semula -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][semula]" value="<?= esc($row['semula']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 11. Menjadi -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][menjadi]" value="<?= esc($row['menjadi']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 12. Alat Scan -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][alat_scan]" value="<?= esc($row['alat_scan']) ?>" class="form-control table-input">
                                </td>

                                <!-- 13. Waktu Scan -->
                                <td>
                                    <input type="datetime-local" name="archives[<?= $i ?>][waktu_scan]" value="<?= date('Y-m-d\TH:i', strtotime($row['waktu_scan'])) ?>" class="form-control table-input">
                                </td>

                                <!-- 14. Tingkat Perkembangan -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][tingkat_perkembangan]" value="<?= esc($row['tingkat_perkembangan']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 15. No Sampul -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][no_sampul]" value="<?= esc($row['no_sampul']) ?>" class="form-control table-input-sm text-center fw-bold">
                                </td>

                                <!-- 16. No Item -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][no_item]" value="<?= esc($row['no_item']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 17. Boks -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][boks]" value="<?= esc($row['boks']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 18. Rak (Otomatis dari Excel Mentah) -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][rak]" value="<?= esc($row['rak']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 19. RO (Otomatis dari Excel Mentah) -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][ro]" value="<?= esc($row['ro']) ?>" class="form-control table-input-sm">
                                </td>

                                <!-- 20. Lokasi (Otomatis dari Excel Mentah) -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][lokasi]" value="<?= esc($row['lokasi']) ?>" class="form-control table-input">
                                </td>

                                <!-- 21. Status Autentikasi -->
                                <td>
                                    <input type="text" name="archives[<?= $i ?>][status_authentication]" value="<?= esc($row['status_authentication']) ?>" class="form-control table-input">
                                </td>

                                <!-- 22. Aksi Hapus Baris -->
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