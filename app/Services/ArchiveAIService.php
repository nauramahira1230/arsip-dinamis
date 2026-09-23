<?php

namespace App\Services;

class ArchiveAIService
{
    public function analyze(array $descriptions): array
    {
        if ($descriptions === []) {
            return [];
        }

        $results = array_map(fn (string $description): array => $this->ruleBased($description), $descriptions);
        if (!env('GEMINI_API_KEY')) {
            return $results;
        }

        $systemPrompt = 'Tugasmu adalah mengklasifikasikan JENIS NASKAH dan KATEGORI ARSIP dari data NAMA BERKAS atau URAIAN ARSIP. '
            . 'Analisis maksud dan isi teks secara dinamis, lalu pilih tepat satu jenis naskah dari daftar berikut: '
            . 'Surat Permohonan untuk Permohonan, Berkas Permohonan, Pengajuan, Berkas Izin, Izin Usaha, atau Permohonan Mendirikan/Melanjutkan; '
            . 'Surat Keputusan untuk Keputusan, SK, Penetapan, atau Pengesahan; '
            . 'Surat Tugas untuk Surat Tugas, Surat Perintah, Penugasan, ST, atau Sprin; '
            . 'Berita Acara untuk Berita Acara, BA, Serah Terima, atau Hasil Pemeriksaan; '
            . 'Surat Pengantar untuk Pengantar atau Surat Pengantar; '
            . 'Surat Keterangan untuk Keterangan atau Suket; '
            . 'Laporan untuk Laporan atau Lap; '
            . 'Perjanjian Kerja Sama untuk Perjanjian, Kontrak, MoU, atau PKS. '
            . 'Jangan gunakan Perlu Verifikasi kecuali teks hanya berisi angka acak atau simbol tanpa makna sama sekali. '
            . 'Gunakan Dinamis Vital atau Vital jika berkaitan dengan Izin Usaha, Pendirian Perusahaan, Kepemilikan, Aset, Sertifikat Tanah atau Aset, Akta, atau Legalitas Hukum Utama. '
            . 'Gunakan Dinamis Aktif untuk administrasi umum, operasional harian, surat menyurat rutin, tugas harian, atau laporan rutin. '
            . 'Kembalikan HANYA JSON array dengan urutan sama. Setiap item wajib memiliki jenis_naskah, kategori_arsip, nama_berkas, confidence, dan metadata. '
            . 'Gunakan hanya fakta yang tertulis dalam uraian. Field metadata hanya boleh berisi nama_pihak, nomor_dokumen, luas_tanah, desa, kecamatan. '
            . 'Gunakan null jika fakta tidak ada. Nama berkas harus ringkas namun mempertahankan identitas penting, bukan salinan seluruh uraian.';
        $prompt = 'Data arsip yang harus dianalisis: '
            . json_encode(array_values($descriptions), JSON_UNESCAPED_UNICODE);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . rawurlencode(env('GEMINI_API_KEY'));
        $payload = [
            'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
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
            return $results;
        }

        foreach ($descriptions as $index => $description) {
            $item = is_array($decoded[$index] ?? null) ? $decoded[$index] : [];
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
        }
        return $results;
    }

    private function ruleBased(string $description): array
    {
        $text = $this->clean($description);
        $upper = strtoupper($text);
        $jenis = 'Surat Keterangan';
        $confidence = 0.35;

        $patterns = [
            '/\b(?:PERMOHONAN|BERKAS\s+PERMOHONAN|PENGAJUAN|BERKAS\s+IZIN|IZIN\s+USAHA|PERMOHONAN\s+(?:MENDIRIKAN|MELANJUTKAN))\b/u' => ['Surat Permohonan', 0.9],
            '/\bSALINAN\s+SK\b/u' => ['Salinan Keputusan', 0.92],
            '/\b(?:KEPUTUSAN|SK|PENETAPAN|PENGESAHAN)\b/u' => ['Surat Keputusan', 0.86],
            '/\b(?:SURAT\s+TUGAS|SURAT\s+PERINTAH|PENUGASAN|ST|SPRIN)\b/u' => ['Surat Tugas', 0.86],
            '/\b(?:BERITA\s+ACARA|BA|SERAH\s+TERIMA|HASIL\s+PEMERIKSAAN)\b/u' => ['Berita Acara', 0.92],
            '/\b(?:SURAT\s+PENGANTAR|PENGANTAR)\b/u' => ['Surat Pengantar', 0.86],
            '/\b(?:SURAT\s+KETERANGAN|KETERANGAN|SUKET)\b/u' => ['Surat Keterangan', 0.8],
            '/\b(?:LAPORAN|LAP)\b/u' => ['Laporan', 0.86],
            '/\b(?:PERJANJIAN|KONTRAK|MOU|PKS)\b/u' => ['Perjanjian Kerja Sama', 0.9],
        ];
        foreach ($patterns as $pattern => [$label, $score]) {
            if (preg_match($pattern, $upper)) {
                $jenis = $label;
                $confidence = $score;
                break;
            }
        }

        $metadata = $this->metadata([], $text);
        return [
            'jenis_naskah' => $jenis,
            'kategori_arsip' => $this->isVital($upper) ? 'Dinamis Vital' : 'Dinamis Aktif',
            'nama_berkas' => $this->fileName($text, $jenis),
            'ai_status' => $this->isMeaningless($text) ? 'PERLU_VERIFIKASI' : 'AUTO',
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
    }

    private function isVital(string $text): bool
    {
        return preg_match('/\b(?:IZIN\s+USAHA|PENDIRIAN\s+PERUSAHAAN|KEPEMILIKAN|ASET|SERT(?:I|I)FIKAT\s+(?:TANAH|ASET)|AKTA|LEGALITAS\s+HUKUM)\b/u', $text) === 1;
    }

    private function isMeaningless(string $text): bool
    {
        return $text === '' || preg_match('/^[\d\W_]+$/u', $text) === 1;
    }
}