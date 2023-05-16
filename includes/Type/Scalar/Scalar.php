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
	public function OnInit() {
		register_graphql_scalar(
			'BlockAttributesObject',
			array(
				'serialize' => function ( $value ) {
					return wp_json_encode( $value );
				},
			)
		);
	}
	/**
	 * Return type name of BlockAttributesObject.
	 */
	public static function BlockAttributesObject() {
		return 'BlockAttributesObject';
	}
}
