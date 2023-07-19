=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.1
Stable tag: 1.1.2
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

= 1.1.2 =

### Patch Changes

- 28fca4a: Bug Fix: CoreImage `width` attribute throws error.

= 1.1.1 =

### Patch Changes

- 6259405: Fix semver overrides to v7.5.2
- b2ddbcb: Fix optionator (for word-wrap vln.) overrides to v0.9.3

= 1.1.0 =

### Minor Changes

- cbcb430: Feat: Add CoreButton and CoreButtons block extra attributes.
- 2e4ac46: Adds the `cssClassName` attribute to the `CoreList` block. This allows you to query for the proper class names that WordPress assigns to the Core List block.

### Patch Changes

- 135252e: Adds cssClassName attribute in CoreHeading.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)