<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeScanPeriodType extends Migration
{
    public function up()
    {
        $this->db->query(
            'ALTER TABLE digitized_archives ALTER COLUMN waktu_scan TYPE text USING waktu_scan::text'
        );
    }

    public function down()
    {
    }
}
