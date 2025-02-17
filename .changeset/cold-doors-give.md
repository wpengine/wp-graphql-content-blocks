---
"@wpengine/wp-graphql-content-blocks": major
---

Replaced core/block with core/synced-pattern for reusable blocks, aligning with WP 6.3's synced patterns. 

**🚨Breaking change🚨** for WordPress versions < 6.3, as core/synced-pattern does not exist in earlier versions.

Query:
```
{
  posts {
    nodes {
      editorBlocks {
        name
        clientId
        parentClientId
        ... on CoreSyncedPattern {
          attributes {
            slug
          }
          name
          innerBlocks {
            name
            clientId
            parentClientId
          }
        }
      }
    }
  }
}
```
Response:
```
{
  "data": {
    "posts": {
      "nodes": [
        {
          "editorBlocks": [
            {
              "name": "core/synced-pattern",
              "clientId": "67b317909b801",
              "parentClientId": null,
              "attributes": {
                "slug": "my-synced-pattern"
              },
              "innerBlocks": [
                {
                  "name": "core/group",
                  "clientId": "67b317909b89a",
                  "parentClientId": null
                }
              ]
            },
            {
              "name": "core/group",
              "clientId": "67b317909b89a",
              "parentClientId": "67b317909b801"
            }
            ]
        }
      ]
    }
  }
}
```
