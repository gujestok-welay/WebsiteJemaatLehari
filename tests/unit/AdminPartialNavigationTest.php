<?php

use App\Controllers\BaseController;
use CodeIgniter\Test\CIUnitTestCase;

final class TestableAdminPageController extends BaseController
{
    public function showNews(): string
    {
        return $this->renderAdminPage('admin/berita/index', [
            'items' => [],
            'term' => '',
            'status' => '',
            'statusOptions' => [],
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
            'hari' => $hari,
            'hitung' => ['semua' => 0, 'aktif' => 0, 'dibatalkan' => 0],
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

    public function showStatistics(array $items = [], ?array $editItem = null): string
    {
        return $this->renderAdminPage('admin/statistik/index', [
            'items' => $items,
            'editItem' => $editItem,
            'success' => null,
            'error' => null,
        ], 'Statistik');
    }
}

final class AdminPartialNavigationTest extends CIUnitTestCase
{
    public function testPartialNewsResponseContainsOnlyContentAndSetsNavigationHeaders(): void
    {
        $request = service('request');
        $request->setHeader('X-Partial', '1');

        $response = service('response');
        $controller = new TestableAdminPageController();
        $controller->initController($request, $response, service('logger'));

        $body = $controller->showNews();

        $this->assertStringContainsString('news-page-title', $body);
        $this->assertStringNotContainsString('<!doctype html>', strtolower($body));
        $this->assertStringNotContainsString('adminSidebar', $body);
        $this->assertSame('1', $response->getHeaderLine('X-Partial-Response'));
        $this->assertSame('Berita', rawurldecode($response->getHeaderLine('X-Page-Title')));
        $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
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
        $this->assertStringNotContainsString('adminSidebar', $schedule);

        $form = $controller->showScheduleForm();
        $this->assertStringContainsString('schedule-form-title', $form);
        $this->assertStringNotContainsString('<!doctype html>', strtolower($form));
        $this->assertStringNotContainsString('adminSidebar', $form);
    }

    public function testPartialProfilePageKeepsTheSharedAdminShellOutOfTheResponse(): void
    {
        $request = service('request');
        $request->setHeader('X-Partial', '1');

        $response = service('response');
        $controller = new TestableAdminPageController();
        $controller->initController($request, $response, service('logger'));

        $profile = $controller->showProfile();

        $this->assertStringContainsString('profile-page-title', $profile);
        $this->assertStringContainsString('periode-panel', $profile);
        $this->assertStringContainsString('anggota-panel', $profile);
        $this->assertStringContainsString('Tambahkan atau pilih periode Majelis', $profile);
        $this->assertStringNotContainsString('<!doctype html>', strtolower($profile));
        $this->assertStringNotContainsString('adminSidebar', $profile);
        $this->assertSame('Profil & Majelis', rawurldecode($response->getHeaderLine('X-Page-Title')));
    }

    public function testPartialStatisticsPageUsesSharedAdminShellAndSupportsEditing(): void
    {
        $request = service('request');
        $request->setHeader('X-Partial', '1');

        $response = service('response');
        $controller = new TestableAdminPageController();
        $controller->initController($request, $response, service('logger'));

        $body = $controller->showStatistics(
            [['id' => 7, 'metric_label' => 'Jumlah sektor', 'metric_value' => 4, 'unit_label' => 'sektor', 'updated_at' => '2026-11-03 10:00:00']],
            ['id' => 7, 'metric_label' => 'Jumlah sektor', 'metric_value' => 4, 'unit_label' => 'sektor']
        );

        $this->assertStringContainsString('statistics-page-title', $body);
        $this->assertStringContainsString('name="id" value="7"', $body);
        $this->assertStringContainsString('name="metric_label"', $body);
        $this->assertStringContainsString('value="Jumlah&#x20;sektor"', $body);
        $this->assertStringContainsString('admin/statistik?edit=7', $body);
        $this->assertStringContainsString('Batal mengubah', $body);
        $this->assertStringNotContainsString('<!doctype html>', strtolower($body));
        $this->assertStringNotContainsString('adminSidebar', $body);
        $this->assertSame('Statistik', rawurldecode($response->getHeaderLine('X-Page-Title')));
    }
}
