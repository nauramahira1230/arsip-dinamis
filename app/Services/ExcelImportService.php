<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    private const HEADER_ALIASES = [
        'no' => ['no', 'nomor', 'nomor urut'],
    'indeks' => ['indeks', 'index'],
    'kode_klasifikasi' => ['kode klasifikasi', 'kode klas', 'klasifikasi', 'klas', 'indeks'],
        'uraian' => ['uraian', 'uraian arsip', 'deskripsi', 'informasi'],
        'kurun_waktu' => ['kurun waktu', 'tahun', 'periode'],
        'tingkat_perkembangan' => ['tingkat perkembangan', 'tingkat', 'status dokumen'],
        'jumlah_lembar' => ['jumlah lembar', 'jml lembar', 'jumlah', 'lembar'],
        'jumlah_sumber' => ['jumlah', 'jumlah berkas', 'volume'],
        'lokasi_simpan' => ['lokasi simpan', 'lokasi', 'tempat simpan'],
        'no_item' => ['no item', 'nomor item', 'item', 'berkas', 'nomor berkas'],
        'boks' => ['boks', 'box'],
        'rak' => ['rak'],
        'ro' => ['ro'],
        'no_sampul' => ['no sampul', 'nomor sampul', 'sampul'],
    ];

    public function inspect(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheets = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $matrix = $sheet->toArray(null, true, true, false);
            [$headerRow, $headers] = $this->findHeader($matrix);
            if ($headers === []) {
                continue;
            }

            $rows = [];
            for ($index = $headerRow + 1; $index < count($matrix); $index++) {
                $row = array_pad($matrix[$index], count($headers), null);
                if ($this->isEmptyRow($row)) {
                    continue;
                }
                $rows[] = array_values($row);
            }

            $sheets[$sheet->getTitle()] = [
                'headers' => $headers,
                'header_row' => $headerRow,
                'rows' => $rows,
                'mapping' => $this->detectMapping($headers),
            ];
        }

        if ($sheets === []) {
            throw new \RuntimeException('Tidak menemukan baris header pada workbook.');
        }

        $firstSheet = array_key_first($sheets);
        return [
            'sheet' => $firstSheet,
            'sheets' => $sheets,
        ];
    }

    public function normalizeHeader(?string $header): string
    {
        $header = trim((string) $header);
        $header = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $header) ?? $header;
        return trim(strtolower($header));
    }

    public function detectMapping(array $headers): array
    {
        $mapping = [];
        foreach (self::HEADER_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                foreach ($headers as $index => $header) {
                    if ($this->normalizeHeader($header) === $alias) {
                        $mapping[$field] = $index;
                        break 2;
                    }
                }
            }
        }
        return $mapping;
    }

    public function fields(): array
    {
        return array_keys(self::HEADER_ALIASES);
    }

    private function findHeader(array $matrix): array
    {
        $bestRow = 0;
        $bestScore = 0;
        $bestHeaders = [];

        foreach (array_slice($matrix, 0, 20, true) as $rowIndex => $row) {
            $headers = array_map(fn ($value) => trim((string) $value), $row);
            $score = count(array_intersect_key($this->detectMapping($headers), array_flip(['uraian', 'kode_klasifikasi', 'kurun_waktu'])));
            if ($score > $bestScore || ($score === 1 && $bestHeaders === [])) {
                $bestRow = $rowIndex;
                $bestScore = $score;
                $bestHeaders = $headers;
            }
        }

        return [$bestRow, $bestHeaders];
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }
}