<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AdminJadwalResponsiveTest extends CIUnitTestCase
{
    public function testScheduleResultCellsExposeResponsiveLabels(): void
    {
        $html = view('admin/jadwal/_schedule-results', [
            'term' => '',
            'hariDipilih' => 'semua',
            'cancelled' => null,
            'archived' => false,
            'items' => [[
                'id' => 1,
                'jenis_ibadah' => 'Ibadah Minggu',
                'tanggal_fmt' => '6 November 2026',
                'waktu_fmt' => '09:00',
                'lokasi' => 'Gedung Gereja',
                'pelayan' => 'Pdt. Ny. J. K. Tutupaty',
                'is_cancelled' => false,
                'deleted_at' => null,
            ]],
            'jumlahHasil' => 1,
        ]);

        foreach (['Ibadah', 'Tanggal &amp; waktu', 'Lokasi', 'Pelayan', 'Status', 'Tindakan'] as $label) {
            $this->assertStringContainsString('data-label="' . $label . '"', $html);
        }
    }

    public function testScheduleActionsShowLabelsAboveAccessibleIcons(): void
    {
        $html = html_entity_decode(view('admin/jadwal/_schedule-results', [
            'term' => '',
            'hariDipilih' => 'semua',
            'cancelled' => null,
            'archived' => false,
            'items' => [
                [
                    'id' => 1,
                    'jenis_ibadah' => 'Ibadah Minggu',
                    'tanggal_fmt' => '6 November 2026',
                    'waktu_fmt' => '09:00',
                    'lokasi' => 'Gedung Gereja',
                    'pelayan' => '',
                    'is_cancelled' => false,
                    'deleted_at' => null,
                ],
                [
                    'id' => 2,
                    'jenis_ibadah' => 'Ibadah Unit',
                    'tanggal_fmt' => '7 November 2026',
                    'waktu_fmt' => '09:00',
                    'lokasi' => 'Gedung Gereja',
                    'pelayan' => '',
                    'is_cancelled' => true,
                    'deleted_at' => null,
                ],
                [
                    'id' => 3,
                    'jenis_ibadah' => 'Ibadah Sektor',
                    'tanggal_fmt' => '8 November 2026',
                    'waktu_fmt' => '09:00',
                    'lokasi' => 'Gedung Gereja',
                    'pelayan' => '',
                    'is_cancelled' => false,
                    'deleted_at' => '2026-11-07 09:00:00',
                ],
            ],
            'jumlahHasil' => 3,
        ]), ENT_QUOTES | ENT_HTML5);

        foreach ([
            'Ubah jadwal: Ibadah Minggu',
            'Batalkan jadwal: Ibadah Minggu',
            'Arsipkan jadwal: Ibadah Minggu',
            'Aktifkan kembali jadwal: Ibadah Unit',
            'Pulihkan jadwal: Ibadah Sektor',
        ] as $label) {
            $this->assertStringContainsString('aria-label="' . $label . '"', $html);
        }

        foreach (['Ubah', 'Batalkan', 'Arsipkan', 'Aktifkan kembali', 'Pulihkan'] as $label) {
            $this->assertMatchesRegularExpression(
                '/<(?:a|button)\b(?=[^>]*class="[^"]*schedule-action-link[^"]*")[^>]*>\s*<span class="schedule-action-label">'
                    . preg_quote($label, '/')
                    . '<\/span>\s*<svg/s',
                $html,
            );
        }

        $this->assertSame(7, substr_count($html, 'aria-hidden="true" focusable="false"'));
        foreach (['>Ubah</a>', '>Batalkan</button>', '>Aktifkan</button>', '>Arsipkan</button>', '>Pulihkan</button>'] as $visibleLabel) {
            $this->assertStringNotContainsString($visibleLabel, $html);
        }
    }
}
