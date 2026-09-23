<?php

namespace App\Controllers;

use App\Models\ArchiveModel;
use App\Models\KegiatanModel;
use App\Services\ArchiveAIService;
use App\Services\ArchiveTransformService;
use App\Services\ArchiveValidationService;
use App\Services\ExcelImportService;
use App\Services\NumberingService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $filter = trim((string) $this->request->getGet('filter'));
        $kegiatanId = trim((string) $this->request->getGet('kegiatan_id'));
        $query = $this->archiveModel;
        $filterLabel = 'Semua arsip';

        if ($filter === 'active') {
            $query->where('kategori_arsip', 'Dinamis Aktif');
            $filterLabel = 'Arsip Dinamis Aktif';
        } elseif ($filter === 'inactive') {
            $query->where('kategori_arsip', 'Dinamis Inaktif');
            $filterLabel = 'Arsip Dinamis Inaktif';
        } elseif ($filter === 'authenticated') {
            $query->where('status_authentication', 'Terautentikasi');
            $filterLabel = 'Arsip Terautentikasi';
        } elseif ($kegiatanId !== '') {
            $query->where('kegiatan_id', $kegiatanId);
            $filterLabel = 'Arsip per kegiatan';
        }

        return view('archives/index', [
            'archives' => $query->findAll(),
            'filterLabel' => $filterLabel,
            'hasFilter' => $filter !== '' || $kegiatanId !== '',
        ]);
    }

    public function preview()
    {
        $previewData = $this->session->get('preview_data');
        $previewSummary = $this->session->get('preview_summary');
        $kegiatanId = $this->session->get('preview_kegiatan_id');
        $kegiatan = $kegiatanId ? $this->kegiatanModel->find($kegiatanId) : null;

        if (!$previewData || !$kegiatan) {
            return redirect()->to('/archives/import')->with('error', 'Silakan upload file Excel terlebih dahulu.');
        }

        return view('archives/preview', [
            'previewData' => $previewData,
            'previewSummary' => $previewSummary,
            'kegiatan' => $kegiatan,
        ]);
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
            $sheet = $inspection['sheets'][$inspection['sheet']];
            if (!array_key_exists('uraian', $sheet['mapping'])) {
                $this->session->set('import_payload', ['kegiatan_id' => $kegiatanId, 'inspection' => $inspection, 'batch' => $batch]);
                return redirect()->to('/archives/mapping')->with('error', 'Beberapa kolom Excel tidak dapat dikenali otomatis. Pilih hanya kolom Uraian.');
            }

            return $this->buildPreview($sheet, $sheet['mapping'], $batch, $kegiatanId);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    public function mapping()
    {
        $payload = $this->session->get('import_payload');
        if (!$payload) {
            return redirect()->to('/archives/import')->with('error', 'Upload Excel terlebih dahulu.');
        }

        $sheet = $payload['inspection']['sheets'][$payload['inspection']['sheet']];
        $missingFields = array_values(array_diff($this->excelImport->fields(), array_keys($sheet['mapping'])));
        return view('archives/mapping', [
            'headers' => $sheet['headers'],
            'mapping' => $sheet['mapping'],
            'fields' => $missingFields,
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
        $payload = $this->session->get('import_payload');
        if (!$payload) {
            return redirect()->to('/archives/import')->with('error', 'Sesi import sudah berakhir.');
        }

        $sheet = $payload['inspection']['sheets'][$payload['inspection']['sheet']];
        $mapping = $sheet['mapping'];
        $postedMapping = $this->request->getPost('mapping');
        $postedMapping = is_array($postedMapping) ? $postedMapping : [];
        foreach ($this->excelImport->fields() as $field) {
            $value = $postedMapping[$field] ?? ($sheet['mapping'][$field] ?? null);
            if ($value !== null && $value !== '') {
                $mapping[$field] = (int) $value;
            }
        }
        if (!array_key_exists('uraian', $mapping)) {
            return redirect()->back()->withInput()->with('error', 'Kolom Uraian wajib dipetakan.');
        }

        return $this->buildPreview($sheet, $mapping, $payload['batch'], $payload['kegiatan_id']);
    }

    private function buildPreview(array $sheet, array $mapping, array $batch, string $kegiatanId)
    {
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

        $rows = $this->transformer->transform($sheet, $mapping, $batch, $aiResults, $kegiatanId);
        $rows = $this->numbering->apply($rows, $kegiatanId);
        $errors = $this->archiveValidator->validate($rows);
        if ($errors !== []) {
            return redirect()->to('/archives/import')->withInput()->with('error', implode(' ', $errors));
        }

        $this->session->set('preview_data', $rows);
        $this->session->set('preview_summary', $this->summary($rows));
    $this->session->set('preview_kegiatan_id', $kegiatanId);
        $this->session->remove('import_payload');
        return redirect()->to('/archives/preview');
    }

    public function saveBulk()
    {
        $dataPost = $this->request->getPost('archives');
        $kegiatanId = $this->session->get('preview_kegiatan_id');
        if (!$kegiatanId || !$this->kegiatanModel->find($kegiatanId)) {
            return redirect()->to('/archives/import')->with('error', 'Kegiatan upload tidak valid atau sudah kedaluwarsa.');
        }

        $allowed = array_flip($this->archiveModel->getAllowedFields());
        $rows = [];
        foreach (is_array($dataPost) ? $dataPost : [] as $archive) {
            $row = array_intersect_key($archive, $allowed);
            foreach ($row as $field => $value) {
                if (is_string($value) && trim($value) === '') {
                    $row[$field] = null;
                }
            }
            $row['kegiatan_id'] = $kegiatanId;
            $row['status_authentication'] = 'Belum';
            $rows[] = $row;
        }
        $errors = $this->archiveValidator->validate($rows);
        if ($rows === [] || $errors !== []) {
            return redirect()->to('/archives/preview')->with('error', $errors[0] ?? 'Tidak ada data untuk disimpan.');
        }

        $this->archiveModel->insertBatch($rows);
        $this->session->remove(['preview_data', 'preview_summary', 'preview_kegiatan_id']);
        return redirect()->to('/archives')->with('success', 'Data arsip berhasil disimpan dengan status Belum.');
    }

    public function export()
    {
        $columns = [
            'no' => 'NO',
            'kode_klasifikasi' => 'KODE_KLASIFIKASI',
            'unit_pencipta' => 'UNIT_PENCIPTA',
            'unit_pengolah' => 'UNIT_PENGOLAH',
            'jenis_naskah' => 'JENIS_NASKAH',
            'kategori_arsip' => 'KATEGORI_ARSIP',
            'nama_berkas' => 'NAMA_BERKAS',
            'uraian_arsip' => 'URAIAN_ARSIP',
            'jumlah_lembar' => 'JUMLAH_LEMBAR',
            'kurun_waktu' => 'KURUN_WAKTU',
            'semula' => 'SEMULA',
            'menjadi' => 'MENJADI',
            'alat_scan' => 'ALAT_SCAN',
            'waktu_scan' => 'WAKTU_SCAN',
            'tingkat_perkembangan' => 'TINGKAT_PERKEMBANGAN',
            'no_sampul' => 'NO_SAMPUL',
            'no_item' => 'NO_ITEM',
            'boks' => 'BOKS',
            'rak' => 'RAK',
            'ro' => 'RO',
            'lokasi' => 'LOKASI',
            'status_authentication' => 'STATUS_AUTHENTICATION',
        ];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(array_values($columns), null, 'A1');
        $rows = $this->session->get('preview_data') ?: $this->archiveModel->findAll();
        $output = [];
        foreach ($rows as $row) {
            $output[] = array_map(static fn (string $field): ?string => $row[$field] ?? null, array_keys($columns));
        }
        if ($output !== []) {
            $sheet->fromArray($output, null, 'A2');
        }
        $stream = fopen('php://memory', 'r+');
        (new Xlsx($spreadsheet))->save($stream);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return $this->response->download('arsip.xlsx', $content, true);
    }

    private function summary(array $rows): array
    {
        $summary = ['total' => count($rows), 'AUTO' => 0, 'PERLU_VERIFIKASI' => 0, 'GAGAL_DIPROSES' => 0];
        foreach ($rows as $row) {
            $status = $row['ai_status'] ?? 'GAGAL_DIPROSES';
            if (!array_key_exists($status, $summary)) {
                $status = 'GAGAL_DIPROSES';
            }
            $summary[$status]++;
        }
        return $summary;
    }
}
