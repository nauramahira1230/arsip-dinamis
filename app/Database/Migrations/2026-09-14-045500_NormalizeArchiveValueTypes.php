<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeArchiveValueTypes extends Migration
{
    public function up()
    {
        foreach (['jumlah_lembar', 'kurun_waktu', 'no_sampul'] as $column) {
            $this->db->query(sprintf(
                'ALTER TABLE digitized_archives ALTER COLUMN "%s" TYPE text USING "%s"::text',
                $column,
                $column
            ));
        }
    }

    public function down()
    {
    }
}
