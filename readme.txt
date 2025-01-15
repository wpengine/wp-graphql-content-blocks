=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.7.1
Stable tag: 4.4.0
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

= 4.4.0 =

### Minor Changes

- 756471a: feat: add support for resolving PostContent blocks
- 19f6e27: feat: add support for resolving Template Part blocks
- 4c548c3: feat: add support for resolving Block Patterns

= 4.3.2 =

### Patch Changes

- c8832fc: fix: improve handling of empty blocks in `ContentBlocksResolver`.
- 9a2ebf7: fix: Ensure correct `EditorBlock.type` field resolution.

= 4.3.1 =

### Patch Changes

- f99f768: Correct version definition

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)