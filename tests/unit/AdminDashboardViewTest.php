<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AdminDashboardViewTest extends CIUnitTestCase
{
    public function testDashboardShowsLiveClockWithoutTimezoneLabel(): void
    {
        $html = view('admin/dashboard', [
            'name' => 'Admin',
            'todayLabel' => 'Kamis, 5 November 2026',
            'publishedNewsCount' => 0,
            'draftNewsCount' => 0,
            'upcomingScheduleCount' => 0,
            'latestNews' => [],
            'nextSchedule' => null,
            'scheduleError' => '',
            'newsError' => '',
            'success' => null,
            'error' => null,
            'newsModalOpen' => false,
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
        ]);

        $this->assertStringContainsString('id="admin-live-clock"', $html);
        $this->assertStringNotContainsString('admin-timezone', $html);
        $this->assertStringNotContainsString('resolvedOptions().timeZone', $html);
    }

    public function testDashboardDoesNotShowScheduleListLinkInNextEvent(): void
    {
        $html = view('admin/dashboard', [
            'name' => 'Admin',
            'todayLabel' => 'Kamis, 5 November 2026',
            'publishedNewsCount' => 0,
            'draftNewsCount' => 0,
            'upcomingScheduleCount' => 1,
            'latestNews' => [],
            'nextSchedule' => [
                'date_label' => 'Minggu, 8 November 2026',
                'jenis_ibadah' => 'Ibadah Minggu',
                'time_label' => '09.00',
                'lokasi' => 'Gereja',
            ],
            'scheduleError' => '',
            'newsError' => '',
            'success' => null,
            'error' => null,
            'newsModalOpen' => false,
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
        ]);

        $this->assertStringNotContainsString('Periksa semua jadwal', $html);
    }
}
