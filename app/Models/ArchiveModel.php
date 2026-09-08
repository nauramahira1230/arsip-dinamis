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
        'status_authentication',
        'kegiatan_id'
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

    public function getActivityProgress(): array
    {
        $query = $this->db->query(
            "SELECT k.id, k.nama_kegiatan, k.tahun, k.jenis_naskah, k.target_lembar,
                    COALESCE(SUM(NULLIF(regexp_replace(a.jumlah_lembar::text, '[^0-9]', '', 'g'), '')::numeric), 0) AS realisasi_lembar
             FROM kegiatan k
             LEFT JOIN digitized_archives a ON a.kegiatan_id = k.id
             GROUP BY k.id, k.nama_kegiatan, k.tahun, k.jenis_naskah, k.target_lembar
             ORDER BY k.tahun DESC, k.nama_kegiatan ASC"
        );

        return array_map(static function (array $activity): array {
            $target = (int) $activity['target_lembar'];
            $realisasi = (int) $activity['realisasi_lembar'];
            $activity['realisasi_lembar'] = $realisasi;
            $activity['sisa_target'] = max($target - $realisasi, 0);
            $activity['persentase_capaian'] = $target > 0
                ? min(round(($realisasi / $target) * 100, 2), 100)
                : 0;

            return $activity;
        }, $query->getResultArray());
    }
}