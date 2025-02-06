<?php
/**
 * Plugin upgrade checker
 *
 * @package WPGraphQL\ContentBlocks\Updates
 */

declare(strict_types=1);

namespace WPGraphQL\ContentBlocks\Updates;

if ( ! function_exists( 'wp_graphql_content_blocks_check_for_upgrades' ) ) {
	/**
	 * Checks for plugin upgrades
	 */
	function wp_graphql_content_blocks_check_for_upgrades(): void {
		$properties = [
			'plugin_slug'     => 'wp-graphql-content-blocks',
			'plugin_basename' => WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_PATH,
		];

		require_once __DIR__ . '/PluginUpdater.php';
		new PluginUpdater( $properties );
	}

	add_action( 'admin_init', __NAMESPACE__ . '\wp_graphql_content_blocks_check_for_upgrades' );
}
