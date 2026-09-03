<?php

namespace App\Controllers;

use App\Models\ArchiveModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ArchiveController extends BaseController
{
    protected $archiveModel;

    public function __construct()
    {
        $this->archiveModel = new ArchiveModel();
    }

    public function dashboard()
    {
        $stats = $this->archiveModel->getDashboardStats();
        return view('archives/dashboard', $stats);
    }

    public function import()
    {
        return view('archives/import');
    }

    public function index()
    {
        $data['archives'] = $this->archiveModel->findAll();
        return view('archives/index', $data);
    }

    public function preview()
    {
        $session = session();
        $previewData = $session->get('preview_data');

        if (!$previewData) {
            return redirect()->to('/archives/import')->with('error', 'Silakan upload file Excel terlebih dahulu.');
        }

        return view('archives/preview', ['previewData' => $previewData]);
    }

    // --- ANALISIS BATCH DENGAN AI ARSIPARIS ALIH MEDIA ---
    private function analyzeArchiveBatchWithAI(array $rawTexts): array
    {
        $apiKey = env('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        $jsonInput = json_encode($rawTexts, JSON_UNESCAPED_UNICODE);

        $prompt = "Bayangkan Anda adalah seorang arsiparis alih media profesional yang diberi tugas untuk menganalisis uraian arsip.

Berikut adalah daftar uraian arsip dalam format JSON array:
{$jsonInput}

Tugas Anda:
Untuk SETIAP uraian arsip di atas, tentukan nilai berikut:
1. 'jenis_naskah': WAJIB PILIH SALAH SATU dari opsi baku berikut (sesuai konteks uraian):
   - Surat Biasa / Surat Keluar
   - Surat Keputusan (SK)
   - Surat Edaran (SE)
   - Notulen Rapat
   - Laporan Kegiatan
   - Contrak / Perjanjian Kerja Sama
   - Berita Acara

2. 'kategori_arsip': WAJIB PILIH SALAH SATU dari opsi berikut:
   - Vital
   - Terjaga
   - Umum
   - Statis
   - Dinaktif

3. 'nama_berkas': Buat nama/judul berkas yang RINGKAS, RAPI, dan PADAT yang merangkum inti dari uraian arsip tersebut (DILARANG meng-copy paste utuh uraian arsip).

Kembalikan respon HANYA dalam format JSON ARRAY OF OBJECTS dengan urutan indeks yang sama persis seperti input, tanpa tanda markdown/backticks:
[
  {
    \"jenis_naskah\": \"...\",
    \"kategori_arsip\": \"...\",
    \"nama_berkas\": \"...\"
  }
]";

        $payload = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ],
            "generationConfig" => [
                "response_mime_type" => "application/json"
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);
            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($jsonText) {
                $parsed = json_decode($jsonText, true);
                if (is_array($parsed)) {
                    return $parsed;
                }
            }
        }

        return [];
    }

    // --- PROSES UPLOAD EXCEL ---
    public function processPreview()
    {
        $file = $this->request->getFile('excel_file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File Excel tidak valid.');
        }

        // Tangkap input default masal (hanya untuk alat scan & fisik)
        $defaultSemula   = $this->request->getPost('default_semula') ?? 'Kertas';
        $defaultMenjadi  = $this->request->getPost('default_menjadi') ?? 'Digital (PDF)';
        $defaultAlatScan = $this->request->getPost('default_alat_scan') ?? 'Flatbed Scanner A4/F4';
        $defaultStatus   = $this->request->getPost('default_status_auth') ?? 'Terautentikasi';

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $rowsToProcess = [];
            $rawTextsForAI = [];

            // Skip 2 baris header Excel
            for ($i = 2; $i < count($sheetData); $i++) {
                $row = $sheetData[$i];
                if (empty($row[3]) || trim($row[3]) == '' || is_numeric($row[3])) {
                    continue;
                }
                $rowsToProcess[] = $row;
                $rawTextsForAI[] = trim($row[3]);
            }

            // Panggil AI sekaligus secara Batch
            $aiResults = [];
            if (!empty($rawTextsForAI)) {
                $aiResults = $this->analyzeArchiveBatchWithAI($rawTextsForAI);
            }

            $transformedData = [];
            $sampulCounter = [];

            foreach ($rowsToProcess as $index => $row) {
                $uraianMentah = trim($row[3]);

                // Ekstrak hasil AI
                $jenisNaskahAI  = $aiResults[$index]['jenis_naskah'] ?? 'Surat Biasa / Surat Keluar';
                $kategoriArsipAI = $aiResults[$index]['kategori_arsip'] ?? 'Umum';
                $namaBerkasAI   = $aiResults[$index]['nama_berkas'] ?? 'Berkas Arsip';

                // Penomoran Sampul otomatis per tahun
                $tahun = !empty($row[4]) ? trim($row[4]) : 'Lainnya';
                if (!isset($sampulCounter[$tahun])) {
                    $sampulCounter[$tahun] = 1;
                } else {
                    $sampulCounter[$tahun]++;
                }
                $noSampulAuto = $sampulCounter[$tahun];

                $transformedData[] = [
                    'unit_pencipta'         => 'KOTA BOGOR',
                    'unit_pengolah'         => 'Bidang Kearsipan',
                    'jenis_naskah'          => $jenisNaskahAI,
                    'kategori_arsip'        => $kategoriArsipAI,
                    'nama_berkas'           => $namaBerkasAI,
                    'uraian_arsip'          => $uraianMentah,
                    'jumlah_lembar'         => $row[6] ?? '1 BERKAS',
                    'kurun_waktu'           => $tahun,
                    'semula'                => $defaultSemula,
                    'menjadi'               => $defaultMenjadi,
                    'alat_scan'             => $defaultAlatScan,
                    'waktu_scan'            => date('Y-m-d H:i:s'),
                    'tingkat_perkembangan'  => $row[5] ?? 'ASLI',
                    'no_sampul'             => $noSampulAuto,
                    'no_item'               => $row[8] ?? '-',
                    'boks'                  => $row[9] ?? '-',
                    // RAK, RO, LOKASI OTOMATIS DARI EXCEL MENTAH
                    'rak'                   => !empty($row[10]) ? trim($row[10]) : 'R-01',
                    'ro'                    => !empty($row[11]) ? trim($row[11]) : 'RO-01',
                    'lokasi'                => !empty($row[12]) ? trim($row[12]) : 'Gedung Depo Lt. 2',
                    'status_authentication' => $defaultStatus
                ];
            }

            session()->set('preview_data', $transformedData);
            return redirect()->to('/archives/preview');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    // --- SIMPAN KE DB (MENANGKAP EDITAN DARI HALAMAN PREVIEW) ---
    public function saveBulk()
    {
        $dataPost = $this->request->getPost('archives');

        if (!empty($dataPost) && is_array($dataPost)) {
            $this->archiveModel->insertBatch($dataPost);
            session()->remove('preview_data');

            return redirect()->to('/archives')->with('success', 'Data arsip berhasil disimpan!');
        }

        return redirect()->to('/archives/import')->with('error', 'Tidak ada data untuk disimpan.');
    }
}