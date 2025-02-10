=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.7.1
Stable tag: 4.7.0
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

= 4.5.0 =

### Minor Changes

- b133a1b: Added WP GraphQL as a required plugin.
- b813352: Adds support for resolving and returning navigation items within the CoreNavigation innerBlocks for WPGraphQL Content Blocks.

  ```graphql
  {
    posts {
      nodes {
        editorBlocks {
          ... on CoreNavigation {
            type
            name
            innerBlocks {
              type
              name
            }
            attributes {
              ref
            }
          }
        }
      }
    }
  }
  ```

  ```json
  {
    "data": {
      "posts": {
        "nodes": [
          {
            "editorBlocks": [
              {
                "type": "CoreNavigation",
                "name": "core/navigation",
                "innerBlocks": [
                  {
                    "type": "CorePageList",
                    "name": "core/page-list"
                  },
                  {
                    "type": "CoreNavigationLink",
                    "name": "core/navigation-link"
                  }
                ],
                "attributes": {
                  "ref": 31
                }
              }
            ]
          },
          {
            "editorBlocks": [{}]
          }
        ]
      }
    }
  }
  ```

### Patch Changes

- dec27c3: feat: Added a `CoreGroup` block class to fix an issue with a missing attribute `cssClassName`

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)