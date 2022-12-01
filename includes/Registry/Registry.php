<?php

namespace WPGraphQL\ContentBlocks\Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Exception;
use WP_Block_Type;
use WPGraphQL\ContentBlocks\Blocks\Block;
use WPGraphQL\ContentBlocks\Interfaces\OnInit;
use WPGraphQL\ContentBlocks\Type\InterfaceType\ContentBlockInterface;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Utils\Utils;

/**
 * Class Registry
 *
 * @package WPGraphQL\ContentBlocks\Registry
 */
final class Registry implements OnInit
{

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
	 * @param TypeRegistry $type_registry
	 * @param WP_Block_Type_Registry $block_type_registry
	 */
	public function __construct(TypeRegistry $type_registry, $block_type_registry)
	{
		$this->type_registry = $type_registry;
		$this->block_type_registry = $block_type_registry;
	}

	/**
	 * Registry init procedure.
	 * @throws Exception
	 */
	public function OnInit()
	{
		ContentBlockInterface::register_type($this->type_registry);
		$this->pass_blocks_to_context();
		$this->register_block_types();
		$this->add_block_fields_to_schema();
	}

	/**
	 * This adds the WP Block Registry to AppContext
	 *
	 * @return void
	 */
	public function pass_blocks_to_context()
	{
		add_filter('graphql_app_context_config', array($this, 'load_registered_editor_blocks'));
	}

	/**
	 * Loads registered_blocks into the app context config
	 *
	 * @return object
	 */
	public function load_registered_editor_blocks($config) {
		$config['registered_editor_blocks'] = $this->registered_blocks;
		return $config;
	}

	/**
	 * Register Block Types to the GraphQL Schema
	 *
	 * @return void
	 */
	protected function register_block_types()
	{
		$this->registered_blocks = $this->block_type_registry->get_all_registered();

		if (empty($this->registered_blocks) || !is_array($this->registered_blocks)) {
			return;
		}

		foreach ($this->registered_blocks as $block) {
			$this->register_block_type($block);
		}
	}

	/**
	 * Register a block from the Gutenberg server registry to the WPGraphQL Registry
	 *
	 * @param WP_Block_Type $block
	 */
	protected function register_block_type(WP_Block_Type $block)
	{

		$block_name = isset($block->name) && !empty($block->name) ? $block->name : 'Core/HTML';

		$type_name = preg_replace('/\//', '', lcfirst(ucwords($block_name, '/')));
		$type_name = Utils::format_type_name($type_name);

		$class_name = Utils::format_type_name($type_name);
		$class_name = '\\WPGraphQL\\ContentBlocks\\Blocks\\' . $class_name;

		/**
		 * This allows 3rd party extensions to hook and and provide
		 * a path to their class for registering a field to the Schema
		 */
		$class_name = apply_filters('graphql_content_blocks_block_class', $class_name, $block, $this);
		if (class_exists($class_name)) {
			new $class_name($block, $this);
		} else {
			new Block($block, $this);
		}
	}

	/**
	 * Adds Block Fields to the WPGraphQL Schema
	 *
	 * @return void
	 */
	public function add_block_fields_to_schema()
	{

		// Get Post Types that are set to Show in GraphQL and Show in REST
		// If it doesn't show in REST, it's not block-editor enabled
		$block_editor_post_types = get_post_types(['show_in_graphql' => true, 'show_in_rest' => true], 'objects');

		$supported_post_types = [];

		if (empty($block_editor_post_types) || !is_array($block_editor_post_types)) {
			return;
		}

		// Iterate over the post types
		foreach ($block_editor_post_types as $block_editor_post_type) {

			// If the post type doesn't support the editor, it's not block-editor enabled
			if (!post_type_supports($block_editor_post_type->name, 'editor')) {
				continue;
			}

			if (!isset($block_editor_post_type->graphql_single_name)) {
				continue;
			}

			$supported_post_types[] = Utils::format_type_name($block_editor_post_type->graphql_single_name);
		}

		// If there are no supported post types, early return
		if (empty($supported_post_types)) {
			return;
		}

		// Register the `NodeWithContentBlocks` Interface to the supported post types
		register_graphql_interfaces_to_types(['NodeWithContentBlocks'], $supported_post_types);
	}
}
