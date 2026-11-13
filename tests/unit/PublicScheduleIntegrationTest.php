<?php

use App\Controllers\AdminJadwal;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class PublicScheduleIntegrationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $originalDatabaseGroup;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite is required for isolated public schedule tests.');
        }

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;
        $database->defaultGroup = 'tests';

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
            'keterangan' => ['type' => 'TEXT', 'null' => true],
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
            'archived_from_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'cover_image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey('slug');
        $forge->createTable('berita');
        $db->resetDataCache();
        session()->set('admin_id', 7);
        session()->remove(['success', 'error']);
    }

    protected function tearDown(): void
    {
        config('Database')->defaultGroup = $this->originalDatabaseGroup;
        session()->remove(['admin_id', 'success', 'error']);

        parent::tearDown();
    }

    private function addSchedule(array $overrides = []): int
    {
        $data = array_merge([
            'jenis_ibadah' => 'Ibadah Minggu',
            'tanggal' => '2026-10-25',
            'waktu' => '09:30:00',
            'lokasi' => 'Gedung Gereja',
            'pelayan' => null,
            'is_cancelled' => 0,
            'created_by' => 7,
            'updated_by' => 7,
            'created_at' => '2026-10-01 09:00:00',
            'updated_at' => '2026-10-01 09:00:00',
        ], $overrides);
        $db = db_connect('tests');
        $db->table('jadwal_ibadah')->insert($data);

        return (int) $db->insertID();
    }

    private function adminController(array $post = []): AdminJadwal
    {
        $request = service('request');
        if ($request instanceof IncomingRequest) {
            $request->setGlobal('post', $post);
        }
        $controller = new AdminJadwal();
        $controller->initController($request, service('response'), service('logger'));

        return $controller;
    }

    private function validPost(array $overrides = []): array
    {
        return array_merge([
            'jenis_ibadah' => 'Ibadah Aktif Baru',
            'tanggal' => '2026-10-25',
            'waktu' => '09:30',
            'lokasi' => 'Gedung Gereja',
            'pelayan' => '',
            'keterangan' => '',
            'is_cancelled' => '0',
            'return_to' => 'admin/jadwal',
        ], $overrides);
    }

    public function testNewActiveAdminScheduleIsImmediatelyVisiblePublicly(): void
    {
        $response = $this->adminController($this->validPost())->save();
        $public = $this->get('/jadwal-ibadah');

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $public->assertSee('Ibadah Aktif Baru');
    }

    public function testActiveScheduleIsRenderedFromDatabaseWithDateTimeAndOptionalMinister(): void
    {
        $this->addSchedule(['pelayan' => 'Pdt. Contoh']);

        $response = $this->get('/jadwal-ibadah');

        $response->assertStatus(200);
        $response->assertSee('Ibadah Minggu');
        $response->assertSee('25 Oktober 2026');
        $response->assertSee('item-ibadah" data-hari="minggu"');
        $response->assertSee('badge-hari">Minggu</span>');
        $response->assertSee('09.30 WIT');
        $response->assertSee('Gedung Gereja');
        $response->assertSee('Pdt. Contoh');
        $response->assertDontSee('Lokasi contoh A');
    }

    public function testScheduleDetailsAreRenderedOnThePublicPage(): void
    {
        $this->addSchedule([
            'keterangan' => "Bawa Alkitab.\nHadir 15 menit lebih awal.",
        ]);

        $response = $this->get('/jadwal-ibadah');

        $response->assertStatus(200);
        $this->assertStringContainsString(
            "Bawa Alkitab.<br>\nHadir 15 menit lebih awal.",
            $response->getBody(),
        );
    }

    public function testPublicScheduleCardsDoNotRenderDecorativeIconContainers(): void
    {
        $this->addSchedule();

        $response = $this->get('/jadwal-ibadah');

        $response->assertSee('Ibadah Minggu');
        $response->assertSee('class="card jadwal-card');
        $response->assertDontSee('card-ikon');
    }

    public function testHomepageUsesDatabaseScheduleInsteadOfScheduleExamples(): void
    {
        $upcomingDate = (new DateTimeImmutable('tomorrow', new DateTimeZone('Asia/Jayapura')))->format('Y-m-d');
        $this->addSchedule([
            'jenis_ibadah' => 'Jadwal Beranda',
            'tanggal' => $upcomingDate,
            'lokasi' => 'Ruang Jemaat',
        ]);

        $response = $this->get('/');

        $response->assertSee('Jadwal Beranda');
        $response->assertSee('Ruang Jemaat');
        $response->assertDontSee('Lokasi contoh A');
    }

    public function testCancelledAndArchivedSchedulesAreNotRenderedPublicly(): void
    {
        $this->addSchedule(['jenis_ibadah' => 'Ibadah Batal', 'is_cancelled' => 1]);
        $this->addSchedule(['jenis_ibadah' => 'Ibadah Arsip', 'deleted_at' => '2026-10-02 10:00:00']);

        $response = $this->get('/jadwal-ibadah');

        $response->assertDontSee('Ibadah Batal');
        $response->assertDontSee('Ibadah Arsip');
    }

    public function testPublicListReflectsEditsAndCancellationActivation(): void
    {
        $id = $this->addSchedule();
        $this->get('/jadwal-ibadah')->assertSee('Ibadah Minggu');

        $this->adminController($this->validPost([
            'id' => (string) $id,
            'jenis_ibadah' => 'Ibadah Keluarga',
            'waktu' => '18:15',
        ]))->save();
        $edited = $this->get('/jadwal-ibadah');
        $edited->assertSee('Ibadah Keluarga');
        $edited->assertSee('18.15 WIT');
        $edited->assertDontSee('Ibadah Minggu');

        $this->adminController()->cancel($id);
        $this->get('/jadwal-ibadah')->assertDontSee('Ibadah Keluarga');

        $this->adminController()->reactivate($id);
        $this->get('/jadwal-ibadah')->assertSee('Ibadah Keluarga');
    }

    public function testRestoringArchivedActiveScheduleMakesItPublicAgain(): void
    {
        $id = $this->addSchedule();
        $this->adminController()->archive($id);
        $this->get('/jadwal-ibadah')->assertDontSee('Ibadah Minggu');

        $this->adminController()->restore($id);
        $this->get('/jadwal-ibadah')->assertSee('Ibadah Minggu');
    }

    public function testRestoringArchivedCancelledScheduleKeepsItHidden(): void
    {
        $id = $this->addSchedule(['is_cancelled' => 1]);
        $this->adminController()->archive($id);
        $this->adminController()->restore($id);

        $this->get('/jadwal-ibadah')->assertDontSee('Ibadah Minggu');
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();
        $this->assertSame('1', (string) $row['is_cancelled']);
        $this->assertNull($row['deleted_at']);
    }

    public function testEmptyPublicScheduleShowsExplicitEmptyStateWithoutSampleFallback(): void
    {
        $response = $this->get('/jadwal-ibadah');

        $response->assertSee('Belum ada jadwal ibadah yang tersedia saat ini.');
        $response->assertDontSee('Ibadah Keluarga');
        $response->assertDontSee('Lokasi contoh');
    }

    public function testHomepageShowsEmptyScheduleStateWithoutExamples(): void
    {
        $response = $this->get('/');

        $response->assertSee('Belum ada jadwal ibadah mendatang yang tersedia saat ini.');
        $response->assertDontSee('Lokasi contoh A');
    }

    public function testPublicSchedulesAreSortedByDateThenTime(): void
    {
        $this->addSchedule(['jenis_ibadah' => 'Jadwal Ketiga', 'tanggal' => '2026-10-26', 'waktu' => '09:00:00']);
        $this->addSchedule(['jenis_ibadah' => 'Jadwal Kedua', 'tanggal' => '2026-10-25', 'waktu' => '10:00:00']);
        $this->addSchedule(['jenis_ibadah' => 'Jadwal Pertama', 'tanggal' => '2026-10-25', 'waktu' => '08:00:00']);

        $body = $this->get('/jadwal-ibadah')->getBody();

        $this->assertLessThan(
            strpos($body, 'Jadwal Kedua'),
            strpos($body, 'Jadwal Pertama'),
            'Schedule cards must be ordered by date and then time.'
        );
        $this->assertLessThan(
            strpos($body, 'Jadwal Ketiga'),
            strpos($body, 'Jadwal Kedua'),
            'Later dates must follow earlier dates.'
        );
    }
}
