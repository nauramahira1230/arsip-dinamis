<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeArchivePeriodType extends Migration
{
    public function up()
    {
        $this->db->query(
            'ALTER TABLE digitized_archives ALTER COLUMN kurun_waktu TYPE text USING kurun_waktu::text'
        );
    }

    public function down()
    {
    }
}
