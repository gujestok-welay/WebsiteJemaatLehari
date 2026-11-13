<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddScheduleDetails extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('jadwal_ibadah')) {
            throw new RuntimeException('The jadwal_ibadah table must exist before schedule details can be added.');
        }

        if (! $this->db->fieldExists('keterangan', 'jadwal_ibadah')) {
            $this->forge->addColumn('jadwal_ibadah', [
                'keterangan' => ['type' => 'TEXT', 'null' => true],
            ]);
        }
    }

    public function down()
    {
        throw new RuntimeException('This migration is irreversible to protect schedule details.');
    }
}
