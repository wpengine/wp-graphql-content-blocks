=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.6.2
Stable tag: 4.1.0
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

= 4.1.0 =

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

= 4.0.1 =

### Patch Changes

- 39e8181: Bug fix: CoreTable column alignment returns null
- 8d8ce66: fix: refactor `Block::resolve_block_attributes_recursive()` and improve type safety
- a910d62: fix: Don't overload `NodeWithEditorBlocks.flat` on implementing Interfaces.

= 4.0.0 =

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

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)
