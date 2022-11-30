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

namespace WPGraphQL\ContentBlocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPGRAPHQL_CONTENT_BLOCKS_FILE', __FILE__ );
define( 'WPGRAPHQL_CONTENT_BLOCKS_DIR', dirname( __FILE__ ) );
define( 'WPGRAPHQL_CONTENT_BLOCKS_URL', plugin_dir_url( __FILE__ ) );
define( 'WPGRAPHQL_CONTENT_BLOCKS_PATH', plugin_basename( WPGRAPHQL_CONTENT_BLOCKS_FILE ) );
define( 'WPGRAPHQL_CONTENT_BLOCKS_SLUG', dirname( plugin_basename( WPGRAPHQL_CONTENT_BLOCKS_FILE ) ) );

require WPGRAPHQL_CONTENT_BLOCKS_DIR . '/includes/utilities/functions.php';

// If the autoload file exists, require it.
// If the plugin was installed from composer, the autoload
// would be required elsewhere in the project
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( 'WPGraphQL_Content_Blocks' ) ) {
	require_once __DIR__ . '/includes/class-wpgraphql-content-blocks.php';
}

/**
 * Check whether WPGraphQL is active, and whether the minimum version requirement has been met,
 * and whether the autoloader is working as expected
 *
 * @return bool
 * @since 0.3
 */
function can_load_plugin() {
	// Is WPGraphQL active?
	if ( ! class_exists( 'WPGraphQL' ) ) {
		return false;
	}

	// Do we have a WPGraphQL version to check against?
	if ( ! defined( 'WPGRAPHQL_VERSION' ) ) {
		return false;
	}

	// If the WPGraphQL_Content_Blocks class doesn't exist, then the autoloader failed to load.
	// This likely means that the plugin was installed via composer and the parent
	// project doesn't have the autoloader setup properly
	if ( ! class_exists( \WPGraphQL\ContentBlocks\WPGraphQL_Content_Blocks::class ) ) {
		return false;
	}

	return true;
}


/**
 * The main function that returns the WPGraphQL_Content_Blocks class
 *
 * @since 1.0.0
 * @return object|WPGraphQL_Content_Blocks
 */
function content_blocks_load_plugin() {
	/**
	* If WPGraphQL is not active, or is an incompatible version, show the admin notice and bail.
	*/
	if ( false === can_load_plugin() ) {
		add_action( 'admin_init', __NAMESPACE__ . '\show_admin_notice' );
		return;
	}
	return \WPGraphQL\ContentBlocks\WPGraphQL_Content_Blocks::instance();
}

// Get the plugin running.
add_action( 'plugins_loaded', __NAMESPACE__ . '\content_blocks_load_plugin' );

/**
 * Show admin notice to admins if this plugin is active but WPGraphQL
 * is not active, or doesn't meet version requirements
 *
 * @return bool
 */
function show_admin_notice() {
	/**
	 * For users with lower capabilities, don't show the notice
	 */
	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	add_action(
		'admin_notices',
		function () {
			?>
		<div class="error notice is-dismissible">
			<p>
				<?php
				$text = sprintf( 'WPGraphQL Content Blocks will not work without WPGraphQL installed and active.', 'wp-graphql-content-blocks' );

				// phpcs:ignore
				esc_html_e($text, 'wp-graphql-content-blocks');
				?>
			</p>
		</div>
			<?php
		}
	);
}
