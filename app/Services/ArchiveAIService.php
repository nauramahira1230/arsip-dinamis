<?php

namespace App\Services;

class ArchiveAIService
{
    public function analyze(array $descriptions): array
    {
<<<<<<< HEAD
        if ($descriptions === [] || !env('GEMINI_API_KEY')) {
            return array_map(fn (string $description): array => $this->fallback($description), $descriptions);
=======
        if ($descriptions === []) {
            return [];
>>>>>>> e2270dc (kolom)
        }

        $results = array_map(fn (string $description): array => $this->ruleBased($description), $descriptions);
        if (!env('GEMINI_API_KEY')) {
            return $results;
        }

        $prompt = 'Analisis setiap uraian arsip berikut dan kembalikan HANYA JSON array dengan urutan sama. '
            . 'Isi jenis_naskah, kategori_arsip, nama_berkas, confidence, dan metadata. '
            . 'Gunakan hanya fakta yang tertulis dalam uraian. Field metadata hanya boleh berisi nama_pihak, nomor_dokumen, luas_tanah, desa, kecamatan. '
            . 'Gunakan null jika fakta tidak ada. Jangan menulis Perlu Verifikasi ke field data. '
            . 'Nama berkas harus ringkas namun mempertahankan identitas penting, bukan salinan seluruh uraian. '
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
<<<<<<< HEAD
            return array_map(fn (string $description): array => $this->fallback($description), $descriptions);
=======
            return $results;
>>>>>>> e2270dc (kolom)
        }

        foreach ($descriptions as $index => $description) {
            $item = is_array($decoded[$index] ?? null) ? $decoded[$index] : [];
<<<<<<< HEAD
            $fallback = $this->fallback($description);
            $results[] = [
                'jenis_naskah' => trim((string) ($item['jenis_naskah'] ?? '')) ?: $fallback['jenis_naskah'],
                'kategori_arsip' => trim((string) ($item['kategori_arsip'] ?? '')) ?: $fallback['kategori_arsip'],
                'nama_berkas' => trim((string) ($item['nama_berkas'] ?? '')) ?: $fallback['nama_berkas'],
            ];
=======
            $candidate = $results[$index];
            foreach (['jenis_naskah', 'kategori_arsip', 'nama_berkas'] as $field) {
                $value = $this->nullableText($item[$field] ?? null);
                if ($value !== null && !$this->isVerificationLabel($value)) {
                    $candidate[$field] = $field === 'nama_berkas'
                        ? $this->fileName($description, $candidate['jenis_naskah'] ?? null)
                        : $value;
                }
            }
            $confidence = is_numeric($item['confidence'] ?? null)
                ? max(0, min(1, (float) $item['confidence']))
                : $candidate['ai_confidence'];
            $candidate['ai_confidence'] = $confidence;
            $candidate['ai_status'] = $confidence >= 0.75 ? 'AUTO' : 'PERLU_VERIFIKASI';
            $candidate['ai_metadata'] = $this->metadata($item['metadata'] ?? [], $description);
            $results[$index] = $candidate;
>>>>>>> e2270dc (kolom)
        }
        return $results;
    }

<<<<<<< HEAD
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
=======
    private function ruleBased(string $description): array
    {
        $text = $this->clean($description);
        $upper = strtoupper($text);
        $jenis = null;
        $confidence = 0.35;

        $patterns = [
            'SALINAN SK' => ['Salinan Keputusan', 0.92],
            'SERTIPIKAT' => ['Sertifikat', 0.92],
            'SERTIFIKAT' => ['Sertifikat', 0.92],
            'SURAT PERMOHONAN' => ['Surat Permohonan', 0.9],
            'BERITA ACARA' => ['Berita Acara', 0.92],
            'SK ' => ['Surat Keputusan', 0.86],
            'KEPUTUSAN ' => ['Surat Keputusan', 0.82],
        ];
        foreach ($patterns as $needle => [$label, $score]) {
            if (str_contains($upper, $needle)) {
                $jenis = $label;
                $confidence = $score;
>>>>>>> e2270dc (kolom)
                break;
            }
        }

<<<<<<< HEAD
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
=======
        $metadata = $this->metadata([], $text);
        return [
            'jenis_naskah' => $jenis,
            'kategori_arsip' => $jenis === null ? null : 'Dinamis Aktif',
            'nama_berkas' => $this->fileName($text, $jenis),
            'ai_status' => $confidence >= 0.75 ? 'AUTO' : 'PERLU_VERIFIKASI',
            'ai_confidence' => $confidence,
            'ai_metadata' => $metadata,
        ];
    }

    private function metadata(mixed $candidate, string $description): array
    {
        $metadata = [];
        foreach (['nama_pihak', 'nomor_dokumen', 'luas_tanah', 'desa', 'kecamatan'] as $field) {
            $value = is_array($candidate) ? $this->nullableText($candidate[$field] ?? null) : null;
            if ($value === null) {
                $value = $this->extractMetadata($field, $description);
            }
            if ($value !== null && $this->occursIn($description, $value)) {
                $metadata[$field] = $value;
            }
        }
        return $metadata;
    }

    private function extractMetadata(string $field, string $description): ?string
    {
        $patterns = [
            'nama_pihak' => '/\b(?:atas nama|pemohon|pihak|kepada)\s*[:.]?\s*([^,;]+)/iu',
            'nomor_dokumen' => '/\b(?:nomor|no\.?)\s*[:.]?\s*([A-Z0-9][A-Z0-9.\/-]*)/iu',
            'luas_tanah' => '/\b(?:luas|seluas)\s*[:.]?\s*([0-9.,]+\s*(?:m2|m²|ha))/iu',
            'desa' => '/\bdesa\s*[:.]?\s*([^,;]+)/iu',
            'kecamatan' => '/\bkecamatan\s*[:.]?\s*([^,;]+)/iu',
        ];
        if (isset($patterns[$field]) && preg_match($patterns[$field], $description, $matches)) {
            return $this->nullableText($matches[1]);
        }
        return null;
    }

    private function fileName(string $description, ?string $jenis): ?string
    {
        if ($description === '') {
            return null;
        }

        $description = $this->clean($description);
        $hasSubject = preg_match('/\b(?:TENTANG|PERIHAL)\b\s*(.+)$/iu', $description, $matches) === 1;
        if ($hasSubject) {
            $name = $matches[1];
            $name = preg_replace(
                '/\s+\b(?:DI|DARI|PADA|UNTUK)\s+\b(?:DESA|KELURAHAN|KEC\.?|KECAMATAN|KAB\.?|KABUPATEN|KOTA)\b.*$/iu',
                '',
                $name
            ) ?? $name;
            $name = preg_replace('/\bPEMOHON\b\s*/iu', '', $name) ?? $name;
            $name = preg_replace('/^\s*IZIN\b/iu', 'Perizinan', $name) ?? $name;
        } else {
            $name = preg_split('/(?<=[.!?;])\s+/u', $description, 2)[0] ?? $description;
        }
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) > 10) {
            $name = implode(' ', array_slice($words, 0, 10));
        }
        if (!$hasSubject && $jenis !== null && !str_contains(strtoupper($name), strtoupper($jenis))) {
            $name = $jenis . ' ' . $name;
        }
        return trim(rtrim($name, '.,;:'));
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' || $this->isVerificationLabel($value) ? null : $value;
    }

    private function isVerificationLabel(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['perlu verifikasi', 'null', 'tidak diketahui'], true);
    }

    private function occursIn(string $source, string $value): bool
    {
        return str_contains(strtolower($source), strtolower($value));
    }

    private function clean(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
>>>>>>> e2270dc (kolom)
    }
}