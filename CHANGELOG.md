# WPGraphQL Content Blocks

## 4.2.0

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

## 4.1.0

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

## 4.0.1

### Patch Changes

- 39e8181: Bug fix: CoreTable column alignment returns null
- 8d8ce66: fix: refactor `Block::resolve_block_attributes_recursive()` and improve type safety
- a910d62: fix: Don't overload `NodeWithEditorBlocks.flat` on implementing Interfaces.

## 4.0.0

### Major Changes

- ed23a32: MAJOR: Update Schema to reflect latest WordPress 6.5 changes.

  - WHAT the breaking change is: Added new `rich-text` type
  - WHY the change was made: WordPress 6.5 replaced some of the attribute types from string to `rich-text` causing breaking changes to the existing block fields.
  - HOW a consumer should update their code: If users need to use WordPress >= 6.5 they need to update this plugin to the latest version and update their graphql schemas.

### Patch Changes

- d62e8db: chore: remove `squizlabs/php_codesniffer` from Composer's direct dependencies.
- e348494: fix: handle arrays before casting when using `Block::normalize_attribute_value()`
- 7bf6bcb: fix: Change Block:get_block_attribute_fields()`$prefix parameter be an optional`string`.
- e6b4ac4: chore: update Composer dev-deps and lint
- 05b21b5: fix: Update parameter type for `$supported_blocks_for_post_type_context` in `wpgraphql_content_blocks_should_apply_post_type_editor_blocks_interfaces` to support boolean values
- 7b49863: chore: Bump PHPStan.neon.dist to level 8 and generate baseline of existing tech debt.
- 0c8e2c7: fix: check for `post_content` before attempting to parse them.
- 8eb1bb8: chore: remove unnecessary `isset()` in Anchor::get_block_interfaces().
- bdff4fb: dev: inline and remove `Block::resolve()` and make `name` field nullable.
- 9b0a63e: fix: Ensure valid `WP_Block_Type` before applying `Anchor` interfaces.
- 2d4a218: fix: : rename `WPGraphQLHelpers` file to match class casing. The file name has been changed from `includes/Utilities/WPGraphqlHelpers.php` to `includes/Utilities/WPGraphQLHelpers.php`.
- d00ee4a: fix: rename `DomHelpers.php` to `DOMHelpers.php` and improve type-safety of internal methods.
- 66f74fb: chore: stub WP_Post_Type and boostrap wp-graphql-content-blocks.php when scanning with PHPStan
- ad03a21: fix: Don't register `NodeWithEditorBlocks` interface to `null` type names.
- 43791db: chore: update PHPStan ruleset for stricter linting, and address newly-discovered tech debt.

## 3.1.2

### Patch Changes

- 1117a18: Fixed issue with updater functionality.

## 3.1.1

### Patch Changes

- bc32b94: No functional changes between 3.1.0 and 3.1.1. This was tagged due to pipeline issues during the 3.1.0 release.

## 3.1.0

### Minor Changes

- 9fab724: Added support for automatic updates hosted from WP Engine infrastructure. Includes warnings when major versions with potential breaking changes are released.

## 3.0.0

### Major Changes

- f15f95c: Adds missing default value for content attribute CoreParagraph and CoreCode blocks. This will make the type of the content field `String!` instead of `String`
- 9b71411: Feature: Add support for querying array type query data from blocks

  Query source block attribute types are supported. See: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#query-source

### Patch Changes

- be7a34f: Interface Types are now registered with the Post Type's `graphql_single_name`, instead of the Post Type's `name`. Fixes a bug where invalid Types were registered.

## 2.0.0

### Major Changes

- 7251fb0: Fix: use `use_block_editor_for_post_type` instead of `post_type_supports` when filtering the post types.

**BREAKING**: Potential schema changes for GraphQL Types representing a Post Type that does not use the Block Editor. Each GraphQL Type representing a Post Type that does not have block editor support previously would have had the `editorBlocks` field but that field will no longer exist on those Types.

## 1.2.1

### Patch Changes

- 54affda: Adds mediaDetails field in CoreImage block:

  ```graphql
  {
    posts {
      nodes {
        editorBlocks {
          ... on CoreImage {
            mediaDetails {
              file
              sizes {
                name
                fileSize
                height
                width
              }
            }
          }
        }
      }
    }
  }
  ```

## 1.2.0

### Minor Changes

- a118662: Added new `wpgraphql_content_blocks_should_apply_post_type_editor_blocks_interfaces` filter to allow controlling whether ${PostType}EditorBlock interfaces should be applied.

### Patch Changes

- 2e7f2e8: Refactored `register_block_types` to remove usages of `register_graphql_interfaces_to_types` to improve performance.

  Deprecated `Anchor::register_to_block` public static method.

## 1.1.3

### Patch Changes

- db52dac: Rename `utilities` folder to `Utilities`
- 748d846: Bug Fix. Boolean block attributes no longer always resolve as false.

## 1.1.2

### Patch Changes

- 28fca4a: Bug Fix: CoreImage `width` attribute throws error.

## 1.1.1

### Patch Changes

- 6259405: Fix semver overrides to v7.5.2
- b2ddbcb: Fix optionator (for word-wrap vln.) overrides to v0.9.3

## 1.1.0

### Minor Changes

- cbcb430: Feat: Add CoreButton and CoreButtons block extra attributes.
- 2e4ac46: Adds the `cssClassName` attribute to the `CoreList` block. This allows you to query for the proper class names that WordPress assigns to the Core List block.

### Patch Changes

- 135252e: Adds cssClassName attribute in CoreHeading.

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
