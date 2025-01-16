<?php
/**
 * Core Navigation Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WPGraphQL\ContentBlocks\Registry\Registry;
use WP_Block_Type;

/**
 * Class CoreNavigation
 */
class CoreNavigation extends Block {
	/**
	 * Block constructor.
	 *
	 * @param \WP_Block_Type $block The Block Type.
	 * @param \WPGraphQL\ContentBlocks\Registry\Registry $block_registry The instance of the WPGraphQL block registry.
	 */
	public function __construct( WP_Block_Type $block, Registry $block_registry ) {
		parent::__construct( $block, $block_registry );

		// Register the navigationItems field
		register_graphql_field(
			$this->type_name,
			'navigationItems',
			[ 
				'type' => [ 'list_of' => 'EditorBlock' ],
				'description' => __( 'Navigation menu items from the referenced navigation post', 'wp-graphql-content-blocks' ),
				'resolve' => [ $this, 'resolve_navigation_items' ],
			]
		);

	}

	/**
	 * Resolve navigation items from the navigation post reference
	 *
	 * @param array $block The block data.
	 * @return array|null
	 */
	public function resolve_navigation_items( $block ) {
		$attributes = $block['attrs'] ?? [];
		$ref = $attributes['ref'] ?? null;

		if ( ! $ref ) {
			return null;
		}

		$navigation_post = get_post( $ref );
		if ( ! $navigation_post || 'publish' !== $navigation_post->post_status ) {
			return [];
		}

		$parsed_blocks = parse_blocks( $navigation_post->post_content );
		return block_core_navigation_filter_out_empty_blocks( $parsed_blocks );;
	}
}

/**
 * Filter out empty blocks from the parsed blocks.
 *
 * @param array $parsed_blocks An array of parsed block data.
 * @return array An array of filtered blocks.
 */
function block_core_navigation_filter_out_empty_blocks( $parsed_blocks ): array {
	$filtered = array_filter(
		$parsed_blocks,
		static function ( $block ) {
			return isset( $block['blockName'] );
		}
	);

	return array_values( $filtered );
}