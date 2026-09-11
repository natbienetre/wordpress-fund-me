## Context

See proposal.md - Why. Relevant mechanics discovered while scoping this change:

- `composer run-script ci-test` runs `wp scaffold plugin-tests fund-me --dir=$(pwd) --force`
  before every test run. That command unconditionally overwrites exactly four fixed files:
  `tests/bootstrap.php`, `tests/test-sample.php`, `phpunit.xml.dist`, `.phpcs.xml.dist`. Nothing
  else under `tests/` is touched.
- The generated `phpunit.xml.dist` already declares:
  ```xml
  <testsuite name="testing">
      <directory prefix="test-" suffix=".php">./tests/</directory>
      <exclude>./tests/test-sample.php</exclude>
  </testsuite>
  ```
  Any committed file matching `tests/test-*.php` is auto-discovered and run.
- The generated `tests/bootstrap.php` loads `fund-me.php` on `muplugins_loaded`, so all five
  functions are defined as globals with full WordPress core available (`add_filter`,
  `has_filter`, `esc_attr`, `_x`, `WP_Theme`, etc.) by the time `WP_UnitTestCase` tests run.
- **Correction, discovered while implementing:** `WP_Theme` is declared `final` in current
  WordPress core (verified against the `7.1` tag downloaded by `install-wp-tests.sh`), so
  `PHPUnit\Framework\MockObject` cannot double it (`ClassIsFinalException`). See Decisions below
  for the fixture-theme approach used instead.
- **Two additional blockers discovered while implementing (not known when this change was
  planned), both required fixes beyond the original no-CI-changes scope:**
  1. PHPUnit 12's `Runner\TestSuiteLoader` requires a matched file's basename (minus `.php`) to
     be a case-insensitive suffix of the loaded test class's short name. wp-cli's mandated
     `test-*.php` naming convention always contains a hyphen, which no valid PHP class name can
     ever satisfy — so no committed test file could ever be discovered by the generated,
     regenerated-every-run `phpunit.xml.dist`, regardless of class name. Fixed by committing a
     project-owned `phpunit.xml` (which PHPUnit prefers over `phpunit.xml.dist` when both exist)
     that references the test file by its actual, hyphen-free class-matching name
     (`tests/TestFundMe.php`) explicitly via `<file>`, instead of relying on the generated file's
     `<directory prefix="test-">` glob.
  2. WordPress core's test suite (`tests/phpunit/includes/abstract-testcase.php`, through at
     least the `7.1` tag) unconditionally calls the removed-in-PHPUnit-10+ internal method
     `PHPUnit\Util\Test::parseTestMethodAnnotations()` from every test's `setUp()`
     (`WP_UnitTestCase_Base::expectDeprecated()`), independent of what the test itself does. This
     is tracked upstream as WordPress core Trac #62004 and made *every* test fail immediately
     under PHPUnit 12. Fixed by reverting the PHPUnit major bump from PR #5
     (`phpunit/phpunit` `^12` → `^9`, `yoast/phpunit-polyfills` `^4` → `^2`) in a separate PR
     (#7), which this change now depends on.

## Goals / Non-Goals

**Goals:**
- Cover all behavior described in `specs/fund-me-plugin/spec.md` with real PHPUnit assertions.
- Keep the tests self-contained in one or more `tests/test-*.php` files, relying only on the
  scaffolding already produced by `ci-test` (no new dev dependencies, no CI workflow edits).

**Non-Goals:**
- Coverage percentage measurement/reporting (see proposal.md - What Changes).
- Changing `fund-me.php` itself. This change adds tests for existing behavior only.
- Patching wp-cli's `scaffold-command` upstream, or the WordPress core test suite upstream
  (Trac #62004), even though both were hit while implementing this change. Both are worked
  around from this repo (a committed `phpunit.xml`, and depending on PR #7's PHPUnit revert)
  rather than fixed at the source.

## Decisions

**Single test file, `tests/test-fund-me.php`, one `WP_UnitTestCase` class.**
The whole plugin is one file with five small, closely related functions; splitting tests
across multiple files would add navigation overhead without a clear seam to split on. If the
plugin grows, tests can be split later — the `test-*.php` discovery pattern supports any number
of files.

**Use two dedicated fixture themes under `tests/data/themes/` for `fundme_themes_action_links`,
instead of mocking `WP_Theme`.**
Originally planned: `createMock(WP_Theme::class)` with a stubbed `get()` method. Rejected after
discovering `WP_Theme` is `final` in current WordPress core, so PHPUnit's mock object generator
cannot double it at all (`ClassIsFinalException`), in any PHPUnit version that enforces `final`
for test doubles.
Alternative considered instead: call `wp_get_theme()` against whatever theme WP-CLI's `latest`
WordPress install ships with, and rely on it lacking a `Funding URI` header for the "no url"
branch. Rejected: it only covers the negative branch (no installed theme in a fresh WP install
declares `Funding URI`), it would silently break if a future WP version's default theme ever
did declare one, and it can't cover the positive branch at all without editing a real theme's
`style.css` inside the test run.
Chosen instead: two minimal fixture theme directories committed under
`tests/data/themes/fundme-test-theme-with-funding/` and `.../fundme-test-theme-no-funding/`,
each with just a `style.css` header block. `WP_Theme`'s constructor is public (only the class
itself is `final`, not its constructor), so `new WP_Theme( $dir, __DIR__ . '/data/themes' )`
builds a real, deterministic instance directly from those fixtures without needing
`wp_get_theme()`'s theme-registry machinery or depending on whichever theme
`install-wp-tests.sh` happens to download. `Funding URI` is parsed correctly because
`fundme_extra_funding_uri` is registered on `extra_theme_headers` unconditionally at plugin load
time (see `fund-me.php`), before any theme headers are read.

**Test `fundme_admin_init` via `has_filter()` assertions, not by asserting filter execution.**
Alternative considered: skip testing `fundme_admin_init` entirely as "trivial glue" (raised
during exploration). Rejected per the proposal's full-coverage scope. `has_filter($hook,
$callback)` after calling `fundme_admin_init()` directly verifies the wiring (hook name,
callback, priority) without needing to simulate a full admin page load — the behavior each
wired filter produces is already covered by the dedicated tests for
`fundme_plugins_action_links` / `fundme_themes_action_links` / `fundme_extra_funding_uri`.

## Risks / Trade-offs

- [Committed test file could be shadowed if wp-cli's scaffold naming convention changes in a
  future wp-cli release] → Low risk (the `test-*.php` glob and `test-sample.php` exclusion are
  stable, long-standing conventions in `wp-cli/scaffold-command`); if it ever changes, CI would
  fail loudly (no tests discovered) rather than silently.
- [Mocking `WP_Theme` tests our own filter-callback logic but not real integration with
  `wp_get_theme()`/theme header parsing] → Accepted trade-off: the goal is unit coverage of this
  plugin's logic, not re-testing WordPress core's own header-parsing, which is out of scope.
