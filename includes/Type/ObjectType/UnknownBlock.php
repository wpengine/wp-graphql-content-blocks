<?php

namespace WPGraphQL\ContentBlocks\Type\ObjectType;

/**
 * Class UnknownBlock
 *
 * @package WPGraphQL\ContentBlocks
 */
final class UnknownBlock {

	/**
	 * Registers the UnknownBlock in the instance a block is not defined
	 * in the registry.
	 */
	public static function register_type() {
		register_graphql_object_type(
			'UnknownBlock',
			array(
				'description'     => __( 'A block used for resolving blocks not found in the WordPress registry', 'wp-graphql-content-blocks' ),
				'interfaces'      => array( 'EditorBlock' ),
				'eagerlyLoadType' => true,
				'fields'          => array(
					'name' => array(
						'type'        => 'String',
						'description' => __( 'The name of the block', 'wp-graphql-content-blocks' ),
						'resolve'     => function () {
							return 'UnknownBlock';
						},
					),
				),
			)
		);
	}
}
