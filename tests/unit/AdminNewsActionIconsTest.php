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
}
