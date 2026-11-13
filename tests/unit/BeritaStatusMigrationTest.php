<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BeritaStatusMigrationTest extends CIUnitTestCase
{
    private string $originalDatabaseGroup;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for the in-memory migration test.');
        }

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;
        $database->defaultGroup = 'tests';

        $db = db_connect('tests');
        $forge = \Config\Database::forge('tests');
        $db->resetDataCache();
        if ($db->tableExists('berita')) {
            $forge->dropTable('berita');
        }
        $db->resetDataCache();

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'content' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey('slug');
        $forge->createTable('berita');
        $db->resetDataCache();
    }

    protected function tearDown(): void
    {
        config('Database')->defaultGroup = $this->originalDatabaseGroup;

        parent::tearDown();
    }

    public function testMigrationMapsSoftDeletedNewsBeforeDroppingTheColumn(): void
    {
        $db = db_connect('tests');
        foreach ([
            ['Draft', 'draft-diarsipkan', '2026-11-04 09:00:00'],
            ['Terbit', 'terbit-diarsipkan', '2026-11-04 10:00:00'],
            ['Terbit', 'berita-aktif', null],
        ] as [$status, $slug, $deletedAt]) {
            $db->table('berita')->insert([
                'title' => $slug,
                'slug' => $slug,
                'summary' => 'Ringkasan',
                'content' => 'Isi berita',
                'status' => $status,
                'deleted_at' => $deletedAt,
            ]);
        }

        $this->assertSame(2, $db->table('berita')->where('deleted_at !=', null)->countAllResults());

        $migrationClass = 'App\\Database\\Migrations\\ConvertBeritaSoftDeletesToStatus';
        require_once APPPATH . 'Database/Migrations/2026-11-04-000001_ConvertBeritaSoftDeletesToStatus.php';
        $this->assertTrue(class_exists($migrationClass), 'A forward migration must convert legacy deleted news before dropping deleted_at.');

        $migration = new $migrationClass(\Config\Database::forge('tests'));
        $migration->up();

        $rows = $db->table('berita')->orderBy('id')->get()->getResultArray();
        $this->assertCount(3, $rows);
        $this->assertFalse($db->fieldExists('deleted_at', 'berita'));
        $this->assertSame(['Arsip', 'Arsip', 'Terbit'], array_column($rows, 'status'));
        $this->assertSame('Arsip', $rows[0]['status']);
        $this->assertSame('Draft', $rows[0]['archived_from_status']);
        $this->assertSame('Arsip', $rows[1]['status']);
        $this->assertSame('Terbit', $rows[1]['archived_from_status']);
        $this->assertSame('Terbit', $rows[2]['status']);
        $this->assertNull($rows[2]['archived_from_status']);
    }
}
