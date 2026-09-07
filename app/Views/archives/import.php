<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Data Arsip</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container py-4">

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Import Data Arsip Dari Excel</h5>
        </div>
        <div class="card-body">
            <form action="<?= base_url('archives/processPreview') ?>" method="post" enctype="multipart/form-data" onsubmit="showLoading()">
                <?= csrf_field() ?>

                <!-- Input File Excel -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls)</label>
                    <input type="file" name="excel_file" class="form-control" required accept=".xlsx, .xls">
                    <div class="form-text">Uraian arsip pada file Excel akan dianalisis AI untuk mengisi jenis naskah dan nama berkas. Hasilnya dapat diperiksa dan disesuaikan pada halaman preview.</div>
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