# Fund Me

[![Tests](https://github.com/natbienetre/wordpress-fund-me/actions/workflows/test.yml/badge.svg)](https://github.com/natbienetre/wordpress-fund-me/actions/workflows/test.yml)

A tiny WordPress plugin that helps developers add a donation / sponsorship link to their
WordPress site — for their own plugins and themes, and for any other plugin or theme that
declares a `Funding URI` header.

## What it does

WordPress core doesn't recognize a `Funding URI` header in plugin or theme files out of the
box. Fund Me:

1. Registers `Funding URI` as a recognized extra header for both plugins and themes (via the
   `extra_plugin_headers` and `extra_theme_headers` filters), so WordPress parses it out of the
   plugin/theme file header block instead of ignoring it.
2. Adds a **❤️ Show support** action link — next to *Deactivate* on the Plugins screen, or next
   to *Customize* on the Themes screen — for any plugin or theme that declares a non-empty
   `Funding URI`, linking to that URL in a new tab.

If a plugin or theme has no `Funding URI`, nothing changes for it.

### Declaring a Funding URI

Add a `Funding URI` header to your plugin or theme's main file (or `style.css` for themes):

```php
/**
 * Plugin Name: My Plugin
 * Funding URI: https://github.com/sponsors/you
 */
```

Fund Me itself declares one, pointing at its own maintainer's GitHub Sponsors page.

## Requirements

- WordPress (admin-side only; no front-end behavior)
- PHP 8.3+ for local development (see `composer.json`'s `config.platform.php`)

## Installation

1. Download `fund-me.zip` from the [Releases](https://github.com/natbienetre/wordpress-fund-me/releases) page.
2. Upload and activate it like any other WordPress plugin.

## Development

Install dependencies:

```sh
composer install
```

Run the test suite (requires `svn` and a local MySQL instance reachable with the credentials
below — see `.github/workflows/test.yml` for the exact CI setup):

```sh
composer run-script ci-test
```

This scaffolds a WordPress test environment via WP-CLI (`wp scaffold plugin-tests`,
`bin/install-wp-tests.sh`) against a `wordpress` database on `localhost` with user/password
`root`/`root`, then runs PHPUnit. Tests live in `tests/TestFundMe.php` and cover all of the
plugin's behavior described above.

Build a release zip locally:

```sh
composer run-script build
```

### OpenSpec

This repo uses [OpenSpec](https://github.com/openspecio/openspec) to plan and track behavior
changes. See `openspec/specs/` for the current capability specs and
`openspec/changes/` for changes in progress.

## License

[MPL-2.0](LICENSE)
