=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.8.1
Stable tag: 4.8.8
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

= 4.8.8 =


### Bug Fixes

* resolve block anchors from saved attributes ([#527](https://github.com/wpengine/wp-graphql-content-blocks/issues/527)) ([8a7a40b](https://github.com/wpengine/wp-graphql-content-blocks/commit/8a7a40bfe464d1e8470e2410d31c364447193d61))
* return stable block clientId values for unchanged content ([#528](https://github.com/wpengine/wp-graphql-content-blocks/issues/528)) ([1c2dcfe](https://github.com/wpengine/wp-graphql-content-blocks/commit/1c2dcfeb202bd71f8ae55aaa9c28106d5c7c01a8))


### Dependencies

* bump markdown-it and @wordpress/scripts ([#513](https://github.com/wpengine/wp-graphql-content-blocks/issues/513)) ([f8e7f17](https://github.com/wpengine/wp-graphql-content-blocks/commit/f8e7f176a3ae97d630cc065500459641f2124c1f))


### Build System

* **deps-dev:** bump follow-redirects from 1.16.0 to 1.16.1 ([#512](https://github.com/wpengine/wp-graphql-content-blocks/issues/512)) ([57ebd83](https://github.com/wpengine/wp-graphql-content-blocks/commit/57ebd83a002d0b825b67a40e780857912b266046))
* **deps-dev:** bump launch-editor from 2.14.1 to 2.14.2 ([#518](https://github.com/wpengine/wp-graphql-content-blocks/issues/518)) ([2182a18](https://github.com/wpengine/wp-graphql-content-blocks/commit/2182a18a24168314b81a1e51466a2a81137cb65a))

= 4.8.7 =

### Patch Changes

- 9d9751b: Fixed `textAlign` disappearing from `CoreHeading` and `CoreButton` on WordPress 7.0, where text alignment moved from a block attribute to a block support. Any block with `supports.typography.textAlign`, including `CoreParagraph`, now exposes `textAlign`, read from `style.typography.textAlign` and falling back to the top-level attribute for content saved before WordPress 7.0.

= 4.8.6 =

### Patch Changes

- 693ea8a: Security: password-protected posts no longer expose block content through the `editorBlocks` field to unauthenticated GraphQL callers.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)