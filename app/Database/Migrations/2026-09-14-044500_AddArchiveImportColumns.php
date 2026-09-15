<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddArchiveImportColumns extends Migration
{
    public function up()
    {
        $columns = [
            'no' => 'text',
            'kode_klasifikasi' => 'text',
            'unit_pencipta' => 'text',
            'unit_pengolah' => 'text',
            'jenis_naskah' => 'text',
            'kategori_arsip' => 'text',
            'nama_berkas' => 'text',
            'uraian_arsip' => 'text',
            'jumlah_lembar' => 'text',
            'kurun_waktu' => 'text',
            'semula' => 'text',
            'menjadi' => 'text',
            'alat_scan' => 'text',
            'waktu_scan' => 'text',
            'tingkat_perkembangan' => 'text',
            'no_sampul' => 'text',
            'no_item' => 'text',
            'boks' => 'text',
            'rak' => 'text',
            'ro' => 'text',
            'lokasi' => 'text',
            'status_authentication' => 'text',
            'kegiatan_id' => 'uuid',
        ];

        foreach ($columns as $name => $type) {
            $this->db->query(sprintf(
                'ALTER TABLE digitized_archives ADD COLUMN IF NOT EXISTS "%s" %s',
                $name,
                $type
            ));
        }
    }

    public function down()
    {
    }
}
