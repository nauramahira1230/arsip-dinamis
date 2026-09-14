<?php

namespace App\Controllers;

use App\Models\ArchiveModel;
use App\Models\KegiatanModel;
use App\Services\ArchiveAIService;
use App\Services\ArchiveTransformService;
use App\Services\ArchiveValidationService;
use App\Services\ExcelImportService;
use App\Services\NumberingService;

class ArchiveController extends BaseController
{
    protected $archiveModel;
    protected $kegiatanModel;
    private ExcelImportService $excelImport;
    private ArchiveAIService $archiveAI;
    private ArchiveTransformService $transformer;
    private ArchiveValidationService $archiveValidator;
    private NumberingService $numbering;

    public function __construct()
    {
        $this->archiveModel = new ArchiveModel();
        $this->kegiatanModel = new KegiatanModel();
        $this->excelImport = new ExcelImportService();
        $this->archiveAI = new ArchiveAIService();
        $this->transformer = new ArchiveTransformService();
        $this->archiveValidator = new ArchiveValidationService();
        $this->numbering = new NumberingService($this->archiveModel);
    }

    public function dashboard()
    {
        $stats = $this->archiveModel->getDashboardStats();
        $stats['activityProgress'] = $this->archiveModel->getActivityProgress();
        return view('archives/dashboard', $stats);
    }

    public function import()
    {
        return view('archives/import', ['kegiatan' => $this->kegiatanModel->getForImport()]);
    }

    public function index()
    {
        return view('archives/index', ['archives' => $this->archiveModel->findAll()]);
    }

    public function preview()
    {
        $previewData = session()->get('preview_data');
        $kegiatanId = session()->get('preview_kegiatan_id');
        $kegiatan = $kegiatanId ? $this->kegiatanModel->find($kegiatanId) : null;

        if (!$previewData || !$kegiatan) {
            return redirect()->to('/archives/import')->with('error', 'Silakan upload file Excel terlebih dahulu.');
        }

        return view('archives/preview', ['previewData' => $previewData, 'kegiatan' => $kegiatan]);
    }

    public function processPreview()
    {
        $kegiatanId = trim((string) $this->request->getPost('kegiatan_id'));
        if (!$this->kegiatanModel->find($kegiatanId)) {
            return redirect()->back()->withInput()->with('error', 'Pilih kegiatan yang valid terlebih dahulu.');
        }

        $file = $this->request->getFile('excel_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File Excel tidak valid.');
        }

        try {
            $inspection = $this->excelImport->inspect($file->getTempName());
            $batch = [
                'unit_pencipta' => trim((string) $this->request->getPost('unit_pencipta')),
                'unit_pengolah' => trim((string) $this->request->getPost('unit_pengolah')),
                'semula' => trim((string) $this->request->getPost('semula')),
                'menjadi' => trim((string) $this->request->getPost('menjadi')),
                'alat_scan' => trim((string) $this->request->getPost('alat_scan')),
                'waktu_scan' => $this->scanPeriod(
                    $this->request->getPost('tahun_scan'),
                    $this->request->getPost('bulan_scan')
                ),
                'lokasi' => trim((string) $this->request->getPost('lokasi')),
            ];
            session()->set('import_payload', ['kegiatan_id' => $kegiatanId, 'inspection' => $inspection, 'batch' => $batch]);
            return redirect()->to('/archives/mapping');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    public function mapping()
    {
        $payload = session()->get('import_payload');
        if (!$payload) {
            return redirect()->to('/archives/import')->with('error', 'Upload Excel terlebih dahulu.');
        }

        $sheet = $payload['inspection']['sheets'][$payload['inspection']['sheet']];
        return view('archives/mapping', [
            'headers' => $sheet['headers'],
            'mapping' => $sheet['mapping'],
            'fields' => $this->excelImport->fields(),
            'sheetName' => $payload['inspection']['sheet'],
        ]);
    }

    private function scanPeriod(mixed $year, mixed $month): ?string
    {
        $year = (int) $year;
        $month = (int) $month;

        if ($year < 1900 || $year > 2200 || $month < 1 || $month > 12) {
            return null;
        }

        return sprintf('%04d-%02d', $year, $month);
    }

    public function processMapping()
    {
        $payload = session()->get('import_payload');
        if (!$payload) {
            return redirect()->to('/archives/import')->with('error', 'Sesi import sudah berakhir.');
        }

        $sheet = $payload['inspection']['sheets'][$payload['inspection']['sheet']];
        $mapping = [];
        $postedMapping = $this->request->getPost('mapping');
        $postedMapping = is_array($postedMapping) ? $postedMapping : [];
        foreach ($this->excelImport->fields() as $field) {
            $value = $postedMapping[$field] ?? null;
            if ($value !== null && $value !== '') {
                $mapping[$field] = (int) $value;
            }
        }
        if (!array_key_exists('uraian', $mapping)) {
            return redirect()->back()->withInput()->with('error', 'Kolom Uraian wajib dipetakan.');
        }

        $descriptions = [];
        $descriptionIndexes = [];
        foreach ($sheet['rows'] as $index => $row) {
            $description = trim((string) ($row[$mapping['uraian']] ?? ''));
            if ($description !== '') {
                $descriptionIndexes[] = $index;
                $descriptions[] = $description;
            }
        }

        $aiResults = [];
        foreach ($this->archiveAI->analyze($descriptions) as $index => $result) {
            $aiResults[$descriptionIndexes[$index] ?? $index] = $result;
        }

        $rows = $this->transformer->transform($sheet, $mapping, $payload['batch'], $aiResults, $payload['kegiatan_id']);
        $rows = $this->numbering->apply($rows, $payload['kegiatan_id']);
        $errors = $this->archiveValidator->validate($rows);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        session()->set('preview_data', $rows);
        session()->set('preview_kegiatan_id', $payload['kegiatan_id']);
        session()->remove('import_payload');
        return redirect()->to('/archives/preview');
    }

    public function saveBulk()
    {
        $dataPost = $this->request->getPost('archives');
        $kegiatanId = session()->get('preview_kegiatan_id');
        if (!$kegiatanId || !$this->kegiatanModel->find($kegiatanId)) {
            return redirect()->to('/archives/import')->with('error', 'Kegiatan upload tidak valid atau sudah kedaluwarsa.');
        }

        $allowed = array_flip($this->archiveModel->allowedFields());
        $rows = [];
        foreach (is_array($dataPost) ? $dataPost : [] as $archive) {
            $row = array_intersect_key($archive, $allowed);
            $row['kegiatan_id'] = $kegiatanId;
            $row['status_authentication'] = 'Belum';
            $rows[] = $row;
        }
        $errors = $this->archiveValidator->validate($rows);
        if ($rows === [] || $errors !== []) {
            return redirect()->to('/archives/preview')->with('error', $errors[0] ?? 'Tidak ada data untuk disimpan.');
        }

        $this->archiveModel->insertBatch($rows);
        session()->remove(['preview_data', 'preview_kegiatan_id']);
        return redirect()->to('/archives')->with('success', 'Data arsip berhasil disimpan dengan status Belum.');
    }
}
