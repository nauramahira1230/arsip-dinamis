<?php

namespace App\Services;

class ArchiveValidationService
{
    public function validate(array $rows): array
    {
        $errors = [];
        foreach ($rows as $index => $row) {
            if (trim((string) ($row['uraian_arsip'] ?? '')) === '') {
                $errors[] = 'Baris ' . ($index + 1) . ': uraian arsip wajib tersedia.';
            }
            if (($row['status_authentication'] ?? null) !== 'Belum') {
                $errors[] = 'Baris ' . ($index + 1) . ': status autentikasi harus Belum.';
            }
        }
        return $errors;
    }
}