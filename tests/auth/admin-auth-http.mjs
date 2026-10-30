// Run against a local server: node tests/auth/admin-auth-http.mjs [baseURL]
// Uses a random nonexistent identity; creates no admin account.
import assert from 'node:assert/strict';
import { randomUUID } from 'node:crypto';

const base = (process.argv[2] || 'http://localhost:8080/').replace(/\/$/, '');
const cookies = new Map();
async function request(url, options = {}) {
  const target = new URL(url, `${base}/`);
  assert.equal(target.origin, new URL(base).origin, 'Request must stay on configured origin');
  const response = await fetch(target, {
    redirect: 'manual', signal: AbortSignal.timeout(10000), ...options,
    headers: { Cookie: [...cookies].map(([key, value]) => `${key}=${value}`).join('; '), ...options.headers },
  });
  for (const cookie of response.headers.getSetCookie()) {
    const pair = cookie.split(';')[0];
    const index = pair.indexOf('=');
    cookies.set(pair.slice(0, index), pair.slice(index + 1));
  }
  return response;
}
const login = `${base}/index.php/admin/login`;
const dashboard = `${base}/index.php/admin/dashboard`;
const identity = `auth-fix-${randomUUID()}@example.invalid`;
let csrf;
async function loginPage(expectedError) {
  const response = await request(login);
  assert.equal(response.status, 200);
  const html = await response.text();
  assert.equal(html.match(/<form[^>]*action="([^"]+)"/)?.[1], login);
  const token = html.match(/<input[^>]*name="([^"]+)"[^>]*value="([^"]+)"/);
  assert.ok(token, 'CSRF hidden field must exist');
  csrf = { [token[1]]: token[2] };
  if (expectedError) assert.ok(html.includes(expectedError), 'Safe error must render');
}
async function assertProtected() {
  const response = await request(dashboard);
  assert.equal(response.status, 302);
  assert.equal(response.headers.get('location'), login);
  await response.text();
}
await loginPage();
console.log('PASS GET login, same-origin form action and index.php');
await assertProtected();
console.log('PASS anonymous dashboard redirects to login');
for (let attempt = 0; attempt < 6; attempt++) {
  await loginPage();
  // Another tab can make the public home the session's previous URL.
  await (await request(`${base}/`)).text();
  const response = await request(login, {
    method: 'POST', headers: { Referer: `${base}/` },
    body: new URLSearchParams({ ...csrf, email: identity, password: randomUUID() }),
  });
  assert.equal(response.status, 303);
  assert.equal(response.headers.get('location'), login);
  await response.text();
  await loginPage(attempt === 5 ? 'Terlalu banyak percobaan.' : 'Email atau kata sandi tidak sesuai.');
}
console.log('PASS invalid credentials and rate limit: POST 303 -> login 200, safe flash, no loop');
await assertProtected();
console.log('PASS failed login never grants dashboard access');
for (const path of ['login', 'logout']) {
  const response = await request(`${base}/index.php/admin/${path}`, {
    method: 'POST', headers: { Referer: login, 'X-Requested-With': 'XMLHttpRequest' },
    body: new URLSearchParams({ email: identity, password: randomUUID() }),
  });
  // AJAX requests expose the CSRF rejection directly, including in production.
  assert.equal(response.status, 403);
  await response.text();
}
console.log('PASS missing CSRF rejected on login and logout');
for (const path of ['/', '/profil', '/berita', '/jadwal-ibadah']) {
  const response = await request(`${base}${path}`);
  assert.equal(response.status, 200);
  assert.ok((await response.text()).includes('Jemaat Leahari'));
}
console.log('PASS four public routes');
