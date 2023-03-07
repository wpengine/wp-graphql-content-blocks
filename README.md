# WPGraphQL Content Blocks

[![End-to-End Tests](https://github.com/wpengine/wp-graphql-content-blocks/actions/workflows/e2e-tests.yml/badge.svg)](https://github.com/wpengine/wp-graphql-content-blocks/actions/workflows/e2e-tests.yml)

WordPress plugin that extends WPGraphQL to support querying (Gutenberg) Blocks as data.

## How to Install

This plugin is an extension of [`wp-graphql`](https://www.wpgraphql.com/), so make sure you have it installed first.

1. Clone the repo or download the zip file of the project.
2. Within the plugin folder use `composer` to install the vendor dependencies:

```bash
composer install
```
3. Activate the plugin within WordPress plugins page.

There is no other configuration needed once you install the plugin.

## Getting started

Once the plugin is installed, head over to the GraphiQL IDE and you should be able to perform queries for the block data (This plugin is an extension of [wp-graphql](https://www.wpgraphql.com/), so make sure you have it installed first.).
There is a new field added in the Post and Page models called `editorBlocks`.
This represents a list of available blocks for that content type:

```graphql
{
  posts {
    nodes {
      # editorBlocks field represents array of Block data
      editorBlocks {
        # fields from the interface
        renderedHtml
        __typename
        # expand the Paragraph block attributes
        ... on CoreParagraph {
          attributes {
            content
          }
        }
        # expand a Custom block attributes
        ... on CreateBlockMyFirstBlock {
          attributes {
            title
          }
        }
      }
    }
  }
}
```

## How do I query block data?

To query specific block data you need to define that data in the `editorBlocks` as the appropriate type.
For example, to use `CoreParagraph` attributes you need to use the following query:

```graphql
{
  posts {
    nodes {
      editorBlocks {
        __typename
        name
        ...on CoreParagraph {
          attributes {
            content
            className
          }
        }
      }
    }
  }
}
```

If the resolved block has values for those fields, it will return them, otherwise it will return `null`.

```json
{
	"__typename" : "CoreParagraph",
	"name": "core/paragraph",
	"attributes": {
    	"content": "Hello world",
    	"className": null
	}
}
```

## What about innerBlocks?

In order to facilitate querying `innerBlocks` fields more efficiently you want to use `editorBlocks(flat: true)` instead of `editorBlocks`.
By passing this argument, all the blocks available (both blocks and innerBlocks) will be returned all flattened in the same list.

For example, given the following HTML Content:

```html
<columns>
	<column>
		<p>Example paragraph in Column<p>
	</column>
<column>
```

It will return the following blocks:

```json
[
	{
	  "__typename": "CoreColumns",
		"name": "core/columns",
		"id": "63dbec9abcf9d",
		"parentClientId": null
	},
	{
	  "__typename": "CoreColumn",
	  "name": "core/column",
	  "id": "63dbec9abcfa6",
      "parentClientId": "63dbec9abcf9d"
	},
	{
	  "__typename": "CoreParagraph",
	  "name": "core/paragraph",
	  "id": "63dbec9abcfa9",
      "parentClientId": "63dbec9abcfa6",
	  "attributes": {
	    "content": "Example paragraph in Column 1",
	    "className": null
	  }
	}
]
```

The `CoreColumns` contains one or more `CoreColumn` block, and each `CoreColumn` contains a `CoreParagraph`.

Given the flattened list of blocks though, how can you put it back? Well that's where you use the `` and `parentId` fields to assign temporary unique ids for each block.

The `clientId` field assigns a temporary unique id for a specific block and the `parentClientId` will
be assigned only if the current block has a parent. If the current block does have a parent, it will get the parent's `clientId` value.

So in order to put everything back in the Headless site, you want to use the `flatListToHierarchical` function as mentioned in the [WPGraphQL docs](https://www.wpgraphql.com/docs/menus#hierarchical-data).

### Note
> Currently the `clientId` field is only unique per request and is not persisted anywhere. If you perform another request each block will be assigned a new `clientId` each time.
