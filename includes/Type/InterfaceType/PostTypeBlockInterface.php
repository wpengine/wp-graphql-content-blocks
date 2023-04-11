<?php

namespace WPGraphQL\ContentBlocks\Type\InterfaceType;

use Exception;
use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

/**
 * Class PostTypeBlockInterface
 *
 * @package WPGraphQL\ContentBlocks
 */
final class PostTypeBlockInterface {

	/**
	 * @param string        $post_type The post type
	 * @param array(string) $$block_names The list of allowed block names
	 * @param TypeRegistry  $type_registry
	 *
	 * @throws Exception
	 */
	public static function register_type( string $post_type, $block_names, TypeRegistry $type_registry ) {
		register_graphql_interface_type(
			ucfirst( $post_type ) . 'EditorBlock',
			array(
				'interfaces'  => array( 'EditorBlock' ),
				'fields'      => array(
					'name' => array(
						'type' => 'String',
					),
				),
				'resolveType' => function ( $block ) use ( $type_registry ) {
					if ( empty( $block['blockName'] ) ) {
						$block['blockName'] = 'core/html';
					}

					$type_name = lcfirst( ucwords( $block['blockName'], '/' ) );
					$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
					return Utils::format_type_name( $type_name );
				},
			)
		);

		register_graphql_interface_type(
			'NodeWith' . ucfirst( $post_type ) . 'EditorBlocks',
			array(
				'description'     => __( 'Node that has ' . $post_type . ' content blocks associated with it', 'wp-graphql-content-blocks' ),
				'eagerlyLoadType' => true,
				'interfaces'      => array( 'NodeWithEditorBlocks' ),
				'fields'          => array(
					'editorBlocks' => array(
						'type'        => array(
							'list_of' => ucfirst( $post_type ) . 'EditorBlock',
						),
						'args'        => array(
							'flat' => array(
								'type' => 'Boolean',
							),
						),
						'description' => __( 'List of ' . $post_type . ' editor blocks', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $node, $args ) use ( $block_names ) {
							return ContentBlocksResolver::resolve_content_blocks( $node, $args, $block_names );
						},
					),
				),
			)
		);
	}

}
