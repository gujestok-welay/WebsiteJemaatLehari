<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class PublicNewsDetailTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for public news detail tests.');
        }

        config('Database')->defaultGroup = 'tests';
        $db = db_connect('tests');
        $forge = \Config\Database::forge('tests');
        if ($db->tableExists('berita')) {
            $forge->dropTable('berita');
        }

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'content' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'archived_from_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'cover_image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey('slug');
        $forge->createTable('berita');
    }

    public function testPublishedArticleIsAvailableAtItsSlug(): void
    {
        db_connect('tests')->table('berita')->insert([
            'title' => 'Transformasi Digital Desa',
            'slug' => 'transformasi-digital-desa',
            'summary' => 'Ringkasan artikel yang diterbitkan.',
            'content' => 'Isi lengkap artikel yang diterbitkan.',
            'status' => 'Terbit',
            'cover_image' => 'uploads/berita/berita-cover.png',
            'created_at' => '2026-11-05 09:00:00',
        ]);

        $response = $this->get('/berita/transformasi-digital-desa');

        $response->assertStatus(200);
        $response->assertSee('Transformasi Digital Desa');
        $response->assertSee('5 November 2026');
        $response->assertSee('Ringkasan artikel yang diterbitkan.');
        $response->assertSee('Isi lengkap artikel yang diterbitkan.');
        $response->assertSee('index.php/berita/gambar/berita-cover.png');
        $response->assertSee('Kembali ke daftar berita');
    }

    public function testCoverImageIsServedOnlyForPublishedNews(): void
    {
        $filename = 'public-news-test-' . bin2hex(random_bytes(5)) . '.png';
        $directory = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'berita';
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j0XQAAAAASUVORK5CYII=', true);
        $this->assertIsString($image);
        file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $image);

        try {
            $db = db_connect('tests');
            $db->table('berita')->insert([
                'title' => 'Berita dengan gambar',
                'slug' => 'berita-dengan-gambar',
                'summary' => 'Ringkasan berita dengan gambar.',
                'content' => 'Isi berita dengan gambar.',
                'status' => 'Terbit',
                'cover_image' => 'uploads/berita/' . $filename,
            ]);

            $imageResponse = $this->get('/berita/gambar/' . $filename);
            $imageResponse->assertStatus(200);
            $this->assertSame('image/png', $imageResponse->response()->getHeaderLine('Content-Type'));
            $this->assertSame($image, $imageResponse->response()->getBody());

            $db->table('berita')->where('slug', 'berita-dengan-gambar')->update(['status' => 'Draft']);
            $this->get('/berita/gambar/' . $filename)->assertStatus(404);
        } finally {
            if (is_file($directory . DIRECTORY_SEPARATOR . $filename)) {
                unlink($directory . DIRECTORY_SEPARATOR . $filename);
            }
        }
    }

    public function testDraftAndArchivedArticlesAreNotAvailableByTheirSlug(): void
    {
        $articles = [
            [
                'title' => 'Draft rahasia',
                'slug' => 'draft-rahasia',
                'summary' => 'Ringkasan draft rahasia.',
                'content' => 'Isi draft rahasia.',
                'status' => 'Draft',
                'archived_from_status' => null,
            ],
            [
                'title' => 'Berita ditarik',
                'slug' => 'berita-ditarik',
                'summary' => 'Ringkasan berita ditarik.',
                'content' => 'Isi berita ditarik.',
                'status' => 'Arsip',
                'archived_from_status' => 'Terbit',
            ],
        ];
        foreach ($articles as $article) {
            db_connect('tests')->table('berita')->insert($article);
        }

        foreach (['draft-rahasia', 'berita-ditarik'] as $slug) {
            $response = $this->get('/berita/' . $slug);

            $response->assertStatus(404);
            $response->assertSee('Berita tidak ditemukan');
            $this->assertStringNotContainsString('Ringkasan ' . str_replace('-', ' ', $slug), $response->getBody());
            $this->assertStringContainsString('href="http://example.com/index.php/berita"', $response->getBody());
        }
    }

    public function testUnknownArticleSlugShowsRecoveryLinkAndNotFoundStatus(): void
    {
        $response = $this->get('/berita/slug-yang-tidak-ada');

        $response->assertStatus(404);
        $response->assertSee('Berita tidak ditemukan');
        $response->assertSee('Lihat semua berita');
        $this->assertStringContainsString('href="http://example.com/index.php/berita"', $response->getBody());
    }
}
