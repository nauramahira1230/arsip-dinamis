<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Alih Media Arsip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= site_url('/archives/dashboard'); ?>">
            <i class="bi bi-archive-fill me-2"></i>Alih Media Arsip Dinamis
        </a>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-secondary">Dashboard Ringkasan</h4>
        <div>
            <a href="<?= site_url('/archives/import'); ?>" class="btn btn-success me-2">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel Mentah
            </a>
            <a href="<?= site_url('/archives'); ?>" class="btn btn-primary">
                <i class="bi bi-table me-1"></i> Data Arsip Supabase
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase opacity-75">Total Arsip</h6>
                    <h2 class="fw-bold mb-0"><?= $total_archives ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase opacity-75">Dinamis Active</h6>
                    <h2 class="fw-bold mb-0"><?= $total_active ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase opacity-75">Dinamis Inactive</h6>
                    <h2 class="fw-bold mb-0"><?= $total_inactive ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-uppercase opacity-75">Authenticated</h6>
                    <h2 class="fw-bold mb-0"><?= $total_authenticated ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>