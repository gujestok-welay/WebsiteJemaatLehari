<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class PublicGalleryTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $originalDatabaseGroup;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite is required for isolated public gallery tests.');
        }

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;
        $database->defaultGroup = 'tests';

        $db = db_connect('tests');
        $forge = \Config\Database::forge('tests');
        foreach (['kegiatan_media', 'kegiatan', 'berita', 'jadwal_ibadah', 'majelis_anggota', 'majelis_periode', 'profil_jemaat'] as $table) {
            if ($db->tableExists($table)) {
                $forge->dropTable($table);
            }
        }

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'event_date' => ['type' => 'DATE', 'null' => true],
            'video_url' => ['type' => 'VARCHAR', 'constraint' => 2048, 'null' => true],
            'poster_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'poster_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'show_on_home' => ['type' => 'INTEGER', 'default' => 0],
            'archived_from_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey('slug');
        $forge->createTable('kegiatan');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'kegiatan_id' => ['type' => 'INTEGER'],
            'media_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('kegiatan_media');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'content' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'cover_image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('berita');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'jenis_ibadah' => ['type' => 'VARCHAR', 'constraint' => 120],
            'tanggal' => ['type' => 'DATE'],
            'waktu' => ['type' => 'TIME'],
            'lokasi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'pelayan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'is_cancelled' => ['type' => 'INTEGER', 'default' => 0],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('jadwal_ibadah');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'history' => ['type' => 'TEXT', 'null' => true],
            'vision_mission' => ['type' => 'TEXT', 'null' => true],
            'address' => ['type' => 'TEXT', 'null' => true],
            'maps_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'contact_info' => ['type' => 'TEXT', 'null' => true],
            'page_content' => ['type' => 'TEXT', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('profil_jemaat');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'is_active' => ['type' => 'INTEGER', 'default' => 0],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('majelis_periode');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'periode_id' => ['type' => 'INTEGER'],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'position' => ['type' => 'VARCHAR', 'constraint' => 150],
            'photo_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('majelis_anggota');
    }

    protected function tearDown(): void
    {
        config('Database')->defaultGroup = $this->originalDatabaseGroup;
        parent::tearDown();
    }

    private function insertActivity(string $title, string $slug, string $status, bool $showOnHome): void
    {
        db_connect('tests')->table('kegiatan')->insert([
            'title' => $title,
            'slug' => $slug,
            'description' => 'Keterangan dokumentasi.',
            'event_date' => '2026-10-25',
            'video_url' => 'https://youtu.be/example',
            'poster_path' => 'uploads/galeri/' . $slug . '.jpg',
            'poster_type' => 'flayer',
            'status' => $status,
            'show_on_home' => $showOnHome ? 1 : 0,
            'created_at' => '2026-10-26 09:00:00',
        ]);
    }

    public function testPublishedActivitiesHavePublicDetailsWhileDraftsRemainHidden(): void
    {
        $this->insertActivity('Perayaan Pemuda', 'perayaan-pemuda', 'Terbit', false);
        $this->insertActivity('Catatan internal', 'catatan-internal', 'Draft', false);

        $gallery = $this->get('/galeri');

        $gallery->assertStatus(200);
        $gallery->assertSee('Perayaan Pemuda');
        $gallery->assertDontSee('Catatan internal');

        $detail = $this->get('/galeri/perayaan-pemuda');

        $detail->assertStatus(200);
        $detail->assertSee('Keterangan dokumentasi.');
        $detail->assertSee('https://youtu.be/example');
        $this->get('/galeri/catatan-internal')->assertStatus(404);
    }

    public function testHomepageReplacesProfilePreviewWithSelectedPublishedGalleryActivities(): void
    {
        $this->insertActivity('Kegiatan pilihan', 'kegiatan-pilihan', 'Terbit', true);
        $this->insertActivity('Kegiatan galeri lainnya', 'kegiatan-lainnya', 'Terbit', false);
        $this->insertActivity('Kegiatan draft', 'kegiatan-draft', 'Draft', true);

        $home = $this->get('/');

        $home->assertStatus(200);
        $home->assertSee('Galeri kegiatan');
        $home->assertSee('Kegiatan pilihan');
        $home->assertDontSee('Kegiatan galeri lainnya');
        $home->assertDontSee('Kegiatan draft');
        $home->assertDontSee('Mengenal jemaat');
        $home->assertSee('index.php/profil');
    }

    public function testHomepageFeaturedPostersUseTheSharedGalleryImageFrame(): void
    {
        $this->insertActivity('Kegiatan pilihan', 'kegiatan-pilihan', 'Terbit', true);

        $home = $this->get('/');

        $home->assertStatus(200);
        $body = $home->getBody();
        $this->assertStringContainsString('class="gallery-card-image-wrap"', $body);
        $this->assertStringContainsString('class="gallery-card-image-backdrop"', $body);
        $this->assertStringContainsString('class="home-gallery-image gallery-card-image"', $body);
    }

    public function testGalleryAdministrationRequiresAnAuthenticatedAdmin(): void
    {
        $response = $this->get('/admin/galeri');

        $response->assertRedirectTo('/admin/login');
    }

    public function testArchivingAnActivityHidesItWithoutDeletingItsDocumentation(): void
    {
        $this->insertActivity('Persekutuan Remaja', 'persekutuan-remaja', 'Terbit', true);
        $activityId = (int) db_connect('tests')->table('kegiatan')->where('slug', 'persekutuan-remaja')->get()->getRowArray()['id'];
        db_connect('tests')->table('kegiatan_media')->insert([
            'kegiatan_id' => $activityId,
            'media_type' => 'foto',
            'file_path' => 'uploads/galeri/dokumentasi.jpg',
        ]);

        $saved = (new \App\Models\KegiatanModel())->update($activityId, [
            'status' => 'Arsip',
            'archived_from_status' => 'Terbit',
        ]);

        $this->assertTrue($saved);
        $this->assertSame('Arsip', db_connect('tests')->table('kegiatan')->where('id', $activityId)->get()->getRowArray()['status']);
        $this->assertSame(1, db_connect('tests')->table('kegiatan_media')->where('kegiatan_id', $activityId)->countAllResults());
    }

    public function testUpdatingAnActivityCanKeepItsExistingSlug(): void
    {
        $this->insertActivity('Persekutuan Remaja', 'persekutuan-remaja', 'Draft', false);
        $activityId = (int) db_connect('tests')->table('kegiatan')->where('slug', 'persekutuan-remaja')->get()->getRowArray()['id'];

        $saved = (new \App\Models\KegiatanModel())->update($activityId, [
            'title' => 'Persekutuan Remaja',
            'slug' => 'persekutuan-remaja',
            'description' => 'Keterangan dokumentasi.',
            'event_date' => '2026-10-25',
            'video_url' => 'https://youtu.be/example',
            'poster_path' => 'uploads/galeri/persekutuan-remaja.jpg',
            'poster_type' => 'flayer',
            'status' => 'Draft',
            'show_on_home' => 0,
        ]);

        $this->assertTrue($saved);
    }
}
