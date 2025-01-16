---
"@wpengine/wp-graphql-content-blocks": patch
---

Adds support for resolving and returning navigation items within the CoreNavigation block for WPGraphQL Content Blocks.

```graphql
{
  posts {
    nodes {
      editorBlocks {
        ... on CoreNavigation {
          renderedHtml
          navigationItems {
            name
            ... on CoreNavigationLink {
              attributes {
                url
              }
            }
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
              "renderedHtml": "<nav>...</nav>",
              "navigationItems": [
                {
                  "name": "core/navigation-link",
                  "attributes": {
                    "url": "http://example.com"
                  }
                }
              ],
              "attributes": {
                "ref": 42
              }
            }
          ]
        }
      ]
    }
  }
}
```
