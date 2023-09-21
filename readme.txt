=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.1
Stable tag: 1.2.1
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

= 1.1.3 =

### Patch Changes

- db52dac: Rename `utilities` folder to `Utilities`
- 748d846: Bug Fix. Boolean block attributes no longer always resolve as false.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)