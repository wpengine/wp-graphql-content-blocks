<?php

namespace WPGraphQL\ContentBlocks\Data;

use WPGraphQL\Model\Post;

/**
 * Class ContentBlocksResolver
 *
 * @package WPGraphQL\ContentBlocks
 */
final class ContentBlocksResolver {
	/**
	 * Retrieves a list of content blocks
	 *
	 * @param mixed $node The node we are resolving.
	 * @param array $args GraphQL query args to pass to the connection resolver.
	 * @param array $allowed_block_names The list of allowed block names to filter.
	 */
	public static function resolve_content_blocks( $node, $args, $allowed_block_names = array() ) {
		$content = null;
		if ( $node instanceof Post ) {

			// @todo: this is restricted intentionally.
			// $content = $node->contentRaw;

			// This is the unrestricted version, but we need to
			// probably have a "Block" Model that handles
			// determining what fields should/should not be
			// allowed to be returned?
			$post    = get_post( $node->databaseId );
			$content = $post->post_content;
		}

		/**
		 * Filters the content retrieved from the node used to parse the blocks.
		 *
		 * @param string                 $content The content to parse.
		 * @param \WPGraphQL\Model\Model $node    The node we are resolving.
		 * @param array                  $args    GraphQL query args to pass to the connection resolver.
		 */
		$content = apply_filters( 'wpgraphql_content_blocks_resolver_content', $content, $node, $args );

		if ( empty( $content ) ) {
			return array();
		}

		// Parse the blocks from HTML comments to an array of blocks
		$parsed_blocks = parse_blocks( $content );
		if ( empty( $parsed_blocks ) ) {
			return array();
		}
		// 1st Level filtering of blocks that are empty
		$parsed_blocks = array_filter(
			$parsed_blocks,
			function ( $parsed_block ) {
				// Strip empty comments and spaces
				return ! empty( trim( preg_replace('/<!--(.*)-->/Uis', '', $parsed_block['innerHTML']) ) );
			},
			ARRAY_FILTER_USE_BOTH
		);

		// 1st Level assigning of unique id's and missing blockNames
		$parsed_blocks = array_map(
			function ( $parsed_block ) {
				$parsed_block['clientId'] = uniqid();
				// Since Gutenberg assigns an empty blockName for Classic block
				// we define the name here
				if ( empty( $parsed_block['blockName'] ) ) {
					$parsed_block['blockName'] = 'core/freeform';
				}
				return $parsed_block;
			},
			$parsed_blocks
		);

		// Flatten block list here if requested or if 'flat' value is not selected (default)
		if ( ! isset( $args['flat'] ) || 'true' == $args['flat'] ) {
			$parsed_blocks = self::flatten_block_list( $parsed_blocks );
		}

		// Final level of filtering out blocks not in the allowed list
		if ( ! empty( $allowed_block_names ) ) {
			$parsed_blocks = array_filter(
				$parsed_blocks,
				function ( $parsed_block ) use ( $allowed_block_names ) {
					return in_array( $parsed_block['blockName'], $allowed_block_names, true );
				},
				ARRAY_FILTER_USE_BOTH
			);
		}
		return $parsed_blocks;
	}

	/**
	 * Flattens a list blocks into a single array
	 *
	 * @param mixed $blocks A list of blocks to flatten.
	 */
	private static function flatten_block_list( $blocks ) {
		$result = array();
		foreach ( $blocks as $block ) {
			$result = array_merge( $result, self::flatten_inner_blocks( $block ) );
		}
		return $result;
	}

	/**
	 * Flattens a block and it's inner blocks into a single while attaching unique clientId's
	 *
	 * @param mixed $block A block.
	 */
	private static function flatten_inner_blocks( $block ) {
		$result            = array();
		$block['clientId'] = isset( $block['clientId'] ) ? $block['clientId'] : uniqid();
		array_push( $result, $block );
		foreach ( $block['innerBlocks'] as $child ) {
			$child['parentClientId'] = $block['clientId'];
			$result                  = array_merge( $result, self::flatten_inner_blocks( $child ) );
		}
		return $result;
	}
}
