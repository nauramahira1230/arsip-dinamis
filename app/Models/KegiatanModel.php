<?php

namespace App\Models;

use CodeIgniter\Model;

class KegiatanModel extends Model
{
    protected $table = 'kegiatan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;
    protected $beforeInsert = ['ensureUuid'];
    protected $allowedFields = [
        'id',
        'nama_kegiatan',
        'tahun',
        'jenis_naskah',
        'target_lembar',
    ];
    protected $useTimestamps = false;

    protected function ensureUuid(array $data): array
    {
        if (!empty($data['data']['id'])) {
            return $data;
        }

        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $data['data']['id'] = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
        return $data;
    }

    public function getForImport(): array
    {
        return $this->orderBy('tahun', 'DESC')
            ->orderBy('nama_kegiatan', 'ASC')
            ->findAll();
    }
}