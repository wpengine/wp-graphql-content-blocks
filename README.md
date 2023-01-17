# WPGraphQL Content Blocks

[![End-to-End Tests](https://github.com/wpengine/wp-graphql-content-blocks/actions/workflows/e2e-tests.yml/badge.svg)](https://github.com/wpengine/wp-graphql-content-blocks/actions/workflows/e2e-tests.yml)

WordPress plugin that extends WPGraphQL to support querying (Gutenberg) Blocks as data.

## How to Install

The plugin is an extension of `wp-graphql`, so first of all make sure you have this plugin installed.

1. Clone the repo or download the zip file of the project.
2. Within the plugin folder use `composer` to install the vendor dependencies:

```bash
composer install
```
3. Activate the plugin within WordPress plugins page.


## How to Use

Once the plugin is installed, you need can perform queries from within the `GraphQLi` IDE Block data using the `contentBlocks` field:

```graphql
{
  posts {
    nodes {
      # contentBlocks field represents array of Block data
      contentBlocks {
        # fields from the interface
        renderedHtml
        __typename
        # expand the Paragraph block attributes
        ... on CoreParagraph {
          attributes {
            content
          }
        }
        # expand a Custom block attributes
        ... on CreateBlockMyFirstBlock {
          attributes {
            title
          }
        }
      }
    }
  }
}
```