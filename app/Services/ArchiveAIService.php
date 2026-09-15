<?php

namespace App\Services;

class ArchiveAIService
{
    public function analyze(array $descriptions): array
    {
        if ($descriptions === [] || !env('GEMINI_API_KEY')) {
            return array_map(fn (string $description): array => $this->fallback($description), $descriptions);
        }

        $prompt = 'Analisis setiap uraian arsip berikut. Kembalikan HANYA JSON array dengan urutan sama. '
            . 'Untuk setiap item isi jenis_naskah, kategori_arsip, nama_berkas. '
            . 'Jenis naskah harus mengikuti isi dokumen, tidak terbatas pada daftar tetap. '
            . 'Jika tidak yakin, gunakan "Perlu Verifikasi". Nama berkas harus ringkas, informatif, berupa frasa nominal, '
            . 'bukan salinan seluruh uraian dan tidak boleh menghilangkan inti objek. Jangan mengubah uraian sumber. '
            . json_encode(array_values($descriptions), JSON_UNESCAPED_UNICODE);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . rawurlencode(env('GEMINI_API_KEY'));
        $payload = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['response_mime_type' => 'application/json'],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 45,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $text = json_decode((string) $response, true)['candidates'][0]['content']['parts'][0]['text'] ?? null;
        $decoded = is_string($text) ? json_decode($text, true) : null;
        if (!is_array($decoded)) {
            return array_map(fn (string $description): array => $this->fallback($description), $descriptions);
        }

        $results = [];
        foreach ($descriptions as $index => $description) {
            $item = is_array($decoded[$index] ?? null) ? $decoded[$index] : [];
            $fallback = $this->fallback($description);
            $results[] = [
                'jenis_naskah' => trim((string) ($item['jenis_naskah'] ?? '')) ?: $fallback['jenis_naskah'],
                'kategori_arsip' => trim((string) ($item['kategori_arsip'] ?? '')) ?: $fallback['kategori_arsip'],
                'nama_berkas' => trim((string) ($item['nama_berkas'] ?? '')) ?: $fallback['nama_berkas'],
            ];
        }
        return $results;
    }

    private function fallback(string $description): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $description) ?? $description);
        $upper = strtoupper($text);
        $jenis = 'Perlu Verifikasi';
        foreach ([
            '/\bSALINAN\s+SK\b/u' => 'Salinan Keputusan',
            '/\b(SK|SURAT KEPUTUSAN)\b/u' => 'Surat Keputusan',
            '/\bSURAT\s+PERMOHONAN\b/u' => 'Surat Permohonan',
            '/\bNOTA\s+DINAS\b/u' => 'Nota Dinas',
            '/\b(SERTIPIKAT|SERTIFIKAT)\b/u' => 'Sertifikat',
            '/\bBERITA\s+ACARA\b/u' => 'Berita Acara',
        ] as $pattern => $label) {
            if (preg_match($pattern, $upper)) {
                $jenis = $label;
                break;
            }
        }

        $kategori = preg_match('/\b(TANAH|PERTANAHAN|HGB|HAK\s+GUNA|PELEPASAN\s+HAK)\b/u', $upper)
            ? 'Pertanahan'
            : 'Perlu Verifikasi';
        $name = $this->makeName($text, $jenis);

        return ['jenis_naskah' => $jenis, 'kategori_arsip' => $kategori, 'nama_berkas' => $name];
    }

    private function makeName(string $text, string $jenis): ?string
    {
        if ($text === '') {
            return null;
        }
        $name = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $name = preg_replace('/\bNO\s*:\s*[^ ]+/iu', '', $name) ?? $name;
        $name = preg_replace('/\b(SALINAN\s+)?SK\b/iu', 'SK', $name) ?? $name;
        $name = preg_replace('/\b(MENERIMA|MEMBATALKAN|TENTANG|DAN|DARI|KEPADA)\b.*$/iu', '', $name) ?? $name;
        $name = trim($name, " .,:;-\t\n\r\0\x0B");
        if ($name === '' || strlen($name) > 120) {
            $name = trim(substr($name !== '' ? $name : $text, 0, 120));
        }
        return $name !== '' ? $name : ($jenis !== 'Perlu Verifikasi' ? $jenis : null);
    }
}