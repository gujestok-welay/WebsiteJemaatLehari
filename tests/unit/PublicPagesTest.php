<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class PublicPagesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

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
            ]);
            $forge->addKey('id', true);
            $forge->addUniqueKey('slug');
            $forge->createTable('berita');
        } else {
            $db->table('berita')->truncate();
        }
    }

    public function testPublicPagesRenderWithPreviewNoticeAndActiveNavigation(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();

        foreach (['/' => 'Beranda', '/profil' => 'Profil', '/berita' => 'Berita', '/jadwal-ibadah' => 'Jadwal Ibadah'] as $path => $label) {
            $result = $this->get($path);
            $result->assertStatus(200);
            $result->assertSee('Pratinjau website informasi Jemaat Leahari.');
            $result->assertSee('Sebagian informasi masih menunggu konfirmasi resmi.');
            $result->assertDontSee('Seluruh konten masih berupa contoh.');
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
        $this->createScheduleTable();
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

    public function testEmptySchedulePageShowsDatabaseEmptyStateWithoutSampleFallback(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createScheduleTable();

        $result = $this->get('/jadwal-ibadah');
        $result->assertStatus(200);
        $result->assertSee('Belum ada jadwal ibadah yang tersedia saat ini.');
        $result->assertDontSee('Ibadah Keluarga');
        $result->assertDontSee('Lokasi contoh');
    }

    private function createScheduleTable(): void
    {
        $db = db_connect('tests');
        $forge = \Config\Database::forge('tests');
        $db->resetDataCache();
        if ($db->tableExists('jadwal_ibadah')) {
            $forge->dropTable('jadwal_ibadah');
        }
        $db->resetDataCache();
        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'jenis_ibadah' => ['type' => 'VARCHAR', 'constraint' => 120],
            'tanggal' => ['type' => 'DATE'],
            'waktu' => ['type' => 'TIME'],
            'lokasi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'pelayan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_cancelled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('jadwal_ibadah');
        $db->resetDataCache();
    }

    public function testProfileDoesNotInventOfficialPeopleOrContactDetails(): void
    {
        $result = $this->get('/profil');
        $result->assertSee('Susunan Majelis aktif belum tersedia');
        $result->assertSee('Menunggu konfirmasi resmi.');
        $result->assertSee('Belum tersedia. Tidak ada nomor pribadi yang ditampilkan.');
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
