<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\AdminProfil;

final class PublicProfileContentIntegrationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $originalDatabaseGroup;

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
        foreach (['profil_jemaat', 'majelis_periode', 'majelis_anggota'] as $table) {
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

    public function testAdminEditorExposesCopyAndRepeatablePublicProfileFields(): void
    {
        $content = \App\Libraries\PublicProfileContent::defaults();
        $html = view('admin/profil/index', [
            'profile' => ['id' => 0, 'history' => '', 'vision_mission' => '', 'address' => '', 'maps_url' => '', 'contact_info' => ''],
            'pageContent' => $content,
            'title' => 'Profil & Majelis',
            'name' => 'Admin',
            'partial' => false,
            'periods' => [],
            'selectedPeriod' => 0,
            'members' => [],
            'success' => null,
            'error' => null,
        ]);

        $this->assertStringContainsString('Judul tab browser', $html);
        $this->assertStringContainsString('content&#x5B;hero&#x5D;&#x5B;title&#x5D;', $html);
        $this->assertStringContainsString('Batas wilayah', $html);
        $this->assertStringContainsString('Rentang usia', $html);
        $this->assertStringContainsString('Pelayanan peribadahan', $html);
        $this->assertStringContainsString('data-add-item', $html);
        $this->assertStringContainsString('Tampilkan anggota Majelis di publik', $html);
        $this->assertStringContainsString('Kelola kartu Majelis', $html);
        $this->assertStringContainsString('id="member-form"', $html);
        $this->assertStringContainsString('id="profile-content-leadership"', $html);
        $this->assertStringContainsString('data-rm-open="#profile-leadership-modal"', $html);
    }

    public function testAdminProfileUsesCompactModuleCardsAndResponsiveDialogs(): void
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
        $this->assertStringContainsString('data-profile-ajax-form', $html);
        $this->assertStringContainsString('data-rm', $html);
        $this->assertStringNotContainsString('profile-section-nav', $html);
        $this->assertStringNotContainsString('id="public-content"', $html);
    }

    public function testTerritoryEditorGroupsRelatedFieldsAndHidesTechnicalIconLabels(): void
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

        $this->assertTrue(str_contains($html, 'Informasi umum wilayah'), 'The territory form should group its general information.');
        $this->assertTrue(str_contains($html, 'Batas wilayah'), 'The territory form should have a dedicated boundaries group.');
        $this->assertTrue(str_contains($html, 'Sektor pelayanan'), 'The territory form should have a dedicated sectors group.');
        $this->assertTrue(str_contains($html, 'Jarak ke layanan penting'), 'The territory form should have a dedicated destinations group.');
        $this->assertTrue(str_contains($html, 'Musim'), 'The territory form should retain its seasons section.');
        $this->assertTrue(str_contains($html, 'Judul bagian wilayah'), 'Territory titles should explain what they change.');
        $this->assertTrue(str_contains($html, 'Kata sebelum arah batas'), 'Boundary prefix settings should be explained in plain language.');
        $this->assertTrue(str_contains($html, 'Nama wilayah'), 'Boundary names should be distinct from sector names.');
        $this->assertTrue(str_contains($html, 'Nama sektor'), 'Sector names should be distinct from boundary names.');
        $this->assertTrue(str_contains($html, 'Kata sebelum nama sektor'), 'Sector prefix settings should be explained in plain language.');
        $this->assertTrue(str_contains($html, 'Judul jarak dan tujuan'), 'Orbit section titles should use familiar terms.');
        $this->assertTrue(str_contains($html, 'Tambah tujuan'), 'Destination rows should have a concise add action.');
        $this->assertTrue(str_contains($html, 'Tambah musim'), 'Season rows should have a concise add action.');
        $this->assertTrue(str_contains($html, 'Daftar musim'), 'The season list should use a specific label.');
        $this->assertTrue(str_contains($html, 'name="territory&#x5B;boundaries&#x5D;&#x5B;0&#x5D;&#x5B;icon&#x5D;"'), 'Boundary icon settings should retain their save contract.');
        $this->assertTrue(str_contains($html, 'Arah penunjuk'), 'Boundary icons should be presented as a simple direction choice.');
        $this->assertTrue(str_contains($html, 'Panah ke atas'), 'The direction choice should use plain-language options.');
        $this->assertFalse(str_contains($html, '<label for="profile-territory-boundaries-0-icon">Ikon Bootstrap</label>'), 'Technical icon settings should not be shown to content editors.');
        $this->assertFalse(str_contains($html, '<label for="profile-territory-section_badge">Label bagian</label>'), 'Internal section numbering should not be shown to content editors.');
    }

    public function testTerritoryEditorUsesFlatAccordionItems(): void
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

        $modalStart = strpos($html, 'id="profile-territory-modal"');
        $modalEnd = strpos($html, 'id="profile-census-modal"');
        $this->assertNotFalse($modalStart);
        $this->assertNotFalse($modalEnd);
        $modalHtml = substr($html, $modalStart, $modalEnd - $modalStart);

        $this->assertSame(5, substr_count($modalHtml, 'class="profile-content-accordion"'));
        $this->assertSame(1, preg_match_all('/<details class="profile-content-accordion"[^>]*open/', $modalHtml));
        $this->assertStringNotContainsString('class="profile-content-section"', $modalHtml);
        $this->assertStringNotContainsString('class="profile-content-subsection"', $modalHtml);
    }

    public function testCensusEditorGroupsFieldsIntoFlatAccordionItems(): void
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

        $modalStart = strpos($html, 'id="profile-census-modal"');
        $modalEnd = strpos($html, 'id="profile-map-modal"');
        $this->assertNotFalse($modalStart);
        $this->assertNotFalse($modalEnd);
        $modalHtml = substr($html, $modalStart, $modalEnd - $modalStart);

        $this->assertStringContainsString('data-census-step-nav', $modalHtml);
        $this->assertStringContainsString('aria-label="Navigasi bagian formulir"', $modalHtml);
        $this->assertStringContainsString('data-census-step-target="census-step-main"', $modalHtml);
        $this->assertStringContainsString('data-census-step-target="census-step-ages"', $modalHtml);
        $this->assertStringContainsString('data-census-step-target="census-step-labels"', $modalHtml);
        $this->assertSame(3, substr_count($modalHtml, 'data-census-step-panel'));
        $this->assertStringContainsString('data-census-step-controls hidden', $modalHtml);
        $this->assertSame(3, preg_match_all('/<section class="profile-census-step-panel"[^>]*data-census-step-panel/', $modalHtml));
        $this->assertSame(0, preg_match_all('/<summary\b/', $modalHtml));
        $this->assertStringContainsString('Jumlah kepala keluarga', $modalHtml);
        $this->assertStringContainsString('aria-labelledby="census-step-main-nav"', $modalHtml);
        $this->assertStringContainsString('aria-labelledby="census-step-ages-nav"', $modalHtml);
        $this->assertStringContainsString('aria-labelledby="census-step-labels-nav"', $modalHtml);
        foreach (array_keys(\App\Libraries\PublicProfileContent::defaults()['census']) as $field) {
            if ($field !== 'age_rows') {
                $this->assertStringContainsString(
                    'name="' . esc('census[' . $field . ']', 'attr') . '"',
                    $modalHtml,
                    'The census form should retain the save contract for ' . $field . '.'
                );
            }
        }
        $this->assertStringContainsString('name="census&#x5B;household_count&#x5D;"', $modalHtml);
        $this->assertStringContainsString('name="census&#x5B;age_rows&#x5D;&#x5B;0&#x5D;&#x5B;label&#x5D;"', $modalHtml);
        $this->assertStringContainsString('name="census&#x5B;age_rows&#x5D;&#x5B;0&#x5D;&#x5B;male&#x5D;"', $modalHtml);
        $this->assertStringContainsString('name="census&#x5B;age_rows&#x5D;&#x5B;0&#x5D;&#x5B;female&#x5D;"', $modalHtml);
        $this->assertStringContainsString('type="hidden" name="census&#x5B;section_badge&#x5D;"', $modalHtml);
        $this->assertStringNotContainsString('<label for="profile-census-section_badge">Label bagian</label>', $modalHtml);
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
