<?php

namespace App\Controllers;

use App\Models\ArchiveModel;
use App\Models\KegiatanModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ArchiveController extends BaseController
{
    protected $archiveModel;
    protected $kegiatanModel;

    public function __construct()
    {
        $this->archiveModel = new ArchiveModel();
        $this->kegiatanModel = new KegiatanModel();
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
        $data['archives'] = $this->archiveModel->findAll();
        return view('archives/index', $data);
    }

    public function preview()
    {
        $session = session();
        $previewData = $session->get('preview_data');
        $kegiatanId = $session->get('preview_kegiatan_id');
        $kegiatan = $kegiatanId ? $this->kegiatanModel->find($kegiatanId) : null;

        if (!$previewData || !$kegiatan) {
            return redirect()->to('/archives/import')->with('error', 'Silakan upload file Excel terlebih dahulu.');
        }

        return view('archives/preview', ['previewData' => $previewData, 'kegiatan' => $kegiatan]);
    }

    // --- ANALISIS BATCH DENGAN AI ARSIPARIS ALIH MEDIA ---
    private function analyzeArchiveBatchWithAI(array $rawTexts): array
    {
        $apiKey = env('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        $jsonInput = json_encode($rawTexts, JSON_UNESCAPED_UNICODE);

          $prompt = "Bayangkan Anda seorang arsiparis alih media. Anda diperintahkan memberi nama berkas dan menentukan jenis naskah berdasarkan uraian arsip.

Berikut daftar uraian arsip dalam format JSON array. Setiap elemen adalah satu arsip dan nomor indeksnya wajib dipertahankan:
{$jsonInput}

Untuk SETIAP uraian, kerjakan aturan berikut.

1. 'jenis_naskah': pilih tepat SATU nilai dari daftar baku ini:
    - Surat Keputusan
    - Surat Edaran
    - Surat Biasa
    - Notulen Rapat
    - Laporan Kegiatan
    - Perjanjian Kerja Sama
    - Berita Acara
    Gunakan 'Surat Keputusan' jika uraian memuat SK atau keputusan/penetapan pejabat. Jangan menambahkan nomor, tanggal, instansi, atau uraian lain ke nilai jenis naskah.

2. 'kategori_arsip': pilih tepat SATU nilai dari: Vital, Terjaga, Umum, Statis, Dinamis.

3. 'nama_berkas': buat judul/nama berkas baru yang menggambarkan inti arsip, seperti nama yang akan dipakai arsiparis pada daftar berkas.
    - Minimal 3 kata; boleh lebih jika diperlukan.
    - Gunakan frasa nominal yang singkat, bukan kalimat dan bukan paragraf.
    - Ambil inti kegiatan, objek, atau pokok keputusan dari uraian.
    - Jangan menyalin uraian secara utuh.
    - Jangan memasukkan nomor surat, tanggal lengkap, alamat, nama pejabat, kata 'uraian arsip', atau penjelasan tambahan.
    - Jangan mengulang label jenis naskah sebagai seluruh nama berkas.
    - Contoh: uraian tentang 'SK ... tentang Izin Lokasi, Pembebasan dan Penggunaan Tanah ...' menghasilkan nama berkas 'Izin Lokasi Pembebasan dan Penggunaan Tanah', bukan nomor SK atau seluruh isi uraian.

Kembalikan HANYA JSON ARRAY OF OBJECTS, tanpa markdown/backticks, dengan jumlah elemen dan urutan indeks yang sama persis seperti input:
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
        $kegiatanId = trim((string) $this->request->getPost('kegiatan_id'));
        $kegiatan = $this->kegiatanModel->find($kegiatanId);
        if (!$kegiatan) {
            return redirect()->back()->withInput()->with('error', 'Pilih kegiatan yang valid terlebih dahulu.');
        }

        $file = $this->request->getFile('excel_file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File Excel tidak valid.');
        }

        // Nilai teknis alih media ditetapkan otomatis agar form hanya memerlukan file Excel.
        $defaultSemula   = 'Kertas';
        $defaultMenjadi  = 'Digital (PDF)';
        $defaultAlatScan = 'Flatbed Scanner A4/F4';
        $defaultStatus   = 'Terautentikasi';

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
            session()->set('preview_kegiatan_id', $kegiatan['id']);
            return redirect()->to('/archives/preview');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    // --- SIMPAN KE DB (MENANGKAP EDITAN DARI HALAMAN PREVIEW) ---
    public function saveBulk()
    {
        $dataPost = $this->request->getPost('archives');
        $kegiatanId = session()->get('preview_kegiatan_id');
        if (!$kegiatanId || !$this->kegiatanModel->find($kegiatanId)) {
            return redirect()->to('/archives/import')->with('error', 'Kegiatan upload tidak valid atau sudah kedaluwarsa.');
        }

        if (!empty($dataPost) && is_array($dataPost)) {
            foreach ($dataPost as &$archive) {
                $archive['kegiatan_id'] = $kegiatanId;
            }
            unset($archive);
            $this->archiveModel->insertBatch($dataPost);
            session()->remove('preview_data');
            session()->remove('preview_kegiatan_id');

            return redirect()->to('/archives')->with('success', 'Data arsip berhasil disimpan!');
        }

        return redirect()->to('/archives/import')->with('error', 'Tidak ada data untuk disimpan.');
    }
}