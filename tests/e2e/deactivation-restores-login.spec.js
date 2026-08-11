// @ts-check
/**
 * Regression suite for: "core-standards deactivation must restore login access."
 *
 * Background: On activation, the plugin writes a `.htaccess` block that
 * rewrites `/login.php` to `/wp-login.php` and blocks direct access to
 * `/wp-login.php` with 403. Until this PR, deactivation did not undo
 * the block — leaving the site unable to reach the login page.
 *
 * The production-security rules (xmlrpc, .git, wp-includes/*.php,
 * security headers, fast 404, uploads no-exec) must stay in force even
 * while the plugin is temporarily deactivated, so deactivation only
 * comments out the single `wp-login.php` block rule. The full block
 * cleanup happens at uninstall time.
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

// Indent matches conf/.htaccess.security-files exactly (2 spaces).
const WP_LOGIN_RULE_ACTIVE = '  RewriteRule ^wp-login\\.php$ - [NS,F,END]';
const WP_LOGIN_RULE_COMMENTED = '  # RewriteRule ^wp-login\\.php$ - [NS,F,END]';
const XMLRPC_RULE = '  RewriteRule ^xmlrpc\\.php$ - [F,END]';

test.describe.configure({ mode: 'serial' });

test.describe('core-standards: deactivation restores login access', () => {
  /** @type {import('@playwright/test').APIRequestContext} */
  let api;

  test.beforeAll(async () => {
    api = await request.newContext({ ignoreHTTPSErrors: true });
    wpCli(`rewrite structure '/%postname%/' --hard`);
    if (!isPluginActive()) {
      activatePlugin();
    }
  });

  test.afterAll(async () => {
    if (!isPluginActive()) {
      activatePlugin();
    }
    await api.dispose();
  });

  test('plugin active: /login.php returns the login form', async () => {
    const res = await api.get('/login.php', { maxRedirects: 0 });
    expect(res.status()).toBe(200);
    expect(await res.text()).toContain('loginform');
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
    expect(root).toContain(WP_LOGIN_RULE_ACTIVE);
    expect(root).not.toContain(WP_LOGIN_RULE_COMMENTED);

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
    expect(await res.text()).toContain('loginform');
  });

  test('after deactivation: only the wp-login rule is disabled; other security stays intact', () => {
    const root = readFile(ROOT_HTACCESS);

    // All marker-delimited blocks must STILL be present — the rest of the
    // security and stability rules must remain in force.
    for (const marker of ROOT_MARKERS) {
      expect(root, `${marker} should remain after deactivation`).toContain(`# BEGIN ${marker}`);
      expect(root, `${marker} should remain after deactivation`).toContain(`# END ${marker}`);
    }
    if (fileExists(UPLOADS_HTACCESS)) {
      expect(readFile(UPLOADS_HTACCESS)).toContain(UPLOADS_MARKER);
    }

    // The wp-login.php block rule is now commented out, and ONLY that line.
    expect(root).toContain(WP_LOGIN_RULE_COMMENTED);
    expect(root).not.toContain(WP_LOGIN_RULE_ACTIVE);

    // Other rules in the same block remain uncommented.
    expect(root).toContain(XMLRPC_RULE);
  });

  test('reactivation: wp-login rule is restored and /wp-login.php is blocked again', async () => {
    activatePlugin();
    const root = readFile(ROOT_HTACCESS);
    expect(root).toContain(WP_LOGIN_RULE_ACTIVE);
    expect(root).not.toContain(WP_LOGIN_RULE_COMMENTED);

    const login = await api.get('/login.php', { maxRedirects: 0 });
    expect(login.status()).toBe(200);
    const blocked = await api.get('/wp-login.php', { maxRedirects: 0 });
    expect(blocked.status()).toBe(403);
  });

  test('uninstall: every core-standards:* block (including legacy marker) is removed', () => {
    // Inject the legacy block to also verify upgrade-safety cleanup.
    const legacy = fs.readFileSync(
      path.join(__dirname, 'fixtures/legacy-security-block.htaccess'),
      'utf8'
    );
    const original = readFile(ROOT_HTACCESS);
    writeFile(ROOT_HTACCESS, legacy + '\n' + original);
    expect(readFile(ROOT_HTACCESS)).toContain('# BEGIN core-standards:security\n');

    // `--skip-delete` fires the uninstall hook (Schema::uninstall) but
    // keeps the plugin files on disk — important because the directory
    // is a bind mount of the host repo.
    wpCli('plugin uninstall core-standards --deactivate --skip-delete');

    const after = readFile(ROOT_HTACCESS);
    for (const marker of [...ROOT_MARKERS, 'core-standards:security']) {
      expect(after, `${marker} should be gone after uninstall`).not.toContain(marker);
    }
    if (fileExists(UPLOADS_HTACCESS)) {
      expect(readFile(UPLOADS_HTACCESS)).not.toContain(UPLOADS_MARKER);
    }
  });
});
