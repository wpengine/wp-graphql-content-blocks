<?php
/**
 * Plugin updates related functions.
 *
 * @package WPGraphQL\ContentBlocks\PluginUpdater
 */

declare(strict_types=1);

namespace WPGraphQL\ContentBlocks\PluginUpdater;

use stdClass;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve and convert custom endpoint response for the WordPress plugin api.
 *
 * Retrieve data from a custom endpoint then create a custom object that can be used by WordPress.
 *
 * @param object $args Plugin API arguments.
 *
 * @return object $api Plugin API arguments.
 */
function get_plugin_data_from_wpe( $args ) {
	$product_info = get_remote_plugin_info();
	if ( empty( $product_info ) ) {
		return $args;
	}

	$current_plugin_data = \get_plugin_data( WPGRAPHQL_CONTENT_BLOCKS_FILE );
	$meets_wp_req        = version_compare( get_bloginfo( 'version' ), $product_info->requires_at_least, '>=' );

	$api                        = new stdClass();
	$api->author                = 'WP Engine';
	$api->homepage              = 'https://wpengine.com';
	$api->name                  = $product_info->name;
	$api->requires              = isset( $product_info->requires_at_least ) ? $product_info->requires_at_least : $current_plugin_data['RequiresWP'];
	$api->sections['changelog'] = isset( $product_info->sections->changelog ) ? $product_info->sections->changelog : '<h4>1.0</h4><ul><li>Initial release.</li></ul>';
	$api->slug                  = 'wp-graphql-content-blocks';

	// Only pass along the update info if the requirements are met and there's actually a newer version.
	if ( $meets_wp_req && version_compare( $current_plugin_data['Version'], $product_info->version, '<' ) ) {
		$api->version       = $product_info->version;
		$api->download_link = $product_info->download_link;
	}

	return $api;
}

/**
 * Fetches and returns the plugin info api error.
 *
 * @return mixed|false The plugin api error or false.
 */
function get_plugin_api_error() {
	return get_option( 'wpgraphql_content_blocks_product_info_api_error', false );
}

/**
 * Retrieve remote plugin information from the custom endpoint.
 *
 * @return \stdClass
 */
function get_remote_plugin_info() {
	$current_plugin_data = \get_plugin_data( WPGRAPHQL_CONTENT_BLOCKS_FILE );
	$response            = get_transient( 'wpgraphql_content_blocks_product_info' );

	if ( false === $response ) {
		$request_args = [
			'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
			'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			'body'       => [
				'version' => $current_plugin_data['Version'],
			],
		];

		$response = request_plugin_updates( $request_args );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			if ( is_wp_error( $response ) ) {
				update_option( 'wpgraphql_content_blocks_product_info_api_error', $response->get_error_code(), false );
			} else {
				$response_body = json_decode( wp_remote_retrieve_body( $response ), false );
				$error_code    = ! empty( $response_body->error_code ) ? $response_body->error_code : 'unknown';
				update_option( 'wpgraphql_content_blocks_product_info_api_error', $error_code, false );
			}

			$response = new stdClass();

			set_transient( 'wpgraphql_content_blocks_product_info', $response, MINUTE_IN_SECONDS * 5 );

			return $response;
		}

		delete_option( 'wpgraphql_content_blocks_product_info_api_error' );

		$response = json_decode(
			wp_remote_retrieve_body( $response )
		);

		if ( ! property_exists( $response, 'icons' ) || empty( $response->icons['default'] ) ) {
			$response->icons['default'] = WPGRAPHQL_CONTENT_BLOCKS_URL . 'includes/updates/images/wpe-logo-stacked-inverse.svg';
		}

		set_transient( 'wpgraphql_content_blocks_product_info', $response, HOUR_IN_SECONDS * 12 );
	}

	return $response;
}

/**
 * Get the remote plugin api error message.
 *
 * @param string $reason The reason/error code received the API.
 *
 * @return string The error message.
 */
function get_api_error_text( string $reason ): string {
	switch ( $reason ) {
		case 'key-unknown':
			return __( 'The product you requested information for is unknown. Please contact support.', 'wp-graphql-content-blocks' );

		default:
			return sprintf(
				/* translators: %1$s: Link to GitHub issues. %2$s: The text that is linked. */
				__(
					'WPGraphQL Content Blocks encountered an unknown error connecting to the update service. This issue could be temporary. Please <a href="%1$s">%2$s</a> if this error persists.',
					'wp-graphql-content-blocks'
				),
				'https://github.com/wpengine/wp-graphql-content-blocks/issues',
				esc_html__( 'contact support', 'wp-graphql-content-blocks' )
			);
	}
}

/**
 * Retrieve plugin update information via http GET request.
 *
 * @param array $args Array of request args.
 *
 * @return array|\WP_Error A response as an array or WP_Error.
 * @uses wp_remote_get()
 * @link https://developer.wordpress.org/reference/functions/wp_remote_get/
 */
function request_plugin_updates( array $args = [] ) {
	return wp_remote_get(
		'https://wp-product-info.wpesvc.net/v1/plugins/wpgraphql-content-blocks',
		$args
	);
}
