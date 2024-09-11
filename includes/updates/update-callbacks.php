<?php
/**
 * Plugin updates related callbacks.
 *
 * @package WPGraphQL\ContentBlocks\PluginUpdater
 */

declare(strict_types=1);

namespace WPGraphQL\ContentBlocks\PluginUpdater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'pre_set_site_transient_update_plugins', __NAMESPACE__ . '\check_for_plugin_updates' );
/**
 * Callback for WordPress 'pre_set_site_transient_update_plugins' filter.
 *
 * Check for plugin updates by retrieving data from the plugin update source.
 *
 * This will let WordPress know an update is available.
 *
 * @param object $data WordPress update object.
 *
 * @return object $data An updated object if an update exists, default object if not.
 */
function check_for_plugin_updates( $data ) {
	if ( empty( $data ) ) {
		return $data;
	}

	$response = get_remote_plugin_info();
	if ( empty( $response->requires_at_least ) || empty( $response->version ) ) {
		return $data;
	}

	$response->slug      = 'wp-graphql-content-blocks';
	$current_plugin_data = \get_plugin_data( WPGRAPHQL_CONTENT_BLOCKS_FILE );
	$meets_wp_req        = version_compare( get_bloginfo( 'version' ), $response->requires_at_least, '>=' );

	// Only update the response if there's a newer version, otherwise WP shows an update notice for the same version.
	if ( $meets_wp_req && version_compare( $current_plugin_data['Version'], $response->version, '<' ) ) {
		$response->plugin                                = plugin_basename( WPGRAPHQL_CONTENT_BLOCKS_FILE );
		$data->response[ WPGRAPHQL_CONTENT_BLOCKS_PATH ] = $response;
	}

	return $data;
}

add_filter( 'plugins_api', __NAMESPACE__ . '\custom_plugin_api_request', 10, 3 );
/**
 * Callback for WordPress 'plugins_api' filter.
 *
 * Return a custom response for this plugin from the custom endpoint.
 *
 * @link https://developer.wordpress.org/reference/hooks/plugins_api/
 *
 * @param false|object|array $api The result object or array. Default false.
 * @param string             $action The type of information being requested from the Plugin Installation API.
 * @param object             $args Plugin API arguments.
 *
 * @return false|\WPGraphQL\ContentBlocks\PluginUpdater\stdClass $response Plugin API arguments.
 */
function custom_plugin_api_request( $api, $action, $args ) {
	if ( empty( $args->slug ) || WPGRAPHQL_CONTENT_BLOCKS_SLUG !== $args->slug ) {
		return $api;
	}

	$response = get_plugin_data_from_wpe( $args );
	if ( empty( $response ) || is_wp_error( $response ) ) {
		return $api;
	}

	return $response;
}

add_action( 'admin_notices', __NAMESPACE__ . '\delegate_plugin_row_notice' );
/**
 * Callback for WordPress 'admin_notices' action.
 *
 * Delegate actions to display an error message on the plugin table row if present.
 *
 * @link https://developer.wordpress.org/reference/hooks/admin_notices/
 *
 * @return void
 */
function delegate_plugin_row_notice() {
	$screen = get_current_screen();
	if ( 'plugins' !== $screen->id ) {
		return;
	}

	$error = get_plugin_api_error();
	if ( ! $error ) {
		return;
	}

	$plugin_basename = plugin_basename( WPGRAPHQL_CONTENT_BLOCKS_FILE );

	remove_action( "after_plugin_row_{$plugin_basename}", 'wp_plugin_update_row' );
	add_action( "after_plugin_row_{$plugin_basename}", __NAMESPACE__ . '\display_plugin_row_notice', 10 );
}

/**
 * Callback for WordPress 'after_plugin_row_{plugin_basename}' action.
 *
 * Callback added in add_plugin_page_notices().
 *
 * Show a notice in the plugin table row when there is an error present.
 *
 * @return void
 */
function display_plugin_row_notice() {
	$error = get_plugin_api_error();

	?>
	<tr class="plugin-update-tr active" id="wp-graphql-content-blocks-update" data-slug="wp-graphql-content-blocks" data-plugin="wp-graphql-content-blocks/wp-graphql-content-blocks.php">
		<td colspan="4" class="plugin-update">
			<div class="update-message notice inline notice-error notice-alt">
				<p>
					<?php echo wp_kses_post( get_api_error_text( $error ) ); ?>
				</p>
			</div>
		</td>
	</tr>
	<?php
}

add_action( 'admin_notices', __NAMESPACE__ . '\display_update_page_notice' );
/**
 * Callback for WordPress 'admin_notices' action.
 *
 * Display an error notice on the "WordPress Updates" page if present.
 *
 * @link https://developer.wordpress.org/reference/hooks/admin_notices/
 *
 * @return void
 */
function display_update_page_notice() {
	$screen = get_current_screen();
	if ( 'update-core' !== $screen->id ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only used to avoid displaying messages when inappropriate.
	if ( ! empty( $_GET['action'] ) && 'do-theme-upgrade' === $_GET['action'] ) {
		return;
	}

	$error = get_plugin_api_error();
	if ( ! $error ) {
		return;
	}

	?>
	<div class="error">
		<p>
			<?php echo wp_kses_post( get_api_error_text( $error ) ); ?>
		</p>
	</div>
	<?php
}

add_filter( 'semantic_versioning_notice_text', __NAMESPACE__ . '\filter_semver_notice_text', 10, 2 );
/**
 * Filters the semver notice text when breaking changes are released.
 *
 * @param string $notice_text The default notice text.
 * @param string $plugin_filename The plugin directory and filename.
 *
 * @return string
 */
function filter_semver_notice_text( $notice_text, $plugin_filename ) {
	if ( WPGRAPHQL_CONTENT_BLOCKS_PATH !== $plugin_filename ) {
		return $notice_text;
	}
	return '<br><br>' . __( '<b>THIS UPDATE MAY CONTAIN BREAKING CHANGES:</b> This plugin uses Semantic Versioning, and this new version is a major release. Please review the changelog before updating.', 'wp-graphql-content-blocks' );
}
