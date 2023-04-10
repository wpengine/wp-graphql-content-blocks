<?php

namespace WPGraphQL\ContentBlocks\Type\Scalar;

use WPGraphQL\ContentBlocks\Interfaces\OnInit;

/**
 * Class Scalar
 *
 * @package WPGraphQL\ContentBlocks
 */
final class Scalar implements OnInit {
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
