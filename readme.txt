=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.6.2
Stable tag: 4.3.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extends WPGraphQL to support querying (Gutenberg) Blocks as data.

== Description ==

Extends WPGraphQL to support querying (Gutenberg) Blocks as data.

== Installation ==

1. Search for the plugin in WordPress under "Plugins -> Add New".
2. Click the “Install Now” button, followed by "Activate".

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 4.3.0 =

### Minor Changes

- d123b81: dev: Refactor attribute resolution into `Data\BlockAttributeResolver`
- d123b81: feat: add support for parsing (deprecated) `meta` attributes.

### Patch Changes

- 96bad40: tests: fix `setUp()`/`tearDown()` methods to prevent PHPUnit lifecycle issues.
- f898d61: tests : Add tests for `CoreList` and `CoreListItem` blocks.
- 3b32f06: tests : Backfill tests for Core Image block.
- 7301ed9: tests: Add tests for CoreHeading block
- d4d7374: tests : Backfill tests for Core Video block.
- 3a1157b: fix: Correctly parse nested attribute and tag sources.
- 8b2e168: tests : Add tests for `CoreSeparator` block.
- 962081d: tests: Add tests for CoreParagraph block
- 5915c06: tests: Add tests for CorePreformatted Block
- 3a1157b: tests: backfill tests for `CoreTable` attributes.
- a02e75a: tests: Add tests for CoreCode Block
- c6bdab0: tests : Add tests for `CoreQuote` block.
- a38e479: tests : backfill tests for ContentBlockResolver

= 4.2.0 =

### Minor Changes

- 766737d: fix: cleanup constants and refactor autoload handling to improve Composer compatibility.
- 7514021: chore: Update Composer dev-dependencies to their latest (semver-compatible) versions.
- b64583f: dev: Add `wpgraphql_content_blocks_pre_resolve_blocks` and `wp_graphql_content_blocks_resolve_blocks` filters.
- 179948c: dev: make `PluginUpdater` namespaced functions PSR-4 compatible.
- bced76d: feat: expose `EditorBlock.type` field

### Patch Changes

- de885f1: Skip the Sonar Qube workflow if the user that opened the PR is not a member of the Github org
- 6ced628: Fix: prevent fatal errors when get_current_screen() is unset.
- 58b6792: chore: remediate non-code PHPStan errors in phpstan-baseline.neon
- c3e11b1: ci: test against WordPress 6.6
- 27f459f: tests: fix PHP deprecation notices
- 4f4b851: tests: fix order of expected/actual values passed to asserts.
- 89b6c60: tests: lint and format PHPUnit tests
- 65f0c2d: Update @since @todo tags and @todo placeholders in \_deprecated_function calls

= 4.1.0 =

### Minor Changes

- 6241c4e: fix: prevent fatal errors by improving type-safety and returning early when parsing HTML.
  The following methods have been deprecated for their stricter-typed counterparts:
  - `DOMHelpers::parseAttribute()` => `::parse_attribute()`
  - `DOMHelpers::parseFirstNodeAttribute()` => `::parse_first_node_attribute()`
  - `DOMHelpers::parseHTML()` => `::parse_html()`
  - `DOMHelpers::getElementsFromHTML()` => `::get_elements_from_html()`
  - `DOMHelpers::parseText()` => `::parse_text()`
  - `DOMHelpers::findNodes()`=> `::find_nodes()`

### Patch Changes

- 2b947dc: chore: update Composer dev-dependencies and fix resulting issues.
- 205da8c: ci: replace `docker-compose` commands with `docker compose`
- 5c21ce3: Bug fix. Reusable block isn't resolved inside innerBlocks.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)