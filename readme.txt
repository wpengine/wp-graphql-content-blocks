=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.4
Stable tag: 3.0.0
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

= 3.0.0 =

### Major Changes

- f15f95c: Adds missing default value for content attribute CoreParagraph and CoreCode blocks. This will make the type of the content field `String!` instead of `String`
- 9b71411: Feature: Add support for querying array type query data from blocks

  Query source block attribute types are supported. See: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/#query-source

### Patch Changes

- be7a34f: Interface Types are now registered with the Post Type's `graphql_single_name`, instead of the Post Type's `name`. Fixes a bug where invalid Types were registered.

= 2.0.0 =

### Major Changes

- 7251fb0: Fix: use `use_block_editor_for_post_type` instead of `post_type_supports` when filtering the post types.

**BREAKING**: Potential schema changes for GraphQL Types representing a Post Type that does not use the Block Editor. Each GraphQL Type representing a Post Type that does not have block editor support previously would have had the `editorBlocks` field but that field will no longer exist on those Types.

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

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)