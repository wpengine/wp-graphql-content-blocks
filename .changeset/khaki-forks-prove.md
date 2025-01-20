---
"@wpengine/wp-graphql-content-blocks": patch
---

Added CoreListItem block for WPGraphQL Content Blocks.

```gql
		query postQuery($id: ID!) {
			  post(id: $id, idType: DATABASE_ID, asPreview: false) {
			    title
			    editorBlocks(flat: false) {
			      name
			      ... on CoreList {
			        type
			        name
			        renderedHtml
			        innerBlocks {
			          ... on CoreListItem {
			            type
			            name
			      
			            renderedHtml
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
    "post": {
      "title": "Hello world!",
      "editorBlocks": [
        {
          "name": "core/list",
          "type": "CoreList",
          "renderedHtml": "\n<ol class=\"wp-block-list\">\n<li>List item 1</li>\n\n\n\n<li>List item 2\n<ul class=\"wp-block-list is-style-default\">\n<li>Child list item 1\n<ul class=\"wp-block-list\">\n<li>Third level list item</li>\n</ul>\n</li>\n\n\n\n<li>Child list item 2</li>\n</ul>\n</li>\n\n\n\n<li>List item 3</li>\n\n\n\n<li>List item 4</li>\n</ol>\n",
          "innerBlocks": [
            {
              "type": "CoreListItem",
              "name": "core/list-item",
              "renderedHtml": "\n<li>List item 1</li>\n"
            },
            {
              "type": "CoreListItem",
              "name": "core/list-item",
              "renderedHtml": "\n<li>List item 2\n<ul class=\"wp-block-list is-style-default\">\n<li>Child list item 1\n<ul class=\"wp-block-list\">\n<li>Third level list item</li>\n</ul>\n</li>\n\n\n\n<li>Child list item 2</li>\n</ul>\n</li>\n"
            },
            {
              "type": "CoreListItem",
              "name": "core/list-item",
              "renderedHtml": "\n<li>List item 3</li>\n"
            },
            {
              "type": "CoreListItem",
              "name": "core/list-item",
              "renderedHtml": "\n<li>List item 4</li>\n"
            }
          ]
        },
        {
          "name": "core/paragraph"
        }
      ]
    }
  }
}
```