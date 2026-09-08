<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Data Arsip</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#102a43; --muted:#6b7c93; --teal:#0f766e; --line:#dce7ee; }
        body { background:#f5f8fa !important; color:var(--ink); font-family:'Manrope',sans-serif; }
        .topbar { background:var(--ink); color:#fff; padding:1rem 0; }
        .brand { color:#fff; text-decoration:none; font:700 1.1rem 'Space Grotesk',sans-serif; letter-spacing:-.03em; }
        .brand i { color:#6ee7d8; }
        .shell { max-width:850px; }
        .kicker { color:var(--teal); text-transform:uppercase; letter-spacing:.14em; font-size:.7rem; font-weight:800; }
        h1 { font:700 clamp(1.7rem,4vw,2.5rem) 'Space Grotesk',sans-serif; letter-spacing:-.05em; }
        .intro { color:var(--muted); max-width:650px; }
        .import-card { border:1px solid var(--line); border-radius:18px; box-shadow:0 16px 35px rgba(16,42,67,.08); background:#fff; overflow:hidden; }
        .card-top { background:#e8f7f4; padding:1.1rem 1.5rem; border-bottom:1px solid #ccebe5; color:var(--teal); font-weight:800; }
        .form-label { font-weight:800; font-size:.84rem; }
        .form-select,.form-control { border-color:#cbd8e1; border-radius:9px; padding:.72rem .85rem; }
        .form-select:focus,.form-control:focus { border-color:#0f766e; box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        .dropzone { border:1.5px dashed #9ab8bf; background:#f8fcfb; border-radius:12px; padding:1.3rem; text-align:center; }
        .dropzone i { color:var(--teal); font-size:1.6rem; }
        .form-text { color:var(--muted); }
        .btn { border-radius:9px; font-weight:800; }
        .btn-primary { background:var(--ink); border-color:var(--ink); }
        .btn-primary:hover { background:#174e61; border-color:#174e61; }
    </style>
</head>
<body>
<div class="topbar"><div class="container"><a class="brand" href="<?= site_url('/archives/dashboard') ?>"><i class="fa fa-archive me-2"></i>ArsipDinamis</a></div></div>
<div class="container shell py-5">

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="mb-4"><div class="kicker mb-2">Alur kerja operator</div><h1>Import arsip dengan konteks kegiatan.</h1><p class="intro mb-0">Pilih kegiatan yang menjadi tujuan upload, lalu biarkan sistem membaca Excel dan membantu menyiapkan metadata arsip dengan AI.</p></div>
    <div class="import-card mb-4">
        <div class="card-top"><i class="fa fa-wand-magic-sparkles me-2"></i>Upload & analisis data</div>
        <div class="card-body p-4 p-md-5">
            <form action="<?= base_url('archives/processPreview') ?>" method="post" enctype="multipart/form-data" onsubmit="showLoading()">
                <?= csrf_field() ?>

                <div class="mb-4">
                    <label class="form-label fw-bold" for="kegiatan_id">Master Kegiatan</label>
                    <select name="kegiatan_id" id="kegiatan_id" class="form-select" required>
                        <option value="">-- Pilih kegiatan --</option>
                        <?php foreach ($kegiatan ?? [] as $item): ?>
                            <option value="<?= esc($item['id']) ?>" <?= old('kegiatan_id') === $item['id'] ? 'selected' : '' ?>>
                                <?= esc($item['nama_kegiatan']) ?> (<?= esc($item['tahun']) ?>, target <?= number_format($item['target_lembar']) ?> lembar)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text mt-2"><i class="fa fa-circle-info me-1"></i>Data hasil upload akan dihitung sebagai realisasi kegiatan terpilih.</div>
                </div>

                <!-- Input File Excel -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls)</label>
                    <div class="dropzone"><i class="fa fa-file-excel mb-2"></i><input type="file" name="excel_file" class="form-control" required accept=".xlsx, .xls"><div class="form-text mt-2">Format yang didukung: .xlsx atau .xls</div></div>
                    <div class="form-text mt-2">Uraian arsip akan dianalisis AI. Hasilnya dapat diperiksa dan disesuaikan pada halaman preview.</div>
                </div>

                <button type="submit" id="btnSubmit" class="btn btn-primary">
                    <span id="btnText"><i class="fa fa-upload me-1"></i> Upload & Process dengan AI</span>
                    <span id="btnLoading" style="display:none;">
                        <i class="fa fa-spinner fa-spin me-1"></i> Menganalisis Data AI...
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function showLoading() {
    document.getElementById('btnText').style.display = 'none';
    document.getElementById('btnLoading').style.display = 'inline-block';
    document.getElementById('btnSubmit').disabled = true;
}
</script>
</body>
</html>