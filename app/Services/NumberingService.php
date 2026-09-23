<?php

namespace App\Services;

use App\Models\ArchiveModel;

class NumberingService
{
    public function __construct(private ArchiveModel $archiveModel)
    {
    }

    public function apply(array $rows, string $kegiatanId, string $mode = 'continue'): array
    {
        $storedMaxByYear = $mode === 'new' ? [] : $this->storedMaxByYear($kegiatanId);
        $nextByYear = [];
        foreach ($rows as &$row) {
            $row['no_item'] = $this->blank($row['no_item'] ?? null)
                ? null
                : $row['no_item'];
            $year = $this->yearKey($row['kurun_waktu'] ?? null);
            if ($this->blank($row['no_sampul'] ?? null)) {
                if (!array_key_exists($year, $nextByYear)) {
                    $nextByYear[$year] = ($storedMaxByYear[$year] ?? 0) + 1;
                }
                $row['no_sampul'] = $nextByYear[$year]++;
            } else {
                $nextByYear[$year] = max(
                    $nextByYear[$year] ?? (($storedMaxByYear[$year] ?? 0) + 1),
                    $this->numberValue($row['no_sampul']) + 1
                );
            }
        }
        unset($row);
        return $rows;
    }

    private function storedMaxByYear(string $kegiatanId): array
    {
        $maxByYear = [];
        foreach ($this->archiveModel->where('kegiatan_id', $kegiatanId)->findAll() as $archive) {
            $year = $this->yearKey($archive['kurun_waktu'] ?? null);
            $maxByYear[$year] = max($maxByYear[$year] ?? 0, $this->numberValue($archive['no_sampul'] ?? null));
        }
        return $maxByYear;
    }

    private function yearKey(mixed $period): string
    {
        if (preg_match('/(?<!\d)(\d{4})(?!\d)/', (string) $period, $matches)) {
            return $matches[1];
        }
        return 'tanpa_tahun';
    }

    private function numberValue(mixed $value): int
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $value);
        return $digits === '' ? 0 : (int) $digits;
    }

    private function blank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }
}