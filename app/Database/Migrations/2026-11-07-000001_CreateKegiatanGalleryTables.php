<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateKegiatanGalleryTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'event_date' => ['type' => 'DATE', 'null' => true],
            'video_url' => ['type' => 'VARCHAR', 'constraint' => 2048, 'null' => true],
            'poster_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'poster_type' => ['type' => 'ENUM', 'constraint' => ['poster', 'flayer', 'spanduk'], 'default' => 'poster'],
            'status' => ['type' => 'ENUM', 'constraint' => ['Draft', 'Terbit', 'Arsip']],
            'show_on_home' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'archived_from_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey(['status', 'show_on_home', 'event_date']);
        $this->forge->createTable('kegiatan');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'kegiatan_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'media_type' => ['type' => 'ENUM', 'constraint' => ['foto', 'poster', 'flayer', 'spanduk']],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kegiatan_id');
        $this->forge->addForeignKey('kegiatan_id', 'kegiatan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kegiatan_media');
    }

    public function down()
    {
        throw new RuntimeException('This migration is irreversible to protect gallery content.');
    }
}
