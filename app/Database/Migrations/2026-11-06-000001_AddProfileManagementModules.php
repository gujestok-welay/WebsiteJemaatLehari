<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddProfileManagementModules extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('profil_jemaat')) {
            return;
        }

        $columns = [];
        foreach ([
            'territory_content' => ['type' => 'TEXT', 'null' => true],
            'census_content' => ['type' => 'TEXT', 'null' => true],
            'service_map_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'service_map_title' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'service_map_alt' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ] as $field => $definition) {
            if (! $this->db->fieldExists($field, 'profil_jemaat')) {
                $columns[$field] = $definition;
            }
        }

        if ($columns !== []) {
            $this->forge->addColumn('profil_jemaat', $columns);
        }
    }

    public function down()
    {
        throw new RuntimeException('This migration is irreversible to protect profile module data.');
    }
}
