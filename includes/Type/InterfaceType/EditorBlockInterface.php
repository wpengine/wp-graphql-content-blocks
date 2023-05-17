<?php
/**
 * The EditorBlockInterface interface type.
 *
 * @package WPGraphQL\ContentBlocks\Type\InterfaceType
 */

namespace WPGraphQL\ContentBlocks\Type\InterfaceType;

use WP_Block_Type_Registry;
use WPGraphQL\AppContext;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;
use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;

/**
 * Class EditorBlockInterface
 */
final class EditorBlockInterface {
	/**
	 * Gets the block from the Block Registry.
	 *
	 * @param array $block The block being resolved.
	 * @param null  $context Deprecated.
	 *
	 * @return \WP_Block_Type|null
	 */
	public static function get_block( array $block, #[Deprecated] $context = null ) {
		if ( null !== $context ) {
			_deprecated_argument(
				__METHOD__,
				'@todo',
				esc_html__( 'The $context argument is no longer used and will be removed in a future version.', 'wp-graphql-content-blocks' )
			);
		}

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
	 *
	 * @param WPGraphQL\Registry\TypeRegistry $type_registry The TypeRegistry.
	 */
	public static function register_type( TypeRegistry $type_registry ) {
		register_graphql_interface_type(
			'NodeWithEditorBlocks',
			array(
				'description'     => __( 'Node that has content blocks associated with it', 'wp-graphql-content-blocks' ),
				'eagerlyLoadType' => true,
				'fields'          => array(
					'editorBlocks' => array(
						'type'        => array(
							'list_of' => 'EditorBlock',
						),
						'args'        => array(
							'flat' => array(
								'type' => 'Boolean',
							),
						),
						'description' => __( 'List of editor blocks', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $node, $args ) {
							return ContentBlocksResolver::resolve_content_blocks( $node, $args );
						},
					),
				),
			)
		);

		// Register the EditorBlock Interface
		register_graphql_interface_type(
			'EditorBlock',
			array(
				'eagerlyLoadType' => true,
				'description'     => __( 'Blocks that can be edited to create content and layouts', 'wp-graphql-content-blocks' ),
				'fields'          => array(
					'clientId'                => array(
						'type'        => 'String',
						'description' => __( 'The id of the Block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return isset( $block['clientId'] ) ? $block['clientId'] : uniqid();
						},
					),
					'parentClientId'          => array(
						'type'        => 'String',
						'description' => __( 'The parent id of the Block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return isset( $block['parentClientId'] ) ? $block['parentClientId'] : null;
						},
					),
					'name'                    => array(
						'type'        => 'String',
						'description' => __( 'The name of the Block', 'wp-graphql-content-blocks' ),
					),
					'blockEditorCategoryName' => array(
						'type'        => 'String',
						'description' => __( 'The name of the category the Block belongs to', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return isset( self::get_block( $block )->category ) ? self::get_block( $block )->category : null;
						},
					),
					'isDynamic'               => array(
						'type'        => array( 'non_null' => 'Boolean' ),
						'description' => __( 'Whether the block is Dynamic (server rendered)', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return isset( self::get_block( $block )->render_callback ) && ! empty( self::get_block( $block )->render_callback );
						},
					),
					'apiVersion'              => array(
						'type'        => 'Integer',
						'description' => __( 'The API version of the Gutenberg Block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return isset( self::get_block( $block )->api_version ) && absint( self::get_block( $block )->api_version ) ? absint( self::get_block( $block )->api_version ) : 2;
						},
					),
					'innerBlocks'             => array(
						'type'        => array(
							'list_of' => 'EditorBlock',
						),
						'description' => __( 'The inner blocks of the Block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ? $block['innerBlocks'] : array();
						},
					),
					'cssClassNames'           => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'CSS Classnames to apply to the block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							if ( isset( $block['attrs']['className'] ) ) {
								return explode( ' ', $block['attrs']['className'] );
							}

							return null;
						},
					),
					'renderedHtml'            => array(
						'type'        => 'String',
						'description' => __( 'The rendered HTML for the block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return render_block( $block );
						},
					),
				),
				'resolveType'     => function ( $block ) use ( $type_registry ) {
					if ( empty( $block['blockName'] ) ) {
						$block['blockName'] = 'core/freeform';
					}

					$type_name = lcfirst( ucwords( $block['blockName'], '/' ) );
					$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
					return Utils::format_type_name( $type_name );
				},
			)
		);
	}
}
