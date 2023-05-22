<?php
/**
 * The Post Type Block Interface
 *
 * @package WPGraphQL\ContentBlocks\Type\InterfaceType
 */

namespace WPGraphQL\ContentBlocks\Type\InterfaceType;

use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;
use WPGraphQL\Utils\Utils;

/**
 * Class PostTypeBlockInterface
 */
final class PostTypeBlockInterface {
	/**
	 * Registers the types to WPGraphQL.
	 *
	 * @param string   $post_type The post type.
	 * @param string[] $block_names The list of allowed block names.
	 */
	public static function register_type( string $post_type, $block_names ) {
		register_graphql_interface_type(
			ucfirst( $post_type ) . 'EditorBlock',
			array(
				'interfaces'  => array( 'EditorBlock' ),
				'fields'      => array(
					'name' => array(
						'type' => 'String',
					),
				),
				'resolveType' => function ( $block ) {
					if ( empty( $block['blockName'] ) ) {
						$block['blockName'] = 'core/freeform';
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
				'description'     => sprintf(
					// translators: %s is the post type.
					__( 'Node that has %s content blocks associated with it', 'wp-graphql-content-blocks' ),
					$post_type
				),
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
						'description' => sprintf(
							// translators: %s is the post type.
							__( 'List of %s editor blocks', 'wp-graphql-content-blocks' ),
							$post_type
						),
						'resolve'     => function ( $node, $args ) use ( $block_names ) {
							return ContentBlocksResolver::resolve_content_blocks( $node, $args, $block_names );
						},
					),
				),
			)
		);
	}

}
