<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProfilJemaatTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('profil_jemaat')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'history' => ['type' => 'TEXT', 'null' => true],
            'vision_mission' => ['type' => 'TEXT', 'null' => true],
            'address' => ['type' => 'TEXT', 'null' => true],
            'maps_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'contact_info' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('profil_jemaat', true);
    }

    public function down()
    {
        $this->forge->dropTable('profil_jemaat', true);
    }
}
