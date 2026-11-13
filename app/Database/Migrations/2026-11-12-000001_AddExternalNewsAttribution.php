<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddExternalNewsAttribution extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('berita')) {
            return;
        }

        $columns = [];
        if (! $this->db->fieldExists('article_type', 'berita')) {
            $columns['article_type'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'internal',
            ];
        }
        if (! $this->db->fieldExists('source_url', 'berita')) {
            $columns['source_url'] = [
                'type' => 'VARCHAR',
                'constraint' => 2048,
                'null' => true,
            ];
        }
        if (! $this->db->fieldExists('source_name', 'berita')) {
            $columns['source_name'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('berita', $columns);
        }
    }

    public function down()
    {
        throw new RuntimeException('This migration is irreversible to protect external news attribution data.');
    }
}
