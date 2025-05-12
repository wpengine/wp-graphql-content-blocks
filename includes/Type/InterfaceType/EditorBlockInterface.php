<?php
/**
 * The EditorBlockInterface interface type.
 *
 * @package WPGraphQL\ContentBlocks\Type\InterfaceType
 */

namespace WPGraphQL\ContentBlocks\Type\InterfaceType;

use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;
use WPGraphQL\ContentBlocks\GraphQL\WPGraphQLRegisterConfig;
use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;
use WP_Block_Type_Registry;

/**
 * Class EditorBlockInterface
 */
final class EditorBlockInterface {
	/**
	 * Gets the block from the Block Registry.
	 *
	 * @param array $block The block being resolved.
	 *
	 * @return \WP_Block_Type|null
	 */
	public static function get_block( array $block ) {
		$registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

		if ( ! isset( $block['blockName'] ) ) {
			return null;
		}

		if ( ! isset( $registered_blocks[ $block['blockName'] ] ) || ! $registered_blocks[ $block['blockName'] ] instanceof \WP_Block_Type ) {
			return null;
		}

		return $registered_blocks[ $block['blockName'] ];
	}

	/**
	 * Registers the types to WPGraphQL.
	 */
	public static function register_type(): void {
		register_graphql_interface_type(
			'NodeWithEditorBlocks',
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'description'     => __( 'Node that has content blocks associated with it', 'wp-graphql-content-blocks' ),
					'eagerlyLoadType' => true,
					'fields'          => [
						'editorBlocks' => [
							'type'        => [
								'list_of' => 'EditorBlock',
							],
							'args'        => [
								'flat' => [
									'description' => __( 'Returns the list of blocks as a flat list if true', 'wp-graphql-content-blocks' ),
									'type'        => 'Boolean',
								],
							],
							'description' => __( 'List of editor blocks', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $node, $args ) {
								return ContentBlocksResolver::resolve_content_blocks( $node, $args );
							},
						],
					],
				]
			)
		);

		// Register the EditorBlock Interface
		register_graphql_interface_type(
			'EditorBlock',
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'eagerlyLoadType' => true,
					'description'     => __( 'Blocks that can be edited to create content and layouts', 'wp-graphql-content-blocks' ),
					'fields'          => [
						'clientId'                => [
							'type'        => 'String',
							'description' => __( 'The id of the Block', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return isset( $block['clientId'] ) ? $block['clientId'] : uniqid();
							},
						],
						'parentClientId'          => [
							'type'        => 'String',
							'description' => __( 'The parent id of the Block', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return isset( $block['parentClientId'] ) ? $block['parentClientId'] : null;
							},
						],
						'name'                    => [
							'type'        => 'String',
							'description' => __( 'The name of the Block', 'wp-graphql-content-blocks' ),
						],
						'blockEditorCategoryName' => [
							'type'        => 'String',
							'description' => __( 'The name of the category the Block belongs to', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return isset( self::get_block( $block )->category ) ? self::get_block( $block )->category : null;
							},
						],
						'isDynamic'               => [
							'type'        => [ 'non_null' => 'Boolean' ],
							'description' => __( 'Whether the block is Dynamic (server rendered)', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return isset( self::get_block( $block )->render_callback );
							},
						],
						'apiVersion'              => [
							'type'        => 'Integer',
							'description' => __( 'The API version of the Gutenberg Block', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return isset( self::get_block( $block )->api_version ) && absint( self::get_block( $block )->api_version ) ? absint( self::get_block( $block )->api_version ) : 2;
							},
						],
						'innerBlocks'             => [
							'type'        => [
								'list_of' => 'EditorBlock',
							],
							'description' => __( 'The inner blocks of the Block', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ? $block['innerBlocks'] : [];
							},
						],
						'cssClassNames'           => [
							'type'        => [ 'list_of' => 'String' ],
							'description' => __( 'CSS Classnames to apply to the block', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								if ( isset( $block['attrs']['className'] ) ) {
									return explode( ' ', $block['attrs']['className'] );
								}

								return null;
							},
						],
						'renderedHtml'            => [
							'type'        => 'String',
							'description' => __( 'The rendered HTML for the block', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return WPGraphQLHelpers::get_rendered_block( $block );
							},
						],
						'type'                    => [
							'type'        => 'String',
							'description' => __( 'The (GraphQL) type of the block', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return WPGraphQLHelpers::get_type_name_for_block( $block['blockName'] ?? null );
							},
						],
					],
					'resolveType'     => static function ( $block ) {
						return WPGraphQLHelpers::get_type_name_for_block( $block['blockName'] ?? null );
					},
				]
			)
		);
	}
}
