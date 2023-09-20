---
"@wpengine/wp-graphql-content-blocks": patch
---

Adds mediaDetails field in CoreImage block:

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
