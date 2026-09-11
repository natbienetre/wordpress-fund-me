## Why

The plugin currently has zero real test coverage. The CI-generated `tests/test-sample.php`
(regenerated every run by `wp scaffold plugin-tests --force` and not committed to the repo)
only asserts `true`. None of the plugin's actual behavior — adding a "Show support" action
link to plugins/themes that declare a Funding URI, and registering that header so WordPress
recognizes it — is verified by anything. This plugin is small enough (5 functions, ~50 lines
of logic in `fund-me.php`) to cover completely in a single change.

## What Changes

- Add real PHPUnit tests, committed under `tests/test-*.php`, covering all behavior currently
  implemented in `fund-me.php`:
  - `fundme_admin_init` registers the three expected filters.
  - `fundme_plugins_action_links` appends the support link only when the plugin declares a
    Funding URI header, and leaves the actions list untouched otherwise.
  - `fundme_themes_action_links` appends the support link only when the theme declares a
    Funding URI header, and leaves the actions list untouched otherwise.
  - `fundme_action_link` builds the expected escaped, localized anchor markup for a given URL.
  - `fundme_extra_funding_uri` registers `Funding URI` as a recognized extra header exactly
    once, for both plugin and theme header lists.
- No changes to `fund-me.php` itself, `composer.json` scripts, or CI workflow configuration.
  The existing `ci-test` script and its scaffolded `phpunit.xml.dist` already discover any
  `tests/test-*.php` file automatically (`<directory prefix="test-" suffix=".php">./tests/</directory>`,
  with `tests/test-sample.php` explicitly excluded), so committing new test files is enough to
  wire them into CI.
- No coverage-percentage measurement or reporting is added (no Xdebug/PCOV wiring in
  `test.yml`) — this change is about adding real assertions, not measuring their coverage.

## Capabilities

### New Capabilities
- `fund-me-plugin`: the plugin's observable behavior — recognizing the `Funding URI` header on
  plugins and themes, and rendering a "Show support" action link when present.

### Modified Capabilities
(none — this change adds tests for existing, unspecified behavior; it does not change what the
plugin does)

## Impact

- Affected code: none (test-only change).
- Affected files: new files under `tests/` (e.g. `tests/test-fund-me.php`); no changes to
  `fund-me.php`, `composer.json`, or `.github/workflows/`.
- Affected systems: `composer run-script ci-test` will now run real assertions instead of only
  the placeholder sample test; the "JUnit Test Report" CI check becomes meaningful.
