---
"@wpengine/wp-graphql-content-blocks": minor
---

Adds support for specifying typed and queryable properties for object attributes in block.json.

Example: Defining a Typed Object in `block.json`:

```json
"attributes": {
  "film": {
    "type": "object",
    "default": {
      "id": 0,
      "title": "Film Title",
      "director": "Director Name",
      "__typed": {
        "id": "integer",
        "title": "string",
        "director": "string",
        "year": "string"
      }
    }
  }
}
```

In this example, the `film` attribute is an object with defined types for each property (`id`, `title`, `director`, and optionally `year`).

Querying Object Properties in GraphQL:


```graphql
fragment Film on MyPluginFilmBlock {
    attributes {
        film {
            id,
            title,
            director,
            year
        }
    },
}

query GetAllPostsWhichSupportBlockEditor {
    posts {
        edges {
            node {
                editorBlocks {
                    __typename
                    name
                    ...Film
                }
            }
        }
    }
}
```