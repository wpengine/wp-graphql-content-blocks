---
"@wpengine/wp-graphql-content-blocks": minor
---

Added CoreListItem block and new fields to query CoreListItem for the CoreList block.

Notable changes:

Added the field items which retrieves the CoreListItem child blocks for a CoreList.
For the CoreListItem block we added children and value so that we can query values and child blocks if they are core/list-iem blcoks

Example query 

```gql
query postQuery($id: ID!) {
  post(id: $id, idType: DATABASE_ID, asPreview: false) {
    title
    editorBlocks(flat: false) {
      name
      ... on CoreList {
        ordered
        items {
          value
          children {
            value
            children {
              value
            }
          }
        }
      }
    }
  }
}
```

This returns an array of items and child items for that block e.g. 

```json
{
  "data": {
    "post": {
      "title": "Hello world!",
      "editorBlocks": [
        {
          "name": "core/list",
          "ordered": true,
          "items": [
            {
              "value": "<li>List item 1</li>",
              "children": []
            },
            {
              "value": "<li>List item 2</li>",
              "children": [
                {
                  "value": "<li>Child list item 1</li>",
                  "children": [
                    {
                      "value": "<li>Third level list item</li>"
                    }
                  ]
                },
                {
                  "value": "<li>Child list item 2</li>",
                  "children": []
                }
              ]
            },
            {
              "value": "<li>List item 3</li>",
              "children": []
            },
            {
              "value": "<li>List item 4</li>",
              "children": []
            }
          ]
        }
      ]
    }
  },
}
```