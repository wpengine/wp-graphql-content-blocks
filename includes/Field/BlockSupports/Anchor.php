<?php
/**
 * Registers the BlockSupports Anchor Field
 *
 * @package WPGraphQL\ContentBlocks\Field\BlockSupports
 */

namespace WPGraphQL\ContentBlocks\Field\BlockSupports;

use WP_Block_Type;
use WPGraphQL\ContentBlocks\Utilities\DOMHelpers;

/**
 * Class - Anchor
 */
class Anchor {
	/**
	 * Registers the Anchor interface
	 */
	public static function register(): void {
		register_graphql_interface_type(
			'BlockWithSupportsAnchor',
			array(
				'description' => __( 'Block that supports Anchor field', 'wp-graphql-content-blocks' ),
				'fields'      => array(
					'anchor' => array(
						'type'        => 'string',
						'description' => __( 'The anchor field for the block.', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							$rendered_block   = wp_unslash( render_block( $block ) );
							if ( empty( $rendered_block ) ) {
								return null;
							}
							return DOMHelpers::parseFirstNodeAttribute( $rendered_block, 'id' );
						},
					),
				),
			)
		);
	}

	/**
	 * Checks if the block spec supports the anchor field and returns the relevant interface
	 *
	 * @param array          $existing An existing list of a block interfaces.
	 * @param \WP_Block_Type $block_spec The block type to register the anchor field against.
	 *
	 * @return string[]
	 */
	public static function get_block_interfaces( $existing, \WP_Block_Type $block_spec ): array {
		if ( isset( $block_spec ) && isset( $block_spec->supports['anchor'] ) && true === $block_spec->supports['anchor'] ) {
			$existing[] = 'BlockWithSupportsAnchor';
		}
		return $existing;
	}

	/**
	 * Checks if the block spec supports the anchor field and returns the relevant interface
	 *
	 * @param array          $existing An existing list of a block attribute interfaces.
	 * @param \WP_Block_Type $block_spec The block type to register the anchor field against.
	 *
	 * @return string[]
	 */
	public static function get_block_attributes_interfaces( $existing, \WP_Block_Type $block_spec ): array {
		return self::get_block_interfaces( $existing, $block_spec );
	}
}
