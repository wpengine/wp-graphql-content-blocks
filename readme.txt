=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.7.1
Stable tag: 4.3.2
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

= 4.3.2 =

### Patch Changes

- c8832fc: fix: improve handling of empty blocks in `ContentBlocksResolver`.
- 9a2ebf7: fix: Ensure correct `EditorBlock.type` field resolution.

= 4.3.1 =

### Patch Changes

- f99f768: Correct version definition

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

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)