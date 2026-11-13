<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\AdminProfil;

final class PublicProfileContentIntegrationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $originalDatabaseGroup;

    private function renderProfileManagementPage(string $module): string
    {
        $titles = [
            'territory' => 'Gambaran Umum Wilayah',
            'census' => 'Jumlah Kepala Keluarga dan Jiwa',
            'worship' => 'Pelayanan Peribadahan',
            'map' => 'Peta Pelayanan',
            'content' => 'Konten Profil Publik',
            'data' => 'Data Jemaat',
            'leadership' => 'Pengelolaan Majelis',
        ];

        return view('admin/profil/manage', [
            'profile' => ['id' => 0, 'history' => '', 'vision_mission' => '', 'address' => '', 'maps_url' => '', 'contact_info' => ''],
            'pageContent' => \App\Libraries\PublicProfileContent::defaults(),
            'editorModule' => $module,
            'editorTitle' => $titles[$module],
            'title' => 'Kelola Profil',
            'name' => 'Admin',
            'partial' => false,
            'periods' => [],
            'selectedPeriod' => 0,
            'members' => [],
            'totalJiwa' => 0,
            'serviceMapPath' => 'assets/images/gereja/Peta Pelayanan.png',
            'serviceMapTitle' => 'Peta Pelayanan',
            'serviceMapAlt' => 'Peta pelayanan Jemaat Leahari',
            'success' => null,
            'error' => null,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        \Config\Services::resetSingle('request');

        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for public profile content tests.');
        }

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;
        $database->defaultGroup = 'tests';

        $db = db_connect('tests');
        $forge = \Config\Database::forge('tests');
        foreach (['profil_jemaat', 'majelis_periode', 'majelis_anggota', 'admin_users'] as $table) {
            if ($db->tableExists($table)) {
                $forge->dropTable($table);
            }
        }

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'history' => ['type' => 'TEXT', 'null' => true],
            'vision_mission' => ['type' => 'TEXT', 'null' => true],
            'address' => ['type' => 'TEXT', 'null' => true],
            'maps_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'contact_info' => ['type' => 'TEXT', 'null' => true],
            'page_content' => ['type' => 'TEXT', 'null' => true],
            'territory_content' => ['type' => 'TEXT', 'null' => true],
            'census_content' => ['type' => 'TEXT', 'null' => true],
            'service_map_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'service_map_title' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'service_map_alt' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('profil_jemaat');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'period_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'start_date' => ['type' => 'DATE'],
            'end_date' => ['type' => 'DATE', 'null' => true],
            'is_active' => ['type' => 'INTEGER', 'default' => 0],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
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
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('majelis_anggota');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'role' => ['type' => 'VARCHAR', 'constraint' => 30],
            'is_active' => ['type' => 'INTEGER', 'default' => 1],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('admin_users');
    }

    protected function tearDown(): void
    {
        config('Database')->defaultGroup = $this->originalDatabaseGroup;
        session()->remove('admin_id');

        parent::tearDown();
    }

    public function testProfilePageRendersSavedHeroCopy(): void
    {
        db_connect('tests')->table('profil_jemaat')->insert([
            'history' => 'Sejarah jemaat yang telah diverifikasi.',
            'vision_mission' => 'Visi dan misi jemaat yang telah diverifikasi.',
            'address' => 'Alamat yang telah diverifikasi.',
            'contact_info' => 'Kontak yang telah diverifikasi.',
            'page_content' => json_encode([
                'hero' => [
                    'badge' => 'Label profil yang disimpan',
                    'title' => 'Judul profil yang disimpan',
                    'description' => 'Deskripsi profil yang disimpan',
                ],
            ], JSON_THROW_ON_ERROR),
        ]);

        $response = $this->get('/profil');

        $response->assertStatus(200);
        $response->assertSee('Judul profil yang disimpan');
        $response->assertSee('Deskripsi profil yang disimpan');
        $this->assertStringNotContainsString(
            '<h1 class="display-5 fw-bold mb-3">Jemaat GPM Leahari</h1>',
            $response->getBody()
        );
    }

    public function testPublicCensusSummarySeparatesNumbersFromDisplayText(): void
    {
        $census = \App\Libraries\PublicProfileContent::defaults()['census'];
        $census['household_suffix'] = 'KK dan';
        $census['population_suffix'] = 'jiwa.';
        db_connect('tests')->table('profil_jemaat')->insert([
            'census_content' => json_encode($census, JSON_THROW_ON_ERROR),
        ]);

        $response = $this->get('/profil');
        $summary = strip_tags($response->getBody());

        $response->assertStatus(200);
        $this->assertMatchesRegularExpression('/199\s+KK dan\s+624\s+jiwa\./', $summary);
    }

    public function testProfileDisplaysSavedHistoryAndVisionMissionAfterStatsAndPreservesParagraphs(): void
    {
        db_connect('tests')->table('profil_jemaat')->insert([
            'history' => "Sejarah jemaat paragraf pertama.\nSejarah jemaat paragraf kedua.",
            'vision_mission' => "Visi jemaat.\nMisi jemaat.",
        ]);

        $response = $this->get('/profil');
        $body = $response->getBody();

        $response->assertStatus(200);
        $response->assertSee('Mengenal Jemaat Leahari');
        $response->assertSee('Sejarah jemaat paragraf pertama.');
        $response->assertSee('Visi jemaat.');
        $response->assertSee('Misi jemaat.');
        $this->assertMatchesRegularExpression(
            '/Sejarah jemaat paragraf pertama\.<br\s*\/?>\s*Sejarah jemaat paragraf kedua\./',
            $body
        );
        $this->assertTrue(
            strpos($body, 'id="sejarah-visi-misi"') < strpos($body, 'id="peta-pelayanan"'),
            'Sejarah and vision-mission should appear before the service map.'
        );
    }

    public function testProfileDoesNotRenderHistorySectionWhenSavedContentIsBlank(): void
    {
        db_connect('tests')->table('profil_jemaat')->insert([
            'history' => '  ',
            'vision_mission' => '',
        ]);

        $response = $this->get('/profil');

        $response->assertStatus(200);
        $response->assertDontSee('id="sejarah-visi-misi"');
        $response->assertDontSee('Mengenal Jemaat Leahari');
    }

    public function testProfileDoesNotRenderOfficialContactSectionWhenFieldsAreBlank(): void
    {
        db_connect('tests')->table('profil_jemaat')->insert([
            'address' => '',
            'maps_url' => '',
            'contact_info' => '',
        ]);

        $response = $this->get('/profil');
        $body = $response->getBody();

        $response->assertStatus(200);
        $response->assertDontSee('id="profil-kontak"');
        $response->assertDontSee('Kontak dan alamat resmi menunggu konfirmasi.');
        $response->assertDontSee('Lihat lokasi di Google Maps');
    }

    public function testProfileRendersOfficialContactFieldsWhenTheyAreAvailable(): void
    {
        db_connect('tests')->table('profil_jemaat')->insert([
            'address' => 'Alamat Jemaat yang sudah disetujui.',
            'maps_url' => 'https://maps.google.com/?q=Leahari',
            'contact_info' => 'kontak-resmi@example.org',
        ]);

        $response = $this->get('/profil');
        $body = $response->getBody();

        $response->assertStatus(200);
        $response->assertSee('id="profil-kontak"');
        $response->assertSee('Alamat Jemaat yang sudah disetujui.');
        $response->assertSee('kontak-resmi@example.org');
        $response->assertSee('href="https://maps.google.com/?q=Leahari"');
        $response->assertSee('Lihat lokasi di Google Maps');
    }

    public function testAdminCanSaveProfileWithoutUnsubmittedHistoryVisionMissionOrContact(): void
    {
        $db = db_connect('tests');
        $db->table('profil_jemaat')->insert([
            'history' => 'Sejarah sementara yang belum disetujui.',
            'vision_mission' => 'Visi-misi sementara yang belum disetujui.',
            'address' => 'Alamat Jemaat yang sudah disetujui.',
            'contact_info' => 'kontak-lama@example.org',
        ]);
        $profileId = (int) $db->insertID();
        $request = service('request');
        $request->setGlobal('post', [
            'id' => $profileId,
            'history' => '',
            'vision_mission' => '',
            'address' => 'Alamat Jemaat yang sudah disetujui.',
            'maps_url' => '',
            'contact_info' => '',
        ]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->saveProfile();
        $savedProfile = $db->table('profil_jemaat')->where('id', $profileId)->get()->getRowArray();

        $this->assertSame(site_url('admin/profil'), $response->getHeaderLine('Location'));
        $this->assertSame('', $savedProfile['history']);
        $this->assertSame('', $savedProfile['vision_mission']);
        $this->assertSame('', $savedProfile['contact_info']);

        $publicPage = $this->get('/profil');
        $publicPage->assertDontSee('id="sejarah-visi-misi"');
        $publicPage->assertSee('id="profil-kontak"');
        $publicPage->assertSee('Alamat Jemaat yang sudah disetujui.');
        $publicPage->assertDontSee('kontak-lama@example.org');
    }

    public function testProfileShowsTheHomeLeadershipGridBeforeTheTerritoryWithoutASectionWrapper(): void
    {
        $db = db_connect('tests');
        $content = \App\Libraries\PublicProfileContent::defaults();
        $content['leadership']['show_members'] = true;
        $db->table('profil_jemaat')->insert([
            'page_content' => json_encode($content, JSON_THROW_ON_ERROR),
        ]);
        $db->table('majelis_periode')->insert([
            'period_name' => 'Periode uji',
            'start_date' => '2026-01-01',
            'is_active' => 1,
        ]);
        $db->table('majelis_anggota')->insert([
            'periode_id' => (int) $db->insertID(),
            'name' => 'Anggota Uji',
            'position' => 'Jabatan Uji',
        ]);

        $response = $this->get('/profil');
        $response->assertStatus(200);
        $body = $response->getBody();
        $leadershipPosition = strpos($body, 'class="leadership-grid"');
        $territoryPosition = strpos($body, 'id="wilayah"');

        $this->assertNotFalse($leadershipPosition, 'The public profile should render the Majelis Jemaat grid.');
        $this->assertNotFalse($territoryPosition, 'The public profile should render its territory section.');
        $this->assertLessThan($territoryPosition, $leadershipPosition);
        $this->assertStringNotContainsString('section class="leadership-section"', $body);
        $this->assertStringContainsString('Majelis Jemaat', $body);
        $this->assertStringContainsString('Anggota Uji', $body);
        $this->assertStringContainsString('Jabatan Uji', $body);
    }

    public function testProfileShowsServiceMapBeforeLeadershipCards(): void
    {
        $response = $this->get('/profil');
        $response->assertStatus(200);
        $body = $response->getBody();
        $mapPosition = strpos($body, 'assets/images/gereja/Peta%20Pelayanan.png');
        $cardsPosition = strpos($body, 'id="leadership-title"');

        $this->assertNotFalse($mapPosition, 'The public profile should render the service map.');
        $this->assertNotFalse($cardsPosition, 'The public profile should render its Majelis area.');
        $this->assertLessThan($cardsPosition, $mapPosition);
        $this->assertStringContainsString('alt="Peta pelayanan Jemaat Leahari"', $body);
        $this->assertStringContainsString('Peta Pelayanan', $body);
    }

    public function testProfileUsesTheSavedServiceMapAndAltText(): void
    {
        db_connect('tests')->table('profil_jemaat')->insert([
            'service_map_path' => 'uploads/profil/peta/peta-uji.png',
            'service_map_title' => 'Peta wilayah yang tersimpan',
            'service_map_alt' => 'Peta pembaruan untuk pengujian',
        ]);

        $response = $this->get('/profil');

        $response->assertStatus(200);
        $this->assertStringContainsString('src="' . base_url('uploads/profil/peta/peta-uji.png') . '"', $response->getBody());
        $this->assertStringContainsString('<h2 class="fw-bold" id="service-map-title">Peta wilayah yang tersimpan</h2>', $response->getBody());
        $this->assertStringContainsString('alt="Peta pembaruan untuk pengujian"', $response->getBody());
    }

    public function testUpdatingMapAltTextDoesNotReplaceTheSavedImage(): void
    {
        $db = db_connect('tests');
        $db->table('profil_jemaat')->insert([
            'service_map_path' => 'uploads/profil/peta/peta-lama.png',
            'service_map_alt' => 'Keterangan lama',
        ]);
        $request = service('request');
        $request->setGlobal('post', ['service_map_alt' => 'Keterangan baru']);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->saveServiceMap();

        $profile = $db->table('profil_jemaat')->get()->getRowArray();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('uploads/profil/peta/peta-lama.png', $profile['service_map_path']);
        $this->assertSame('Keterangan baru', $profile['service_map_alt']);
    }

    public function testInvalidMapUploadLeavesThePreviouslySavedImageAndAltTextUntouched(): void
    {
        $db = db_connect('tests');
        $db->table('profil_jemaat')->insert([
            'service_map_path' => 'uploads/profil/peta/peta-lama.png',
            'service_map_alt' => 'Keterangan lama',
        ]);
        $tempPath = tempnam(sys_get_temp_dir(), 'profile-map-');
        file_put_contents($tempPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rZsAAAAASUVORK5CYII=', true));
        $request = service('request');
        $request->setGlobal('post', ['service_map_alt' => 'Jangan ubah sebelum validasi']);
        service('superglobals')->setGlobalArray('files', [
            'service_map' => [
                'name' => 'map.gif',
                'type' => 'image/png',
                'tmp_name' => $tempPath,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tempPath),
            ],
        ]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        try {
            $response = $controller->saveServiceMap();
        } finally {
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }

        $profile = $db->table('profil_jemaat')->get()->getRowArray();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('uploads/profil/peta/peta-lama.png', $profile['service_map_path']);
        $this->assertSame('Keterangan lama', $profile['service_map_alt']);
    }

    public function testAdminSaveUpdatesContentConsumedByThePublicProfile(): void
    {
        $content = $this->asPostValues(\App\Libraries\PublicProfileContent::defaults());
        $content['hero']['title'] = 'Judul profil hasil suntingan admin';
        $content['hero']['description'] = 'Paragraf profil hasil suntingan admin';
        $request = service('request');
        $request->setGlobal('post', ['content' => $content]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->savePageContent();

        $this->assertSame(site_url('admin/profil'), $response->getHeaderLine('Location'));
        $this->assertSame(1, db_connect('tests')->table('profil_jemaat')->countAllResults());
        $publicPage = $this->get('/profil');
        $publicPage->assertStatus(200);
        $publicPage->assertSee('Judul profil hasil suntingan admin');
        $publicPage->assertSee('Paragraf profil hasil suntingan admin');
    }

    public function testSavingWorshipServicesPreservesOtherPublicProfileContent(): void
    {
        $storedContent = \App\Libraries\PublicProfileContent::defaults();
        $storedContent['hero']['title'] = 'Judul profil tetap tersimpan';
        $db = db_connect('tests');
        $db->table('profil_jemaat')->insert([
            'page_content' => json_encode($storedContent, JSON_THROW_ON_ERROR),
        ]);
        $request = service('request');
        $request->setGlobal('post', ['content' => [
            'worship' => [
                'section_badge' => '2.2.2',
                'title' => 'Pelayanan yang diperbarui',
                'description' => 'Deskripsi pelayanan yang diperbarui',
                'items' => [[
                    'day' => 'Sabtu',
                    'name' => 'Ibadah Pelayanan Baru',
                    'place' => 'Gedung gereja',
                    'note' => 'Catatan pelayanan baru',
                ]],
            ],
        ]]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->savePageContent();

        $savedContent = json_decode($db->table('profil_jemaat')->get()->getRowArray()['page_content'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Judul profil tetap tersimpan', $savedContent['hero']['title']);
        $this->assertSame('Ibadah Pelayanan Baru', $savedContent['worship']['items'][0]['name']);
        $this->assertSame(site_url('admin/profil'), $response->getHeaderLine('Location'));

        $publicPage = $this->get('/profil');
        $publicPage->assertStatus(200);
        $publicPage->assertSee('Pelayanan yang diperbarui');
        $publicPage->assertSee('Ibadah Pelayanan Baru');
    }

    public function testAdminEditorExposesCopyAndRepeatablePublicProfileFields(): void
    {
        $html = '';
        foreach (['territory', 'census', 'worship', 'map', 'content', 'data', 'leadership'] as $module) {
            $html .= $this->renderProfileManagementPage($module);
        }

        $this->assertStringContainsString('Judul tab browser', $html);
        $this->assertStringContainsString('content&#x5B;hero&#x5D;&#x5B;title&#x5D;', $html);
        $this->assertStringContainsString('Batas wilayah', $html);
        $this->assertStringContainsString('Rentang usia', $html);
        $this->assertStringContainsString('id="profile-worship-editor"', $html);
        $this->assertStringContainsString('content&#x5B;worship&#x5D;&#x5B;items&#x5D;', $html);
        $this->assertStringContainsString('data-add-item', $html);
        $this->assertStringContainsString('Tampilkan anggota Majelis di publik', $html);
        $this->assertStringContainsString('Pengelolaan Majelis', $html);
        $this->assertStringContainsString('id="member-form"', $html);
        $this->assertStringContainsString('id="profile-content-leadership"', $html);
        $this->assertStringNotContainsString('data-rm', $html);
        $dataEditor = $this->renderProfileManagementPage('data');
        $this->assertStringNotContainsString('name="history" required', $dataEditor);
        $this->assertStringNotContainsString('name="vision_mission" required', $dataEditor);
        $this->assertStringNotContainsString('name="contact_info" required', $dataEditor);
    }

    public function testAdminProfileUsesCompactModuleCardsAndDedicatedManagementLinks(): void
    {
        $html = view('admin/profil/index', [
            'profile' => ['id' => 0, 'history' => '', 'vision_mission' => '', 'address' => '', 'maps_url' => '', 'contact_info' => ''],
            'pageContent' => \App\Libraries\PublicProfileContent::defaults(),
            'title' => 'Profil Jemaat',
            'name' => 'Admin',
            'partial' => false,
            'periods' => [],
            'selectedPeriod' => 0,
            'members' => [],
            'territorySummary' => '4 bagian informasi',
            'householdCount' => 199,
            'totalJiwa' => 624,
            'scheduleCount' => 0,
            'serviceMapPath' => 'assets/images/gereja/Peta Pelayanan.png',
            'serviceMapAlt' => 'Peta pelayanan Jemaat Leahari',
            'success' => null,
            'error' => null,
        ]);

        $this->assertStringContainsString('profile-module-grid', $html);
        $this->assertStringContainsString('Gambaran Umum Wilayah', $html);
        $this->assertStringContainsString('Jumlah Kepala Keluarga dan Jiwa', $html);
        $this->assertStringContainsString('Pelayanan Peribadahan', $html);
        $this->assertStringContainsString('Peta Pelayanan', $html);
        $this->assertStringContainsString('Konten Profil &amp; Majelis', $html);
        $this->assertStringContainsString('admin/jadwal', $html);
        $this->assertStringContainsString('admin/profil/kelola/territory', $html);
        $this->assertStringNotContainsString('data-rm', $html);
        $this->assertStringNotContainsString('profile-section-nav', $html);
        $this->assertStringNotContainsString('id="public-content"', $html);
    }

    public function testProfileModuleCardsNavigateToDedicatedManagementPages(): void
    {
        $html = view('admin/profil/index', [
            'profile' => ['id' => 0, 'history' => '', 'vision_mission' => '', 'address' => '', 'maps_url' => '', 'contact_info' => ''],
            'pageContent' => \App\Libraries\PublicProfileContent::defaults(),
            'title' => 'Profil Jemaat',
            'name' => 'Admin',
            'partial' => false,
            'periods' => [],
            'selectedPeriod' => 0,
            'members' => [],
            'serviceMapPath' => 'assets/images/gereja/Peta Pelayanan.png',
            'serviceMapAlt' => 'Peta pelayanan Jemaat Leahari',
            'success' => null,
            'error' => null,
        ]);

        foreach (['territory', 'census', 'worship', 'map', 'content', 'data', 'leadership'] as $module) {
            $this->assertStringContainsString('admin/profil/kelola/' . $module, $html);
        }

        $this->assertStringNotContainsString('data-rm-open="#profile-', $html);
    }

    public function testManagementPageRendersOnlyItsEditorAndProvidesReturnNavigation(): void
    {
        $html = $this->renderProfileManagementPage('territory');

        $this->assertStringContainsString('data-profile-editor-page', $html);
        $this->assertStringContainsString('Kembali ke Profil', $html);
        $this->assertStringContainsString('id="profile-territory-editor"', $html);
        $this->assertStringContainsString('action="http://example.com/index.php/admin/profil/modules/territory"', $html);
        $this->assertStringNotContainsString('profile-census-editor', $html);
        $this->assertStringNotContainsString('data-rm', $html);
    }

    public function testManagementPageOmitsItsTitleAndDescriptionWhileKeepingAnAccessibleName(): void
    {
        $html = $this->renderProfileManagementPage('content');

        $this->assertStringNotContainsString('id="profile-manage-title"', $html);
        $this->assertStringNotContainsString('Perubahan yang disimpan akan tampil di halaman Profil publik.', $html);
        $this->assertStringContainsString('aria-label="Pengelolaan profil jemaat"', $html);
        $this->assertStringContainsString('Kembali ke Profil', $html);
    }

    public function testManagementRoutesRenderEachEditorAndRejectUnknownModules(): void
    {
        db_connect('tests')->table('admin_users')->insert([
            'id' => 1,
            'role' => 'operator',
            'is_active' => 1,
        ]);

        foreach (['territory', 'census', 'worship', 'map', 'content', 'data', 'leadership'] as $module) {
            $response = $this->withSession(['admin_id' => 1])->get('/admin/profil/kelola/' . $module);

            $response->assertStatus(200);
            $response->assertSee('data-profile-editor-page');
            $response->assertSee('Kembali ke Profil');
            $response->assertSee('profile-' . $module . '-editor');
            $response->assertDontSee('data-rm');
        }

        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->withSession(['admin_id' => 1])->get('/admin/profil/kelola/unknown');
    }

    public function testWorshipModuleEditsPublicProfileServicesInsteadOfSchedules(): void
    {
        $html = $this->renderProfileManagementPage('worship');

        $this->assertStringContainsString('name="content&#x5B;worship&#x5D;&#x5B;items&#x5D;&#x5B;0&#x5D;&#x5B;day&#x5D;"', $html);
        $this->assertStringContainsString('name="content&#x5B;worship&#x5D;&#x5B;items&#x5D;&#x5B;0&#x5D;&#x5B;name&#x5D;"', $html);
        $this->assertStringContainsString('action="http://example.com/index.php/admin/profil/save-page-content"', $html);
        $this->assertStringNotContainsString('data-rm', $html);
    }

    public function testTerritoryEditorGroupsRelatedFieldsAndHidesTechnicalIconLabels(): void
    {
        $html = $this->renderProfileManagementPage('territory');

        $this->assertTrue(str_contains($html, 'Informasi wilayah'), 'The territory form should group its general information.');
        $this->assertTrue(str_contains($html, 'Batas wilayah'), 'The territory form should have a dedicated boundaries group.');
        $this->assertTrue(str_contains($html, 'Sektor pelayanan'), 'The territory form should have a dedicated sectors group.');
        $this->assertTrue(str_contains($html, 'Jarak ke lokasi penting'), 'The territory form should have a dedicated destinations group.');
        $this->assertTrue(str_contains($html, 'Musim wilayah'), 'The territory form should retain its seasons section.');
        $this->assertTrue(str_contains($html, 'Judul bagian wilayah'), 'Territory titles should explain what they change.');
        $this->assertTrue(str_contains($html, 'Uraian status administrasi'), 'Administration text fields should describe the content expected.');
        $this->assertTrue(str_contains($html, 'Uraian topografi'), 'Topography text fields should describe the content expected.');
        $this->assertTrue(str_contains($html, 'Uraian kehidupan masyarakat'), 'Community text fields should describe the content expected.');
        $this->assertTrue(str_contains($html, 'Teks sebelum arah batas'), 'Boundary prefix settings should be explained in plain language.');
        $this->assertTrue(str_contains($html, 'Nama wilayah'), 'Boundary names should be distinct from sector names.');
        $this->assertTrue(str_contains($html, 'Nama sektor'), 'Sector names should be distinct from boundary names.');
        $this->assertTrue(str_contains($html, 'Teks sebelum nama sektor'), 'Sector prefix settings should be explained in plain language.');
        $this->assertTrue(str_contains($html, 'Judul bagian jarak'), 'Distance section titles should use familiar terms.');
        $this->assertTrue(str_contains($html, 'Tambah tujuan'), 'Destination rows should have a concise add action.');
        $this->assertTrue(str_contains($html, 'Tambah musim'), 'Season rows should have a concise add action.');
        $this->assertTrue(str_contains($html, 'Daftar musim'), 'The season list should use a specific label.');
        $this->assertTrue(str_contains($html, 'name="territory&#x5B;boundaries&#x5D;&#x5B;0&#x5D;&#x5B;icon&#x5D;"'), 'Boundary icon settings should retain their save contract.');
        $this->assertStringContainsString('Satuan jumlah unit', $html);
        $this->assertStringContainsString('Teks setelah persentase luas', $html);
        $this->assertStringContainsString('<option value="Utara" selected>Utara</option>', $html);
        $this->assertStringContainsString('<option value="Barat"', $html);
        $this->assertStringNotContainsString('Ikon yang sedang digunakan', $html);
        $this->assertFalse(str_contains($html, '<label for="profile-territory-boundaries-0-icon">Ikon Bootstrap</label>'), 'Technical icon settings should not be shown to content editors.');
        $this->assertFalse(str_contains($html, '<label for="profile-territory-section_badge">Label bagian</label>'), 'Internal section numbering should not be shown to content editors.');
    }

    public function testTerritoryEditorKeepsDisplayFormattingInItsOwnStepAndPreviewsSavedData(): void
    {
        $html = $this->renderProfileManagementPage('territory');

        $this->assertSame(6, substr_count($html, 'data-profile-step-panel'));
        $this->assertStringContainsString('Tampilan publik', $html);
        $this->assertStringContainsString('Jarak ke lokasi penting', $html);
        $this->assertStringContainsString('Musim wilayah', $html);
        preg_match('/<section class="profile-step-panel" id="profile-territory-step-0"[^>]*>(.*?)<\/section>/s', $html, $firstStep);
        preg_match('/<section class="profile-step-panel" id="profile-territory-step-5"[^>]*>(.*?)<\/section>/s', $html, $displayStep);
        $this->assertArrayHasKey(1, $firstStep);
        $this->assertArrayHasKey(1, $displayStep);
        foreach (['boundary_prefix', 'sector_prefix', 'unit_suffix', 'area_share_suffix'] as $setting) {
            $field = 'territory&#x5B;' . $setting . '&#x5D;';
            $this->assertStringNotContainsString($field, implode('', array_slice(explode('</section>', $html), 0, 5)));
            $this->assertStringContainsString($field, $displayStep[1]);
        }
        $this->assertStringContainsString('Contoh batas wilayah', $displayStep[1]);
        $this->assertStringContainsString('Contoh sektor pelayanan', $displayStep[1]);
        $this->assertStringContainsString('Sebelah Utara — Jemaat GPM Soya', $displayStep[1]);
        $this->assertStringContainsString('Sektor Koinonia · 3 unit · 34.15 Ha · 64% dari total wilayah', $displayStep[1]);
        $this->assertStringContainsString('name="territory&#x5B;boundaries&#x5D;&#x5B;0&#x5D;&#x5B;direction&#x5D;"', $html);
        $this->assertMatchesRegularExpression('/<input\b(?=[^>]*type="hidden")(?=[^>]*name="territory&#x5B;boundaries&#x5D;&#x5B;0&#x5D;&#x5B;icon&#x5D;")/', $html);
    }

    public function testTerritoryEditorUsesSelectableFormSteps(): void
    {
        $html = $this->renderProfileManagementPage('territory');

        $this->assertStringContainsString('data-profile-step-nav aria-label="Langkah pengisian"', $html);
        $this->assertSame(6, substr_count($html, 'data-profile-step-target='));
        $this->assertSame(6, substr_count($html, 'data-profile-step-panel'));
        $this->assertStringContainsString('data-profile-step-controls hidden', $html);
        $this->assertStringContainsString('formnovalidate data-profile-submit', $html);
        $this->assertStringNotContainsString('<details class="profile-content-accordion"', $html);
        $this->assertStringNotContainsString('class="profile-content-subsection"', $html);
    }

    public function testProfileContentEditorUsesSelectableFormSteps(): void
    {
        $html = $this->renderProfileManagementPage('content');

        $this->assertStringContainsString('data-profile-step-nav aria-label="Langkah pengisian"', $html);
        $this->assertStringContainsString('data-profile-step-target="profile-content-hero"', $html);
        $this->assertSame(count(\App\Libraries\PublicProfileContent::defaults()) - 4, substr_count($html, 'data-profile-step-panel'));
        $this->assertStringContainsString('formnovalidate data-profile-submit', $html);
    }

    public function testCensusEditorUsesSelectableWizardSteps(): void
    {
        $html = $this->renderProfileManagementPage('census');

        $this->assertStringContainsString('data-census-step-nav', $html);
        $this->assertStringContainsString('aria-label="Navigasi bagian formulir"', $html);
        $this->assertStringContainsString('data-census-step-target="census-step-main"', $html);
        $this->assertStringContainsString('data-census-step-target="census-step-ages"', $html);
        $this->assertStringContainsString('data-census-step-target="census-step-labels"', $html);
        $this->assertStringContainsString('Tampilan publik', $html);
        $this->assertStringNotContainsString('Label &amp; aksesibilitas', $html);
        $this->assertSame(3, substr_count($html, 'data-census-step-panel'));
        $this->assertStringContainsString('data-census-step-controls hidden', $html);
        $this->assertSame(3, preg_match_all('/<section class="profile-census-step-panel"[^>]*data-census-step-panel/', $html));
        $this->assertSame(0, preg_match_all('/<summary\b/', $html));
        $this->assertStringContainsString('Jumlah kepala keluarga', $html);
        $this->assertStringContainsString('aria-labelledby="census-step-main-nav"', $html);
        $this->assertStringContainsString('aria-labelledby="census-step-ages-nav"', $html);
        $this->assertStringContainsString('aria-labelledby="census-step-labels-nav"', $html);
        preg_match('/<section class="profile-census-step-panel" id="census-step-main"[^>]*>(.*?)<\/section>/s', $html, $mainStep);
        preg_match('/<section class="profile-census-step-panel" id="census-step-labels"[^>]*>(.*?)<\/section>/s', $html, $publicDisplayStep);
        $this->assertArrayHasKey(1, $mainStep);
        $this->assertArrayHasKey(1, $publicDisplayStep);
        $this->assertStringNotContainsString('census&#x5B;household_suffix&#x5D;', $mainStep[1]);
        $this->assertStringNotContainsString('census&#x5B;population_suffix&#x5D;', $mainStep[1]);
        $this->assertStringContainsString('census&#x5B;household_suffix&#x5D;', $publicDisplayStep[1]);
        $this->assertStringContainsString('census&#x5B;population_suffix&#x5D;', $publicDisplayStep[1]);
        $this->assertStringContainsString('Pratinjau tampilan di halaman publik', $publicDisplayStep[1]);
        foreach (array_keys(\App\Libraries\PublicProfileContent::defaults()['census']) as $field) {
            if ($field !== 'age_rows') {
                $this->assertStringContainsString(
                    'name="' . esc('census[' . $field . ']', 'attr') . '"',
                    $html,
                    'The census form should retain the save contract for ' . $field . '.'
                );
            }
        }
        $this->assertStringContainsString('name="census&#x5B;household_count&#x5D;"', $html);
        $this->assertStringContainsString('name="census&#x5B;age_rows&#x5D;&#x5B;0&#x5D;&#x5B;label&#x5D;"', $html);
        $this->assertStringContainsString('name="census&#x5B;age_rows&#x5D;&#x5B;0&#x5D;&#x5B;male&#x5D;"', $html);
        $this->assertStringContainsString('name="census&#x5B;age_rows&#x5D;&#x5B;0&#x5D;&#x5B;female&#x5D;"', $html);
        $this->assertStringContainsString('type="hidden" name="census&#x5B;section_badge&#x5D;"', $html);
        $this->assertStringNotContainsString('<label for="profile-census-section_badge">Label bagian</label>', $html);
    }

    public function testSavingCensusModuleKeepsLegacyContentAndCalculatesPublicTotals(): void
    {
        $db = db_connect('tests');
        $legacy = \App\Libraries\PublicProfileContent::defaults();
        $legacy['hero']['title'] = 'Judul legacy tetap';
        $legacyJson = json_encode($legacy, JSON_THROW_ON_ERROR);
        $db->table('profil_jemaat')->insert(['page_content' => $legacyJson]);

        $census = \App\Libraries\PublicProfileContent::defaults()['census'];
        $census['household_count'] = '77';
        $census['age_rows'] = [
            ['label' => '10-19', 'male' => '20', 'female' => '15'],
            ['label' => '20-29', 'male' => '4', 'female' => '5'],
        ];
        $request = service('request');
        $request->setGlobal('post', ['census' => $this->asPostValues($census)]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        if (! method_exists($controller, 'saveCensusModule')) {
            $this->fail('AdminProfil must expose an independent census module save endpoint.');
            return;
        }

        $response = $controller->saveCensusModule();

        $this->assertSame(200, $response->getStatusCode(), $response->getBody());
        $profile = $db->table('profil_jemaat')->get()->getRowArray();
        $this->assertSame($legacyJson, $profile['page_content']);
        $storedCensus = json_decode($profile['census_content'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(77, $storedCensus['household_count']);
        $publicPage = $this->get('/profil');
        $publicPage->assertSee('Judul legacy tetap');
        $publicPage->assertSee('77');
        $publicPage->assertSee('44');
        $publicPage->assertSee('Kelompok terbanyak');
        $publicPage->assertSee('10-19');
    }

    public function testSavingTerritoryModuleDoesNotChangeCensusOrLegacyContent(): void
    {
        $db = db_connect('tests');
        $legacyJson = json_encode(\App\Libraries\PublicProfileContent::defaults(), JSON_THROW_ON_ERROR);
        $censusJson = json_encode(['household_count' => 12, 'age_rows' => []], JSON_THROW_ON_ERROR);
        $db->table('profil_jemaat')->insert([
            'page_content' => $legacyJson,
            'census_content' => $censusJson,
        ]);

        $content = \App\Libraries\PublicProfileContent::defaults();
        $territory = [
            'territory' => $content['territory'],
            'seasons' => $content['seasons'],
        ];
        $territory['territory']['administration_text'] = 'Informasi administrasi terbaru.';
        $request = service('request');
        $request->setGlobal('post', $this->asPostValues($territory));
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        if (! method_exists($controller, 'saveTerritoryModule')) {
            $this->fail('AdminProfil must expose an independent territory module save endpoint.');
            return;
        }

        $response = $controller->saveTerritoryModule();

        $this->assertSame(200, $response->getStatusCode(), $response->getBody());
        $profile = $db->table('profil_jemaat')->get()->getRowArray();
        $this->assertSame($legacyJson, $profile['page_content']);
        $this->assertSame($censusJson, $profile['census_content']);
        $storedTerritory = json_decode($profile['territory_content'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Informasi administrasi terbaru.', $storedTerritory['territory']['administration_text']);
    }

    public function testMemberNamesArePublicOnlyAfterExplicitPublicationIsEnabled(): void
    {
        $db = db_connect('tests');
        $db->table('majelis_periode')->insert([
            'period_name' => 'Periode uji',
            'start_date' => '2026-01-01',
            'is_active' => 1,
        ]);
        $periodId = (int) $db->insertID();
        $db->table('majelis_anggota')->insert([
            'periode_id' => $periodId,
            'name' => 'Nama Anggota Uji',
            'position' => 'Jabatan Uji',
        ]);

        $content = \App\Libraries\PublicProfileContent::defaults();
        $content['leadership']['show_members'] = true;
        $db->table('profil_jemaat')->insert([
            'page_content' => json_encode($content, JSON_THROW_ON_ERROR),
        ]);

        $published = $this->get('/profil');
        $published->assertSee('Nama Anggota Uji');
        $published->assertSee('Jabatan Uji');
        $published->assertSee('class="leadership-grid"');
        $published->assertSee('class="leader-card"');

        $content['leadership']['show_members'] = false;
        $db->table('profil_jemaat')->update(['page_content' => json_encode($content, JSON_THROW_ON_ERROR)]);
        $hidden = $this->get('/profil');
        $this->assertStringNotContainsString('Nama Anggota Uji', $hidden->getBody());
        $this->assertStringNotContainsString('Pdt. Ny. J. K. Tutupary/L', $hidden->getBody());
        $this->assertStringNotContainsString('class="leadership-grid"', $hidden->getBody());
    }

    public function testEditingMemberInAdminUpdatesThePublishedProfileCard(): void
    {
        $db = db_connect('tests');
        $content = \App\Libraries\PublicProfileContent::defaults();
        $content['leadership']['show_members'] = true;
        $db->table('profil_jemaat')->insert([
            'page_content' => json_encode($content, JSON_THROW_ON_ERROR),
        ]);
        $db->table('majelis_periode')->insert([
            'period_name' => 'Periode uji',
            'start_date' => '2026-01-01',
            'is_active' => 1,
        ]);
        $periodId = (int) $db->insertID();
        $db->table('majelis_anggota')->insert([
            'periode_id' => $periodId,
            'name' => 'Nama Awal Uji',
            'position' => 'Jabatan Awal Uji',
        ]);
        $memberId = (int) $db->insertID();

        $request = service('request');
        $request->setGlobal('post', [
            'id' => (string) $memberId,
            'periode_id' => (string) $periodId,
            'name' => 'Nama Kartu Diperbarui',
            'position' => 'Jabatan Kartu Diperbarui',
        ]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        $controller->saveAnggota();

        $publicPage = $this->get('/profil');
        $publicPage->assertSee('Nama Kartu Diperbarui');
        $publicPage->assertSee('Jabatan Kartu Diperbarui');
        $this->assertStringNotContainsString('Nama Awal Uji', $publicPage->getBody());
    }

    public function testInvalidPublicProfileContentDoesNotCreateOrOverwriteProfile(): void
    {
        $content = $this->asPostValues(\App\Libraries\PublicProfileContent::defaults());
        $content['territory']['sectors'][0]['area'] = '-1';
        $request = service('request');
        $request->setGlobal('post', ['content' => $content]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->savePageContent();

        $this->assertSame(0, db_connect('tests')->table('profil_jemaat')->countAllResults());
        $this->assertStringContainsString('harus berupa angka nol atau lebih', (string) session()->getFlashdata('error'));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testOversizedPublicProfileContentIsRejectedBeforeSaving(): void
    {
        $content = \App\Libraries\PublicProfileContent::defaults();
        $content['territory']['sectors'] = array_fill(0, 100, [
            'name' => str_repeat('S', 1000),
            'units' => '3',
            'area' => '12.3',
            'area_unit' => 'Ha',
        ]);
        $request = service('request');
        $request->setGlobal('post', ['content' => $this->asPostValues($content)]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->savePageContent();

        $this->assertSame(0, db_connect('tests')->table('profil_jemaat')->countAllResults());
        $this->assertStringContainsString('maksimal 60 KB', (string) session()->getFlashdata('error'));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testNewActiveLeadershipPeriodBecomesThePublicActivePeriod(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'id' => '0',
            'period_name' => 'Periode baru',
            'start_date' => '2026-01-01',
            'end_date' => '',
            'is_active' => '1',
        ]);
        session()->set('admin_id', 7);
        $controller = new AdminProfil();
        $controller->initController($request, service('response'), service('logger'));

        $controller->savePeriode();

        $activePeriods = db_connect('tests')->table('majelis_periode')->where('is_active', 1)->get()->getResultArray();
        $this->assertCount(1, $activePeriods);
        $this->assertSame('Periode baru', $activePeriods[0]['period_name']);
    }

    private function asPostValues(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->asPostValues($value);
            } elseif (is_bool($value)) {
                $values[$key] = $value ? '1' : '0';
            } elseif (is_int($value) || is_float($value)) {
                $values[$key] = (string) $value;
            }
        }

        return $values;
    }
}
