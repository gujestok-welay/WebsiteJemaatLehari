<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AdminNewsActionIconsTest extends CIUnitTestCase
{
    public function testNewsEditAndArchiveActionsUseAccessibleIcons(): void
    {
        $html = html_entity_decode(view('admin/berita/index', [
            'items' => [[
                'id' => 1,
                'title' => 'Berita uji',
                'slug' => 'berita-uji',
                'summary' => 'Ringkasan uji',
                'content' => 'Isi berita uji',
                'status' => 'Terbit',
                'updated_at' => '2026-11-07 05:00:00',
            ]],
            'statusOptions' => ['Draft', 'Terbit', 'Arsip'],
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
            'term' => '',
            'status' => '',
            'newsError' => '',
            'newsModalOpen' => false,
            'name' => 'Admin',
        ]), ENT_QUOTES | ENT_HTML5);

        $this->assertMatchesRegularExpression(
            '/<button(?=[^>]*aria-label="Ubah berita: Berita uji")[^>]*>\s*<svg[^>]*aria-hidden="true"[^>]*>.*?<\/svg>\s*<\/button>/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/<button(?=[^>]*aria-label="Arsipkan berita: Berita uji")[^>]*>\s*<svg[^>]*aria-hidden="true"[^>]*>.*?<\/svg>\s*<\/button>/s',
            $html,
        );
        $this->assertStringNotContainsString('>Ubah</button>', $html);
        $this->assertStringNotContainsString('>Arsipkan</button>', $html);
    }

    public function testArchivedNewsRestoreActionShowsIconAlongsideItsLabel(): void
    {
        $html = html_entity_decode(view('admin/berita/index', [
            'items' => [[
                'id' => 2,
                'title' => 'Berita arsip',
                'slug' => 'berita-arsip',
                'summary' => 'Ringkasan berita arsip',
                'content' => 'Isi berita arsip',
                'status' => 'Arsip',
                'updated_at' => '2026-11-07 05:00:00',
            ]],
            'statusOptions' => ['Draft', 'Terbit', 'Arsip'],
            'formStatusOptions' => \App\Models\BeritaModel::formStatusOptions(),
            'term' => '',
            'status' => 'Arsip',
            'newsError' => '',
            'newsModalOpen' => false,
            'name' => 'Admin',
        ]), ENT_QUOTES | ENT_HTML5);

        $this->assertMatchesRegularExpression(
            '/<button(?=[^>]*class="[^"]*news-action-link[^"]*")[^>]*>\s*<svg[^>]*aria-hidden="true"[^>]*>.*?<\/svg>\s*<span>Pulihkan<\/span>\s*<\/button>/s',
            $html,
        );
    }
}
