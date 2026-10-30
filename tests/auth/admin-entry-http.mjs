// Run from the project root with `php spark serve` on localhost:8080.
// Synthetic file sessions verify redirects/logout, not credential authentication.
// Does not create accounts or access the database. Never prints session/CSRF data.
import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { existsSync, readFileSync, unlinkSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

const base = 'http://localhost:8080';
const sessionDirectory = resolve('writable/session');
const ownedIds = new Set();
const cookies = new Map();
const login = `${base}/index.php/admin/login`;
const dashboard = `${base}/index.php/admin/dashboard`;
const entries = ['/admin', '/index.php/admin'];

function sessionPath(id) {
  assert.match(id, /^[a-zA-Z0-9,-]+$/);
  return resolve(sessionDirectory, `ci_session${id}`);
}

async function request(path, options = {}) {
  const url = new URL(path, base);
  assert.equal(url.origin, base, 'Requests must stay on the local test server');
  const response = await fetch(url, {
    ...options, redirect: 'manual', signal: AbortSignal.timeout(10000),
    headers: {
      Cookie: [...cookies].map(([key, value]) => `${key}=${value}`).join('; '),
      ...options.headers,
    },
  });
  for (const cookie of response.headers.getSetCookie()) {
    const pair = cookie.split(';')[0];
    const index = pair.indexOf('=');
    const key = pair.slice(0, index);
    const value = pair.slice(index + 1);
    cookies.set(key, value);
    // Only identifiers issued to this runner are eligible for cleanup.
    if (key === 'ci_session') ownedIds.add(value);
  }
  return response;
}

async function redirect(path, target, status = 302, options = {}) {
  const response = await request(path, options);
  assert.equal(response.status, status);
  assert.equal(response.headers.get('location'), target);
  await response.text();
}

async function page(path, marker) {
  const response = await request(path);
  assert.equal(response.status, 200);
  const html = await response.text();
  assert.ok(html.includes(marker));
  return html;
}

try {
  for (const entry of entries) {
    await redirect(entry, login);
    await page(login, '<form');
  }
  await redirect(dashboard, login);
  console.log('PASS anonymous /admin and /index.php/admin -> login 200; dashboard protected; no loop');

  // Match the current FileHandler/php session serialization, without an account.
  const fixtureId = randomBytes(16).toString('hex');
  writeFileSync(sessionPath(fixtureId),
    `__ci_last_regenerate|i:${Math.floor(Date.now() / 1000)};admin_id|i:2147483647;admin_name|s:13:"Entry fixture";`,
    { flag: 'wx' });
  ownedIds.add(fixtureId);
  cookies.set('ci_session', fixtureId);
  for (const entry of entries) {
    await redirect(entry, dashboard);
    await page(dashboard, 'Entry fixture');
  }
  await redirect(login, dashboard);
  const html = await page(dashboard, 'Entry fixture');
  console.log('PASS synthetic active session: both entry URLs and existing login -> dashboard 200; no loop');

  for (const path of [login, '/index.php/admin/logout']) {
    const response = await request(path, {
      method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams(),
    });
    assert.equal(response.status, 403);
    await response.text();
  }
  await page(dashboard, 'Entry fixture');
  console.log('PASS existing POST login/logout reject missing CSRF; session remains active');

  const token = html.match(/<input[^>]*name="([^"]+)"[^>]*value="([^"]+)"/);
  assert.ok(token, 'Dashboard must provide a CSRF field');
  await redirect('/index.php/admin/logout', login, 303, {
    method: 'POST', headers: { Referer: dashboard },
    body: new URLSearchParams({ [token[1]]: token[2] }),
  });
  assert.notEqual(cookies.get('ci_session'), fixtureId);
  const sessionData = readFileSync(sessionPath(cookies.get('ci_session')), 'utf8');
  assert.ok(!sessionData.includes('admin_id|') && !sessionData.includes('admin_name|'));
  for (const entry of entries) await redirect(entry, login);
  await redirect(dashboard, login);
  await page(login, '<form');
  console.log('PASS POST logout rotates session, removes admin state; entry/dashboard -> login 200 afterward');

  cookies.set('ci_session', fixtureId);
  await redirect('/index.php/admin', login);
  await redirect(dashboard, login);
  console.log('PASS old session replay cannot regain admin access');

  for (const path of ['/', '/profil', '/berita', '/jadwal-ibadah']) {
    const publicHtml = await page(path, 'Jemaat Leahari');
    assert.ok(!/href=["'][^"']*\/admin(?:[\/"'])/.test(publicHtml));
  }
  console.log('PASS four public pages return 200 without an admin link');
} finally {
  for (const id of ownedIds) {
    const file = sessionPath(id);
    if (existsSync(file)) unlinkSync(file);
  }
}
