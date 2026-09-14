<?php

namespace App\Services;

class ArchiveAIService
{
    public function analyze(array $descriptions): array
    {
        if ($descriptions === [] || !env('GEMINI_API_KEY')) {
            return array_fill(0, count($descriptions), $this->fallback());
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
            return array_fill(0, count($descriptions), $this->fallback());
        }

        $results = [];
        foreach ($descriptions as $index => $description) {
            $item = is_array($decoded[$index] ?? null) ? $decoded[$index] : [];
            $results[] = [
                'jenis_naskah' => trim((string) ($item['jenis_naskah'] ?? 'Perlu Verifikasi')) ?: 'Perlu Verifikasi',
                'kategori_arsip' => trim((string) ($item['kategori_arsip'] ?? 'Perlu Verifikasi')) ?: 'Perlu Verifikasi',
                'nama_berkas' => trim((string) ($item['nama_berkas'] ?? '')) ?: null,
            ];
        }
        return $results;
    }

    private function fallback(): array
    {
        return ['jenis_naskah' => 'Perlu Verifikasi', 'kategori_arsip' => 'Perlu Verifikasi', 'nama_berkas' => null];
    }
}