<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStatistikAgregatTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('statistik_agregat')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'metric_label' => ['type' => 'VARCHAR', 'constraint' => 150],
            'metric_value' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'unit_label' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('statistik_agregat', true);
    }

    public function down()
    {
        $this->forge->dropTable('statistik_agregat', true);
    }
}
