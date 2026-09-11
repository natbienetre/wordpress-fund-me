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
- `WP_Theme` is a concrete, non-final class. PHPUnit's `createMock()` can stub its `get()`
  method without needing a real installed theme that declares a `Funding URI` header.

## Goals / Non-Goals

**Goals:**
- Cover all behavior described in `specs/fund-me-plugin/spec.md` with real PHPUnit assertions.
- Keep the tests self-contained in one or more `tests/test-*.php` files, relying only on the
  scaffolding already produced by `ci-test` (no new dev dependencies, no CI workflow edits).

**Non-Goals:**
- Coverage percentage measurement/reporting (see proposal.md - What Changes).
- Fixing the unrelated, pre-existing deprecation risk in the scaffolded `phpunit.xml.dist`
  (PHPUnit-9-era config attributes like `backupGlobals`/`convertErrorsToExceptions`, which
  PHPUnit 12 has dropped support for). That file is regenerated from `wp-cli/scaffold-command`'s
  own template every run, so it can't be durably fixed from this repo without either patching
  `ci-test` to post-process the generated file or waiting on an upstream wp-cli fix. Out of
  scope here since CI currently passes anyway (tolerated, not a hard error).
- Changing `fund-me.php` itself. This change adds tests for existing behavior only.

## Decisions

**Single test file, `tests/test-fund-me.php`, one `WP_UnitTestCase` class.**
The whole plugin is one file with five small, closely related functions; splitting tests
across multiple files would add navigation overhead without a clear seam to split on. If the
plugin grows, tests can be split later — the `test-*.php` discovery pattern supports any number
of files.

**Mock `WP_Theme` for `fundme_themes_action_links` instead of using a real installed theme.**
Alternative considered: call `wp_get_theme()` against whatever theme WP-CLI's `latest`
WordPress install ships with, and rely on it lacking a `Funding URI` header for the "no url"
branch. Rejected: it only covers the negative branch (no installed theme in a fresh WP install
declares `Funding URI`), it would silently break if a future WP version's default theme ever
did declare one, and it can't cover the positive branch at all without editing a real theme's
`style.css` inside the test run. A `createMock(WP_Theme::class)` with a stubbed `get()` method
covers both branches deterministically and doesn't depend on which theme ships with whatever WP
version `install-wp-tests.sh` downloads.

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
