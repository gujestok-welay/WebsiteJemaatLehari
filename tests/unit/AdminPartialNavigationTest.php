<?php

use App\Controllers\AdminBerita;
use App\Controllers\AdminJadwal;
use App\Controllers\BaseController;
use CodeIgniter\Test\CIUnitTestCase;

final class TestableAdminPageController extends BaseController
{
    public function showNews(array $items = []): string
    {
        return $this->renderAdminPage('admin/berita/index', [
            'items' => $items,
            'term' => '',
            'status' => '',
            'statusOptions' => [],
            'formStatusOptions' => [],
            'success' => null,
            'error' => null,
        ], 'Berita');
    }

    public function showSchedule(): string
    {
        $hari = [
            'minggu' => ['label' => 'Minggu', 'index' => 0],
            'senin' => ['label' => 'Senin', 'index' => 1],
            'selasa' => ['label' => 'Selasa', 'index' => 2],
            'rabu' => ['label' => 'Rabu', 'index' => 3],
            'kamis' => ['label' => 'Kamis', 'index' => 4],
            'jumat' => ['label' => 'Jumat', 'index' => 5],
            'sabtu' => ['label' => 'Sabtu', 'index' => 6],
        ];

        return $this->renderAdminPage('admin/jadwal/index', [
            'items' => [],
            'term' => '',
            'cancelled' => null,
            'archived' => false,
            'hari' => $hari,
            'hitung' => ['semua' => 0, 'aktif' => 0, 'dibatalkan' => 0, 'arsip' => 0],
            'hitungHari' => array_fill_keys(array_keys($hari), 0),
            'hariDipilih' => 'semua',
            'hariIni' => 'rabu',
            'jumlahHasil' => 0,
            'success' => null,
            'error' => null,
            'scheduleError' => '',
        ], 'Jadwal Ibadah');
    }

    public function showScheduleForm(): string
    {
        return $this->renderAdminPage('admin/jadwal/form', [
            'item' => null,
            'error' => null,
        ], 'Tambah Jadwal');
    }

    public function showProfile(): string
    {
        return $this->renderAdminPage('admin/profil/index', [
            'profile' => ['id' => 0, 'history' => '', 'vision_mission' => '', 'address' => '', 'maps_url' => '', 'contact_info' => ''],
            'periods' => [],
            'selectedPeriod' => 0,
            'members' => [],
            'success' => null,
            'error' => null,
        ], 'Profil & Majelis');
    }
}

final class AdminPartialNavigationTest extends CIUnitTestCase
{
    public function testAdminLayoutVersionsItsStylesheetWhenTheFileChanges(): void
    {
        $html = view('layouts/admin', ['title' => 'Uji cache stylesheet', 'name' => 'Admin']);
        $version = (string) filemtime(FCPATH . 'assets/css/admin.css');

        $this->assertStringContainsString('assets/css/admin.css?v=' . $version, $html);
    }

    public function testCreateRouteRedirectsToNewsListWithModalOpen(): void
    {
        $controller = (new ReflectionClass(AdminBerita::class))->newInstanceWithoutConstructor();
        $controller->initController(service('request'), service('response'), service('logger'));

        $response = $controller->create();

        $this->assertSame(site_url('admin/berita?create=1'), $response->getHeaderLine('Location'));
    }

    public function testScheduleCreateRouteRedirectsToScheduleListWithModalOpen(): void
    {
        $controller = (new ReflectionClass(AdminJadwal::class))->newInstanceWithoutConstructor();
        $controller->initController(service('request'), service('response'), service('logger'));

        $response = $controller->create();

        $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
    }

    public function testPartialNewsResponseContainsOnlyContentAndSetsNavigationHeaders(): void
    {
        $request = service('request');
        $request->setHeader('X-Partial', '1');

        $response = service('response');
        $controller = new TestableAdminPageController();
        $controller->initController($request, $response, service('logger'));

        $body = $controller->showNews();

        $this->assertStringContainsString('news-page-title', $body);
        $this->assertStringContainsString('news-create-modal', $body);
        $this->assertStringContainsString('data-rm-open="#news-create-modal"', $body);
        $this->assertStringContainsString('name="cover_image"', $body);
        $this->assertStringContainsString('Simpan Berita', $body);
        $this->assertStringNotContainsString('<!doctype html>', strtolower($body));
        $this->assertStringNotContainsString('adminSidebar', $body);
        $this->assertSame('1', $response->getHeaderLine('X-Partial-Response'));
        $this->assertSame('Berita', rawurldecode($response->getHeaderLine('X-Page-Title')));
        $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
    }

    public function testNewsEditTriggerUsesSharedModalAndCarriesSelectedNewsSafely(): void
    {
        $controller = new TestableAdminPageController();
        $controller->initController(service('request'), service('response'), service('logger'));
        $news = [
            'id' => 12,
            'title' => 'Berita "uji"',
            'slug' => 'berita-uji',
            'summary' => 'Ringkasan uji',
            'content' => "Isi berita\nbaris kedua",
            'status' => 'Draft',
            'cover_image' => 'uploads/berita/cover.jpg',
        ];

        $body = $controller->showNews([$news]);

        $this->assertStringContainsString('data-rm-open="#news-create-modal"', $body);
        $this->assertStringContainsString('data-news-edit=', $body);
        $this->assertStringContainsString('data-news-form', $body);
        $this->assertStringNotContainsString('admin/berita/edit/', $body);
        preg_match('/data-news-edit="([^"]+)"/', $body, $matches);
        $payload = html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $this->assertSame($news, json_decode($payload, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testPartialSchedulePagesKeepTheSharedAdminShellOutOfTheResponse(): void
    {
        $request = service('request');
        $request->setHeader('X-Partial', '1');

        $response = service('response');
        $controller = new TestableAdminPageController();
        $controller->initController($request, $response, service('logger'));

        $schedule = $controller->showSchedule();
        $this->assertStringContainsString('schedule-page-title', $schedule);
        $this->assertStringContainsString('schedule-create-modal', $schedule);
        $this->assertStringNotContainsString('data-rm-default-open', $schedule);
        $this->assertStringNotContainsString('adminSidebar', $schedule);

        $scheduleWithCreateModal = view('components/ui/schedule-create-modal', [
            'defaultOpen' => true,
            'statusOptions' => [],
        ]);
        $this->assertStringContainsString('data-rm-default-open', $scheduleWithCreateModal);

        $form = $controller->showScheduleForm();
        $this->assertStringContainsString('schedule-form-title', $form);
        $this->assertStringNotContainsString('<!doctype html>', strtolower($form));
        $this->assertStringNotContainsString('adminSidebar', $form);
    }

    public function testPartialProfilePageKeepsTheSharedAdminShellOutAndLinksToDedicatedEditors(): void
    {
        $request = service('request');
        $request->setHeader('X-Partial', '1');

        $response = service('response');
        $controller = new TestableAdminPageController();
        $controller->initController($request, $response, service('logger'));

        $profile = $controller->showProfile();

        $this->assertStringContainsString('profile-page-title', $profile);
        $this->assertStringContainsString('admin/profil/kelola/leadership', $profile);
        $this->assertStringNotContainsString('profile-leadership-editor', $profile);
        $this->assertStringNotContainsString('periode-panel', $profile);
        $this->assertStringNotContainsString('<!doctype html>', strtolower($profile));
        $this->assertStringNotContainsString('adminSidebar', $profile);
        $this->assertSame('Profil & Majelis', rawurldecode($response->getHeaderLine('X-Page-Title')));
    }

    public function testAdminSidebarDoesNotExposeTheRemovedStatisticsModule(): void
    {
        $sidebar = view('admin/partials/sidebar');

        $this->assertStringNotContainsString('admin/statistik', $sidebar);
        $this->assertStringContainsString('Profil &amp; Majelis', $sidebar);
    }

}
