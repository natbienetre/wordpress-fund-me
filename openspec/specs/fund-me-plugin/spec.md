## Purpose

Lets plugin and theme authors who declare a `Funding URI` header get a "Show support" link
shown next to their plugin or theme in the WordPress admin, without requiring any additional
configuration beyond that header.

## Requirements

### Requirement: Funding URI is a recognized extra header
The system SHALL register `Funding URI` as a recognized extra header for both plugins and
themes, so WordPress core parses it out of plugin/theme file headers instead of ignoring it.

#### Scenario: Header list is missing Funding URI
- **WHEN** the list of extra plugin headers (or extra theme headers) does not already contain
  `Funding URI`
- **THEN** `Funding URI` is added to that list

#### Scenario: Header list already contains Funding URI
- **WHEN** the list of extra plugin headers (or extra theme headers) already contains
  `Funding URI`
- **THEN** the list is returned unchanged, with no duplicate entry

### Requirement: Support link is added to plugins declaring a Funding URI
The system SHALL append a "Show support" action link to a plugin's action links whenever that
plugin declares a non-empty `Funding URI` header, and SHALL leave the action links unchanged
otherwise.

#### Scenario: Plugin declares a Funding URI
- **WHEN** a plugin's header data includes a non-empty `Funding URI`
- **THEN** a "Show support" action link pointing to that URI is appended to the plugin's
  action links

#### Scenario: Plugin has no Funding URI
- **WHEN** a plugin's header data has no `Funding URI` entry, or it is empty
- **THEN** the plugin's action links are returned unchanged

### Requirement: Support link is added to themes declaring a Funding URI
The system SHALL append a "Show support" action link to a theme's action links whenever that
theme declares a non-empty `Funding URI` header, and SHALL leave the action links unchanged
otherwise.

#### Scenario: Theme declares a Funding URI
- **WHEN** a theme's `Funding URI` header value is non-empty
- **THEN** a "Show support" action link pointing to that URI is appended to the theme's action
  links

#### Scenario: Theme has no Funding URI
- **WHEN** a theme's `Funding URI` header value is empty or absent
- **THEN** the theme's action links are returned unchanged

### Requirement: Support link markup
The system SHALL render the "Show support" action link as an anchor that opens in a new tab,
with the target URL escaped for use in an HTML attribute, and a localized "Show support" label.

#### Scenario: Link is built from a funding URL
- **WHEN** the support link is built for a given funding URL
- **THEN** the resulting markup is an `<a>` element with `target="_blank"`, an `href` containing
  the escaped URL, and a "Show support" label

### Requirement: Filters are registered on admin_init
The system SHALL register its action-link and header-recognition filters when the WordPress
admin initializes, so the behavior above is active for every admin page load.

#### Scenario: Admin initializes
- **WHEN** the WordPress admin `admin_init` action fires
- **THEN** the plugin's plugin-action-links filter, network-admin-plugin-action-links filter,
  and theme-action-links filter are all registered
