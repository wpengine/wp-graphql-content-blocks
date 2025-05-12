<?php
/**
 * Registers the BlockSupports Anchor Field
 *
 * @package WPGraphQL\ContentBlocks\Field\BlockSupports
 */

namespace WPGraphQL\ContentBlocks\Field\BlockSupports;

use WPGraphQL\ContentBlocks\GraphQL\WPGraphQLRegisterConfig;
use WPGraphQL\ContentBlocks\Utilities\DOMHelpers;
use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;

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
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'description' => __( 'Block that supports Anchor field', 'wp-graphql-content-blocks' ),
					'fields'      => [
						'anchor' => [
							'type'        => 'string',
							'description' => __( 'The anchor field for the block.', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								$rendered_block = wp_unslash( WPGraphQLHelpers::get_rendered_block( $block ) );
								if ( empty( $rendered_block ) ) {
									return null;
								}
								return DOMHelpers::parse_first_node_attribute( $rendered_block, 'id' );
							},
						],
					],
				]
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
		if ( isset( $block_spec->supports['anchor'] ) && true === $block_spec->supports['anchor'] ) {
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

	/**
	 * Registers an Anchor field on a block if it supports it.
	 *
	 * @param \WP_Block_Type $block_spec The block type to register the anchor field against.
	 *
	 * @deprecated 1.1.4 No longer used by internal code and not recommended.
	 */
	public static function register_to_block( \WP_Block_Type $block_spec ): void {
		_deprecated_function( __METHOD__, '1.1.4' );
		if ( isset( $block_spec->supports['anchor'] ) && true === $block_spec->supports['anchor'] ) {
			register_graphql_interfaces_to_types( 'BlockWithSupportsAnchor', [ WPGraphQLHelpers::format_type_name( $block_spec->name ) . 'Attributes' ] );
			register_graphql_interfaces_to_types( 'BlockWithSupportsAnchor', [ WPGraphQLHelpers::format_type_name( $block_spec->name ) ] );
		}
	}
}
