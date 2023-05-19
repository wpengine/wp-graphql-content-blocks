<?php
/**
 * The Type Registry.
 *
 * @package WPGraphQL\ContentBlocks\Registry
 */

namespace WPGraphQL\ContentBlocks\Registry;

use Exception;
use WP_Block_Type;
use WPGraphQL\ContentBlocks\Blocks\Block;
use WPGraphQL\ContentBlocks\Type\Scalar\Scalar;
use WPGraphQL\ContentBlocks\Type\InterfaceType\EditorBlockInterface;
use WPGraphQL\ContentBlocks\Type\InterfaceType\PostTypeBlockInterface;
use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;
use WPGraphQL\ContentBlocks\Utilities\WPHelpers;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

/**
 * Class Registry
 */
final class Registry {

	/**
	 * The instance of the WPGraphQL type registry.
	 *
	 * @var \WPGraphQL\Registry\TypeRegistry
	 */
	public $type_registry;

	/**
	 * The instance of the WP_Block_Type_Registry.
	 *
	 * @var \WP_Block_Type_Registry
	 */
	public $block_type_registry;

	/**
	 * The registered blocks.
	 *
	 * @var array
	 */
	public $registered_blocks;

	/**
	 * The registered block interfaces.
	 *
	 * @var array
	 */
	public $block_interfaces = array();

	/**
	 * Registry constructor.
	 *
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry .
	 * @param \WP_Block_Type_Registry          $block_type_registry .
	 */
	public function __construct( TypeRegistry $type_registry, $block_type_registry ) {
		$this->type_registry       = $type_registry;
		$this->block_type_registry = $block_type_registry;
	}

	/**
	 * Registry init procedure.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register_interface_types();
		$this->register_scalar_types();
		$this->register_block_types();
	}

	/**
	 * Given the name of a block, return a list of Interfaces the block should implement to represent which contexts the block can be accessed from.
	 *
	 * In effect:
	 * - a Block that is allowed on the "Post" post type will implement the "PostEditorBlock" interface
	 * - a Block allowed on the "Page" post type will implement the "PageEditorBlock" interface
	 * - a Block that is exposed in another context, for example NavMenu would implement the "NavMenuEditorBlock" interface (not yet supported)
	 *
	 * @param string $block_name The name of the block to get the interfaces for.
	 */
	public function get_block_context_interfaces( string $block_name ): array {

		// If there's already block interfaces defined for the block, return them
		if ( ! empty( $this->block_interfaces[ $block_name ] ) ) {
			return $this->block_interfaces[ $block_name ];
		}

		$all_registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
		// Get a list of Gutenberg and GraphQL enabled post types
		$block_and_graphql_enabled_post_types = WPHelpers::get_supported_post_types();

		if ( empty( $block_and_graphql_enabled_post_types ) ) {
			return array();
		}

		$post_id = -1;

		foreach ( $block_and_graphql_enabled_post_types as $post_type ) {

			// retrieve a block_editor_context for the current post type
			$block_editor_context = WPHelpers::get_block_editor_context( $post_type->name, $post_id-- );

			// Fetch the list of allowed blocks for the current post type
			$supported_blocks_for_post_type_context = get_allowed_block_types( $block_editor_context );

			// If all blocks are supported in this context, we need a list of all the blocks
			if ( true === $supported_blocks_for_post_type_context ) {
				$supported_blocks_for_post_type_context = $all_registered_blocks;
				$supported_blocks_for_post_type_context = array_keys( $supported_blocks_for_post_type_context );
			}

			// If there are no supported blocks, return an empty array
			if ( empty( $supported_blocks_for_post_type_context ) ) {
				continue;
			}

			if ( in_array( $block_name, $supported_blocks_for_post_type_context, true ) ) {
				$this->block_interfaces[ $block_name ][] = Utils::format_type_name( Utils::format_type_name( $post_type->graphql_single_name ) . 'EditorBlock' );
			}
		}//end foreach

		return ! empty( $this->block_interfaces[ $block_name ] ) ? $this->block_interfaces[ $block_name ] : array();
	}

	/**
	 * Given the name of a Block, return interfaces the Block should implement.
	 *
	 * @param string $block_name The name of the block to get the interfaces for.
	 *
	 * @return string[]
	 */
	public function get_block_interfaces( string $block_name ): array {

		// Get the interfaces a block should implement based on the context a block is available to be accessed from.
		$context_interfaces = $this->get_block_context_interfaces( $block_name );

		// @todo: if blocks need to implement other interfaces (i.e. "BlockSupports" interfaces, that could be handled here as well)
		return array_merge( array( 'EditorBlock' ), $context_interfaces );
	}

	/**
	 * Register Interface types to the GraphQL Schema
	 *
	 * @return void
	 */
	protected function register_interface_types(): void {
		// First register the NodeWithEditorBlocks interface by default
		EditorBlockInterface::register_type( $this->type_registry );

		// Then try to register both NodeWithEditorBlocks and NodeWith[PostType]Blocks per post type
		$supported_post_types = WPHelpers::get_supported_post_types();
		if ( empty( $supported_post_types ) ) {
			return;
		}

		$type_names = array_map(
			function( $post_type ) {
				return $post_type->graphql_single_name ?? null;
			},
			$supported_post_types
		);
		register_graphql_interfaces_to_types( array( 'NodeWithEditorBlocks' ), $type_names );
		$post_id = -1;
		// For each Post type
		foreach ( $supported_post_types as $post_type ) {
			// Normalize the post type name
			$type_name = WPGraphQLHelpers::format_type_name( $post_type->name );

			// retrieve a block_editor_context for the current post type
			$block_editor_context = WPHelpers::get_block_editor_context( $type_name, $post_id-- );

			// Fetch the list of allowed blocks for the current post type
			$supported_blocks_for_post_type = get_allowed_block_types( $block_editor_context );

			if ( true === $supported_blocks_for_post_type ) {
				$supported_blocks_for_post_type = \WP_Block_Type_Registry::get_instance()->get_all_registered();
				$supported_blocks_for_post_type = array_keys( $supported_blocks_for_post_type );
			}

			// If there is a list of supported blocks for current post type
			if ( is_array( $supported_blocks_for_post_type ) ) {
				// Register an [PostType]Block type for the blocks using that post type
				PostTypeBlockInterface::register_type( $type_name, $supported_blocks_for_post_type, $this->type_registry );

				// Register the `NodeWith[PostType]Blocks` Interface to the post type
				register_graphql_interfaces_to_types( array( 'NodeWith' . Utils::format_type_name( $post_type->graphql_single_name ) . 'EditorBlocks' ), array( $type_name ) );
			}
		}//end foreach
	}

	/**
	 * Register Scalar types to the GraphQL Schema
	 *
	 * @return void
	 */
	protected function register_scalar_types() {
		( new Scalar() )->init();
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
	 * @param \WP_Block_Type $block The block type to register.
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
