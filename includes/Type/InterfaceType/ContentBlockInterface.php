<?php

namespace WPGraphQL\ContentBlocks\Type\InterfaceType;

use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Model\Post;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

/**
 * Class ContentBlockInterface
 *
 * @package WPGraphQL\ContentBlocks
 */
final class ContentBlockInterface {


	/**
	 * @param array      $block   The block being resolved
	 * @param AppContext $context The AppContext
	 *
	 * @return mixed WP_Block_Type|null
	 */
	public static function get_block( array $block, AppContext $context ) {
		$registered_blocks = $context->config['registered_editor_blocks'];

		if ( ! isset( $block['blockName'] ) ) {
			return null;
		}

		if ( ! isset( $registered_blocks[ $block['blockName'] ] ) || ! $registered_blocks[ $block['blockName'] ] instanceof \WP_Block_Type ) {
			return null;
		}

		return $registered_blocks[ $block['blockName'] ];
	}

	/**
	 * @param TypeRegistry $type_registry
	 *
	 * @throws Exception
	 */
	public static function register_type( TypeRegistry $type_registry ) {
		register_graphql_interface_type(
			'NodeWithContentBlocks',
			array(
				'description'     => __( 'Node that has content blocks associated with it', 'wp-graphql-content-blocks' ),
				'eagerlyLoadType' => true,
				'fields'          => array(
					'contentBlocks' => array(
						'type'        => array(
							'list_of' => 'ContentBlock',
						),
						'args'        => array(
							'flat' => array(
								'type' => 'Boolean',
							),
						),
						'description' => __( 'List of content blocks', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $node, $args ) {
							$content = null;
							if ( $node instanceof Post ) {

								// @todo: this is restricted intentionally.
								// $content = $node->contentRaw;

								// This is the unrestricted version, but we need to
								// probably have a "Block" Model that handles
								// determining what fields should/should not be
								// allowed to be returned?
								$post    = get_post( $node->databaseId );
								$content = $post->post_content;
							}

							if ( empty( $content ) ) {
								return array();
							}

							// Parse the blocks from HTML comments to an array of blocks
							$parsed_blocks = parse_blocks( $content );
							if ( empty( $parsed_blocks ) ) {
								return array();
							}

							// Filter out blocks that have no name
							$parsed_blocks = array_filter(
								$parsed_blocks,
								function ( $parsed_block ) {
									return isset( $parsed_block['blockName'] ) && ! empty( $parsed_block['blockName'] );
								},
								ARRAY_FILTER_USE_BOTH
							);

							$parsed_blocks = array_map(
								function ( $parsed_block ) {
									$parsed_block['nodeId'] = uniqid();
									return $parsed_block;
								},
								$parsed_blocks
							);
							return collectBlocks_iter( $parsed_blocks );
						},
					),
				),
			)
		);

		// Register the ContentBlock Interface
		register_graphql_interface_type(
			'ContentBlock',
			array(
				'eagerlyLoadType' => true,
				'description'     => __( 'Blocks that can be edited to create content and layouts', 'wp-graphql-content-blocks' ),
				'fields'          => array(
					'nodeId'                  => array(
						'type'        => 'String',
						'description' => __( 'The id of the Block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return isset( $block['nodeId'] ) ? $block['nodeId'] : uniqid();
						},
					),
					'parentId'                => array(
						'type'        => 'String',
						'description' => __( 'The parent id of the Block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return isset( $block['parentId'] ) ? $block['parentId'] : null;
						},
					),
					'name'                    => array(
						'type'        => 'String',
						'description' => __( 'The name of the Block', 'wp-graphql-content-blocks' ),
					),
					'blockEditorCategoryName' => array(
						'type'        => 'String',
						'description' => __( 'The name of the category the Block belongs to', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block, $args, AppContext $context, ResolveInfo $info ) {
							return isset( self::get_block( $block, $context )->category ) ? self::get_block( $block, $context )->category : null;
						},
					),
					'isDynamic'               => array(
						'type'        => array( 'non_null' => 'Boolean' ),
						'description' => __( 'Whether the block is Dynamic (server rendered)', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block, $args, AppContext $context, ResolveInfo $info ) {
							return isset( self::get_block( $block, $context )->render_callback ) && ! empty( self::get_block( $block, $context )->render_callback );
						},
					),
					'apiVersion'              => array(
						'type'        => 'Integer',
						'description' => __( 'The API version of the Gutenberg Block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block, $args, AppContext $context, ResolveInfo $info ) {
							return isset( self::get_block( $block, $context )->api_version ) && absint( self::get_block( $block, $context )->api_version ) ? absint( self::get_block( $block, $context )->api_version ) : 2;
						},
					),
					'innerBlocks'             => array(
						'type'        => array(
							'list_of' => 'ContentBlock',
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
						$block['blockName'] = 'core/html';
					}

					$type_name = lcfirst( ucwords( $block['blockName'], '/' ) );
					$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
					$type_name = Utils::format_type_name( $type_name );
					return $type_registry->get_type( $type_name );
				},
			)
		);
	}
}

function collectBlocks_iter( $blocks ) {
	$result = array();
	foreach ( $blocks as $block ) {
		$result = array_merge( $result, collectBlocks( $block ) );
	}
	return $result;
}

function collectBlocks( $block ) {
	$result          = array();
	$block['nodeId'] = isset( $block['nodeId'] ) ? $block['nodeId'] : uniqid();
	array_push( $result, $block );
	foreach ( $block['innerBlocks'] as $child ) {
		$child['parentId'] = $block['nodeId'];
		$result            = array_merge( $result, collectBlocks( $child ) );
	}
	return $result;
}
