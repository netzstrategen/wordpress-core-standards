// @ts-check
/**
 * Thin wrapper around `npx wp-env run cli ...` so specs can run commands
 * inside the WordPress container (wp-cli, shell, file IO).
 *
 * Reused by all E2E specs. Keep this module side-effect-free and small.
 */
const { execSync } = require('child_process');

const PLUGIN_SLUG = 'core-standards';

/**
 * Run an arbitrary shell command inside the wp-env "cli" service.
 *
 * The wp-env cli image is debian-based with bash and wp-cli available.
 * `bash -lc` is used so quoting works as expected on the host shell.
 */
function runInContainer(shellCommand) {
  const escaped = shellCommand.replace(/'/g, `'\\''`);
  return execSync(`npx wp-env run cli bash -lc '${escaped}'`, {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'pipe'],
  });
}

function wpCli(args) {
  return runInContainer(`wp ${args}`).trim();
}

function readFile(absolutePath) {
  try {
    return runInContainer(`cat "${absolutePath}"`);
  }
  catch {
    return '';
  }
}

function fileExists(absolutePath) {
  try {
    runInContainer(`test -f "${absolutePath}"`);
    return true;
  }
  catch {
    return false;
  }
}

/**
 * Write content into a file inside the container by piping base64 via stdin.
 * Avoids quoting issues for content with newlines / special chars.
 */
function writeFile(absolutePath, content) {
  const b64 = Buffer.from(content, 'utf8').toString('base64');
  runInContainer(`echo '${b64}' | base64 -d > "${absolutePath}"`);
}

function appendFile(absolutePath, content) {
  const b64 = Buffer.from(content, 'utf8').toString('base64');
  runInContainer(`echo '${b64}' | base64 -d >> "${absolutePath}"`);
}

function activatePlugin(slug = PLUGIN_SLUG) {
  wpCli(`plugin activate ${slug}`);
}

function deactivatePlugin(slug = PLUGIN_SLUG) {
  wpCli(`plugin deactivate ${slug}`);
}

function isPluginActive(slug = PLUGIN_SLUG) {
  try {
    wpCli(`plugin is-active ${slug}`);
    return true;
  }
  catch {
    return false;
  }
}

module.exports = {
  PLUGIN_SLUG,
  runInContainer,
  wpCli,
  readFile,
  fileExists,
  writeFile,
  appendFile,
  activatePlugin,
  deactivatePlugin,
  isPluginActive,
};
