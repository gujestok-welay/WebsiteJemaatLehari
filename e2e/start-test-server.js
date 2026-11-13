const fs = require('node:fs');
const net = require('node:net');
const path = require('node:path');
const { spawn, spawnSync } = require('node:child_process');
const { adminEmail, adminPassword } = require('./test-credentials');

const projectRoot = path.resolve(__dirname, '..');
const writableDirectory = path.join(projectRoot, 'writable');
const testDataDirectoryName = process.env.PLAYWRIGHT_TEST_DATA_DIRECTORY || 'playwright-test-data';
if (!/^playwright-test-data(?:-[a-z0-9]+(?:-[a-z0-9]+)*)?$/.test(testDataDirectoryName)) {
  console.error('Direktori data tes harus memakai nama aman berawalan playwright-test-data.');
  process.exit(1);
}
const testDataDirectory = path.join(writableDirectory, testDataDirectoryName);
const sessionDirectory = path.join(writableDirectory, 'playwright-session');
const databasePath = path.join(testDataDirectory, 'profile-modules.sqlite');
const port = Number(process.env.PLAYWRIGHT_PORT || 8091);
const baseURL = `http://127.0.0.1:${port}/`;

const serverEnvironment = {
  ...process.env,
  CI_ENVIRONMENT: 'development',
  'app.baseURL': baseURL,
  'database.defaultGroup': 'tests',
  'database.tests.database': databasePath,
  'database.tests.DBDriver': 'SQLite3',
  'database.tests.DBPrefix': '',
  'database.tests.hostname': '',
  'database.tests.username': '',
  'database.tests.password': '',
  'database.tests.DBDebug': 'true',
  'database.tests.foreignKeys': 'true',
  'session.savePath': sessionDirectory,
  'session.cookieName': 'playwright_session',
  PLAYWRIGHT_TEST_DATA_DIRECTORY: testDataDirectoryName,
  PLAYWRIGHT_TEST_DATABASE_PATH: databasePath,
  PLAYWRIGHT_TEST_ADMIN_EMAIL: adminEmail,
  PLAYWRIGHT_TEST_ADMIN_PASSWORD: adminPassword,
};

const reservation = net.createServer();

reservation.once('error', (error) => {
  console.error(`Port ${port} dipakai. Server Playwright tidak dijalankan agar tidak menyentuh aplikasi lain.`);
  console.error(error.message);
  process.exitCode = 1;
});

reservation.listen(port, '127.0.0.1', () => {
  try {
    if (fs.lstatSync(writableDirectory).isSymbolicLink()) {
      throw new Error('Folder writable proyek tidak boleh berupa symlink untuk tes Playwright.');
    }

    fs.mkdirSync(testDataDirectory, { recursive: true });
    fs.mkdirSync(sessionDirectory, { recursive: true });

    if (fs.lstatSync(testDataDirectory).isSymbolicLink() || fs.lstatSync(sessionDirectory).isSymbolicLink()) {
      throw new Error('Direktori data dan sesi Playwright tidak boleh berupa symlink.');
    }

    const setup = spawnSync('php', ['e2e/prepare-test-db.php'], {
      cwd: projectRoot,
      env: serverEnvironment,
      stdio: 'inherit',
    });

    if (setup.error) throw setup.error;
    if (setup.status !== 0) {
      process.exitCode = setup.status || 1;
      reservation.close();
      return;
    }
  } catch (error) {
    console.error('Gagal menyiapkan lingkungan Playwright yang terisolasi:', error);
    process.exitCode = 1;
    reservation.close();
    return;
  }

  reservation.close(() => {
    const server = spawn('php', [
      '-d', 'max_file_uploads=31',
      '-d', 'post_max_size=320M',
      '-S', `127.0.0.1:${port}`,
      '-t', 'public',
      'system/rewrite.php',
    ], {
      cwd: projectRoot,
      env: serverEnvironment,
      stdio: 'inherit',
    });

    server.on('error', (error) => {
      console.error('Gagal menjalankan server Playwright:', error);
      process.exitCode = 1;
    });

    server.on('exit', (code) => {
      process.exitCode = code || 0;
    });

    for (const signal of ['SIGINT', 'SIGTERM']) {
      process.on(signal, () => server.kill(signal));
    }
  });
});
