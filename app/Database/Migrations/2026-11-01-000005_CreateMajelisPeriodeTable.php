<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMajelisPeriodeTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('majelis_periode')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'period_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'start_date' => ['type' => 'DATE', 'null' => true],
            'end_date' => ['type' => 'DATE', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('majelis_periode', true);
    }

    public function down()
    {
        $this->forge->dropTable('majelis_periode', true);
    }
}
