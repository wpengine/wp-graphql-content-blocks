=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.7.1
Stable tag: 4.8.0
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

= 4.8.0 =

### Minor Changes

- 742f18a: # Querying Object-Type Block Attributes in WPGraphQL

  ## Overview

  With this update, you can now query object-type block attributes with each property individually, provided that the **typed structure** is defined in the class `typed_object_attributes` property or through a **WordPress filter**.

  ## How It Works

  The `typed_object_attributes` is a **filterable** array that defines the expected **typed structure** for object-type block attributes.

  - The **keys** in `typed_object_attributes` correspond to **object attribute names** in the block.
  - Each value is an **associative array**, where:
    - The key represents the **property name** inside the object.
    - The value defines the **WPGraphQL type** (e.g., `string`, `integer`, `object`, etc.).
  - If a block attribute has a specified **typed structure**, only the properties listed within it will be processed.

  ## Defining Typed Object Attributes

  Typed object attributes can be **defined in two ways**:

  ### 1. In a Child Class (`typed_object_attributes` property)

  Developers can extend the `Block` class and specify **typed properties** directly:

  ```php
  class CustomMovieBlock extends Block {
  	/**
  	 * {@inheritDoc}
  	 *
  	 * @var array<string, array<string, "array"|"boolean"|"number"|"integer"|"object"|"rich-text"|"string">>
  	 */
  	protected array $typed_object_attributes = [
  		'film' => [
  			'id'         => 'integer',
  			'title'      => 'string',
  			'director'   => 'string',
  			'soundtrack' => 'object',
  		],
  		'soundtrack' => [
  			'title'  => 'string',
  			'artist' => 'string'
  		],
  	];
  }
  ```

  ### 2. Via WordPress Filter

  You can also define **typed structures dynamically** using a WordPress filter.

  ```php
  add_filter(
      'wpgraphql_content_blocks_object_typing_my-custom-plugin_movie-block',
      function () {
          return [
              'film'       => [
                  'id'         => 'integer',
                  'title'      => 'string',
                  'director'   => 'string',
                  'soundtrack' => 'object',
              ],
              'soundtrack' => [
                  'title'  => 'string',
                  'artist' => 'string'
              ],
          ];
      }
  );
  ```

  ## Filter Naming Convention

  To apply custom typing via a filter, use the following format:

  ```
  wpgraphql_content_blocks_object_typing_{block-name}
  ```

  - Replace `/` in the block name with `-`.
  - Example:
    - **Block name**: `my-custom-plugin/movie-block`
    - **Filter name**: `wpgraphql_content_blocks_object_typing_my-custom-plugin_movie-block`

  ## Example:

  ### Example `block.json` Definition

  If the block has attributes defined as **objects**, like this:

  ```json
  "attributes": {
      "film": {
        "type": "object",
        "default": {
          "id": 1,
          "title": "The Matrix",
          "director": "Director Name"
        }
      },
      "soundtrack": {
        "type": "object",
        "default": {
          "title": "The Matrix Revolutions...",
          "artist": "Artist Name"
        }
      }
  }
  ```

  This means:

  - The `film` attribute contains `id`, `title`, `director`.
  - The `soundtrack` attribute contains `title` and `artist`.

  ## WPGraphQL Query Example

  Once the typed object attributes are **defined**, you can query them **individually** in WPGraphQL.

  ```graphql
  fragment Movie on MyCustomPluginMovieBlock {
    attributes {
      film {
        id
        title
        director
        soundtrack {
          title
        }
      }
      soundtrack {
        title
        artist
      }
    }
  }

  query GetAllPostsWhichSupportBlockEditor {
    posts {
      edges {
        node {
          editorBlocks {
            __typename
            name
            ...Movie
          }
        }
      }
    }
  }
  ```

= 4.7.0 =

### Minor Changes

- 82c6080: Adds support for resolving and returning related term items as a `terms` connection for the CorePostTerms block along with `taxonomy` connection.
  Adds support for resolving and returning the `prefix` and `suffix` items within the correspondent fields of the CorePostTerms block.

  ```graphql
  query TestPostTerms($uri: String! = "test-terms") {
    nodeByUri(uri: $uri) {
      id
      uri
      ... on NodeWithPostEditorBlocks {
        editorBlocks {
          __typename
          ... on CorePostTerms {
            prefix
            suffix
            taxonomy {
              __typename
              node {
                __typename
                id
                name
              }
            }
            terms {
              __typename
              nodes {
                __typename
                id
                name
              }
            }
          }
        }
      }
    }
  }
  ```

= 4.6.0 =

### Minor Changes

- 7838c93: Replaced old plugin service to use the WPE updater service for checking for updates. The new API endpoint will be https://wpe-plugin-updates.wpengine.com/wp-graphql-content-blocks/info.json

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)