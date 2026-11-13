<?php

use App\Controllers\AdminJadwal;
use App\Models\JadwalIbadahModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class TestableAdminJadwal extends AdminJadwal
{
    public function __construct()
    {
    }

    public function setModel(JadwalIbadahModel $model): void
    {
        $this->model = $model;
    }
}

final class AdminJadwalCrudTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $originalDatabaseGroup;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for isolated schedule CRUD tests.');
        }

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;
        $database->defaultGroup = 'tests';

        $db = db_connect('tests');
        $forge = \Config\Database::forge('tests');
        $db->resetDataCache();
        if ($db->tableExists('jadwal_ibadah')) {
            $forge->dropTable('jadwal_ibadah');
        }
        $db->resetDataCache();

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'jenis_ibadah' => ['type' => 'VARCHAR', 'constraint' => 120],
            'tanggal' => ['type' => 'DATE'],
            'waktu' => ['type' => 'TIME'],
            'lokasi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'pelayan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_cancelled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('jadwal_ibadah');
        $db->resetDataCache();
        session()->remove(['admin_id', 'success', 'error', 'schedule_error']);
        session()->set('admin_id', 7);
    }

    protected function tearDown(): void
    {
        config('Database')->defaultGroup = $this->originalDatabaseGroup;
        session()->remove(['admin_id', 'success', 'error', 'schedule_error']);

        parent::tearDown();
    }

    private function controller(array $post = [], array $get = []): TestableAdminJadwal
    {
        $request = service('request');
        if ($request instanceof IncomingRequest) {
            $request->setGlobal('post', $post);
            $request->setGlobal('get', $get);
        }

        $controller = new TestableAdminJadwal();
        $controller->setModel(new JadwalIbadahModel());
        $controller->initController($request, service('response'), service('logger'));

        return $controller;
    }

    private function validPost(array $overrides = []): array
    {
        return array_merge([
            'jenis_ibadah' => 'Ibadah Minggu',
            'tanggal' => '2026-12-06',
            'waktu' => '09:00',
            'lokasi' => 'Gedung Gereja',
            'pelayan' => '',
            'is_cancelled' => '0',
            'return_to' => 'admin/jadwal',
        ], $overrides);
    }

    private function insertSchedule(array $overrides = []): int
    {
        $data = array_merge([
            'jenis_ibadah' => 'Ibadah Minggu',
            'tanggal' => '2026-12-06',
            'waktu' => '09:00:00',
            'lokasi' => 'Gedung Gereja',
            'pelayan' => null,
            'is_cancelled' => 0,
            'created_by' => 7,
            'updated_by' => 7,
        ], $overrides);
        $db = db_connect('tests');
        $db->table('jadwal_ibadah')->insert($data);

        return (int) $db->insertID();
    }

    public function testInsertSuccessPersistsScheduleAndShowsSuccess(): void
    {
        $response = $this->controller($this->validPost())->save();
        $rows = db_connect('tests')->table('jadwal_ibadah')->get()->getResultArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertCount(1, $rows);
        $this->assertSame('Ibadah Minggu', $rows[0]['jenis_ibadah']);
        $this->assertSame('Jadwal baru berhasil disimpan.', session()->getFlashdata('success'));
    }

    public function testInsertValidationFailureShowsFieldErrorAndDoesNotPersist(): void
    {
        $response = $this->controller($this->validPost(['jenis_ibadah' => 'A']))->save();
        $db = db_connect('tests');

        $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
        $this->assertSame(0, $db->table('jadwal_ibadah')->countAllResults());
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertStringContainsString('Jenis ibadah', (string) session()->getFlashdata('schedule_error'));
    }

    public function testScheduleFilterRequestReturnsOnlyFilterAndResultRegions(): void
    {
        $this->insertSchedule(['jenis_ibadah' => 'Jadwal aktif']);
        $this->insertSchedule([
            'jenis_ibadah' => 'Jadwal arsip',
            'deleted_at' => '2026-11-06 09:00:00',
        ]);
        service('request')->setHeader('X-Schedule-Partial', '1');

        $response = service('response');
        $controller = $this->controller(get: ['archived' => '1', 'hari' => 'minggu']);
        $html = $controller->index();

        $this->assertSame('1', $response->getHeaderLine('X-Schedule-Partial-Response'));
        $this->assertStringContainsString('data-schedule-controls', $html);
        $this->assertStringContainsString('data-schedule-toolbar', $html);
        $this->assertStringContainsString('data-schedule-results', $html);
        $this->assertStringContainsString('Jadwal arsip', $html);
        $this->assertStringNotContainsString('Jadwal aktif', $html);
        $this->assertStringContainsString('cancelled&#x3D;0&amp;hari&#x3D;minggu', $html);
        $this->assertStringNotContainsString('schedule-page-title', $html);
        $this->assertStringNotContainsString('schedule-metrics', $html);
        $this->assertStringNotContainsString('schedule-create-modal', $html);
    }

    public function testUpdateSuccessPersistsChangesAndShowsSuccess(): void
    {
        $id = $this->insertSchedule();
        $response = $this->controller($this->validPost([
            'id' => (string) $id,
            'jenis_ibadah' => 'Ibadah Keluarga',
        ]))->save();
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('Ibadah Keluarga', $row['jenis_ibadah']);
        $this->assertSame('Jadwal berhasil diperbarui.', session()->getFlashdata('success'));
    }

    public function testUpdateWithMissingIdReturnsRedirectResponseWithoutCreatingRow(): void
    {
        $response = $this->controller($this->validPost(['id' => '999']))->edit(999);

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('Jadwal tidak ditemukan.', session()->getFlashdata('error'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
    }

    public function testSaveWithMissingRecordIdDoesNotCreateSchedule(): void
    {
        $response = $this->controller($this->validPost(['id' => '999']))->save();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertSame('Jadwal tidak ditemukan.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testUpdateValidationFailureKeepsOriginalRowAndShowsValidationError(): void
    {
        $id = $this->insertSchedule();
        $response = $this->controller($this->validPost([
            'id' => (string) $id,
            'jenis_ibadah' => 'A',
        ]))->save();
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal/edit/' . $id), $response->getHeaderLine('Location'));
        $this->assertSame('Ibadah Minggu', $row['jenis_ibadah']);
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertStringContainsString('Jenis ibadah', (string) session()->getFlashdata('error'));
    }

    public function testInvalidIdIsRejectedInsteadOfTreatedAsInsert(): void
    {
        $response = $this->controller($this->validPost(['id' => 'not-an-id']))->save();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertSame('ID jadwal tidak valid.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testArrayShapedIdIsRejectedInsteadOfTreatedAsInsert(): void
    {
        $response = $this->controller($this->validPost(['id' => ['1']]))->save();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertSame('ID jadwal tidak valid.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testEmptySubmittedIdIsRejectedInsteadOfTreatedAsInsert(): void
    {
        $response = $this->controller($this->validPost(['id' => '']))->save();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertSame('ID jadwal tidak valid.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testInvalidCancellationStatusIsRejectedInsteadOfNormalizedToActive(): void
    {
        $response = $this->controller($this->validPost(['is_cancelled' => '2']))->save();

        $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertSame('Status jadwal tidak valid.', session()->getFlashdata('schedule_error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testInvalidTimeFormatsAreRejectedWithoutPersisting(): void
    {
        foreach (['9am', '24:00', '09:60', '09:00:00x'] as $time) {
            session()->remove(['success', 'schedule_error']);
            $response = $this->controller($this->validPost(['waktu' => $time]))->save();

            $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
            $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
            $this->assertStringContainsString('Waktu', (string) session()->getFlashdata('schedule_error'));
        }
    }

    public function testInvalidDateIsRejectedWithoutPersisting(): void
    {
        foreach (['2026-02-30', '06/12/2026'] as $date) {
            session()->remove(['success', 'schedule_error']);
            $response = $this->controller($this->validPost(['tanggal' => $date]))->save();

            $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
            $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
            $this->assertStringContainsString('Tanggal', (string) session()->getFlashdata('schedule_error'));
        }
    }

    public function testWhitespaceOnlyRequiredFieldsAreRejected(): void
    {
        foreach ([
            ['jenis_ibadah', " \t\n "],
            ['jenis_ibadah', "\u{00A0}\u{2003}"],
            ['tanggal', " \t\n "],
            ['waktu', " \t\n "],
            ['lokasi', " \t\n "],
        ] as [$field, $value]) {
            $response = $this->controller($this->validPost([$field => $value]))->save();

            $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
            $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
            $this->assertFalse((bool) session()->getFlashdata('success'));
        }
    }

    public function testInvalidUtf8ScheduleTextIsRejected(): void
    {
        $response = $this->controller($this->validPost(['lokasi' => "\xFF\xFE"]))->save();

        $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testArrayShapedFieldsAndStatusAreRejected(): void
    {
        foreach (['jenis_ibadah', 'tanggal', 'waktu', 'lokasi', 'pelayan', 'is_cancelled'] as $field) {
            $response = $this->controller($this->validPost([$field => ['unexpected']]))->save();

            $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
            $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
            $this->assertFalse((bool) session()->getFlashdata('success'));
        }
    }

    public function testControlCharactersInScheduleTextAreRejected(): void
    {
        $response = $this->controller($this->validPost(['lokasi' => "Gedung\tGereja"]))->save();

        $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testScheduleTextFieldLengthBoundariesAreEnforced(): void
    {
        $validResponse = $this->controller($this->validPost([
            'jenis_ibadah' => str_repeat('J', 120),
            'lokasi' => str_repeat('L', 255),
            'pelayan' => str_repeat('P', 255),
        ]))->save();
        $this->assertSame(site_url('admin/jadwal'), $validResponse->getHeaderLine('Location'));
        $this->assertSame(1, db_connect('tests')->table('jadwal_ibadah')->countAllResults());

        foreach ([
            'jenis_ibadah' => str_repeat('J', 121),
            'lokasi' => str_repeat('L', 256),
            'pelayan' => str_repeat('P', 256),
        ] as $field => $value) {
            db_connect('tests')->table('jadwal_ibadah')->truncate();
            session()->remove(['success', 'schedule_error']);
            $response = $this->controller($this->validPost([$field => $value]))->save();

            $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
            $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
            $this->assertFalse((bool) session()->getFlashdata('success'));
        }
    }

    public function testSpecialCharactersAreStoredAndAuditFieldsComeFromSession(): void
    {
        $response = $this->controller($this->validPost([
            'jenis_ibadah' => 'Ibadah <script>alert(1)</script> & Remaja',
            'lokasi' => "Gedung #2 (Sektor 3)",
            'pelayan' => "Pdt. O'Neil",
            'created_by' => '999',
            'updated_by' => '999',
        ]))->save();
        $row = db_connect('tests')->table('jadwal_ibadah')->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('Ibadah <script>alert(1)</script> & Remaja', $row['jenis_ibadah']);
        $this->assertSame("Gedung #2 (Sektor 3)", $row['lokasi']);
        $this->assertSame("Pdt. O'Neil", $row['pelayan']);
        $this->assertSame('7', (string) $row['created_by']);
        $this->assertSame('7', (string) $row['updated_by']);
        $html = $this->controller()->index();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);

        $id = (int) $row['id'];
        $db = db_connect('tests');
        $db->table('jadwal_ibadah')->where('id', $id)->update(['created_by' => 5]);
        $this->controller($this->validPost([
            'id' => (string) $id,
            'created_by' => '999',
            'updated_by' => '999',
        ]))->save();
        $updated = $db->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame('5', (string) $updated['created_by']);
        $this->assertSame('7', (string) $updated['updated_by']);
    }

    public function testDeleteDatabaseFailureKeepsRowAndDoesNotShowSuccess(): void
    {
        $id = $this->insertSchedule();
        $db = db_connect('tests');
        $db->query(
            'CREATE TRIGGER reject_schedule_archive BEFORE UPDATE ON ' . $db->getPrefix() . "jadwal_ibadah
             BEGIN SELECT RAISE(ABORT, 'forced archive failure'); END"
        );

        $response = $this->controller()->delete($id);
        $row = $db->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertNull($row['deleted_at']);
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertSame('Jadwal gagal diarsipkan. Silakan coba lagi.', session()->getFlashdata('error'));
    }

    public function testInsertDatabaseFailureDoesNotShowSuccessOrPersistRow(): void
    {
        $db = db_connect('tests');
        $db->query(
            'CREATE TRIGGER reject_schedule_insert BEFORE INSERT ON ' . $db->getPrefix() . "jadwal_ibadah
             BEGIN SELECT RAISE(ABORT, 'forced insert failure'); END"
        );

        $response = $this->controller($this->validPost())->save();

        $this->assertSame(site_url('admin/jadwal?create=1'), $response->getHeaderLine('Location'));
        $this->assertSame(0, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertSame('Jadwal gagal disimpan. Silakan coba lagi.', session()->getFlashdata('schedule_error'));
    }

    public function testUpdateDatabaseFailureKeepsOriginalRowAndDoesNotShowSuccess(): void
    {
        $id = $this->insertSchedule();
        $db = db_connect('tests');
        $db->query(
            'CREATE TRIGGER reject_schedule_update BEFORE UPDATE ON ' . $db->getPrefix() . "jadwal_ibadah
             BEGIN SELECT RAISE(ABORT, 'forced update failure'); END"
        );

        $response = $this->controller($this->validPost([
            'id' => (string) $id,
            'jenis_ibadah' => 'Ibadah Keluarga',
        ]))->save();
        $row = $db->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal/edit/' . $id), $response->getHeaderLine('Location'));
        $this->assertSame('Ibadah Minggu', $row['jenis_ibadah']);
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertSame('Jadwal gagal disimpan. Silakan coba lagi.', session()->getFlashdata('error'));
    }

    public function testEditDatabaseReadFailureReturnsAnErrorRedirect(): void
    {
        $db = db_connect('tests');
        \Config\Database::forge('tests')->dropTable('jadwal_ibadah');
        $db->resetDataCache();

        $response = $this->controller()->edit(1);

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('Jadwal gagal dimuat. Silakan coba lagi.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testDeleteDatabaseReadFailureReturnsAnErrorInsteadOfThrowing(): void
    {
        $db = db_connect('tests');
        \Config\Database::forge('tests')->dropTable('jadwal_ibadah');
        $db->resetDataCache();

        $response = $this->controller()->delete(1);

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('Jadwal gagal diarsipkan. Silakan coba lagi.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testCancelChangesActiveScheduleToCancelled(): void
    {
        $id = $this->insertSchedule();

        $response = $this->controller()->cancel($id);
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('1', (string) $row['is_cancelled']);
        $this->assertNull($row['deleted_at']);
        $this->assertSame('Jadwal berhasil dibatalkan.', session()->getFlashdata('success'));
    }

    public function testReactivateChangesCancelledScheduleToActive(): void
    {
        $id = $this->insertSchedule(['is_cancelled' => 1]);

        $response = $this->controller()->reactivate($id);
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('0', (string) $row['is_cancelled']);
        $this->assertNull($row['deleted_at']);
        $this->assertSame('Jadwal berhasil diaktifkan kembali.', session()->getFlashdata('success'));
    }

    public function testArchivePreservesActiveStateAndDoesNotDeleteSchedule(): void
    {
        $id = $this->insertSchedule();

        $response = $this->controller()->archive($id);
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertNotNull($row['deleted_at']);
        $this->assertSame('0', (string) $row['is_cancelled']);
        $this->assertSame(1, db_connect('tests')->table('jadwal_ibadah')->countAllResults());
        $this->assertSame('Jadwal berhasil diarsipkan.', session()->getFlashdata('success'));
    }

    public function testArchivePreservesCancelledStateForRestore(): void
    {
        $id = $this->insertSchedule(['is_cancelled' => 1]);

        $this->controller()->archive($id);
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertNotNull($row['deleted_at']);
        $this->assertSame('1', (string) $row['is_cancelled']);
    }

    public function testRestoreArchivedActiveScheduleReturnsItToActiveState(): void
    {
        $id = $this->insertSchedule();
        $this->controller()->archive($id);

        $response = $this->controller()->restore($id);
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertNull($row['deleted_at']);
        $this->assertSame('0', (string) $row['is_cancelled']);
        $this->assertSame('Jadwal berhasil dipulihkan.', session()->getFlashdata('success'));
    }

    public function testRestoreArchivedCancelledScheduleReturnsItToCancelledState(): void
    {
        $id = $this->insertSchedule(['is_cancelled' => 1]);
        $this->controller()->archive($id);

        $this->controller()->restore($id);
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertNull($row['deleted_at']);
        $this->assertSame('1', (string) $row['is_cancelled']);
    }

    public function testStatusActionsRejectMissingScheduleWithoutSuccess(): void
    {
        $response = $this->controller()->cancel(999);

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('Jadwal tidak ditemukan.', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->getFlashdata('success'));
    }

    public function testStatusDatabaseFailureDoesNotShowSuccessOrChangeSchedule(): void
    {
        $id = $this->insertSchedule();
        $db = db_connect('tests');
        $db->query(
            'CREATE TRIGGER reject_schedule_cancel BEFORE UPDATE ON ' . $db->getPrefix() . "jadwal_ibadah
             BEGIN SELECT RAISE(ABORT, 'forced cancel failure'); END"
        );

        $response = $this->controller()->cancel($id);
        $row = $db->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('0', (string) $row['is_cancelled']);
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertSame('Jadwal gagal diperbarui. Silakan coba lagi.', session()->getFlashdata('error'));
    }

    public function testRestoreDatabaseFailureKeepsScheduleArchivedAndDoesNotShowSuccess(): void
    {
        $id = $this->insertSchedule();
        $this->controller()->archive($id);
        session()->remove('success');
        $db = db_connect('tests');
        $db->query(
            'CREATE TRIGGER reject_schedule_restore BEFORE UPDATE ON ' . $db->getPrefix() . "jadwal_ibadah
             BEGIN SELECT RAISE(ABORT, 'forced restore failure'); END"
        );

        $response = $this->controller()->restore($id);
        $row = $db->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertNotNull($row['deleted_at']);
        $this->assertFalse((bool) session()->getFlashdata('success'));
        $this->assertSame('Jadwal gagal dipulihkan. Silakan coba lagi.', session()->getFlashdata('error'));
    }

    public function testAdminArchiveFilterExcludesArchivedRowsFromTheActiveList(): void
    {
        $activeId = $this->insertSchedule(['jenis_ibadah' => 'Jadwal Aktif']);
        $archiveId = $this->insertSchedule(['jenis_ibadah' => 'Jadwal Tersimpan']);
        $this->controller()->archive($archiveId);

        $activeList = $this->controller()->index();
        $archiveList = $this->controller([], ['archived' => '1'])->index();

        $this->assertStringContainsString('Jadwal Aktif', $activeList);
        $this->assertStringNotContainsString('Jadwal Tersimpan</td>', $activeList);
        $this->assertStringNotContainsString('Jadwal Aktif</td>', $archiveList);
        $this->assertStringContainsString('Jadwal Tersimpan', $archiveList);
        $this->assertStringContainsString('admin/jadwal/edit/' . $activeId, $activeList);
        $this->assertStringNotContainsString('admin/jadwal/edit/' . $archiveId, $archiveList);
        $this->assertStringContainsString('Pulihkan', $archiveList);
    }

    public function testListIgnoresArrayShapedFilterParameters(): void
    {
        $this->insertSchedule(['jenis_ibadah' => 'Jadwal Aman']);

        $html = $this->controller([], [
            'q' => ['unexpected'],
            'cancelled' => ['1'],
            'archived' => ['1'],
            'hari' => ['senin'],
        ])->index();

        $this->assertStringContainsString('Jadwal Aman', $html);
    }

    public function testArchivedScheduleCannotBeEditedThroughTheActiveEditRoute(): void
    {
        $id = $this->insertSchedule();
        $this->controller()->archive($id);

        $response = $this->controller()->edit($id);

        $this->assertSame(site_url('admin/jadwal'), $response->getHeaderLine('Location'));
        $this->assertSame('Jadwal tidak ditemukan.', session()->getFlashdata('error'));
    }

    public function testScheduleStatusActionsRequireAdminAuthentication(): void
    {
        $id = $this->insertSchedule();
        session()->remove('admin_id');

        $response = $this->post('/admin/jadwal/cancel/' . $id);
        $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray();

        $response->assertRedirectTo('/admin/login');
        $this->assertSame('0', (string) $row['is_cancelled']);
    }
}
