## 1. Test scaffolding

- [ ] 1.1 Create `tests/test-fund-me.php` with a `WP_UnitTestCase`-based test class and verify
      it is picked up by `composer run-script ci-test` (matches the scaffolded
      `phpunit.xml.dist`'s `test-*.php` discovery pattern, not excluded like `test-sample.php`).

## 2. Header registration coverage

- [ ] 2.1 Test `fundme_extra_funding_uri` adds `Funding URI` when missing from the header list,
      and verify via a direct call asserting the returned array contains it.
- [ ] 2.2 Test `fundme_extra_funding_uri` returns the list unchanged (no duplicate) when
      `Funding URI` is already present, and verify via a direct call asserting array equality.

## 3. Plugin action links coverage

- [ ] 3.1 Test `fundme_plugins_action_links` appends a support link when `plugin_data['Funding
      URI']` is a non-empty URL, and verify the returned actions array contains markup matching
      `fundme_action_link()`'s expected output for that URL.
- [ ] 3.2 Test `fundme_plugins_action_links` returns the actions array unchanged when
      `plugin_data` has no `Funding URI` entry, and again when it is present but empty, and
      verify both via array equality against the original input.

## 4. Theme action links coverage

- [ ] 4.1 Test `fundme_themes_action_links` appends a support link when a mocked `WP_Theme`'s
      `get('Funding URI')` returns a non-empty URL, and verify the returned actions array
      contains the expected markup.
- [ ] 4.2 Test `fundme_themes_action_links` returns the actions array unchanged when the mocked
      `WP_Theme`'s `get('Funding URI')` returns an empty value, and verify via array equality.

## 5. Support link markup coverage

- [ ] 5.1 Test `fundme_action_link` builds an anchor with `target="_blank"`, an escaped `href`
      matching the input URL, and the localized "Show support" label, and verify via a string
      assertion (or DOM/attribute parsing) against the expected markup shape.

## 6. Filter wiring coverage

- [ ] 6.1 Test `fundme_admin_init` registers the `plugin_action_links`,
      `network_admin_plugin_action_links`, and `theme_action_links` filters with the expected
      callback and priority, and verify via `has_filter()` assertions after calling it directly.

## 7. Verification

- [ ] 7.1 Run `composer run-script ci-test` locally (or via CI) and verify all new tests pass
      alongside the existing scaffolded `SampleTest`, and that `openspec validate
      add-fund-me-test-coverage --strict` passes.
