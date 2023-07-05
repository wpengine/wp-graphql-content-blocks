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
				'serialize' => static function ( $value ) {
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
}
