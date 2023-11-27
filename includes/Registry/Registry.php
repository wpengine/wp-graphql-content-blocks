<?php
/**
 * The Type Registry.
 *
 * @package WPGraphQL\ContentBlocks\Registry
 */

namespace WPGraphQL\ContentBlocks\Registry;

use WPGraphQL\ContentBlocks\Blocks\Block;
use WPGraphQL\ContentBlocks\Field\BlockSupports\Anchor;
use WPGraphQL\ContentBlocks\Type\InterfaceType\EditorBlockInterface;
use WPGraphQL\ContentBlocks\Type\InterfaceType\PostTypeBlockInterface;
use WPGraphQL\ContentBlocks\Type\Scalar\Scalar;
use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;
use WPGraphQL\ContentBlocks\Utilities\WPHelpers;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;
use WP_Block_Type;

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
	public $block_interfaces = [];

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
	 */
	public function init(): void {
		$this->register_interface_types();
		$this->register_scalar_types();
		$this->register_support_block_types();
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
			return [];
		}

		$post_id = -1;

		foreach ( $block_and_graphql_enabled_post_types as $post_type ) {

			// retrieve a block_editor_context for the current post type
			$block_editor_context = WPHelpers::get_block_editor_context( $post_type->name, $post_id-- );

			// Fetch the list of allowed blocks for the current post type
			$supported_blocks_for_post_type_context = get_allowed_block_types( $block_editor_context );

			/**
			 * Filter before applying per-post_type Interfaces to blocks. This allows 3rd parties to control
			 * whether the interface(s) should or should not be applied based on custom logic.
			 *
			 * @param bool                     $should                                 Whether to apply the ${PostType}EditorBlock Interface. If the filter returns false, the default
			 *                                                                         logic will not execute and the ${PostType}EditorBlock will not be applied.
			 * @param string                   $block_name                             The name of the block Interfaces will be applied to
			 * @param \WP_Block_Editor_Context $block_editor_context                   The context of the Block Editor
			 * @param \WP_Post_Type            $post_type                              The Post Type an Interface might be applied to the block for
			 * @param array                    $all_registered_blocks                  Array of all registered blocks
			 * @param array                    $supported_blocks_for_post_type_context Array of all supported blocks for the context
			 * @param array                    $block_and_graphql_enabled_post_types   Array of Post Types that have block editor and GraphQL support
			 */
			$should_apply_post_type_editor_block_interface = apply_filters( 'wpgraphql_content_blocks_should_apply_post_type_editor_blocks_interfaces', true, $block_name, $block_editor_context, $post_type, $all_registered_blocks, $supported_blocks_for_post_type_context, $block_and_graphql_enabled_post_types );

			if ( true !== $should_apply_post_type_editor_block_interface ) {
				continue;
			}

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

		return ! empty( $this->block_interfaces[ $block_name ] ) ? $this->block_interfaces[ $block_name ] : [];
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

		// Get additional interfaces a block should implement.
		$additional_interfaces = $this->get_block_additional_interfaces( $block_name );

		return array_merge( array( 'EditorBlock' ), $context_interfaces, $additional_interfaces );
	}

	/**
	 * Given the name of a Block, return interfaces the Block object should implement.
	 *
	 * @param string $block_name The name of the block to get the interfaces for.
	 *
	 * @return string[]
	 */
	public function get_block_additional_interfaces( string $block_name ): array {
		$block_spec       = $this->block_type_registry->get_registered( $block_name );
		$block_interfaces = array();
		// NOTE: Using add_filter here creates a performance penalty.
		$block_interfaces = Anchor::get_block_interfaces( $block_interfaces, $block_spec );
		return $block_interfaces;
	}

	/**
	 * Given the name of a Block, return interfaces the Block attributes object should implement.
	 *
	 * @param string $block_name The name of the block to get the interfaces for.
	 *
	 * @return string[]
	 */
	public function get_block_attributes_interfaces( string $block_name ): array {
		$block_spec       = $this->block_type_registry->get_registered( $block_name );
		$block_interfaces = array();
		// NOTE: Using add_filter here creates a performance penalty.
		$block_interfaces = Anchor::get_block_attributes_interfaces( $block_interfaces, $block_spec );
		return $block_interfaces;
	}

	/**
	 * Register Interface types to the GraphQL Schema
	 */
	protected function register_interface_types(): void {
		// First register the NodeWithEditorBlocks interface by default
		EditorBlockInterface::register_type();

		// Then try to register both NodeWithEditorBlocks and NodeWith[PostType]Blocks per post type
		$supported_post_types = WPHelpers::get_supported_post_types();
		if ( empty( $supported_post_types ) ) {
			return;
		}

		$type_names = array_map(
			static function ( $post_type ) {
				return $post_type->graphql_single_name ?? null;
			},
			$supported_post_types
		);
		register_graphql_interfaces_to_types( [ 'NodeWithEditorBlocks' ], $type_names );
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
				PostTypeBlockInterface::register_type( $type_name, $supported_blocks_for_post_type );

				// Register the `NodeWith[PostType]Blocks` Interface to the post type
				register_graphql_interfaces_to_types( [ 'NodeWith' . Utils::format_type_name( $post_type->graphql_single_name ) . 'EditorBlocks' ], [ $type_name ] );
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
	 */
	protected function register_block_types(): void {
		$this->registered_blocks = $this->block_type_registry->get_all_registered();

		// Bail early if there are no registered blocks.
		if ( empty( $this->registered_blocks ) ) {
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
	protected function register_block_type( WP_Block_Type $block ): void {
		$block_name = ! empty( $block->name ) ? $block->name : 'Core/HTML';

		$type_name  = WPGraphQLHelpers::format_type_name( $block_name );
		$class_name = Utils::format_type_name( $type_name );
		$class_name = '\\WPGraphQL\\ContentBlocks\\Blocks\\' . $class_name;

		/**
		 * This allows 3rd party extensions to hook and provide
		 * a path to their class for registering a field to the Schema
		 */
		$class_name = apply_filters( 'wpgraphql_content_blocks_block_class', $class_name, $block, $this );
		if ( class_exists( $class_name ) ) {
			new $class_name( $block, $this );
		} else {
			new Block( $block, $this );
		}
	}

	/**
	 * Register supporting block types
	 *
	 * @return void
	 */
	protected function register_support_block_types() {
		Anchor::register();
	}
}
