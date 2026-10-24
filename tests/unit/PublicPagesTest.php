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
            $result->assertSee($label . ' | Jemaat Leihari');
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
}
