=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.1
Stable tag: 1.1.0
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

= 1.1.0 =

### Minor Changes

- cbcb430: Feat: Add CoreButton and CoreButtons block extra attributes.
- 2e4ac46: Adds the `cssClassName` attribute to the `CoreList` block. This allows you to query for the proper class names that WordPress assigns to the Core List block.

### Patch Changes

- 135252e: Adds cssClassName attribute in CoreHeading.

= 1.0.0 =

### Major Changes

- 44f075b: Transitioned to [Semantic Versioning](https://semver.org). There are no breaking changes in this release.

### Patch Changes

- aeeb613: Added support for cssClassName attribute in CoreSeparator

= 0.3.0 =

### Minor Changes

- 5765443: Fix regression where intentionally empty blocks were removed, if blocks have names they are now retained.
- eb8e364: Add support for Reusable Blocks
- 1bde257: Fix regression with addition of anchor support - only register interface once
- bc0b5a4: Rename BlockAttributesObject() to get_block_attributes_object_type_name

### Patch Changes

- a42c828: Bug Fix: CPTs containing dashes creates error in Block Registration.
- b900f1f: chore: bump min PHP version to 7.4
- b075a98: fix: Correctly check if `$block_attributes` are set when attempting to register the block attribute fields to WPGraphQL.
- 5d043b4: fix: Implement better type checking in `ContentBlocksResolver::resolve_content_blocks()` to prevent possible fatal errors on edge cases.
- 6621170: Use render_block instead of innerHTML when filtering blocks
- 8b13b32: dev: Change comparison of `$attribute_config['type']` to use Yoda conditional.
- addf06f: fix: Ensure `WPHelpers::get_supported_post_types()` correctly returns `\WP_Post_Type[]`.
- eff9847: chore: Add missing `\` to docblock types.
- 733737f: tests: Fix `RegistryTestCase` autoloading and lint `DomHelperTest`
- ddac2eb: fix: Cleanup unnecessary conditional checks.
- 536848a: fix: Don't return the `WPGraphQLContentBlocks` instance when initializing the plugin via the `plugins_loaded` action.
- 8b13b32: dev: Remove unused method params from the block attribute field resolver callback.
- 8b13b32: fix: Replace the usage of `'wp-graphql'` text-domain with the correct `'wp-graphql-content-blocks'`.
- 99bc5a4: chore: Add missing return types to multiple methods.
- f6541d9: fix: Implement better type checking in `Blocks\Block` class to prevent possible fatal errors on edge cases.
- f0bc286: fix: Improve `WPGraphQLHelpers::format_type_name()` handling of `null` and empty strings, and use it in more places in the codebase.
- 11c0676: Added `cssClassName` attribute on `CoreQuote` Block
- 45f9ce3: fix: Bad check for empty value in `DOMHelpers::parseFirstNodeAttribute()`.
- 56f1b1e: dev: Rename `WPGraphQL\ContentBlocks\Registry::OnInit()` and `WPGraphQL\ContentBlocks\Type\Scalar::OnInit()` methods to `::init()` to comply with WPCS ruleset.
- fe38180: dev: Remove unnecessary `use( $type_registry )` from Interface 'resolveType' callbacks.
- c7290cd: chore: Disable PHPCS linting for `/tests` directory
- ee722d2: chore: Fix existing PHPCS smells for doc-blocks and comments.
- 2f02d7d: dev: Deprecate the unused `$context` param on EditorBlockInterface::get_blocks(), and update all internal usage of that method.
- 8b13b32: fix: Ensure proper string translation, concatenation, and escaping.
- f44fb6f: fix: Use `wp_rand()` instead of `rand()`.
- 16d43eb: chore: Set the minimum PHP version in `composer.json` to v7.2 (and the platform req to v7.3) to ensure contributions are built against the correct dependencies.
- 949af70: fix: Use strict string comparison when parsing the attribute selector.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)