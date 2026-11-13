<?php

use CodeIgniter\Test\CIUnitTestCase;

final class ExternalNewsMigrationTest extends CIUnitTestCase
{
    private string $originalDatabaseGroup;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for the in-memory external-news migration test.');
        }

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;
        $database->defaultGroup = 'tests';

        $db = db_connect('tests');
        $forge = \Config\Database::forge('tests');
        if ($db->tableExists('berita')) {
            $forge->dropTable('berita');
        }

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'content' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
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

    public function testMigrationAddsAttributionFieldsAndPreservesExistingNews(): void
    {
        $db = db_connect('tests');
        $db->table('berita')->insert([
            'title' => 'Berita lama',
            'slug' => 'berita-lama',
            'summary' => 'Ringkasan lama',
            'content' => 'Isi berita lama',
            'status' => 'Terbit',
        ]);

        require_once APPPATH . 'Database/Migrations/2026-11-12-000001_AddExternalNewsAttribution.php';
        $migration = new App\Database\Migrations\AddExternalNewsAttribution(\Config\Database::forge('tests'));
        $migration->up();
        $db->resetDataCache();

        $this->assertTrue($db->fieldExists('article_type', 'berita'));
        $this->assertTrue($db->fieldExists('source_url', 'berita'));
        $this->assertTrue($db->fieldExists('source_name', 'berita'));
        $this->assertSame('internal', $db->table('berita')->get()->getRowArray()['article_type']);
        $this->assertSame(1, $db->table('berita')->countAllResults());
    }
}
