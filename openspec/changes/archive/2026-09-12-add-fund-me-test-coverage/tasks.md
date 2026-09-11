## 1. Test scaffolding

- [x] 1.1 Create `tests/TestFundMe.php` with a `WP_UnitTestCase`-based test class and verify
      it is picked up by `composer run-script ci-test`. (Renamed from the originally-planned
      `test-fund-me.php`, and added a project-owned `phpunit.xml` referencing it explicitly:
      PHPUnit 12's test loader can never discover a hyphenated `test-*.php` filename via the
      generated `phpunit.xml.dist`'s directory glob, regardless of class name — see design.md.)

## 2. Header registration coverage

- [x] 2.1 Test `fundme_extra_funding_uri` adds `Funding URI` when missing from the header list,
      and verify via a direct call asserting the returned array contains it.
- [x] 2.2 Test `fundme_extra_funding_uri` returns the list unchanged (no duplicate) when
      `Funding URI` is already present, and verify via a direct call asserting array equality.

## 3. Plugin action links coverage

- [x] 3.1 Test `fundme_plugins_action_links` appends a support link when `plugin_data['Funding
      URI']` is a non-empty URL, and verify the returned actions array contains markup matching
      `fundme_action_link()`'s expected output for that URL.
- [x] 3.2 Test `fundme_plugins_action_links` returns the actions array unchanged when
      `plugin_data` has no `Funding URI` entry, and again when it is present but empty, and
      verify both via array equality against the original input.

## 4. Theme action links coverage

- [x] 4.1 Test `fundme_themes_action_links` appends a support link when a real `WP_Theme`
      instance (built from the `tests/data/themes/fundme-test-theme-with-funding` fixture)
      declares a non-empty `Funding URI`, and verify the returned actions array contains the
      expected markup. (`WP_Theme` is `final` in current WordPress core and can't be mocked;
      see design.md.)
- [x] 4.2 Test `fundme_themes_action_links` returns the actions array unchanged when a real
      `WP_Theme` instance (built from the `fundme-test-theme-no-funding` fixture) has no
      `Funding URI`, and verify via array equality.

## 5. Support link markup coverage

- [x] 5.1 Test `fundme_action_link` builds an anchor with `target="_blank"`, an escaped `href`
      matching the input URL, and the localized "Show support" label, and verify via a string
      assertion (or DOM/attribute parsing) against the expected markup shape.

## 6. Filter wiring coverage

- [x] 6.1 Test `fundme_admin_init` registers the `plugin_action_links`,
      `network_admin_plugin_action_links`, and `theme_action_links` filters with the expected
      callback and priority, and verify via `has_filter()` assertions after calling it directly.

## 7. Verification

- [x] 7.1 Run `composer run-script ci-test` locally (or via CI) and verify all new tests pass
      alongside the existing scaffolded `SampleTest`, and that `openspec validate
      add-fund-me-test-coverage --strict` passes.
