---
"@wpengine/wp-graphql-content-blocks": minor
---

Adds support for resolving and returning related term items within the `terms` field of the CorePostTerms block.
Adds support for resolving and returning the `prefix`, `suffix` and `term` items within the correspondent fields of the CorePostTerms block.

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
                    term
                    terms {
                        __typename
                        name
                        id
                    }
                }
            }
        }
    }
}
```