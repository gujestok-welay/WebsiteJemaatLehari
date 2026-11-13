<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AdminGalleryActionIconsTest extends CIUnitTestCase
{
    public function testGalleryEditAndArchiveActionsShowIconsAlongsideTheirLabels(): void
    {
        $html = html_entity_decode(view('admin/galeri/index', [
            'items' => [[
                'id' => 1,
                'title' => 'Kegiatan uji',
                'event_date' => '2026-11-07',
                'poster_type' => 'flayer',
                'status' => 'Terbit',
                'show_on_home' => 0,
            ]],
            'term' => '',
            'status' => '',
            'statusOptions' => ['Draft', 'Terbit', 'Arsip'],
            'success' => '',
            'error' => '',
            'name' => 'Admin',
        ]), ENT_QUOTES | ENT_HTML5);

        $this->assertMatchesRegularExpression(
            '/<a(?=[^>]*href="[^"]*admin\/galeri\/edit\/1")[^>]*>\s*<svg[^>]*aria-hidden="true"[^>]*>.*?<\/svg>\s*<span>Edit<\/span>\s*<\/a>/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/<button(?=[^>]*news-action-archive)[^>]*>\s*<svg[^>]*aria-hidden="true"[^>]*>.*?<\/svg>\s*<span>Arsipkan<\/span>\s*<\/button>/s',
            $html,
        );
    }
}
