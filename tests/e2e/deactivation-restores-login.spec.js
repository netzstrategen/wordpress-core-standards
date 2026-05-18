// @ts-check
/**
 * Regression suite for: "core-standards deactivation must restore login access."
 *
 * Background: On activation, the plugin writes a `.htaccess` block that
 * rewrites `/login.php` to `/wp-login.php` and blocks direct access to
 * `/wp-login.php` with 403. Until PR #94, deactivation did not remove
 * those rules — leaving the site unable to reach the login page.
 *
 * These tests verify the activated behavior, the deactivation cleanup,
 * and the upgrade-safety path for the legacy `core-standards:security`
 * marker that was used by older plugin versions.
 */
const fs = require('fs');
const path = require('path');
const { test, expect, request } = require('@playwright/test');
const {
  wpCli,
  readFile,
  fileExists,
  writeFile,
  activatePlugin,
  deactivatePlugin,
  isPluginActive,
} = require('./helpers/wp-env');

const ROOT_HTACCESS = '/var/www/html/.htaccess';
const UPLOADS_HTACCESS = '/var/www/html/wp-content/uploads/.htaccess';

const ROOT_MARKERS = [
  'core-standards:security-files',
  'core-standards:fast404',
  'core-standards:assets-cache',
  'core-standards:security-headers',
];
const UPLOADS_MARKER = 'core-standards:uploads.noscript';

test.describe.configure({ mode: 'serial' });

test.describe('core-standards: deactivation restores login access', () => {
  /** @type {import('@playwright/test').APIRequestContext} */
  let api;

  test.beforeAll(async () => {
    api = await request.newContext({ ignoreHTTPSErrors: true });
    // Ensure pretty permalinks (also done by wp-env afterStart, but defensive).
    wpCli(`rewrite structure '/%postname%/' --hard`);
    if (!isPluginActive()) {
      activatePlugin();
    }
  });

  test.afterAll(async () => {
    // Leave the env in a clean, activated state for subsequent suites.
    if (!isPluginActive()) {
      activatePlugin();
    }
    await api.dispose();
  });

  test('plugin active: /login.php returns the login form', async () => {
    const res = await api.get('/login.php', { maxRedirects: 0 });
    expect(res.status()).toBe(200);
    const body = await res.text();
    expect(body).toContain('loginform');
  });

  test('plugin active: /wp-login.php is blocked with 403', async () => {
    const res = await api.get('/wp-login.php', { maxRedirects: 0 });
    expect(res.status()).toBe(403);
  });

  test('plugin active: .htaccess contains all core-standards blocks', () => {
    const root = readFile(ROOT_HTACCESS);
    for (const marker of ROOT_MARKERS) {
      expect(root, `root .htaccess should contain ${marker}`).toContain(`# BEGIN ${marker}`);
      expect(root, `root .htaccess should close ${marker}`).toContain(`# END ${marker}`);
    }
    if (fileExists(UPLOADS_HTACCESS)) {
      const uploads = readFile(UPLOADS_HTACCESS);
      expect(uploads).toContain(`# BEGIN ${UPLOADS_MARKER}`);
      expect(uploads).toContain(`# END ${UPLOADS_MARKER}`);
    }
  });

  test('after deactivation: /wp-login.php is reachable again', async () => {
    deactivatePlugin();
    const res = await api.get('/wp-login.php', { maxRedirects: 0 });
    expect(res.status()).toBe(200);
    const body = await res.text();
    expect(body).toContain('loginform');
  });

  test('after deactivation: no core-standards:* markers remain in .htaccess', () => {
    const root = readFile(ROOT_HTACCESS);
    for (const marker of [...ROOT_MARKERS, 'core-standards:security']) {
      expect(root, `root .htaccess should not contain ${marker}`).not.toContain(marker);
    }
    if (fileExists(UPLOADS_HTACCESS)) {
      const uploads = readFile(UPLOADS_HTACCESS);
      expect(uploads).not.toContain(UPLOADS_MARKER);
    }
  });

  test('reactivation: blocks are restored and login URL works again', async () => {
    activatePlugin();
    const root = readFile(ROOT_HTACCESS);
    for (const marker of ROOT_MARKERS) {
      expect(root).toContain(`# BEGIN ${marker}`);
    }
    const login = await api.get('/login.php', { maxRedirects: 0 });
    expect(login.status()).toBe(200);
    const blocked = await api.get('/wp-login.php', { maxRedirects: 0 });
    expect(blocked.status()).toBe(403);
  });

  test('legacy core-standards:security block is also stripped on deactivation', () => {
    // Pre-condition: plugin is active (set up above).
    const legacy = fs.readFileSync(
      path.join(__dirname, 'fixtures/legacy-security-block.htaccess'),
      'utf8'
    );

    // Inject legacy block into the existing .htaccess so both modern AND
    // legacy markers coexist (this is the upgrade scenario commit 95aefe6
    // partially addressed; deactivation needs to clean both up).
    const original = readFile(ROOT_HTACCESS);
    writeFile(ROOT_HTACCESS, legacy + '\n' + original);

    expect(readFile(ROOT_HTACCESS)).toContain('core-standards:security');

    deactivatePlugin();

    const after = readFile(ROOT_HTACCESS);
    expect(after).not.toContain('core-standards:security');
    expect(after).not.toContain('core-standards:security-files');
  });
});
