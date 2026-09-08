<style>
    .app-navbar { background: #102a43; padding: 1rem 0; box-shadow: 0 8px 24px rgba(16,42,67,.12); }
    .app-navbar .container, .app-navbar .container-fluid { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    .app-brand { color: #fff; text-decoration: none; font: 700 1.1rem 'Space Grotesk', sans-serif; letter-spacing: -.03em; white-space: nowrap; }
    .app-brand:hover { color: #fff; }
    .app-brand-mark { display: inline-grid; place-items: center; width: 36px; height: 36px; margin-right: .6rem; border-radius: 10px; background: #f97316; color: #fff; }
    .app-nav-actions { display: flex; gap: .5rem; flex-wrap: wrap; justify-content: flex-end; }
    .app-nav-actions .btn { border-radius: 9px; font-weight: 700; white-space: nowrap; }
    .app-nav-actions .btn-primary { background: #0f766e; border-color: #0f766e; }
    .app-nav-actions .btn-primary:hover { background: #0b5f59; border-color: #0b5f59; }
    .app-nav-menu { display: none; position: relative; }
    .app-nav-menu summary { list-style: none; cursor: pointer; border: 1px solid rgba(255,255,255,.35); border-radius: 9px; color: #fff; padding: .55rem .85rem; font-weight: 700; white-space: nowrap; }
    .app-nav-menu summary::-webkit-details-marker { display: none; }
    .app-nav-menu summary:hover { background: rgba(255,255,255,.12); }
    .app-nav-menu[open] summary { background: #fff; color: #102a43; }
    .app-nav-dropdown { position: absolute; right: 0; top: calc(100% + .55rem); z-index: 20; width: 230px; padding: .45rem; border: 1px solid #dce7ee; border-radius: 12px; background: #fff; box-shadow: 0 14px 30px rgba(16,42,67,.18); }
    .app-nav-dropdown a { display: flex; align-items: center; gap: .6rem; padding: .7rem .75rem; border-radius: 8px; color: #102a43; text-decoration: none; font-weight: 700; }
    .app-nav-dropdown a:hover { background: #e8f7f4; color: #0f766e; }
    .app-nav-dropdown i { width: 1.1rem; color: #0f766e; text-align: center; }
    @media (max-width: 768px) {
        .app-navbar .container, .app-navbar .container-fluid { align-items: center; flex-direction: row; flex-wrap: wrap; }
        .app-nav-actions { display: none; }
        .app-nav-menu { display: block; margin-left: auto; }
        .app-nav-dropdown { right: 0; width: min(230px, calc(100vw - 2rem)); }
    }
</style>
<nav class="app-navbar mb-4">
    <div class="<?= $navbarFluid ?? 'container' ?>">
        <a class="app-brand" href="<?= site_url('/archives/dashboard') ?>">
            <span class="app-brand-mark"><i class="bi bi-archive-fill"></i></span>ArsipDinamis
        </a>
        <div class="app-nav-actions">
            <a href="<?= site_url('/archives/dashboard') ?>" class="btn btn-light"><i class="bi bi-grid-1x2 me-1"></i> Dashboard</a>
            <a href="<?= site_url('/kegiatan') ?>" class="btn btn-outline-light"><i class="bi bi-kanban me-1"></i> Master Kegiatan</a>
            <a href="<?= site_url('/archives/import') ?>" class="btn btn-warning"><i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel Mentah</a>
            <a href="<?= site_url('/archives') ?>" class="btn btn-primary"><i class="bi bi-table me-1"></i> Data Arsip</a>
        </div>
        <details class="app-nav-menu">
            <summary><i class="bi bi-list me-1"></i> Menu</summary>
            <div class="app-nav-dropdown">
                <a href="<?= site_url('/archives/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Dashboard</a>
                <a href="<?= site_url('/kegiatan') ?>"><i class="bi bi-kanban"></i> Master Kegiatan</a>
                <a href="<?= site_url('/archives/import') ?>"><i class="bi bi-file-earmark-arrow-up"></i> Import Excel Mentah</a>
                <a href="<?= site_url('/archives') ?>"><i class="bi bi-table"></i> Data Arsip</a>
            </div>
        </details>
    </div>
</nav>
