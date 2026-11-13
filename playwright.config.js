const path = require('node:path');
const { defineConfig, devices } = require('@playwright/test');
const { adminEmail, adminPassword } = require('./e2e/test-credentials');

const port = Number(process.env.PLAYWRIGHT_PORT || 8091);
const baseURL = `http://127.0.0.1:${port}/index.php/`;
const testDataDirectory = process.env.PLAYWRIGHT_TEST_DATA_DIRECTORY || 'playwright-test-data';
const testDatabase = path.join(__dirname, 'writable', testDataDirectory, 'profile-modules.sqlite');

module.exports = defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  forbidOnly: Boolean(process.env.CI),
  retries: 0,
  workers: 1,
  reporter: 'html',
  use: {
    baseURL,
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: {
    command: 'node e2e/start-test-server.js',
    url: `${baseURL}admin/login`,
    reuseExistingServer: false,
    timeout: 60_000,
    env: {
      CI_ENVIRONMENT: 'development',
      'app.baseURL': `http://127.0.0.1:${port}/`,
      'database.defaultGroup': 'tests',
      'database.tests.database': testDatabase,
      'database.tests.DBDriver': 'SQLite3',
      'database.tests.DBPrefix': '',
      'database.tests.hostname': '',
      'database.tests.username': '',
      'database.tests.password': '',
      'database.tests.DBDebug': 'true',
      'database.tests.foreignKeys': 'true',
      PLAYWRIGHT_TEST_DATABASE_PATH: testDatabase,
      PLAYWRIGHT_TEST_ADMIN_EMAIL: adminEmail,
      PLAYWRIGHT_TEST_ADMIN_PASSWORD: adminPassword,
      PLAYWRIGHT_PORT: String(port),
      PLAYWRIGHT_TEST_DATA_DIRECTORY: testDataDirectory,
    },
  },
});
