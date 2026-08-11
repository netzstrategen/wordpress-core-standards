# E2E tests

End-to-end test suite for the `core-standards` WordPress plugin. The suite
runs against a real WordPress install (Apache + MariaDB) booted by
[`@wordpress/env`](https://github.com/WordPress/gutenberg/tree/trunk/packages/env)
and is driven by [Playwright](https://playwright.dev/).

## Prerequisites

- Node.js **20+**
- Docker Desktop (or any Docker engine) running

## Running locally

```sh
# One-time: install deps and Playwright's chromium binary.
npm ci
npx playwright install --with-deps chromium

# Boot WordPress (port 8989) with this plugin auto-activated.
npm run env:start

# Run the suite.
npm run test:e2e

# When done.
npm run env:stop
```

`npm run env:clean` wipes the wp-env data volumes if you need a clean slate.

Visit the test site at <http://localhost:8989> (admin: `admin` / `password`).

## CI

The GitHub Actions workflow `.github/workflows/e2e.yml` runs the suite on
every pull request and on pushes to `master`. Failing runs upload the
Playwright HTML report as an artifact.

## Adding a new spec

1. Create `tests/e2e/<feature>.spec.js`.
2. Use the helpers in `tests/e2e/helpers/wp-env.js` to talk to the
   container (run wp-cli, read/write files, toggle the plugin).
3. Use Playwright's `request` context for HTTP-only assertions, or
   `page` for full-browser flows.
4. Tests that mutate global state (`.htaccess`, plugin activation,
   options) should declare `test.describe.configure({ mode: 'serial' })`
   and restore state in `beforeAll` / `afterAll`.

The helper module is intentionally small; extend it when a new spec
needs a new primitive rather than duplicating `execSync` calls.
