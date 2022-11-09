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
 * @package WPGraphQLContentBlocks
 */

namespace WPGraphQLContentBlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGRAPHQL_CONTENT_BLOCKS_FILE', __FILE__ );
define( 'WPGRAPHQL_CONTENT_BLOCKS_DIR', dirname( __FILE__ ) );
define( 'WPGRAPHQL_CONTENT_BLOCKS_URL', plugin_dir_url( __FILE__ ) );
define( 'WPGRAPHQL_CONTENT_BLOCKS_PATH', plugin_basename( WPGRAPHQL_CONTENT_BLOCKS_FILE ) );
define( 'WPGRAPHQL_CONTENT_BLOCKS_SLUG', dirname( plugin_basename( WPGRAPHQL_CONTENT_BLOCKS_FILE ) ) );

require WPGRAPHQL_CONTENT_BLOCKS_DIR . '/includes/utilities/functions.php';
