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
	 * Type name of BlockAttributesArray.
	 */
	public const ATTRIBUTES_ARRAY_TYPE_NAME = 'BlockAttributesArray';

	/**
	 * Type name of BlockAttributesObject.
	 */
	public const ATTRIBUTES_OBJECT_TYPE_NAME = 'BlockAttributesObject';

	/**
	 * Scalar init procedure.
	 */
	public function init(): void {
		foreach ( [
			self::ATTRIBUTES_ARRAY_TYPE_NAME  => __( 'Generic Array Scalar Type', 'wp-graphql-content-blocks' ),
			self::ATTRIBUTES_OBJECT_TYPE_NAME => __( 'Generic Object Scalar Type', 'wp-graphql-content-blocks' ),
		] as $item => $description ) {
			register_graphql_scalar(
				$item,
				[
					'description' => $description,
					'serialize'   => static function ( $value ) {
						return wp_json_encode( $value );
					},
				]
			);
		}
	}
}
