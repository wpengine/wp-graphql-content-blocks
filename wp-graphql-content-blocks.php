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
 * Version: 4.8.2
 * Requires PHP: 7.4
 * Requires at least: 5.7
 * Requires Plugins: wp-graphql
 * WPGraphQL requires at least: 1.14.5
 * WPGraphQL tested up to: 2.0.0
 *
 * @package WPGraphQL\ContentBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpgraphql_content_blocks_constants' ) ) {
	/**
	 * Defines plugin constants.
	 *
	 * @since 4.2.0
	 */
	function wpgraphql_content_blocks_constants(): void {
		// Whether to autoload the files or not.
		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD', true );
		}

		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR', __DIR__ );
		}

		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE', __FILE__ );
		}

		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_PATH' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_PATH', plugin_basename( WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE ) );
		}

		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_VERSION' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_VERSION', '4.8.2' );
		}

		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_URL' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_PATH' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_PATH', plugin_basename( WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_URL ) );
		}
	}
}


if ( ! function_exists( 'wpgraphql_content_blocks_init' ) ) {
	/**
	 * The main function that returns the WPGraphQLContentBlocks class
	 *
	 * @since 0.0.1
	 */
	function wpgraphql_content_blocks_init(): void {
		// Define plugin constants.
		wpgraphql_content_blocks_constants();

		// Load the autoloader.
		require_once __DIR__ . '/includes/Autoloader.php';
		if ( ! \WPGraphQL\ContentBlocks\Autoloader::autoload() ) {
			return;
		}

		// Instantiate the plugin class.
		WPGraphQLContentBlocks::instance();
	}
}

// Get the plugin running.
add_action( 'plugins_loaded', 'wpgraphql_content_blocks_init', 15 );
