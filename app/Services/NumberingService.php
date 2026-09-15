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
        foreach ($rows as &$row) {
            $row['no_item'] = $this->blank($row['no_item'] ?? null)
                ? null
                : $row['no_item'];
            if ($this->blank($row['no_sampul'] ?? null)) {
                $row['no_sampul'] = null;
            }
        }
        unset($row);
        return $rows;
    }

    private function blank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }
}