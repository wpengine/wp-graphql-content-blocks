=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.4
Stable tag: 3.1.1
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

= 3.1.1 =

### Patch Changes

- bc32b94: No functional changes between 3.1.0 and 3.1.1. This was tagged due to pipeline issues during the 3.1.0 release.

= 3.1.0 =

### Minor Changes

- 9fab724: Added support for automatic updates hosted from WP Engine infrastructure. Includes warnings when major versions with potential breaking changes are released.

= 3.0.0 =

### Major Changes

- f15f95c: Adds missing default value for content attribute CoreParagraph and CoreCode blocks. This will make the type of the content field `String!` instead of `String`
- 9b71411: Feature: Add support for querying array type query data from blocks

  Query source block attribute types are supported. See: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#query-source

### Patch Changes

- be7a34f: Interface Types are now registered with the Post Type's `graphql_single_name`, instead of the Post Type's `name`. Fixes a bug where invalid Types were registered.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)