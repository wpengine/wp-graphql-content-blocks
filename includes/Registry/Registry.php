<?php

namespace WPGraphQL\ContentBlocks\Registry;

use Exception;
use WP_Block_Type;
use WPGraphQL\ContentBlocks\Blocks\Block;
use WPGraphQL\ContentBlocks\Interfaces\OnInit;
use WPGraphQL\ContentBlocks\Type\Scalar\Scalar;
use WPGraphQL\ContentBlocks\Type\InterfaceType\EditorBlockInterface;
use WPGraphQL\ContentBlocks\Type\InterfaceType\PostTypeBlockInterface;
use WPGraphQL\ContentBlocks\Type\ObjectType\UnknownBlock;
use WPGraphQL\ContentBlocks\Utilities\WPHelpers;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

/**
 * Class Registry
 *
 * @package WPGraphQL\ContentBlocks\Registry
 */
final class Registry implements OnInit {

	/**
	 * @var TypeRegistry
	 */
	public $type_registry;

	/**
	 * @var WP_Block_Type_Registry
	 */
	public $block_type_registry;

	/**
	 * @var array
	 */
	public $registered_blocks;

	/**
	 * Registry constructor.
	 *
	 * @param TypeRegistry           $type_registry
	 * @param WP_Block_Type_Registry $block_type_registry
	 */
	public function __construct( TypeRegistry $type_registry, $block_type_registry ) {
		$this->type_registry       = $type_registry;
		$this->block_type_registry = $block_type_registry;
	}

	/**
	 * Registry init procedure.
	 *
	 * @throws Exception
	 */
	public function OnInit() {
		$this->register_interface_types();
		$this->register_scalar_types();
		$this->register_block_types();
		UnknownBlock::register_type();
	}

	/**
	 * Register Interface types to the GraphQL Schema
	 *
	 * @return void
	 */
	protected function register_interface_types() {
		// First register the NodeWithEditorBlocks interface by default
		EditorBlockInterface::register_type( $this->type_registry );

		// Then try to register both NodeWithEditorBlocks and NodeWith[PostType]Blocks per post type
		$supported_post_types = WPHelpers::get_supported_post_types();
		if ( empty( $supported_post_types ) ) {
			return;
		}
		register_graphql_interfaces_to_types( array( 'NodeWithEditorBlocks' ), $supported_post_types );
		$post_id = -1;
		// For each Post type
		foreach ( $supported_post_types as $post_type ) {
			// Normalize the post type name
			$type_name = strtolower( $post_type );

			// retrieve a block_editor_context for the current post type
			$block_editor_context = WPHelpers::get_block_editor_context( $type_name, $post_id-- );

			// Fetch the list of allowed blocks for the current post type
			$supported_blocks_for_post_type = get_allowed_block_types( $block_editor_context );

			// If there is a list of supported blocks for current post type
			if ( is_array( $supported_blocks_for_post_type ) ) {
				// Register an [PostType]Block type for the blocks using that post type
				PostTypeBlockInterface::register_type( $type_name, $supported_blocks_for_post_type, $this->type_registry );

				// Normalize the list of supported block names
				$block_names = array_map(
					function( $supported_block ) {
						$block_name = preg_replace( '/\//', '', lcfirst( ucwords( $supported_block, '/' ) ) );
						return \WPGraphQL\Utils\Utils::format_type_name( $block_name );
					},
					$supported_blocks_for_post_type
				);
				// Register [PostType]Block type to allowed block names
				register_graphql_interfaces_to_types( array( $type_name . 'Block' ), $block_names );

				// Register the `NodeWith[PostType]Blocks` Interface to the post type
				register_graphql_interfaces_to_types( array( 'NodeWith' . $post_type . 'Blocks' ), array( $type_name ) );
			}
		}//end foreach
	}

	/**
	 * Register Scalar types to the GraphQL Schema
	 *
	 * @return void
	 */
	protected function register_scalar_types() {
		( new Scalar() )->OnInit();
	}

	/**
	 * Register Block Types to the GraphQL Schema
	 *
	 * @return void
	 */
	protected function register_block_types() {
		$this->registered_blocks = $this->block_type_registry->get_all_registered();

		if ( empty( $this->registered_blocks ) || ! is_array( $this->registered_blocks ) ) {
			return;
		}

		foreach ( $this->registered_blocks as $block ) {
			$this->register_block_type( $block );
		}
	}

	/**
	 * Register a block from the Gutenberg server registry to the WPGraphQL Registry
	 *
	 * @param WP_Block_Type $block
	 */
	protected function register_block_type( WP_Block_Type $block ) {
		$block_name = isset( $block->name ) && ! empty( $block->name ) ? $block->name : 'Core/HTML';

		$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $block_name, '/' ) ) );
		$type_name = Utils::format_type_name( $type_name );

		$class_name = Utils::format_type_name( $type_name );
		$class_name = '\\WPGraphQL\\ContentBlocks\\Blocks\\' . $class_name;

		/**
		 * This allows 3rd party extensions to hook and and provide
		 * a path to their class for registering a field to the Schema
		 */
		$class_name = apply_filters( 'wpgraphql_content_blocks_block_class', $class_name, $block, $this );
		if ( class_exists( $class_name ) ) {
			new $class_name( $block, $this );
		} else {
			new Block( $block, $this );
		}
	}
}
