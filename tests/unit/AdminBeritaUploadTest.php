<?php

use App\Controllers\AdminBerita;
use App\Models\BeritaModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;

final class FakeBeritaModel extends BeritaModel
{
    public ?array $record = null;
    public array $updatedData = [];
    public array $insertedData = [];
    public array $slugsById = [];
    public array $validationErrors = [];
    public bool $updateResult = true;
    public bool $updateThrows = false;
    public bool $insertResult = true;
    public ?string $readStatus = null;
    public bool $coverCleanupThrows = false;

    public function __construct()
    {
    }

    public function find($id = null)
    {
        return $this->record;
    }

    public function findForAdmin(int $id): array
    {
        if ($this->readStatus !== null) {
            return [
                'status' => $this->readStatus,
                'record' => $this->readStatus === 'found' ? $this->record : null,
            ];
        }

        return [
            'status' => $this->record === null ? 'not_found' : 'found',
            'record' => $this->record,
        ];
    }

    public function update($id = null, $row = null): bool
    {
        $this->updatedData = $row;
        $this->validationErrors = [];

        if ($this->updateThrows) {
            throw new DatabaseException('Database unavailable.');
        }

        if (array_key_exists('slug', $row)) {
            foreach ($this->slugsById as $existingId => $slug) {
                if ((int) $existingId !== (int) $id && $slug === $row['slug']) {
                    $this->validationErrors = ['slug' => 'The slug field must contain a unique value.'];

                    return false;
                }
            }
        }

        if ($this->updateResult) {
            if (array_key_exists('slug', $row)) {
                $this->slugsById[$id] = $row['slug'];
            }
            if ($this->record !== null) {
                $this->record = array_merge($this->record, $row, ['id' => $id]);
            }
        }

        return $this->updateResult;
    }

    public function insert($row = null, bool $returnID = true)
    {
        $this->insertedData = $row;
        $this->validationErrors = [];

        if (in_array($row['slug'], $this->slugsById, true)) {
            $this->validationErrors = ['slug' => 'The slug field must contain a unique value.'];

            return false;
        }

        return $this->insertResult ? 1 : false;
    }

    public function errors(bool $forceDB = false): array
    {
        return $this->validationErrors;
    }

}

final class TestableAdminBeritaController extends AdminBerita
{
    public ?array $externalImageResponse = null;

    public function __construct()
    {
    }

    public function setModel(BeritaModel $model): void
    {
        $this->model = $model;
    }

    protected function downloadExternalImage(string $url): array
    {
        if ($this->externalImageResponse !== null) {
            return $this->externalImageResponse;
        }

        return parent::downloadExternalImage($url);
    }

    public function cleanupCover(string $storedPath): void
    {
        $this->removeStoredCover($storedPath);
    }

    protected function removeStoredCover(string $storedPath): void
    {
        if ($this->model instanceof FakeBeritaModel && $this->model->coverCleanupThrows) {
            throw new \ErrorException('Cover cleanup failed.');
        }

        parent::removeStoredCover($storedPath);
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

    public function testEditingWithExistingSlugPassesIdForUniqueValidationAndPreservesCover(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'slug' => 'same-news-slug', 'cover_image' => 'uploads/berita/old.jpg'];
        $model->slugsById = [5 => 'same-news-slug'];
        $controller = $this->saveController($model, [
            'id' => '5',
            'title' => 'Judul diperbarui',
            'slug' => 'same-news-slug',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/dashboard',
        ]);
        session()->set('admin_id', 9);

        $response = $controller->save();

        $this->assertSame('permit_empty|integer', $model->getValidationRules()['id']);
        $this->assertArrayHasKey('id', $model->updatedData, 'The update payload must include the current ID so {id} is substituted in is_unique validation.');
        $this->assertSame(5, $model->updatedData['id']);
        $this->assertArrayNotHasKey('cover_image', $model->updatedData);
        $this->assertSame(site_url('admin/dashboard'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita berhasil diperbarui.', session()->getFlashdata('success'));
    }

    public function testEditingDraftToPublishedKeepsSlugAndSavesStatus(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'slug' => 'existing-news'];
        $model->slugsById = [5 => 'existing-news'];
        $controller = $this->saveController($model, [
            'id' => '5',
            'title' => 'Berita diperbarui',
            'slug' => 'existing-news',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Terbit',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame('Terbit', $model->updatedData['status']);
        $this->assertSame('existing-news', $model->updatedData['slug']);
        $this->assertSame('Berita berhasil diperbarui.', session()->getFlashdata('success'));
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
    }

    public function testCoverCleanupErrorDoesNotChangeSuccessfulDatabaseSave(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'cover_image' => 'uploads/berita/old.jpg'];
        $model->coverCleanupThrows = true;
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

        try {
            $response = $controller->save();
        } catch (\ErrorException) {
            $response = null;
        }

        $this->assertNotNull($response, 'A cover cleanup warning must not escape after the database save succeeds.');
        $this->assertSame('Berita berhasil diperbarui.', session()->getFlashdata('success'));
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
    }

    public function testEditingPublishedToDraftKeepsSlugAndSavesStatus(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'slug' => 'existing-news'];
        $model->slugsById = [5 => 'existing-news'];
        $controller = $this->saveController($model, [
            'id' => '5',
            'title' => 'Berita diperbarui',
            'slug' => 'existing-news',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame('Draft', $model->updatedData['status']);
        $this->assertSame('existing-news', $model->updatedData['slug']);
        $this->assertSame('Berita berhasil diperbarui.', session()->getFlashdata('success'));
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
    }

    public function testChangingTitleWithoutSlugGeneratesNewSlug(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'slug' => 'old-news-title'];
        $model->slugsById = [5 => 'old-news-title'];
        $controller = $this->saveController($model, [
            'id' => '5',
            'title' => 'Judul Berita Baru',
            'slug' => '',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame('judul-berita-baru', $model->updatedData['slug']);
        $this->assertSame('Berita berhasil diperbarui.', session()->getFlashdata('success'));
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
    }

    public function testEditingWithSlugOwnedByAnotherNewsIsRejected(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'slug' => 'current-news'];
        $model->slugsById = [5 => 'current-news', 8 => 'another-news'];
        $controller = $this->saveController($model, [
            'id' => '5',
            'title' => 'Berita diperbarui',
            'slug' => 'another-news',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame(site_url('admin/berita?edit=5'), $response->getHeaderLine('Location'));
        $this->assertSame('The slug field must contain a unique value.', session()->getFlashdata('news_error'));
        $this->assertSame('current-news', $model->slugsById[5]);
    }

    public function testAddingNewsWithDuplicateSlugIsRejected(): void
    {
        $model = new FakeBeritaModel();
        $model->slugsById = [8 => 'existing-news'];
        $controller = $this->saveController($model, [
            'id' => '',
            'title' => 'Berita baru',
            'slug' => 'existing-news',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame(site_url('admin/berita?create=1'), $response->getHeaderLine('Location'));
        $this->assertSame('The slug field must contain a unique value.', session()->getFlashdata('news_error'));
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

    public function testSaveShowsGenericErrorWhenNewsLookupFails(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 5, 'status' => 'Draft'];
        $model->readStatus = 'error';
        $controller = $this->saveController($model, [
            'id' => '5',
            'title' => 'Judul berita',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame([], $model->updatedData);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita gagal dibaca. Silakan coba lagi.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testArchiveShowsGenericErrorWhenNewsLookupFails(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Draft'];
        $model->readStatus = 'error';
        $controller = $this->saveController($model, []);

        $response = $controller->archive(17);

        $this->assertSame([], $model->updatedData);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita gagal diarsipkan. Silakan coba lagi.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testRestoreShowsGenericErrorWhenNewsLookupFails(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Arsip', 'archived_from_status' => 'Draft'];
        $model->readStatus = 'error';
        $controller = $this->saveController($model, []);

        $response = $controller->restore(17);

        $this->assertSame([], $model->updatedData);
        $this->assertSame(site_url('admin/berita?status=Arsip'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita gagal dibaca. Silakan coba lagi.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testArchiveChangesStatusAndPreservesPreviousStatus(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Terbit', 'archived_from_status' => null];
        $controller = $this->saveController($model, []);

        $response = $controller->archive(17);

        $this->assertSame('Arsip', $model->updatedData['status'] ?? null);
        $this->assertSame('Terbit', $model->updatedData['archived_from_status'] ?? null);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita berhasil diarsipkan.', session()->getFlashdata('success'));
        $this->assertFalse((bool) session()->getFlashdata('error'));
    }

    public function testArchiveChangesDraftStatusWithoutDeletingRecord(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Draft', 'archived_from_status' => null];
        $controller = $this->saveController($model, []);

        $response = $controller->archive(17);

        $this->assertSame('Arsip', $model->updatedData['status'] ?? null);
        $this->assertSame('Draft', $model->updatedData['archived_from_status'] ?? null);
        $this->assertSame(17, $model->record['id']);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
    }

    public function testArchiveFailureShowsErrorInsteadOfSuccess(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Draft', 'archived_from_status' => null];
        $model->updateResult = false;
        $controller = $this->saveController($model, []);

        $response = $controller->archive(17);

        $this->assertSame('Arsip', $model->updatedData['status'] ?? null);
        $this->assertSame('Draft', $model->updatedData['archived_from_status'] ?? null);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertSame('Berita gagal diarsipkan. Silakan coba lagi.', session()->getFlashdata('error'));
    }

    public function testArchiveDatabaseExceptionShowsErrorInsteadOfSuccess(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Draft', 'archived_from_status' => null];
        $model->updateThrows = true;
        $controller = $this->saveController($model, []);

        $response = $controller->archive(17);

        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertSame('Berita gagal diarsipkan. Silakan coba lagi.', session()->getFlashdata('error'));
    }

    public function testArchiveOfUnknownNewsIdShowsFailure(): void
    {
        $model = new FakeBeritaModel();
        $controller = $this->saveController($model, []);

        $response = $controller->delete(0);

        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita tidak ditemukan.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testRestoreActionExistsAndRestoresPreviousStatus(): void
    {
        $this->assertTrue(method_exists(AdminBerita::class, 'restore'), 'Archived news needs a restore action.');

        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Arsip', 'archived_from_status' => 'Terbit'];
        $controller = $this->saveController($model, []);

        $response = $controller->restore(17);

        $this->assertSame('Terbit', $model->updatedData['status'] ?? null);
        $this->assertNull($model->updatedData['archived_from_status'] ?? null);
        $this->assertSame(site_url('admin/berita?status=Arsip'), $response->getHeaderLine('Location'));
        $this->assertSame('Berita berhasil dipulihkan ke status Terbit.', session()->getFlashdata('success'));
    }

    public function testRestoreWithoutPreviousStatusDefaultsToDraft(): void
    {
        $this->assertTrue(method_exists(AdminBerita::class, 'restore'), 'Archived news needs a restore action.');

        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Arsip', 'archived_from_status' => null];
        $controller = $this->saveController($model, []);

        $response = $controller->restore(17);

        $this->assertSame('Draft', $model->updatedData['status'] ?? null);
        $this->assertNull($model->updatedData['archived_from_status'] ?? null);
        $this->assertSame(site_url('admin/berita?status=Arsip'), $response->getHeaderLine('Location'));
    }

    public function testRestoreFailureDoesNotShowSuccess(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Arsip', 'archived_from_status' => 'Terbit'];
        $model->updateResult = false;
        $controller = $this->saveController($model, []);

        $response = $controller->restore(17);

        $this->assertSame('Terbit', $model->updatedData['status']);
        $this->assertSame(site_url('admin/berita?status=Arsip'), $response->getHeaderLine('Location'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertSame('Berita gagal dipulihkan. Silakan coba lagi.', session()->getFlashdata('error'));
    }

    public function testArchivedNewsMustBeRestoredBeforeEditing(): void
    {
        $model = new FakeBeritaModel();
        $model->record = ['id' => 17, 'status' => 'Arsip', 'archived_from_status' => 'Terbit'];
        $controller = $this->saveController($model, [
            'id' => '17',
            'title' => 'Judul diperbarui',
            'slug' => 'judul-diperbarui',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Draft',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame([], $model->updatedData);
        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertSame('Pulihkan berita terlebih dahulu sebelum mengubahnya.', session()->getFlashdata('error'));
    }

    public function testArchiveCannotBeSubmittedThroughNewsForm(): void
    {
        $model = new FakeBeritaModel();
        $controller = $this->saveController($model, [
            'id' => '',
            'title' => 'Berita baru',
            'slug' => 'berita-baru',
            'summary' => 'Ringkasan berita',
            'content' => 'Isi berita.',
            'status' => 'Arsip',
            'return_to' => 'admin/berita',
        ]);

        $response = $controller->save();

        $this->assertSame([], $model->insertedData);
        $this->assertSame(site_url('admin/berita?create=1'), $response->getHeaderLine('Location'));
        $this->assertSame('Judul, ringkasan, isi, dan status berita wajib diisi.', session()->getFlashdata('news_error'));
    }

    public function testNewsFormStatusOptionsExcludeArchiveAndModelStillAllowsArchiveStatus(): void
    {
        $this->assertTrue(method_exists(BeritaModel::class, 'formStatusOptions'), 'The form needs a status list separate from the admin filter.');
        $this->assertSame(['Draft', 'Terbit'], BeritaModel::formStatusOptions());
        $this->assertSame(['Draft', 'Terbit', 'Arsip'], BeritaModel::statusOptions());
        $this->assertSame('required|in_list[Draft,Terbit,Arsip]', (new BeritaModel())->getValidationRules()['status']);
        $this->assertSame('permit_empty|in_list[Draft,Terbit]', (new BeritaModel())->getValidationRules()['archived_from_status']);
        $allowedFields = $this->getPrivateProperty(new BeritaModel(), 'allowedFields');
        $this->assertContains('archived_from_status', $allowedFields);
        $this->assertNotContains('deleted_at', $allowedFields);

        $form = view('components/ui/news-create-modal', [
            'formStatusOptions' => BeritaModel::formStatusOptions(),
        ]);
        $this->assertStringContainsString('Draft', $form);
        $this->assertStringContainsString('Terbit', $form);
        $this->assertStringNotContainsString('Arsip', $form);
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

    public function testSavingExternalNewsKeepsItsSourceAttribution(): void
    {
        $model = new FakeBeritaModel();
        $controller = $this->saveController($model, [
            'id' => '',
            'title' => 'Berita Sinode',
            'slug' => '',
            'summary' => 'Ringkasan yang ditulis Media Center.',
            'content' => 'Ringkasan yang ditulis Media Center.',
            'status' => 'Draft',
            'article_type' => 'external',
            'source_url' => 'https://sinodegpm.org/berita/pelayanan',
            'source_name' => 'Sinode GPM',
            'return_to' => 'admin/berita',
        ]);
        session()->set('admin_id', 9);

        $controller->save();

        $this->assertSame('external', $model->insertedData['article_type'] ?? null);
        $this->assertSame('https://sinodegpm.org/berita/pelayanan', $model->insertedData['source_url'] ?? null);
        $this->assertSame('Sinode GPM', $model->insertedData['source_name'] ?? null);
    }

    public function testSavingExternalNewsStoresTheSelectedSourceCoverLocally(): void
    {
        $model = new FakeBeritaModel();
        $controller = $this->saveController($model, [
            'id' => '',
            'title' => 'Berita dari sumber lain',
            'slug' => '',
            'summary' => 'Ringkasan singkat dari metadata halaman.',
            'content' => 'Ringkasan singkat dari metadata halaman.',
            'status' => 'Draft',
            'article_type' => 'external',
            'source_url' => 'https://pelagandong.com/berita',
            'source_name' => 'Pelagandong',
            'source_image_url' => 'https://images.example.org/cover.png',
            'use_source_image' => '1',
            'return_to' => 'admin/berita',
        ]);
        $controller->externalImageResponse = [
            'contents' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC', true),
            'mime' => 'image/png',
        ];
        session()->set('admin_id', 9);

        $response = $controller->save();
        $coverPath = $model->insertedData['cover_image'] ?? '';

        $this->assertSame(site_url('admin/berita'), $response->getHeaderLine('Location'));
        $this->assertMatchesRegularExpression('~\Auploads/berita/[A-Za-z0-9._-]+\.png\z~', $coverPath);
        $this->assertFileExists(WRITEPATH . $coverPath);
        $controller->cleanupCover($coverPath);
    }

    public function testCreatingNewsFormOffersExternalSourceAndMetadataLookup(): void
    {
        $html = view('components/ui/news-create-modal', [
            'formStatusOptions' => BeritaModel::formStatusOptions(),
        ]);

        $this->assertStringContainsString('name="article_type"', $html);
        $this->assertStringContainsString('name="source_url"', $html);
        $this->assertStringContainsString('Ambil judul dan sumber dari tautan', $html);
        $this->assertStringContainsString('name="source_name"', $html);
    }

    public function testMetadataLookupRejectsPrivateNetworkUrls(): void
    {
        $request = service('request');
        $request->setGlobal('post', ['url' => 'http://127.0.0.1/admin']);
        $controller = new TestableAdminBeritaController();
        $controller->setModel(new FakeBeritaModel());
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->metadata();
        $body = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Tautan harus menggunakan HTTP atau HTTPS dan domain publik.', $body['message']);
        $this->assertSame('csrf_test_name', $body['csrf_token']);
        $this->assertNotEmpty($body['csrf_hash']);
    }
}
