<?php

use App\Models\BeritaModel;
use CodeIgniter\Test\CIUnitTestCase;

final class BeritaModelArchiveTest extends CIUnitTestCase
{
    private string $originalDatabaseGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;

        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for the in-memory news database test.');
        }

        $database->defaultGroup = 'tests';
        $this->assertSame('tests', $database->defaultGroup);
        $this->assertSame('SQLite3', $database->tests['DBDriver']);
        $this->assertSame(':memory:', $database->tests['database']);

        $db = db_connect('tests');
        if (! $db->tableExists('berita')) {
            $forge = \Config\Database::forge('tests');
            $forge->addField([
                'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
                'summary' => ['type' => 'TEXT', 'null' => true],
                'content' => ['type' => 'LONGTEXT'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 20],
                'cover_image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
                'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
                'archived_from_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id', true);
            $forge->addUniqueKey('slug');
            $forge->createTable('berita');
        } else {
            $db->table('berita')->truncate();
        }
    }

    protected function tearDown(): void
    {
        config('Database')->defaultGroup = $this->originalDatabaseGroup;

        parent::tearDown();
    }

    public function testStatusIsTheOnlyArchiveMechanismAndPublicQueryOnlyReturnsPublishedNews(): void
    {
        $db = db_connect('tests');
        $db->table('berita')->insert([
            'title' => 'Berita draft',
            'slug' => 'berita-draft',
            'summary' => 'Ringkasan draft',
            'content' => 'Isi draft',
            'status' => 'Draft',
            'created_at' => '2026-11-04 09:00:00',
        ]);
        $db->table('berita')->insert([
            'title' => 'Berita terbit',
            'slug' => 'berita-terbit',
            'summary' => 'Ringkasan terbit',
            'content' => 'Isi terbit',
            'status' => 'Terbit',
            'created_at' => '2026-11-04 10:00:00',
        ]);
        $db->table('berita')->insert([
            'title' => 'Berita diarsipkan',
            'slug' => 'berita-diarsipkan',
            'summary' => 'Ringkasan arsip',
            'content' => 'Isi arsip',
            'status' => 'Arsip',
            'archived_from_status' => 'Terbit',
            'created_at' => '2026-11-04 11:00:00',
        ]);
        $model = new BeritaModel();

        $this->assertFalse($this->getPrivateProperty($model, 'useSoftDeletes'));
        $this->assertFalse($db->fieldExists('deleted_at', 'berita'));
        $this->assertSame(['Arsip'], array_column($model->findByTerm(null, 'Arsip'), 'status'));
        $this->assertSame(['Terbit'], array_column($model->findPublishedByTerm(), 'status'));
        $this->assertSame('Terbit', $model->findByTerm('terbit')[0]['status']);
        $this->assertSame(3, $db->table('berita')->countAllResults());
    }

    public function testAdminLookupDistinguishesFoundFromNotFound(): void
    {
        $db = db_connect('tests');
        $db->table('berita')->insert([
            'title' => 'Berita tersedia',
            'slug' => 'berita-tersedia',
            'summary' => 'Ringkasan',
            'content' => 'Isi berita',
            'status' => 'Draft',
        ]);
        $id = (int) $db->insertID();
        $model = new BeritaModel();

        $found = $this->findForAdmin($model, $id);
        $missing = $this->findForAdmin($model, $id + 100);

        $this->assertSame('found', $found['status']);
        $this->assertSame($id, (int) $found['record']['id']);
        $this->assertSame('not_found', $missing['status']);
        $this->assertNull($missing['record']);
    }

    public function testUpdatingExistingNewsWithUnchangedValuesRemainsSuccessful(): void
    {
        $db = db_connect('tests');
        $db->table('berita')->insert([
            'title' => 'Berita tersedia',
            'slug' => 'berita-tersedia',
            'summary' => 'Ringkasan',
            'content' => 'Isi berita',
            'status' => 'Draft',
        ]);
        $id = (int) $db->insertID();
        $model = new BeritaModel();
        $this->setPrivateProperty($model, 'useTimestamps', false);

        $result = $model->update($id, [
            'id' => $id,
            'title' => 'Berita tersedia',
            'slug' => 'berita-tersedia',
            'summary' => 'Ringkasan',
            'content' => 'Isi berita',
            'status' => 'Draft',
        ]);

        $this->assertTrue($result);
        $this->assertSame('Berita tersedia', $db->table('berita')->where('id', $id)->get()->getRowArray()['title']);
    }

    public function testUpdateOfNewsDeletedAfterLookupIsReportedAsFailure(): void
    {
        $db = db_connect('tests');
        $db->table('berita')->insert([
            'title' => 'Berita tersedia',
            'slug' => 'berita-tersedia',
            'summary' => 'Ringkasan',
            'content' => 'Isi berita',
            'status' => 'Draft',
        ]);
        $id = (int) $db->insertID();
        $model = new BeritaModel();
        $this->assertSame('found', $this->findForAdmin($model, $id)['status']);

        $db->table('berita')->where('id', $id)->delete();
        $result = $model->update($id, [
            'id' => $id,
            'title' => 'Berita tersedia',
            'slug' => 'berita-tersedia',
            'summary' => 'Ringkasan',
            'content' => 'Isi berita',
            'status' => 'Draft',
        ]);

        $this->assertFalse($result);
    }

    public function testAdminLookupReportsDatabaseExceptionAsError(): void
    {
        $model = new BeritaModel();
        $model->setTable('missing_news_lookup_table');

        $result = $this->findForAdmin($model, 1);

        $this->assertSame('error', $result['status']);
        $this->assertNull($result['record']);
    }

    public function testAdminLookupReportsFalseQueryResultAsErrorWhenDebugIsDisabled(): void
    {
        $db = db_connect('tests');
        $wasDebugEnabled = $this->getPrivateProperty($db, 'DBDebug');
        $this->setPrivateProperty($db, 'DBDebug', false);
        $model = new BeritaModel();
        $model->setTable('missing_news_lookup_table');

        try {
            $result = $this->findForAdmin($model, 1);
        } finally {
            $this->setPrivateProperty($db, 'DBDebug', $wasDebugEnabled);
        }

        $this->assertSame('error', $result['status']);
        $this->assertNull($result['record']);
    }

    private function findForAdmin(BeritaModel $model, int $id): array
    {
        $this->assertTrue(
            method_exists($model, 'findForAdmin'),
            'BeritaModel must expose the dedicated admin lookup helper.',
        );

        return $model->findForAdmin($id);
    }
}
