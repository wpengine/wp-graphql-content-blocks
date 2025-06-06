<?php
/**
 * The Post Type Block Interface
 *
 * @package WPGraphQL\ContentBlocks\Type\InterfaceType
 */

namespace WPGraphQL\ContentBlocks\Type\InterfaceType;

use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;
use WPGraphQL\ContentBlocks\GraphQL\WPGraphQLRegisterConfig;
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
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'description' => static fn () => sprintf(
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
						return WPGraphQLHelpers::get_type_name_for_block( $block['blockName'] ?? null );
					},
				]
			)
		);

		register_graphql_interface_type(
			'NodeWith' . ucfirst( $post_type ) . 'EditorBlocks',
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'description'     => static fn () => sprintf(
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
							'description' => static fn () => sprintf(
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
			)
		);
	}
}
