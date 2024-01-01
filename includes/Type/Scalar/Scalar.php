<?php
/**
 * The BlockAttributesObject scalar type.
 *
 * @package WPGraphQL\ContentBlocks\Type\Scalar
 */

namespace WPGraphQL\ContentBlocks\Type\Scalar;

/**
 * Class Scalar
 */
final class Scalar {
	/**
	 * Scalar init procedure.
	 */
	public function init(): void {
		register_graphql_scalar(
			'BlockAttributesObject',
			[
				'description' => __( 'Generic Object Scalar Type', 'wp-graphql-content-blocks' ),
				'serialize'   => static function ( $value ) {
					return wp_json_encode( $value );
				},
			]
		);
		register_graphql_scalar(
			'BlockAttributesArray',
			[
				'description' => __( 'Generic Array Scalar Type', 'wp-graphql-content-blocks' ),
				'serialize'   => static function ( $value ) {
					return wp_json_encode( $value );
				},
			]
		);
	}

	/**
	 * Return type name of BlockAttributesObject.
	 */
	public static function get_block_attributes_object_type_name(): string {
		return 'BlockAttributesObject';
	}

	/**
	 * Return type name of BlockAttributesArray.
	 */
	public static function get_block_attributes_array_type_name() {
		return 'BlockAttributesArray';
	}
}
