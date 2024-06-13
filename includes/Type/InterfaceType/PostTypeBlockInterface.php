<?php
/**
 * The Post Type Block Interface
 *
 * @package WPGraphQL\ContentBlocks\Type\InterfaceType
 */

namespace WPGraphQL\ContentBlocks\Type\InterfaceType;

use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;
use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;

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
	public static function register_type( string $post_type, array $block_names = [] ): void {
		register_graphql_interface_type(
			ucfirst( $post_type ) . 'EditorBlock',
			[
				'description' => sprintf(
					// translators: EditorBlock Interface for %s Block Type.
					__( 'EditorBlock Interface for %s Block Type', 'wp-graphql-content-blocks' ),
					ucfirst( $post_type )
				),
				'interfaces'  => [ 'EditorBlock' ],
				'fields'      => [
					'name' => [
						'type' => 'String',
					],
				],
				'resolveType' => static function ( $block ) {
					if ( empty( $block['blockName'] ) ) {
						$block['blockName'] = 'core/freeform';
					}

					$type_name = lcfirst( ucwords( $block['blockName'], '/' ) );

					return WPGraphQLHelpers::format_type_name( $type_name );
				},
			]
		);

		register_graphql_interface_type(
			'NodeWith' . ucfirst( $post_type ) . 'EditorBlocks',
			[
				'description'     => sprintf(
					// translators: %s is the post type.
					__( 'Node that has %s content blocks associated with it', 'wp-graphql-content-blocks' ),
					$post_type
				),
				'eagerlyLoadType' => true,
				'interfaces'      => [ 'NodeWithEditorBlocks' ],
				'fields'          => [
					'editorBlocks' => [
						'type'        => [
							'list_of' => ucfirst( $post_type ) . 'EditorBlock',
						],
						'args'        => [
							'flat' => [
								'description' => __( 'Returns the list of blocks as a flat list if true', 'wp-graphql-content-blocks' ),
								'type' => 'Boolean',
							],
						],
						'description' => sprintf(
							// translators: %s is the post type.
							__( 'List of %s editor blocks', 'wp-graphql-content-blocks' ),
							$post_type
						),
						'resolve'     => static function ( $node, $args ) use ( $block_names ) {
							return ContentBlocksResolver::resolve_content_blocks( $node, $args, $block_names );
						},
					],
				],
			]
		);
	}
}
