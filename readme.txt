=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.1
Stable tag: 2.0.0
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

= 2.0.0 =

### Major Changes

- 7251fb0: Fix: use `use_block_editor_for_post_type` instead of `post_type_supports` when filtering the post types.
  **BREAKING**: Potential schema changes on previously exposed blocks that do not support the block editor. Those blocks will no longer inherit the `editorBlocks` field.

= 1.2.1 =

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

= 1.2.0 =

### Minor Changes

- a118662: Added new `wpgraphql_content_blocks_should_apply_post_type_editor_blocks_interfaces` filter to allow controlling whether ${PostType}EditorBlock interfaces should be applied.

### Patch Changes

- 2e7f2e8: Refactored `register_block_types` to remove usages of `register_graphql_interfaces_to_types` to improve performance.

  Deprecated `Anchor::register_to_block` public static method.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)