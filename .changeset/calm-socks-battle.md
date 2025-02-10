---
"@wpengine/wp-graphql-content-blocks": minor
---

Adds support for resolving and returning related term items as a `terms` connection for the CorePostTerms block along with `taxonomy` connection.
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