---
"@wpengine/wp-graphql-content-blocks": minor
---

Adds support for resolving and returning navigation items within the CoreNavigation innerBlocks for WPGraphQL Content Blocks.

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
            },
          ]
        },
        {
          "editorBlocks": [
            {}
          ]
        }
      ]
    }
  },
}
```
