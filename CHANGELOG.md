# WPGraphQL Content Blocks

## 1.0.0

### Major Changes

- 44f075b: Transitioned to [Semantic Versioning](https://semver.org). There are no breaking changes in this release.

### Patch Changes

- aeeb613: Added support for cssClassName attribute in CoreSeparator

## 0.3.0

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

## 0.2.1

### Patch Changes

- 0a29e79: Added support for the `multiline` property in `html` sourced block attributes
- 0a29e79: Added support for `integer` type block attributes
- 0a29e79: Added support for `text` sourced block attributes
- 51011a6: Fix: slow schema / slow queries / unexpected Schema output
- c2e6648: Warn the user if they downloaded the source code .zip instead of the production ready .zip file
- 8955fac: Bug Fix: inner blocks "anchor" field being applied to parent block resulting in duplicates
- c474da8: Add support for querying blocks per post type
- a12542c: Add interface BlockWithSupportsAnchor for querying blocks that supports Anchor field

## 0.2.0

### Minor Changes

- 3b27c03: - **[BREAKING]** Changed the `contentBlocks` field to be `editorBlocks`.
- 72e75ea: - **[BREAKING]** Changed `flatlist` to true by default
- 3b27c03: - **[BREAKING]** Changed the `nodeId` field to be `clientId`
  - **[BREAKING]** Changed the `parentId` field to be `parentClientId`

### Patch Changes

- e57855f: Remove the `composer install` step by bundling the prod `vendor` directory with the plugin
- e965de9: Fixed: Undefined index error in Block.php. Thanks @kidunot89!

## 0.1.0

- Proof of concept.
