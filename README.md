# WPGraphQL Content Blocks

[![Test Plugin](https://github.com/wpengine/wp-graphql-content-blocks/actions/workflows/test-plugin.yml/badge.svg)](https://github.com/wpengine/wp-graphql-content-blocks/actions/workflows/test-plugin.yml)

[![Download Latest Version](https://img.shields.io/github/package-json/version/wpengine/wp-graphql-content-blocks?label=Download%20Latest%20Version)](https://github.com/wpengine/wp-graphql-content-blocks/releases/latest/download/wp-graphql-content-blocks.zip)

WordPress plugin that extends WPGraphQL to support querying (Gutenberg) Blocks as data.

## How to Install

This plugin is an extension of [`wp-graphql`](https://www.wpgraphql.com/), so make sure you have it installed first.

1. Download the [latest .zip version of the plugin](https://github.com/wpengine/wp-graphql-content-blocks/releases/latest/download/wp-graphql-content-blocks.zip)
2. Upload the plugin .zip to your WordPress site
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
      editorBlocks(flat: false) {
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
      editorBlocks(flat: false) {
        __typename
        name
        ... on CoreParagraph {
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
  "__typename": "CoreParagraph",
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
    <p>Example paragraph in Column</p>
    <p></p
  ></column>

  <column></column
></columns>
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

Given the flattened list of blocks though, how can you put it back? Well that's where you use the \`\` and `parentId` fields to assign temporary unique ids for each block.

The `clientId` field assigns a temporary unique id for a specific block and the `parentClientId` will
be assigned only if the current block has a parent. If the current block does have a parent, it will get the parent's `clientId` value.

So in order to put everything back in the Headless site, you want to use the `flatListToHierarchical` function as mentioned in the [WPGraphQL docs](https://www.wpgraphql.com/docs/menus#hierarchical-data).

### Note

> Currently the `clientId` field is only unique per request and is not persisted anywhere. If you perform another request each block will be assigned a new `clientId` each time.

---

## Querying Object-Type Block Attributes in WPGraphQL

### Overview
With this update, you can now query object-type block attributes with each property individually, provided that the **typed structure** is defined in the class `typed_object_attributes` property or through a **WordPress filter**.

### How It Works
The `typed_object_attributes` is a **filterable** array that defines the expected **typed structure** for object-type block attributes.

- The **keys** in `typed_object_attributes` correspond to **object attribute names** in the block.
- Each value is an **associative array**, where:
    - The key represents the **property name** inside the object.
    - The value defines the **WPGraphQL type** (e.g., `string`, `integer`, `object`, etc.).
- If a block attribute has a specified **typed structure**, only the properties listed within it will be processed.

### Defining Typed Object Attributes
Typed object attributes can be **defined in two ways**:

#### 1. In a Child Class (`typed_object_attributes` property)
Developers can extend the `Block` class and specify **typed properties** directly:

```php
class CustomMovieBlock extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array<string, array<string, "array"|"boolean"|"number"|"integer"|"object"|"rich-text"|"string">>
	 */
	protected array $typed_object_attributes = [
		'film' => [
			'id'         => 'integer',
			'title'      => 'string',
			'director'   => 'string',
			'soundtrack' => 'object',
		],
		'soundtrack' => [
			'title'  => 'string',
			'artist' => 'string'
		],
	];
}
```

#### 2. Via WordPress Filter
You can also define **typed structures dynamically** using a WordPress filter.

```php
add_filter(
    'wpgraphql_content_blocks_object_typing_my-custom-plugin_movie-block',
    function () {
        return [
            'film'       => [
                'id'         => 'integer',
                'title'      => 'string',
                'director'   => 'string',
                'soundtrack' => 'object',
            ],
            'soundtrack' => [
                'title'  => 'string',
                'artist' => 'string'
            ],
        ];
    }
);
```

### Filter Naming Convention
To apply custom typing via a filter, use the following format:

```
wpgraphql_content_blocks_object_typing_{block-name}
```
- Replace `/` in the block name with `-`.
- Example:
    - **Block name**: `my-custom-plugin/movie-block`
    - **Filter name**: `wpgraphql_content_blocks_object_typing_my-custom-plugin_movie-block`

### Example:


#### Example `block.json` Definition
If the block has attributes defined as **objects**, like this:

```json
"attributes": {
    "film": {
      "type": "object",
      "default": {
        "id": 1,
        "title": "The Matrix",
        "director": "Director Name"
      }
    },
    "soundtrack": {
      "type": "object",
      "default": {
        "title": "The Matrix Revolutions...",
        "artist": "Artist Name"
      }
    }
}
```
This means:
- The `film` attribute contains `id`, `title`, `director`.
- The `soundtrack` attribute contains `title` and `artist`.

### WPGraphQL Query Example
Once the typed object attributes are **defined**, you can query them **individually** in WPGraphQL.

```graphql
fragment Movie on MyCustomPluginMovieBlock {
    attributes {
        film {
            id
            title
            director
            soundtrack {
                title
            }
        }
        soundtrack {
            title
            artist
        }
    }
}

query GetAllPostsWhichSupportBlockEditor {
    posts {
        edges {
            node {
                editorBlocks {
                    __typename
                    name
                    ...Movie
                }
            }
        }
    }
}
```
---

### Contributor License Agreement

All external contributors to WP Engine products must have a signed Contributor License Agreement (CLA) in place before the contribution may be accepted into any WP Engine codebase.

1. [Submit your name and email](https://wpeng.in/cla/)
2. 📝 Sign the CLA emailed to you
3. 📥 Receive copy of signed CLA

❤️ Thank you for helping us fulfill our legal obligations in order to continue empowering builders through headless WordPress.
