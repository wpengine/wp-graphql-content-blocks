=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.1
Stable tag: 0.2.1
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

= 0.2.1 =

### Patch Changes

- 0a29e79: Added support for the `multiline` property in `html` sourced block attributes
- 0a29e79: Added support for `integer` type block attributes
- 0a29e79: Added support for `text` sourced block attributes
- 51011a6: Fix: slow schema / slow queries / unexpected Schema output
- c2e6648: Warn the user if they downloaded the source code .zip instead of the production ready .zip file
- 8955fac: Bug Fix: inner blocks "anchor" field being applied to parent block resulting in duplicates
- c474da8: Add support for querying blocks per post type
- a12542c: Add interface BlockWithSupportsAnchor for querying blocks that supports Anchor field

= 0.2.0 =

### Minor Changes

- 3b27c03: - **[BREAKING]** Changed the `contentBlocks` field to be `editorBlocks`.
- 72e75ea: - **[BREAKING]** Changed `flatlist` to true by default
- 3b27c03: - **[BREAKING]** Changed the `nodeId` field to be `clientId`
  - **[BREAKING]** Changed the `parentId` field to be `parentClientId`

### Patch Changes

- e57855f: Remove the `composer install` step by bundling the prod `vendor` directory with the plugin
- e965de9: Fixed: Undefined index error in Block.php. Thanks @kidunot89!

= 0.1.0 =

- Proof of concept.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)
