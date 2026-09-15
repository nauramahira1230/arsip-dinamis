<?php

namespace App\Services;

class ArchiveTransformService
{
    public function transform(array $sheet, array $mapping, array $batch, array $aiResults, string $kegiatanId): array
    {
        $rows = [];
        foreach ($sheet['rows'] as $index => $source) {
            $description = $this->value($source, $mapping['uraian'] ?? null);
            if ($description === null || $description === '') {
                continue;
            }

            $ai = $aiResults[$index] ?? ['jenis_naskah' => 'Perlu Verifikasi', 'kategori_arsip' => 'Perlu Verifikasi', 'nama_berkas' => null];
            $rows[] = [
                'no' => $this->value($source, $mapping['no'] ?? null),
                'kode_klasifikasi' => $this->value($source, $mapping['kode_klasifikasi'] ?? null),
                'unit_pencipta' => $this->batchValue($batch, 'unit_pencipta'),
                'unit_pengolah' => $this->batchValue($batch, 'unit_pengolah'),
                'jenis_naskah' => $ai['jenis_naskah'] ?? 'Perlu Verifikasi',
                'kategori_arsip' => $ai['kategori_arsip'] ?? 'Perlu Verifikasi',
                'nama_berkas' => $ai['nama_berkas'] ?? null,
                'uraian_arsip' => $this->cleanText($description),
                'jumlah_lembar' => $this->value($source, $mapping['jumlah_lembar'] ?? null),
                'kurun_waktu' => $this->value($source, $mapping['kurun_waktu'] ?? null),
                'semula' => $this->batchValue($batch, 'semula'),
                'menjadi' => $this->batchValue($batch, 'menjadi'),
                'alat_scan' => $this->batchValue($batch, 'alat_scan'),
                'waktu_scan' => $this->batchValue($batch, 'waktu_scan'),
                'tingkat_perkembangan' => $this->value($source, $mapping['tingkat_perkembangan'] ?? null),
                'no_sampul' => $this->value($source, $mapping['no_sampul'] ?? null),
                'no_item' => $this->value($source, $mapping['no_item'] ?? null),
                'boks' => $this->value($source, $mapping['boks'] ?? null),
                'rak' => $this->value($source, $mapping['rak'] ?? null),
                'ro' => $this->value($source, $mapping['ro'] ?? null),
                'lokasi' => $this->value($source, $mapping['lokasi_simpan'] ?? null) ?? $this->batchValue($batch, 'lokasi'),
                'status_authentication' => 'Belum',
                'kegiatan_id' => $kegiatanId,
            ];
        }
        return $rows;
    }

    private function value(array $row, mixed $index): ?string
    {
        if ($index === null || !array_key_exists((int) $index, $row)) {
            return null;
        }
        $value = trim((string) $row[(int) $index]);
        return $value === '' ? null : $value;
    }

    private function batchValue(array $batch, string $key): ?string
    {
        $value = trim((string) ($batch[$key] ?? ''));
        return $value === '' ? null : $value;
    }

    private function cleanText(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}