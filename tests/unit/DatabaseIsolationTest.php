<?php

use CodeIgniter\Test\CIUnitTestCase;

final class DatabaseIsolationTest extends CIUnitTestCase
{
    public function testTestingDefaultGroupUsesAnIsolatedSqliteDatabase(): void
    {
        $database = config('Database');

        $this->assertSame('tests', $database->defaultGroup);
        $this->assertIsolatedSqliteGroup($database->default);
    }

    public function testTestingTestsGroupUsesAnIsolatedSqliteDatabase(): void
    {
        $this->assertIsolatedSqliteGroup(config('Database')->tests);
    }

    public function testExplicitDefaultConnectionCannotSelectMysql(): void
    {
        $connection = \Config\Database::connect('default', false);

        $this->assertSame('SQLite3', $connection->DBDriver);
        $this->assertSame(':memory:', $connection->database);
    }

    public function testProductionDatabaseDefaultsAreNotForcedToSqlite(): void
    {
        $root = dirname(__DIR__, 2);
        $script = <<<'PHP'
define('ENVIRONMENT', 'production');
define('APPPATH', getcwd() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
require getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Modules.php';
require APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Database.php';
\CodeIgniter\Config\BaseConfig::$override = false;
$database = new Config\Database();
echo json_encode([
    'defaultGroup' => $database->defaultGroup,
    'driver'       => $database->default['DBDriver'],
]);
PHP;
        $process = proc_open(
            [PHP_BINARY, '-r', $script],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $root
        );

        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $error  = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $this->assertSame(0, proc_close($process), $error);
        $configuration = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('default', $configuration['defaultGroup']);
        $this->assertSame('MySQLi', $configuration['driver']);
    }

    private function assertIsolatedSqliteGroup(array $group): void
    {
        $this->assertSame('SQLite3', $group['DBDriver']);
        $this->assertSame(':memory:', $group['database']);
        $this->assertSame('', $group['DSN']);
        $this->assertSame('', $group['hostname']);
        $this->assertSame('', $group['username']);
        $this->assertSame('', $group['password']);
        $this->assertNull($group['port']);
        $this->assertSame([], $group['failover']);
        $this->assertSame('utf8', $group['charset']);
        $this->assertSame('', $group['DBCollat']);
        $this->assertTrue($group['foreignKeys']);
        $this->assertSame(1000, $group['busyTimeout']);
    }
}
