<?php
/**
 * Plugin Name: WPGraphQL Content Blocks
 * Description: Extends WPGraphQL to support querying (Gutenberg) Blocks as data.
 * Author: WP Engine
 * Author URI: https://wpengine.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-graphql-content-blocks
 * Domain Path: /languages
 * Version: 0.1.0
 * Requires PHP: 7.2
 * Requires at least: 5.7
 *
 * @package WPGraphQL\ContentBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPGraphQLBlockEditor' ) ) {
	require_once __DIR__ . '/includes/WPGraphQLBlockEditor.php';
}

if ( ! function_exists( 'graphql_block_editor_init' ) ) {
	/**
	 * Function that instantiates the plugins main class
	 *
	 * @return object
	 */
	function graphql_block_editor_init() {
		/**
		 * Return an instance of the action
		 */
		return \WPGraphQLBlockEditor::instance();
	}
}

add_action( 'plugins_loaded', 'graphql_block_editor_init', 15 );
