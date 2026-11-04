<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class PublicPagesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $originalDatabaseGroup;
    private array $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;
        $this->originalDefaultConnection = $database->default;
        $database->defaultGroup = 'default';
        $database->default['database'] = '';
    }

    protected function tearDown(): void
    {
        $database = config('Database');
        $database->defaultGroup = $this->originalDatabaseGroup;
        $database->default = $this->originalDefaultConnection;

        parent::tearDown();
    }

    private function createNewsTable(): void
    {
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
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id', true);
            $forge->addUniqueKey('slug');
            $forge->createTable('berita');
        } else {
            $db->table('berita')->truncate();
        }
    }

    public function testPublicPagesRenderWithSampleNoticeAndActiveNavigation(): void
    {
        foreach (['/' => 'Beranda', '/profil' => 'Profil', '/berita' => 'Berita', '/jadwal-ibadah' => 'Jadwal Ibadah'] as $path => $label) {
            $result = $this->get($path);
            $result->assertStatus(200);
            $result->assertSee('Pratinjau dengan konten dummy.');
            $this->assertStringContainsString('aria-current="page"', $result->getBody());
            $result->assertSee($label . ' | Jemaat Leahari');
            $this->assertStringContainsString('id="konten"', $result->getBody());
        }
    }

    public function testPublicNewsUsesPublishedDatabaseContentAndReflectsEdits(): void
    {
        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for the in-memory news database test.');
        }

        $database = config('Database');
        $database->defaultGroup = 'tests';
        $this->createNewsTable();
        $db = db_connect('tests');

        $db->table('berita')->insertBatch([
            [
                'title' => 'Berita yang diterbitkan',
                'slug' => 'berita-terbit',
                'summary' => 'Ringkasan publik',
                'content' => 'Isi publik',
                'status' => 'Terbit',
                'created_at' => '2026-11-04 09:00:00',
            ],
            [
                'title' => 'Berita draft rahasia',
                'slug' => 'berita-draft',
                'summary' => 'Ringkasan draft',
                'content' => 'Isi draft',
                'status' => 'Draft',
                'created_at' => '2026-11-04 10:00:00',
            ],
            [
                'title' => 'Berita arsip',
                'slug' => 'berita-arsip',
                'summary' => 'Ringkasan arsip',
                'content' => 'Isi arsip',
                'status' => 'Arsip',
                'created_at' => '2026-11-04 11:00:00',
            ],
        ]);

        $home = $this->get('/');
        $home->assertStatus(200);
        $home->assertSee('Berita yang diterbitkan');
        $this->assertStringNotContainsString('Berita draft rahasia', $home->getBody());
        $this->assertStringNotContainsString('Berita arsip', $home->getBody());

        $archive = $this->get('/berita');
        $archive->assertStatus(200);
        $archive->assertSee('Berita yang diterbitkan');
        $this->assertStringNotContainsString('Berita draft rahasia', $archive->getBody());
        $this->assertStringNotContainsString('Berita arsip', $archive->getBody());

        $db->table('berita')
            ->where('slug', 'berita-terbit')
            ->update(['title' => 'Judul berita diperbarui']);

        $this->get('/')->assertSee('Judul berita diperbarui');
        $this->get('/berita')->assertSee('Judul berita diperbarui');
    }

    public function testScheduleHasCompleteFallbackAndExplicitTimeZone(): void
    {
        $result = $this->get('/jadwal-ibadah');
        $result->assertStatus(200);
        $result->assertSee('Ibadah Minggu');
        $result->assertSee('Ibadah Keluarga');
        $result->assertSee('Waktu Indonesia Timur (WIT)');
        $result->assertSee('Lokasi contoh B');
        $result->assertSee('2026-11-01');
    }

    public function testProfileDoesNotInventOfficialPeopleOrContactDetails(): void
    {
        $result = $this->get('/profil');
        $result->assertSee('Susunan Majelis aktif belum tersedia');
        $result->assertSee('Menunggu konfirmasi resmi.');
    }

    public function testProfileAgeChartShowsBothGenderCountsForEveryRange(): void
    {
        $result = $this->get('/profil');
        $result->assertStatus(200);

        foreach ([
            ['0-3', 12, 7, 19],
            ['4-6', 11, 6, 17],
            ['7-9', 8, 4, 12],
            ['10-12', 13, 17, 30],
            ['13-15', 12, 19, 31],
            ['16-45', 171, 143, 314],
            ['46-59', 61, 64, 125],
            ['60-85', 35, 39, 74],
            ['> 86', 2, 0, 2],
        ] as [$ageRange, $men, $women, $total]) {
            $this->assertStringContainsString(
                'aria-label="Jemaat usia ' . htmlspecialchars($ageRange, ENT_QUOTES, 'UTF-8') . ' tahun: ' . $men . ' laki-laki, ' . $women . ' perempuan, total ' . $total . ' orang"',
                $result->getBody()
            );
            $this->assertStringContainsString('Laki-laki: ' . $men, $result->getBody());
            $this->assertStringContainsString('Perempuan: ' . $women, $result->getBody());
        }
    }
}
