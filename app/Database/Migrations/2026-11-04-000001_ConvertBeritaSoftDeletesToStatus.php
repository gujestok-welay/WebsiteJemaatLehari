<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class ConvertBeritaSoftDeletesToStatus extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('berita')) {
            return;
        }

        if (! $this->db->fieldExists('archived_from_status', 'berita')) {
            $this->forge->addColumn('berita', [
                'archived_from_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            ]);
        }

        if (! $this->db->fieldExists('deleted_at', 'berita')) {
            return;
        }

        $this->db->table('berita')
            ->set(
                'archived_from_status',
                "CASE
                    WHEN status IN ('Draft', 'Terbit') THEN status
                    WHEN archived_from_status IN ('Draft', 'Terbit') THEN archived_from_status
                    ELSE 'Draft'
                END",
                false,
            )
            ->set('status', 'Arsip')
            ->where('deleted_at !=', null)
            ->update();

        $unmappedDeletedNews = $this->db->table('berita')
            ->where('deleted_at !=', null)
            ->where('status !=', 'Arsip')
            ->countAllResults();
        if ($unmappedDeletedNews > 0) {
            throw new RuntimeException('Berita lama belum seluruhnya dipetakan ke status Arsip; kolom deleted_at dipertahankan.');
        }

        $this->forge->dropColumn('berita', 'deleted_at');
    }

    public function down()
    {
        if (! $this->db->tableExists('berita') || $this->db->fieldExists('deleted_at', 'berita')) {
            return;
        }

        $this->forge->addColumn('berita', [
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->db->table('berita')
            ->set(
                'status',
                "CASE
                    WHEN archived_from_status IN ('Draft', 'Terbit') THEN archived_from_status
                    ELSE 'Draft'
                END",
                false,
            )
            ->set('deleted_at', date('Y-m-d H:i:s'))
            ->where('status', 'Arsip')
            ->update();
    }
}
