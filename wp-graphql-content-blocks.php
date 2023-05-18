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
 * Version: 0.2.1
 * Requires PHP: 7.4
 * Requires at least: 5.7
 *
 * @package WPGraphQL\ContentBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGRAPHQL_CONTENT_BLOCKS_DIR', __DIR__ );

if ( ! class_exists( 'WPGraphQLContentBlocks' ) ) {
	require_once __DIR__ . '/includes/WPGraphQLContentBlocks.php';
}


if ( ! function_exists( 'wpgraphql_content_blocks_init' ) ) {
	/**
	 * The main function that returns the WPGraphQLContentBlocks class
	 *
	 * @since 1.0.0
	 * @return object|WPGraphQLContentBlocks
	 */
	function wpgraphql_content_blocks_init() {
		/**
		 * Return an instance of the action
		 */
		return \WPGraphQLContentBlocks::instance();
	}
}

// Get the plugin running.
add_action( 'plugins_loaded', 'wpgraphql_content_blocks_init', 15 );
require WPGRAPHQL_CONTENT_BLOCKS_DIR . '/includes/utilities/functions.php';
