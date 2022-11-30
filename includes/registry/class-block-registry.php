<?php

namespace WPGraphQL\ContentBlocks\Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Exception;
use WP_Block_Type;
use WPGraphQLContentBlocks\interfaces\OnInit;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

/**
 * Class Block_Registry
 *
 * @package WPGraphQLContentBlocks\Block_Registry
 */
class Block_Registry implements OnInit {


	/**
	 * The instance of the TypeRegistry.
	 *
	 * @var TypeRegistry
	 */
	public $type_registry;

	/**
	 * The instance of the WP_Block_Type_Registry.
	 *
	 * @var WP_Block_Type_Registry
	 */
	public $block_type_registry;

	/**
	 * The list of registered blocks.
	 *
	 * @var array
	 */
	public $registered_blocks;

	/**
	 * Registry constructor.
	 *
	 * @param TypeRegistry $type_registry
	 * @return object|WPGraphQLContentBlocks
	 * @since  0.0.1
	 */
	public function __construct( TypeRegistry $type_registry, WP_Block_Type_Registry $block_type_registry ) {
		$this->type_registry       = $type_registry;
		$this->block_type_registry = $block_type_registry;
	}

	/**
	 * @throws Exception
	 */
	public function onInit() {  }

	/**
	 * This adds the WP Block Registry to AppContext
	 *
	 * @return void
	 */
	public function pass_blocks_to_context() {
		add_filter(
			'graphql_app_context_config',
			function ( $config ) {
				$config['registered_editor_blocks'] = $this->registered_blocks;
				return $config;
			}
		);
	}

	/**
	 * Register Block Types to the GraphQL Schema
	 *
	 * @return void
	 */
	protected function register_block_types() {
		 $this->registered_blocks = $block_registry->get_all_registered();

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
	}

	/**
	 * Adds Block Fields to the WPGraphQL Schema
	 *
	 * @return void
	 */
	public function add_block_fields_to_schema() {
		// Get Post Types that are set to Show in GraphQL and Show in REST
		// If it doesn't show in REST, it's not enabled in the block editor
		$block_editor_post_types = get_post_types(
			array(
				'show_in_graphql' => true,
				'show_in_rest'    => true,
			),
			'objects'
		);

		$supported_post_types = array();

		if ( empty( $block_editor_post_types ) || ! is_array( $block_editor_post_types ) ) {
			return;
		}

		// Iterate over the post types
		foreach ( $block_editor_post_types as $block_editor_post_type ) {

			// If the post type doesn't support the editor, it's not block-editor enabled
			if ( ! post_type_supports( $block_editor_post_type->name, 'editor' ) ) {
				continue;
			}

			if ( ! isset( $block_editor_post_type->graphql_single_name ) ) {
				continue;
			}

			$supported_post_types[] = Utils::format_type_name( $block_editor_post_type->graphql_single_name );
		}

		// If there are no supported post types, early return
		if ( empty( $supported_post_types ) ) {
			return;
		}
	}
}
