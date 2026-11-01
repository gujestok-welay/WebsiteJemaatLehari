<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMajelisAnggotaTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('majelis_anggota')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'periode_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'position' => ['type' => 'VARCHAR', 'constraint' => 150],
            'photo_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('periode_id');
        $this->forge->createTable('majelis_anggota', true);
    }

    public function down()
    {
        $this->forge->dropTable('majelis_anggota', true);
    }
}
