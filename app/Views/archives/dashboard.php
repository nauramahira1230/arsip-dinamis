<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Alih Media Arsip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --navy: #102a43; --teal: #0f766e; --orange: #f97316; --paper: #f5f7fa; --ink: #172b4d; --muted: #6b7c93; }
        body { background: var(--paper) !important; color: var(--ink); font-family: 'DM Sans', sans-serif; }
        h1, h2, h3, h4, h5, .navbar-brand { font-family: 'Space Grotesk', sans-serif; }
        .navbar { background: var(--navy) !important; padding: 1rem 0; box-shadow: 0 8px 24px rgba(16,42,67,.12); }
        .navbar-brand { letter-spacing: -.03em; }
        .brand-mark { display: inline-grid; place-items: center; width: 36px; height: 36px; margin-right: .6rem; border-radius: 10px; background: var(--orange); color: #fff; }
        .eyebrow { color: var(--teal); font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .page-title { font-size: clamp(1.65rem, 3vw, 2.35rem); letter-spacing: -.05em; }
        .intro { color: var(--muted); max-width: 560px; }
        .btn { border-radius: 10px; font-weight: 600; }
        .btn-primary { background: var(--teal); border-color: var(--teal); }
        .btn-primary:hover { background: #0b5f59; border-color: #0b5f59; }
        .btn-success { background: var(--orange); border-color: var(--orange); }
        .btn-success:hover { background: #ea580c; border-color: #ea580c; }
        .stat-card, .progress-card { border: 1px solid rgba(16,42,67,.08) !important; border-radius: 16px; box-shadow: 0 8px 24px rgba(16,42,67,.06) !important; }
        .stat-card { position: relative; overflow: hidden; color: var(--ink) !important; background: #fff !important; }
        .stat-card::after { content: ''; position: absolute; right: -22px; bottom: -28px; width: 90px; height: 90px; border-radius: 50%; background: rgba(15,118,110,.09); }
        .stat-card .stat-icon { color: var(--teal); font-size: 1.4rem; }
        .stat-label { color: var(--muted); font-size: .76rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .stat-number { font-family: 'Space Grotesk', sans-serif; font-size: 2rem; }
        .section-title { letter-spacing: -.03em; }
        .progress-card { background: #fff; }
        .progress { height: 10px; background: #e8eef2; border-radius: 99px; }
        .progress-bar { background: linear-gradient(90deg, var(--teal), #2aa198); border-radius: 99px; }
        .activity-meta { color: var(--muted); font-size: .88rem; }
        @media (max-width: 768px) { .dashboard-actions { width: 100%; } .dashboard-actions .btn { flex: 1; } }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= site_url('/archives/dashboard'); ?>">
            <span class="brand-mark"><i class="bi bi-archive-fill"></i></span>Alih Media Arsip Dinamis
        </a>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4 flex-wrap">
        <div><div class="eyebrow mb-2">Pusat kendali arsip</div><h1 class="page-title fw-bold mb-2">Dashboard Ringkasan</h1><p class="intro mb-0">Pantau volume arsip, status autentikasi, dan capaian setiap kegiatan dalam satu layar.</p></div>
        <div class="dashboard-actions d-flex gap-2">
            <a href="<?= site_url('/kegiatan'); ?>" class="btn btn-outline-dark"><i class="bi bi-kanban me-1"></i> Master Kegiatan</a>
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
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span class="stat-label">Total Arsip</span><i class="bi bi-archive stat-icon"></i></div>
                    <h2 class="stat-number fw-bold mb-0 mt-3"><?= number_format($total_archives ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span class="stat-label">Dinamis Aktif</span><i class="bi bi-lightning-charge stat-icon"></i></div>
                    <h2 class="stat-number fw-bold mb-0 mt-3"><?= number_format($total_active ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span class="stat-label">Dinamis Inaktif</span><i class="bi bi-hourglass-split stat-icon"></i></div>
                    <h2 class="stat-number fw-bold mb-0 mt-3"><?= number_format($total_inactive ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span class="stat-label">Terautentikasi</span><i class="bi bi-patch-check stat-icon"></i></div>
                    <h2 class="stat-number fw-bold mb-0 mt-3"><?= number_format($total_authenticated ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-end justify-content-between mt-5 mb-3"><div><div class="eyebrow mb-1">Target dan capaian</div><h4 class="section-title fw-bold mb-0">Realisasi Kegiatan</h4></div><span class="activity-meta">Diperbarui dari data arsip tersimpan</span></div>
    <div class="row g-3">
        <?php foreach ($activityProgress ?? [] as $activity): ?>
            <div class="col-md-6">
                <div class="card progress-card h-100"><div class="card-body p-4">
                    <div class="d-flex justify-content-between gap-3"><strong><?= esc($activity['nama_kegiatan']) ?></strong><span class="fw-bold" style="color: var(--teal)"><?= esc($activity['persentase_capaian']) ?>%</span></div>
                    <div class="progress my-2" role="progressbar" aria-valuenow="<?= esc($activity['persentase_capaian']) ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: <?= esc($activity['persentase_capaian']) ?>%"></div></div>
                    <small class="activity-meta"><?= number_format($activity['realisasi_lembar']) ?> / <?= number_format($activity['target_lembar']) ?> lembar <span class="mx-1">&middot;</span> sisa <?= number_format($activity['sisa_target']) ?></small>
                </div></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>