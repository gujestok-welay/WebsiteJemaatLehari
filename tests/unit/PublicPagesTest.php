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

    private function createProfileTables(): void
    {
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

    public function testPublicPagesRenderWithoutPreviewNoticeAndWithActiveNavigation(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();

        foreach (['/' => 'Beranda', '/profil' => 'Profil', '/berita' => 'Berita', '/jadwal-ibadah' => 'Jadwal Ibadah'] as $path => $label) {
            $result = $this->get($path);
            $result->assertStatus(200);
            $result->assertDontSee('alert-warning');
            $this->assertSame(1, preg_match('/<body[^>]*>(.*?)<\/body>/s', $result->getBody(), $pageBody));
            $visibleBody = strip_tags($pageBody[1]);
            $this->assertStringNotContainsString('Pratinjau website informasi Jemaat Leahari.', $visibleBody);
            $this->assertStringNotContainsString('Sebagian informasi masih menunggu konfirmasi resmi.', $visibleBody);
            $result->assertDontSee('Seluruh konten masih berupa contoh.');
            $this->assertSame(1, preg_match('/<a class="navbar-brand"[^>]*>(.*?)<\/a>/s', $result->getBody(), $navbarBrand));
            $this->assertStringContainsString('assets/images/logo/footer-brand.png', $navbarBrand[1]);
            $this->assertSame(
                1,
                preg_match('/<img class="navbar-brand-wordmark"[^>]*width="50" height="58"/', $navbarBrand[1])
            );
            $this->assertStringContainsString('aria-current="page"', $result->getBody());
            $result->assertSee($label . ' | Jemaat Leahari');
            $this->assertStringContainsString('id="konten"', $result->getBody());
        }
    }

    public function testPublicNavigationUsesResponsiveOffcanvasForMobileAndTablet(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();

        foreach (['/' => 'Beranda', '/profil' => 'Profil', '/berita' => 'Berita', '/jadwal-ibadah' => 'Jadwal Ibadah'] as $path => $label) {
            $result = $this->get($path);
            $result->assertStatus(200);
            $body = $result->getBody();

            $this->assertStringContainsString('class="navbar navbar-expand-lg', $body);
            $this->assertStringContainsString('data-bs-toggle="offcanvas"', $body);
            $this->assertStringContainsString('id="navigasi"', $body);
            $this->assertStringContainsString('offcanvas-lg offcanvas-start', $body);
            $this->assertStringContainsString('data-bs-dismiss="offcanvas"', $body);
            $this->assertStringContainsString('>' . $label . '</a>', $body);
            $this->assertStringContainsString('navbar-nav ms-auto', $body);
            $this->assertSame(1, preg_match('/<button class="navbar-toggler"[^>]*>(.*?)<\/button>/s', $body, $toggle));
            $this->assertStringContainsString('navbar-menu-icon', $toggle[1]);
            $this->assertSame('', trim(strip_tags($toggle[1])));
        }
    }

    public function testPublicFooterAnimatesQuickLinksAndKeepsSocialIconAccessible(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();

        $result = $this->get('/');
        $result->assertStatus(200);
        $result->assertSee('assets/images/logo/footer-brand.png');
        $this->assertSame(
            1,
            preg_match('/<nav[^>]*class="[^"]*footer-links[^"]*"[^>]*>(.*?)<\/nav>/s', $result->getBody(), $footerLinks)
        );
        $this->assertSame(4, substr_count($footerLinks[1], 'class="css-link home-inline-link"'));
        $this->assertStringNotContainsString(
            'class="css-link home-inline-link footer-social-link"',
            $result->getBody()
        );
        $this->assertStringContainsString(
            '<svg class="footer-social-icon" viewbox="0 0 24 24" aria-hidden="true"',
            strtolower($result->getBody())
        );
    }

    public function testHomepageUsesTheSameNewsCardAsTheNewsPage(): void
    {
        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for the in-memory news database test.');
        }

        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();
        $db = db_connect('tests');
        $db->table('berita')->insert([
            'title' => 'Berita kartu bersama',
            'slug' => 'berita-kartu-bersama',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita',
            'status' => 'Terbit',
            'created_at' => '2026-11-04 09:00:00',
        ]);
        $articleId = $db->insertID();

        $home = $this->get('/');
        $archive = $this->get('/berita');
        $pattern = '/<article id="berita-' . $articleId . '"[^>]*>.*?<\/article>/s';

        $this->assertSame(1, preg_match($pattern, $home->getBody(), $homeCard));
        $this->assertSame(1, preg_match($pattern, $archive->getBody(), $archiveCard));
        $homeMarkup = preg_replace('/<!--.*?-->/s', '', $homeCard[0]);
        $archiveMarkup = preg_replace('/<!--.*?-->/s', '', $archiveCard[0]);

        $this->assertSame($archiveMarkup, $homeMarkup);
    }

    public function testHomepageHighlightsOnlyTheEarliestUpcomingSchedule(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();

        $timezone = new DateTimeZone('Asia/Jayapura');
        $now = new DateTimeImmutable('now', $timezone);
        $tomorrow = (new DateTimeImmutable('tomorrow', $timezone))->format('Y-m-d');
        $yesterday = (new DateTimeImmutable('yesterday', $timezone))->format('Y-m-d');
        db_connect('tests')->table('jadwal_ibadah')->insertBatch([
            [
                'jenis_ibadah' => 'Ibadah lampau',
                'tanggal' => $yesterday,
                'waktu' => '09:00:00',
                'lokasi' => 'Lokasi uji',
            ],
            [
                'jenis_ibadah' => 'Ibadah hari ini sudah lewat',
                'tanggal' => $now->format('Y-m-d'),
                'waktu' => $now->sub(new DateInterval('PT2M'))->format('H:i:00'),
                'lokasi' => 'Lokasi uji',
            ],
            [
                'jenis_ibadah' => 'Ibadah mendatang kedua',
                'tanggal' => $tomorrow,
                'waktu' => '18:00:00',
                'lokasi' => 'Lokasi uji',
            ],
            [
                'jenis_ibadah' => 'Ibadah mendatang pertama',
                'tanggal' => $tomorrow,
                'waktu' => '09:00:00',
                'lokasi' => 'Lokasi uji',
            ],
        ]);

        $result = $this->get('/');
        $this->assertSame(1, preg_match('/<aside class="hero-panel".*?<\/aside>/s', $result->getBody(), $hero));
        $this->assertStringContainsString('Ibadah mendatang pertama', $hero[0]);
        $this->assertStringNotContainsString('Ibadah lampau', $result->getBody());
        $this->assertStringNotContainsString('Ibadah hari ini sudah lewat', $result->getBody());
        $this->assertStringNotContainsString('Ibadah mendatang kedua', $hero[0]);
        $this->assertStringNotContainsString($tomorrow, $hero[0]);
    }

    public function testHomepageShowsAnEmptyStateWhenNoFutureScheduleExists(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();

        $yesterday = (new DateTimeImmutable('yesterday', new DateTimeZone('Asia/Jayapura')))->format('Y-m-d');
        db_connect('tests')->table('jadwal_ibadah')->insert([
            'jenis_ibadah' => 'Ibadah lampau',
            'tanggal' => $yesterday,
            'waktu' => '09:00:00',
            'lokasi' => 'Lokasi uji',
        ]);

        $result = $this->get('/');
        $result->assertSee('Belum ada jadwal mendatang');
        $result->assertDontSee('Ibadah lampau');
    }

    public function testHomepageShowsSampleNewsStatusOnEachSampleCard(): void
    {
        $markup = view('components/berita-card', [
            'item' => [
                'id' => 'contoh',
                'title' => 'Berita contoh',
                'date' => '6 November 2026',
                'summary' => 'Ringkasan contoh',
            ],
            'index' => 0,
            'isSample' => true,
        ]);

        $this->assertStringContainsString('class="sample-content-badge">Contoh</span>', $markup);
    }

    public function testPublishedLoremIpsumNewsIsLabeledAsExampleWithoutLabelingRealNews(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();
        db_connect('tests')->table('berita')->insertBatch([
            [
                'title' => 'Berita uji contoh',
                'slug' => 'berita-uji-contoh',
                'summary' => 'Lorem ipsum dolor sit amet.',
                'content' => 'Konten contoh',
                'status' => 'Terbit',
                'created_at' => '2026-11-05 10:00:00',
            ],
            [
                'title' => 'Berita terverifikasi',
                'slug' => 'berita-terverifikasi',
                'summary' => 'Informasi kegiatan yang telah diperiksa.',
                'content' => 'Isi berita yang telah diperiksa.',
                'status' => 'Terbit',
                'created_at' => '2026-11-05 09:00:00',
            ],
        ]);

        foreach (['/', '/berita'] as $path) {
            $body = $this->get($path)->getBody();
            $this->assertSame(2, preg_match_all('/<article id="berita-[^"]*"[^>]*>.*?<\/article>/s', $body, $cards));
            $sampleCard = current(array_filter($cards[0], static fn (string $card): bool => str_contains($card, 'Berita uji contoh')));
            $realCard = current(array_filter($cards[0], static fn (string $card): bool => str_contains($card, 'Berita terverifikasi')));

            $this->assertIsString($sampleCard);
            $this->assertIsString($realCard);
            $this->assertStringContainsString('class="sample-content-badge">Contoh</span>', $sampleCard);
            $this->assertStringNotContainsString('sample-content-badge', $realCard);
        }
    }

    public function testHomepagePlacesNewsAndSchedulesBeforeLeadership(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();

        $body = $this->get('/')->getBody();
        $updatesPosition = strpos($body, 'class="container-xl home-updates"');
        $leadershipPosition = strpos($body, 'class="leadership-section"');

        $this->assertNotFalse($updatesPosition);
        $this->assertNotFalse($leadershipPosition);
        $this->assertLessThan($leadershipPosition, $updatesPosition);
        $this->assertSame(1, substr_count($body, 'Baca profil jemaat'));
    }

    public function testRecentlyPublishedOlderDraftAppearsAmongHomepageNews(): void
    {
        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for the in-memory news database test.');
        }

        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $db = db_connect('tests');
        $db->table('berita')->insertBatch([
            [
                'title' => 'Berita lama pertama',
                'slug' => 'berita-lama-pertama',
                'summary' => 'Ringkasan pertama',
                'content' => 'Isi pertama',
                'status' => 'Terbit',
                'created_at' => '2026-11-04 10:00:00',
                'updated_at' => '2026-11-04 10:00:00',
            ],
            [
                'title' => 'Berita lama kedua',
                'slug' => 'berita-lama-kedua',
                'summary' => 'Ringkasan kedua',
                'content' => 'Isi kedua',
                'status' => 'Terbit',
                'created_at' => '2026-11-03 10:00:00',
                'updated_at' => '2026-11-03 10:00:00',
            ],
            [
                'title' => 'Draft lama baru diterbitkan',
                'slug' => 'draft-lama-baru-diterbitkan',
                'summary' => 'Ringkasan berita terbaru',
                'content' => 'Isi berita terbaru',
                'status' => 'Terbit',
                'created_at' => '2026-10-01 10:00:00',
                'updated_at' => '2026-11-05 10:00:00',
            ],
        ]);

        $home = $this->get('/');
        $archive = $this->get('/berita');
        $latestHref = 'href="http://example.com/index.php/berita/draft-lama-baru-diterbitkan"';
        $olderHref = 'href="http://example.com/index.php/berita/berita-lama-pertama"';

        $homeLatestPosition = strpos($home->getBody(), $latestHref);
        $homeOlderPosition = strpos($home->getBody(), $olderHref);
        $archiveLatestPosition = strpos($archive->getBody(), $latestHref);
        $archiveOlderPosition = strpos($archive->getBody(), $olderHref);

        $this->assertNotFalse($homeLatestPosition);
        $this->assertNotFalse($homeOlderPosition);
        $this->assertNotFalse($archiveLatestPosition);
        $this->assertNotFalse($archiveOlderPosition);
        $this->assertLessThan($homeOlderPosition, $homeLatestPosition);
        $this->assertLessThan($archiveOlderPosition, $archiveLatestPosition);
        $home->assertSee('5 November 2026');
    }

    public function testNewsCardsLinkDirectlyToTheArticleSlug(): void
    {
        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for the in-memory news database test.');
        }

        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        db_connect('tests')->table('berita')->insert([
            'title' => 'Artikel dengan tautan langsung',
            'slug' => 'artikel-tautan-langsung',
            'summary' => 'Ringkasan artikel',
            'content' => 'Isi artikel',
            'status' => 'Terbit',
            'created_at' => '2026-11-04 09:00:00',
            'updated_at' => '2026-11-04 09:00:00',
        ]);

        foreach (['/', '/berita'] as $path) {
            $result = $this->get($path);
            $this->assertStringContainsString(
                'href="http://example.com/index.php/berita/artikel-tautan-langsung"',
                $result->getBody()
            );
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

    public function testProfileDoesNotShowUnmanagedMajelisCardsOrRemovedContactSection(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createProfileTables();

        $result = $this->get('/profil');
        $result->assertSee('Majelis Jemaat');
        $result->assertSee('Susunan Majelis aktif belum tersedia untuk publikasi.');
        $result->assertDontSee('Pdt. Ny. J. K. Tutupary/L');
        $result->assertDontSee('class="leader-card"');
        $result->assertDontSee('Lokasi dan kontak');
        $result->assertDontSee('Menunggu konfirmasi resmi.');
    }

    public function testHomepageAndProfileSharePublishedMajelisCards(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();

        $db = db_connect('tests');
        $content = \App\Libraries\PublicProfileContent::defaults();
        $content['leadership']['show_members'] = true;
        $db->table('profil_jemaat')->insert([
            'page_content' => json_encode($content, JSON_THROW_ON_ERROR),
        ]);
        $db->table('majelis_periode')->insert([
            'period_name' => 'Periode publik',
            'start_date' => '2026-01-01',
            'is_active' => 1,
        ]);
        $periodId = (int) $db->insertID();
        $db->table('majelis_anggota')->insert([
            'periode_id' => $periodId,
            'name' => 'Ketua Majelis Terkelola',
            'position' => 'Ketua',
            'photo_path' => 'uploads/majelis/ketua-uji.jpg',
        ]);
        $db->table('majelis_anggota')->insert([
            'periode_id' => $periodId,
            'name' => 'Wakil Ketua Majelis Terkelola',
            'position' => 'Wakil Ketua',
            'photo_path' => 'uploads/majelis/wakil-ketua-uji.jpg',
        ]);

        foreach (['/', '/profil'] as $path) {
            $result = $this->get($path);
            $result->assertStatus(200);
            $result->assertSee('Ketua Majelis Terkelola');
            $result->assertSee('Wakil Ketua Majelis Terkelola');
            $result->assertSee('Ketua');
            $result->assertSee('uploads/majelis/ketua-uji.jpg');
            $result->assertSee('uploads/majelis/wakil-ketua-uji.jpg');
            $result->assertSee('data-leadership-carousel');
            $result->assertSee('aria-label="Anggota sebelumnya"');
            $result->assertSee('aria-label="Anggota berikutnya"');
            $result->assertSee('aria-label="Jeda pergantian otomatis"');
        }

        $db->table('majelis_anggota')
            ->where('name', 'Ketua Majelis Terkelola')
            ->update(['name' => 'Nama Ketua Diperbarui Admin']);

        foreach (['/', '/profil'] as $path) {
            $this->get($path)->assertSee('Nama Ketua Diperbarui Admin');
        }
    }

    public function testHomepageDoesNotShowUnmanagedMajelisCards(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createNewsTable();
        $this->createScheduleTable();
        $this->createProfileTables();

        $result = $this->get('/');

        $result->assertStatus(200);
        $result->assertSee('Susunan Majelis aktif belum tersedia untuk publikasi.');
        $result->assertDontSee('Pdt. Ny. J. K. Tutupary/L');
        $result->assertDontSee('class="leader-card"');
    }

    public function testProfileAgeChartShowsBothGenderCountsForEveryRange(): void
    {
        config('Database')->defaultGroup = 'tests';
        $this->createProfileTables();

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
