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
- Adds a project-owned `phpunit.xml` (PHPUnit prefers this over the regenerated
  `phpunit.xml.dist` when both exist), pointing directly at `tests/TestFundMe.php`. Discovered
  while implementing: PHPUnit 12's test loader can never discover any file matching wp-cli's
  mandated `test-*.php` naming convention (the hyphen makes the class-name match structurally
  impossible), so the originally-planned "just add `tests/test-*.php`, it's auto-discovered"
  approach doesn't work as-is. See design.md for detail.
- Adds two minimal fixture themes under `tests/data/themes/` used to test
  `fundme_themes_action_links` against real `WP_Theme` instances (discovered while
  implementing: `WP_Theme` is `final` in current WordPress core and can't be mocked).
- Depends on PR #7 (`revert-phpunit-major`): WordPress core's test suite is incompatible with
  PHPUnit 10+ (Trac #62004), discovered while implementing this change, and reverted
  separately.
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

- Affected code: none (test-only change to `fund-me.php`).
- Affected files: `tests/TestFundMe.php`, `tests/data/themes/**/style.css` (fixtures), and a new
  `phpunit.xml`; no changes to `fund-me.php`, `composer.json` scripts, or `.github/workflows/`
  in *this* PR (the PHPUnit major-version revert it depends on is PR #7).
- Affected systems: `composer run-script ci-test` will now run real assertions instead of only
  the placeholder sample test; the "JUnit Test Report" CI check becomes meaningful.
