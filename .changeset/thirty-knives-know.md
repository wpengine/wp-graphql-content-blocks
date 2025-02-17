---
"@wpengine/wp-graphql-content-blocks": minor
---

# Querying Object-Type Block Attributes in WPGraphQL

## Overview
With this update, you can now query object-type block attributes with each property individually, provided that the **typed structure** is defined in the class `typed_object_attributes` property or through a **WordPress filter**.

## How It Works
The `typed_object_attributes` is a **filterable** array that defines the expected **typed structure** for object-type block attributes.

- The **keys** in `typed_object_attributes` correspond to **object attribute names** in the block.
- Each value is an **associative array**, where:
    - The key represents the **property name** inside the object.
    - The value defines the **WPGraphQL type** (e.g., `string`, `integer`, `object`, etc.).
- If a block attribute has a specified **typed structure**, only the properties listed within it will be processed.

## Defining Typed Object Attributes
Typed object attributes can be **defined in two ways**:

### 1. In a Child Class (`typed_object_attributes` property)
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

### 2. Via WordPress Filter
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

## Filter Naming Convention
To apply custom typing via a filter, use the following format:

```
wpgraphql_content_blocks_object_typing_{block-name}
```
- Replace `/` in the block name with `-`.
- Example:
    - **Block name**: `my-custom-plugin/movie-block`
    - **Filter name**: `wpgraphql_content_blocks_object_typing_my-custom-plugin_movie-block`

## Example:


### Example `block.json` Definition
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

## WPGraphQL Query Example
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
