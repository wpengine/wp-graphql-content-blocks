<?php
/**
 * Registers the BlockSupports Anchor Field
 *
 * @package WPGraphQL\ContentBlocks\Field\BlockSupports
 */

namespace WPGraphQL\ContentBlocks\Field\BlockSupports;

use WP_Block_Type;
use WPGraphQL\ContentBlocks\Utilities\DOMHelpers;
use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;

/**
 * Class - Anchor
 */
class Anchor {
	/**
	 * Registers an Anchor field on a block if it supports it.
	 *
	 * @param \WP_Block_Type $block_spec .
	 */
	public static function register( \WP_Block_Type $block_spec ) {
		if ( isset( $block_spec->supports['anchor'] ) && true === $block_spec->supports['anchor'] ) {
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
								$value            = null;
								if ( empty( $rendered_block ) ) {
									return $value;
								}
								$value = DOMHelpers::parseFirstNodeAttribute( $rendered_block, 'id' );
								return $value;
							},
						),
					),
				)
			);
			register_graphql_interfaces_to_types( 'BlockWithSupportsAnchor', array( WPGraphQLHelpers::format_type_name( $block_spec->name ) . 'Attributes' ) );
			register_graphql_interfaces_to_types( 'BlockWithSupportsAnchor', array( WPGraphQLHelpers::format_type_name( $block_spec->name ) ) );
		}//end if
	}
}
