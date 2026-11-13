<?php

use App\Controllers\AdminAuth;
use App\Filters\AdminAuthFilter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class TestableAdminAuth extends AdminAuth
{
    public function __construct()
    {
    }

    public function initialize(IncomingRequest $request): void
    {
        $this->initController($request, service('response'), service('logger'));
    }
}

final class AdminAuthSecurityTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $originalDatabaseGroup;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('sqlite3')) {
            $this->markTestSkipped('SQLite is required for isolated admin security tests.');
        }

        $database = config('Database');
        $this->originalDatabaseGroup = $database->defaultGroup;
        $database->defaultGroup = 'tests';

        $db = db_connect('tests');
        $forge = \Config\Database::forge('tests');
        foreach (['admin_login_attempts', 'admin_users', 'jadwal_ibadah'] as $table) {
            $db->resetDataCache();
            if ($db->tableExists($table)) {
                $forge->dropTable($table);
            }
        }

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'email' => ['type' => 'VARCHAR', 'constraint' => 191],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'operator'],
            'is_active' => ['type' => 'TINYINT', 'default' => 1],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey('email');
        $forge->createTable('admin_users');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'identity_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'attempted_at' => ['type' => 'INTEGER'],
        ]);
        $forge->addKey('id', true);
        $forge->addKey(['identity_hash', 'attempted_at']);
        $forge->createTable('admin_login_attempts');

        $forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'jenis_ibadah' => ['type' => 'VARCHAR', 'constraint' => 120],
            'tanggal' => ['type' => 'DATE'],
            'waktu' => ['type' => 'TIME'],
            'lokasi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'pelayan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_cancelled' => ['type' => 'TINYINT', 'default' => 0],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'updated_by' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('jadwal_ibadah');
        $db->resetDataCache();

        session()->remove(['admin_id', 'admin_name', 'success', 'error']);
    }

    protected function tearDown(): void
    {
        config('Database')->defaultGroup = $this->originalDatabaseGroup;
        session()->remove(['admin_id', 'admin_name', 'success', 'error']);

        parent::tearDown();
    }

    private function insertAdmin(array $overrides = []): int
    {
        $data = array_merge([
            'name' => 'Operator Uji',
            'email' => 'operator@example.test',
            'password_hash' => password_hash('correct-horse-battery', PASSWORD_DEFAULT),
            'role' => 'operator',
            'is_active' => 1,
            'deleted_at' => null,
        ], $overrides);
        $db = db_connect('tests');
        $db->table('admin_users')->insert($data);

        return (int) $db->insertID();
    }

    private function insertSchedule(int $cancelled = 0, ?string $deletedAt = null): int
    {
        $db = db_connect('tests');
        $db->table('jadwal_ibadah')->insert([
            'jenis_ibadah' => 'Ibadah Uji',
            'tanggal' => '2026-12-06',
            'waktu' => '09:00:00',
            'lokasi' => 'Gedung Gereja',
            'is_cancelled' => $cancelled,
            'deleted_at' => $deletedAt,
        ]);

        return (int) $db->insertID();
    }

    private function invokeLogin(mixed $email, mixed $password = 'correct-horse-battery')
    {
        $request = service('request');
        if ($request instanceof IncomingRequest) {
            $request->setGlobal('post', ['email' => $email, 'password' => $password]);
        }

        $controller = new TestableAdminAuth();
        $controller->initialize($request);

        return $controller->login();
    }

    public function testLoginAllowsActiveAdminAccount(): void
    {
        $adminId = $this->insertAdmin();

        $response = $this->invokeLogin('operator@example.test');

        $this->assertSame(site_url('admin/dashboard'), $response->getHeaderLine('Location'));
        $this->assertSame($adminId, session()->get('admin_id'));
    }

    public function testLoginRejectsInactiveAdminAccount(): void
    {
        $this->insertAdmin(['is_active' => 0]);

        $response = $this->invokeLogin('operator@example.test');

        $this->assertSame(site_url('admin/login'), $response->getHeaderLine('Location'));
        $this->assertNull(session()->get('admin_id'));
        $this->assertSame('Email atau kata sandi tidak sesuai.', session()->getFlashdata('error'));
    }

    public function testLoginRejectsSoftDeletedAdminAccount(): void
    {
        $this->insertAdmin(['deleted_at' => '2026-11-01 10:00:00']);

        $response = $this->invokeLogin('operator@example.test');

        $this->assertSame(site_url('admin/login'), $response->getHeaderLine('Location'));
        $this->assertNull(session()->get('admin_id'));
        $this->assertSame('Email atau kata sandi tidak sesuai.', session()->getFlashdata('error'));
    }

    public function testLoginRejectsArrayShapedCredentials(): void
    {
        $this->insertAdmin();

        $response = $this->invokeLogin(['operator@example.test'], ['not', 'a', 'password']);

        $this->assertSame(site_url('admin/login'), $response->getHeaderLine('Location'));
        $this->assertNull(session()->get('admin_id'));
    }

    public function testExistingSessionIsRejectedAfterAdminIsDeactivated(): void
    {
        $adminId = $this->insertAdmin();
        $id = $this->insertSchedule();

        $this->withSession(['admin_id' => $adminId])->get('/admin/jadwal')->assertStatus(200);
        db_connect('tests')->table('admin_users')->where('id', $adminId)->update(['is_active' => 0]);

        $response = $this->withSession(['admin_id' => $adminId])->get('/admin/jadwal');

        $response->assertRedirectTo('/admin/login');
        $this->assertNull(session()->get('admin_id'));
        $this->assertSame('0', (string) db_connect('tests')->table('jadwal_ibadah')->where('id', $id)->get()->getRowArray()['is_cancelled']);
    }

    public function testExistingSessionIsRejectedAfterAdminIsSoftDeleted(): void
    {
        $adminId = $this->insertAdmin();
        $this->insertSchedule();
        db_connect('tests')->table('admin_users')->where('id', $adminId)->update(['deleted_at' => '2026-11-01 10:00:00']);

        $response = $this->withSession(['admin_id' => $adminId])->get('/admin/jadwal');

        $response->assertRedirectTo('/admin/login');
        $this->assertNull(session()->get('admin_id'));
    }

    public function testRequestWithoutAdminSessionIsRejected(): void
    {
        $this->insertSchedule();

        $this->withSession([])->get('/admin/jadwal')->assertRedirectTo('/admin/login');
    }

    public function testSessionWithNonAdminRoleIsRejected(): void
    {
        $adminId = $this->insertAdmin(['role' => 'viewer']);
        $this->insertSchedule();

        $this->withSession(['admin_id' => $adminId])->get('/admin/jadwal')->assertRedirectTo('/admin/login');
        $this->assertNull(session()->get('admin_id'));
    }

    public function testMalformedAdminSessionIdIsRejectedAndCleared(): void
    {
        $this->insertSchedule();

        $this->withSession(['admin_id' => ['1']])->get('/admin/jadwal')->assertRedirectTo('/admin/login');
        $this->assertNull(session()->get('admin_id'));
    }

    public function testAccountStatusLookupFailureRejectsSession(): void
    {
        $adminId = $this->insertAdmin();
        \Config\Database::forge('tests')->dropTable('admin_users');
        db_connect('tests')->resetDataCache();
        $this->insertSchedule();

        $this->withSession(['admin_id' => $adminId])->get('/admin/jadwal')->assertRedirectTo('/admin/login');
        $this->assertNull(session()->get('admin_id'));
    }

    public function testEveryScheduleMutationEndpointRejectsMissingCsrfToken(): void
    {
        $adminId = $this->insertAdmin();
        $routes = [
            ['save', 0, ['id' => '']],
            ['cancel', 0, []],
            ['reactivate', 1, []],
            ['archive', 0, []],
            ['restore', 0, []],
            ['delete', 0, []],
        ];

        foreach ($routes as [$action, $cancelled, $post]) {
            $deletedAt = $action === 'restore' ? '2026-11-01 10:00:00' : null;
            $scheduleId = $this->insertSchedule($cancelled, $deletedAt);
            $path = $action === 'save'
                ? '/admin/jadwal/save'
                : '/admin/jadwal/' . $action . '/' . $scheduleId;
            $payload = array_merge([
                'id' => (string) $scheduleId,
                'jenis_ibadah' => 'Jadwal Setelah CSRF',
                'tanggal' => '2026-12-06',
                'waktu' => '09:00',
                'lokasi' => 'Gedung Gereja',
                'pelayan' => '',
                'is_cancelled' => (string) $cancelled,
            ], $post);

            $csrfRejected = false;
            try {
                $this->withSession(['admin_id' => $adminId])->post($path, $payload);
            } catch (SecurityException) {
                $csrfRejected = true;
            }
            $row = db_connect('tests')->table('jadwal_ibadah')->where('id', $scheduleId)->get()->getRowArray();

            $this->assertTrue($csrfRejected, $action . ' must be rejected without a CSRF token.');
            $this->assertSame('Ibadah Uji', $row['jenis_ibadah'], $action . ' must not change schedule content.');
            $this->assertSame((string) $cancelled, (string) $row['is_cancelled'], $action . ' must not change cancellation state.');
            $this->assertSame($deletedAt, $row['deleted_at'], $action . ' must not change archive state.');
        }
    }
}
