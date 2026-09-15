<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddArchiveTimestamps extends Migration
{
    public function up()
    {
        $this->db->query(
            'ALTER TABLE digitized_archives ADD COLUMN IF NOT EXISTS created_at timestamp NULL'
        );
        $this->db->query(
            'ALTER TABLE digitized_archives ADD COLUMN IF NOT EXISTS updated_at timestamp NULL'
        );
    }

    public function down()
    {
    }
}
