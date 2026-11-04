<?php

use App\Controllers\AdminBerita;
use App\Models\BeritaModel;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;

final class FakeBeritaModel extends BeritaModel
{
    public ?array $record = null;
    public array $updatedData = [];
    public array $insertedData = [];
    public bool $updateResult = true;
    public bool $insertResult = true;

    public function __construct()
    {
    }

    public function find($id = null)
    {
        return $this->record;
    }

    public function update($id = null, $row = null): bool
    {
        $this->updatedData = $row;

        return $this->updateResult;
    }

    public function insert($row = null, bool $returnID = true)
    {
        $this->insertedData = $row;

        return $this->insertResult ? 1 : false;
    }

    public function errors(bool $forceDB = false): array
    {
        return [];
    }
}

final class TestableAdminBeritaController extends AdminBerita
{
    public function __construct()
    {
    }

    public function setModel(BeritaModel $model): void
    {
        $this->model = $model;
    }
}

final class AdminBeritaUploadTest extends CIUnitTestCase
{
    private function saveController(
        FakeBeritaModel $model,
        array $post,
        ?IncomingRequest $request = null,
    ): TestableAdminBeritaController
    {
        session()->remove(['admin_id', 'success', 'error', 'news_error']);
        $request ??= service('request');
        if ($request instanceof IncomingRequest) {
            $request->setGlobal('post', $post);
        }

        $controller = new TestableAdminBeritaController();
        $controller->setModel($model);
        $controller->initController($request, service('response'), service('logger'));

        return $controller;
    }

    public function testCoverImageLargerThanTenMegabytesIsRejected(): void
    {
        $controller = new class extends AdminBerita {
            public function __construct()
            {
            }

            public function validateCoverUpload(UploadedFile $file): array
            {
                return $this->handleCoverUpload($file);
            }
        };
        $file = new class('', 'cover.jpg') extends UploadedFile {
            public function isValid(): bool
            {
                return true;
            }

            public function getExtension(): string
            {
                return 'jpg';
            }

            public function getMimeType(): string
            {
                return 'image/jpeg';
            }

            public function getSize(): false|int
            {
                return 10 * 1024 * 1024 + 1;
            }
        };

        $this->assertSame(
            ['success' => false, 'message' => 'Ukuran gambar maksimal 10 MB.'],
            $controller->validateCoverUpload($file),
        );
    }

    public function testArrayNewsIdIsRejectedWithoutWarningOrUpdate(): void
    {
        $model = new FakeBeritaModel();
        $controller = $this->saveController($model, [
            'id' => ['5'],
            'title' => 'Judul berita',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame([], $model->updatedData);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertSame('ID berita tidak valid.', session()->getFlashdata('error'));
    }

    public function testEditingWithoutNewImagePreservesExistingCover(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'cover_image' => 'uploads/berita/old.jpg'];
        $controller = $this->saveController($model, [
            'id' => '5',
            'title' => 'Judul diperbarui',
            'slug' => 'judul-diperbarui',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/dashboard',
        ]);
        session()->set('admin_id', 9);

        $response = $controller->save();

        $this->assertArrayNotHasKey('cover_image', $model->updatedData);
        $this->assertSame(site_url('admin/dashboard'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita berhasil diperbarui.', session()->getFlashdata('success'));
    }

    public function testEditingWithNewImageSavesNewCoverPath(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'cover_image' => 'uploads/berita/old.jpg'];
        $file = new class('', 'cover.jpg') extends UploadedFile {
            public function isValid(): bool
            {
                return true;
            }

            public function getExtension(): string
            {
                return 'jpg';
            }

            public function getMimeType(): string
            {
                return 'image/jpeg';
            }

            public function getSize(): false|int
            {
                return 1024;
            }

            public function getRandomName(): string
            {
                return 'replacement-cover.jpg';
            }

            public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
            {
                return true;
            }
        };
        $post = [
            'id' => '5',
            'title' => 'Judul diperbarui',
            'slug' => 'judul-diperbarui',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ];
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getPost')->willReturnCallback(static fn (?string $key = null) => $post[$key] ?? null);
        $request->method('getFile')->with('cover_image')->willReturn($file);
        $controller = $this->saveController($model, $post, $request);

        $response = $controller->save();

        $this->assertSame('uploads/berita/replacement-cover.jpg', $model->updatedData['cover_image']);
        $this->assertSame('Berita berhasil diperbarui.', session()->getFlashdata('success'));
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
    }

    public function testUploadMoveExceptionReturnsFormErrorInsteadOfServerFailure(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'cover_image' => 'uploads/berita/old.jpg'];
        $file = new class('', 'cover.jpg') extends UploadedFile {
            public function isValid(): bool
            {
                return true;
            }

            public function getExtension(): string
            {
                return 'jpg';
            }

            public function getMimeType(): string
            {
                return 'image/jpeg';
            }

            public function getSize(): false|int
            {
                return 1024;
            }

            public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
            {
                throw HTTPException::forMoveFailed('temporary', $targetPath, 'disk unavailable');
            }
        };
        $post = [
            'id' => '5',
            'title' => 'Judul diperbarui',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ];
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getPost')->willReturnCallback(static fn (?string $key = null) => $post[$key] ?? null);
        $request->method('getFile')->with('cover_image')->willReturn($file);
        $controller = $this->saveController($model, $post, $request);

        $response = $controller->save();

        $this->assertSame([], $model->updatedData);
        $this->assertSame(site_url('admin/berita?edit=5'), $response->getHeaderLine('Location'));
        $this->assertSame(
            'Gambar gagal dipindahkan ke server. Silakan coba lagi.',
            session()->getFlashdata('news_error'),
        );
    }

    public function testNewCoverIsRemovedWhenDatabaseUpdateFails(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'cover_image' => 'uploads/berita/old.jpg'];
        $model->updateResult = false;
        $coverName = 'news-cleanup-' . bin2hex(random_bytes(8)) . '.jpg';
        $storedFilePath = WRITEPATH . 'uploads/berita/' . $coverName;
        $file = new class('', 'cover.jpg', 'image/jpeg', 1024, UPLOAD_ERR_OK) extends UploadedFile {
            public string $targetName = '';

            public function isValid(): bool
            {
                return true;
            }

            public function getExtension(): string
            {
                return 'jpg';
            }

            public function getMimeType(): string
            {
                return 'image/jpeg';
            }

            public function getSize(): false|int
            {
                return 1024;
            }

            public function getRandomName(): string
            {
                return $this->targetName;
            }

            public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
            {
                return file_put_contents($targetPath . DIRECTORY_SEPARATOR . $name, 'test image') !== false;
            }
        };
        $file->targetName = $coverName;
        $post = [
            'id' => '5',
            'title' => 'Judul diperbarui',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ];
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getPost')->willReturnCallback(static fn (?string $key = null) => $post[$key] ?? null);
        $request->method('getFile')->with('cover_image')->willReturn($file);
        $controller = $this->saveController($model, $post, $request);

        try {
            $response = $controller->save();
            $coverRemains = is_file($storedFilePath);
        } finally {
            if (is_file($storedFilePath)) {
                unlink($storedFilePath);
            }
        }

        $this->assertSame(site_url('admin/berita?edit=5'), $response->getHeaderLine('Location'));
        $this->assertFalse($coverRemains);
    }

    public function testEditingUnknownNewsIdShowsFailureInsteadOfSuccess(): void
    {
        $model = new FakeBeritaModel();
        $controller = $this->saveController($model, [
            'id' => '99',
            'title' => 'Judul berita',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame([], $model->updatedData);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita tidak ditemukan.', session()->getFlashdata('error'));
    }

    public function testDatabaseSaveFailureReopensEditFormWithFailureMessage(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'cover_image' => 'uploads/berita/old.jpg'];
        $model->updateResult = false;
        $controller = $this->saveController($model, [
            'id' => '5',
            'title' => 'Judul diperbarui',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame(site_url('admin/berita?edit=5'), $response->getHeaderLine('Location'));
        $this->assertSame(
            'Berita gagal disimpan. Silakan periksa kembali data dan coba lagi.',
            session()->getFlashdata('news_error'),
        );
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testAddingNewsStillUsesExistingSaveEndpoint(): void
    {
        $model = new FakeBeritaModel();
        $controller = $this->saveController($model, [
            'id' => '',
            'title' => 'Berita baru',
            'slug' => '',
            'summary' => 'Ringkasan berita baru',
            'content' => 'Isi berita baru.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);
        session()->set('admin_id', 9);

        $response = $controller->save();

        $this->assertSame('berita-baru', $model->insertedData['slug']);
        $this->assertSame(9, $model->insertedData['created_by']);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita baru berhasil disimpan.', session()->getFlashdata('success'));
    }
}
