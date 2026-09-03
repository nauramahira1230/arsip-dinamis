<?php

namespace App\Models;

use CodeIgniter\Model;

class ArchiveModel extends Model
{
    protected $table            = 'digitized_archives';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // 22 Kolom Baku Sesuai Request
    protected $allowedFields    = [
        'unit_pencipta',
        'unit_pengolah',
        'jenis_naskah',
        'kategori_arsip',
        'nama_berkas',
        'uraian_arsip',
        'jumlah_lembar',
        'kurun_waktu',
        'semula',
        'menjadi',
        'alat_scan',
        'waktu_scan',
        'tingkat_perkembangan',
        'no_sampul',
        'no_item',
        'boks',
        'rak',
        'ro',
        'lokasi',
        'status_authentication'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getDashboardStats()
    {
        return [
            'total_archives'      => $this->countAllResults(),
            'total_active'        => $this->where('kategori_arsip', 'Dinamis Aktif')->countAllResults(),
            'total_inactive'      => $this->where('kategori_arsip', 'Dinamis Inaktif')->countAllResults(),
            'total_authenticated' => $this->where('status_authentication', 'Terautentikasi')->countAllResults(),
        ];
    }
}