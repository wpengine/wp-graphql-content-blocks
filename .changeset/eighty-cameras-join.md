---
"@wpengine/wp-graphql-content-blocks": patch
---

Added content resolver for CoreFootnotes when the post_meta isn't loaded


```gql
fragment CoreFootnotesBlockFragment on CoreFootnotes {
  innerBlocks {
    renderedHtml
  }
}

query Post($id: ID!) {
  post(id: $id, idType: DATABASE_ID) {
    databaseId
    editorBlocks {
      ...CoreFootnotesBlockFragment
    }
  }
}
```


```json
{
  "data": {
    "post": {
      "databaseId": 16,
      "editorBlocks": [
        {},
        {
          "innerBlocks": [
            {
              "renderedHtml": "<ol class=\"footnotes\"><li id=\"d4051e5e-1547-49ff-ab6d-bec1caa6aabc\"><a href=\"https://wpengine.com/about-us/\">https://wpengine.com/about-us/</a></li><li id=\"2e79de23-68a8-42eb-87ab-1f2467a21752\"><a href=\"https://wpengine.com/support/\">https://wpengine.com/support/</a></li></ol>"
            }
          ]
        },
        {}
      ]
    }
  }
}
```