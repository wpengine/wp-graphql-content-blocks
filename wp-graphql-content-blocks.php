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

define( 'WPGRAPHQL_CONTENT_BLOCKS_DIR', dirname( __FILE__ ) );

if ( ! class_exists( 'WPGraphQLContentBlocks' ) ) {
	require_once __DIR__ . '/includes/WPGraphQLContentBlocks.php';
}

include_once __DIR__ . '/includes/utilities/updater.php';

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
		if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
			$config = array(
				'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
				'proper_folder_name' => 'wp-graphql-content-blocks', // this is the name of the folder your plugin lives in
				'api_url' => 'https://api.github.com/repos/wpengine/wp-graphql-content-blocks', // the GitHub API url of your GitHub repo
				'raw_url' => 'https://raw.github.com/wpengine/wp-graphql-content-blocks/main', // the GitHub raw url of your GitHub repo
				'github_url' => 'https://github.com/wpengine/wp-graphql-content-blocks', // the GitHub url of your GitHub repo
				'zip_url' => 'https://github.com/wpengine/wp-graphql-content-blocks/releases/latest/download/wp-graphql-content-blocks.zip', // the zip url of the GitHub repo
				'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
				'requires' => '5.7', // which version of WordPress does your plugin require?
				'tested' => '6.1', // which version of WordPress is your plugin tested up to?
				'readme' => 'readme.txt', // which file to use as the readme for the version number
				'access_token' => '', // Access private repositories by authorizing under Plugins > GitHub Updates when this example plugin is installed
			);
			new WP_GitHub_Updater($config);
		}

		return \WPGraphQLContentBlocks::instance();
	}
}

// Get the plugin running.
add_action( 'plugins_loaded', 'wpgraphql_content_blocks_init', 15 );
require WPGRAPHQL_CONTENT_BLOCKS_DIR . '/includes/utilities/functions.php';
