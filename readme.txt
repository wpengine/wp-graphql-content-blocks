=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.8.1
Stable tag: 4.8.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires Plugins: wp-graphql

Extends WPGraphQL to support querying (Gutenberg) Blocks as data.

== Description ==

Extends WPGraphQL to support querying (Gutenberg) Blocks as data.

== Installation ==

1. Search for the plugin in WordPress under "Plugins -> Add New".
2. Click the “Install Now” button, followed by "Activate".

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 4.8.7 =

### Patch Changes

- 9d9751b: Fixed `textAlign` disappearing from `CoreHeading` and `CoreButton` on WordPress 7.0, where text alignment moved from a block attribute to a block support. Any block with `supports.typography.textAlign`, including `CoreParagraph`, now exposes `textAlign`, read from `style.typography.textAlign` and falling back to the top-level attribute for content saved before WordPress 7.0.

= 4.8.6 =

### Patch Changes

- 693ea8a: Security: password-protected posts no longer expose block content through the `editorBlocks` field to unauthenticated GraphQL callers.

= 4.8.5 =

### Patch Changes

- 1c3b6df: Patch development-only security advisories by overriding transitive `uuid` (→ ^11.1.1) and `webpack-dev-server` (→ ^5.2.4). These bumps affect build/test tooling only and do not change any code shipped to consumers of the plugin.
- f52cb9e: Add WordPress 6.9 compatibility and update test matrix to support WordPress 6.9, 6.8 with PHP 8.3, 8.1.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)