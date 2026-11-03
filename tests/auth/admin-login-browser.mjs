import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { mkdtempSync, mkdirSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve, sep } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const chrome = process.env.CHROME_PATH;
assert.ok(chrome, 'Set CHROME_PATH to a local Chrome executable; no packages are installed.');
const base = process.argv[2] || 'http://localhost:8080';
assert.ok(['localhost', '127.0.0.1'].includes(new URL(base).hostname), 'Local test server only');
const profile = mkdtempSync(join(tmpdir(), 'jl-login-'));
const output = resolve('writable/login-checks');
mkdirSync(output, { recursive: true });
const browser = spawn(chrome, [
    '--headless', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
    '--remote-debugging-port=0', `--user-data-dir=${profile}`, 'about:blank',
], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
let socket;
try {
    const endpoint = await new Promise((accept, reject) => {
        const timeout = setTimeout(() => reject(new Error('Chrome startup timeout')), 15000);
        browser.on('error', reject);
        browser.stderr.on('data', (chunk) => {
            const match = chunk.toString().match(/DevTools listening on (ws:\/\/\S+)/);
            if (match) { clearTimeout(timeout); accept(match[1]); }
        });
    });
    const debug = new URL(endpoint);
    const targets = await (await fetch(`http://${debug.host}/json/list`)).json();
    socket = new WebSocket(targets.find((target) => target.type === 'page').webSocketDebuggerUrl);
    await new Promise((accept, reject) => { socket.onopen = accept; socket.onerror = reject; });
    let sequence = 0;
    const pending = new Map();
    socket.onmessage = ({ data }) => {
        const message = JSON.parse(data);
        const waiter = pending.get(message.id);
        if (!waiter) return;
        pending.delete(message.id);
        if (message.error) waiter.reject(new Error(message.error.message));
        else waiter.accept(message.result);
    };
    const command = (method, params = {}) => new Promise((accept, reject) => {
        const id = ++sequence;
        pending.set(id, { accept, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });
    const evaluate = async (expression) => {
        const result = await command('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        assert.ok(!result.exceptionDetails, JSON.stringify(result.exceptionDetails));
        return result.result.value;
    };
    const waitFor = async (expression) => {
        for (let attempt = 0; attempt < 100; attempt++) {
            if (await evaluate(expression)) return;
            await delay(100);
        }
        throw new Error(`Timed out: ${expression}`);
    };
    await command('Page.enable');
    await command('Page.navigate', { url: `${base}/admin/login` });
    await waitFor('document.readyState === "complete" && !!document.querySelector(".login-form")');
    await evaluate('document.fonts.ready');

    const captureMatrix = async (state) => {
        await waitFor('document.readyState === "complete"');
        await evaluate('document.fonts.ready');
        for (const width of [320, 360, 390, 430, 768, 1440]) {
            await command('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: false });
            const metrics = await evaluate(`(() => {
                const photo = document.querySelector('.login-photo');
                return {
                    overflow: document.documentElement.scrollWidth > innerWidth,
                    photoVisible: getComputedStyle(photo).display !== 'none',
                    left: document.querySelector('.login-content').getBoundingClientRect().left,
                    photoLeft: photo.getBoundingClientRect().left,
                    controls: [...document.querySelectorAll('input:not([type=hidden]), button, .login-public-link')].map(element => element.getBoundingClientRect().height),
                    imagesLoaded: [...document.images].every(image => image.complete && image.naturalWidth > 0)
                };
            })()`);
            if (metrics.overflow) console.log(await evaluate('[...document.querySelectorAll("body *")].filter(element => element.getBoundingClientRect().right > innerWidth).map(element => [element.tagName, element.className, element.getBoundingClientRect().width])'));
            assert.equal(metrics.overflow, false, `${state} ${width}: overflow`);
            assert.equal(metrics.photoVisible, width >= 768);
            assert.ok(metrics.controls.every((height) => height >= 44));
            assert.ok(metrics.imagesLoaded, 'Local images must load');
            if (width >= 768) assert.ok(metrics.left < metrics.photoLeft, 'Form must be on left');
            const screenshot = await command('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true });
            writeFileSync(join(output, `${state}-${width}.png`), Buffer.from(screenshot.data, 'base64'));
            console.log(`PASS ${state} ${width}px: no overflow, image breakpoint, touch targets, assets`);
        }
    };
    await captureMatrix('normal');
    for (const id of ['email', 'password', '', '', '']) {
        await command('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9 });
        await command('Input.dispatchKeyEvent', { type: 'keyUp', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9 });
        const focused = await evaluate(`(() => {
            const element = document.activeElement;
            const style = getComputedStyle(element);
            return { id: element.id, visible: style.outlineStyle !== 'none' || style.boxShadow !== 'none', tag: element.tagName };
        })()`);
        if (id) assert.equal(focused.id, id);
        assert.ok(focused.visible, `Focus ring on ${focused.tag}`);
    }
    console.log('PASS keyboard navigation and visible focus on all five controls');
    const identity = `login-ui-${randomUUID()}@example.invalid`;
    const password = randomUUID();
    await evaluate(`document.querySelector('#email').value = ${JSON.stringify(identity)}; document.querySelector('#password').value = ${JSON.stringify(password)}; document.querySelector('.login-form').requestSubmit()`);
    await waitFor('!!document.querySelector("#login-error")');
    assert.ok(await evaluate('document.querySelector("#login-error").textContent.includes("Email atau kata sandi tidak sesuai.")'));
    assert.equal(await evaluate('document.querySelector("#email").value'), identity);
    assert.equal(await evaluate('document.querySelector("#password").value'), '');
    assert.equal(await evaluate(`document.documentElement.outerHTML.includes(${JSON.stringify(password)})`), false);
    assert.ok(await evaluate('[...document.querySelectorAll(".form-control")].every(element => element.getAttribute("aria-describedby") === "login-error" && element.getAttribute("aria-invalid") === "true")'));
    await captureMatrix('error');
    console.log('PASS real invalid POST, generic error, preserved email, password absent, error associations');
    assert.ok(await evaluate(`(() => {
        const form = document.querySelector('.login-form');
        form.addEventListener('submit', event => event.preventDefault(), { once: true });
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        const loading = document.querySelector('.btn-login').disabled && !document.querySelector('.spinner-border').hidden;
        const duplicateBlocked = !form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        window.dispatchEvent(new Event('pageshow'));
        return loading && duplicateBlocked && !document.querySelector('.btn-login').disabled;
    })()`));
    await command('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    assert.equal(await evaluate('getComputedStyle(document.querySelector(".spinner-border")).animationName'), 'none');
    console.log('PASS loading/duplicate-submit guard, pageshow recovery, reduced motion');
    await command('Emulation.setScriptExecutionDisabled', { value: true });
    await command('Page.navigate', { url: `${base}/admin/login` });
    await waitFor('document.readyState === "complete" && !!document.querySelector(".login-form")');
    assert.equal(await evaluate('document.querySelector(".password-toggle").hidden'), true);
    await evaluate(`document.querySelector('#email').value = ${JSON.stringify(identity)}; document.querySelector('#password').value = ${JSON.stringify(password)}; document.querySelector('.login-form').requestSubmit()`);
    await waitFor('!!document.querySelector("#login-error")');
    assert.equal(await evaluate('document.querySelector("#email").value'), identity);
    console.log('PASS native POST login without JavaScript');
} finally {
    socket?.close();
    const closed = new Promise((accept) => browser.once('close', accept));
    browser.kill();
    await Promise.race([closed, delay(5000)]);
    const resolvedProfile = resolve(profile);
    assert.ok(resolvedProfile.startsWith(resolve(tmpdir()) + sep) && resolvedProfile.includes('jl-login-'));
    rmSync(resolvedProfile, { recursive: true, force: true, maxRetries: 5, retryDelay: 200 });
}
