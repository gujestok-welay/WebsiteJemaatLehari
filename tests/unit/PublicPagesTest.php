<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class PublicPagesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

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
