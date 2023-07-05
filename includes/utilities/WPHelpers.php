<?php
/**
 * Helper functions for WordPress
 *
 * @package WPGraphQL\ContentBlocks\Utilities
 */

namespace WPGraphQL\ContentBlocks\Utilities;

use stdClass;
use WP_Post;
use WP_Block_Editor_Context;

/**
 * Class WPHelpers
 */
final class WPHelpers {
	/**
	 * Gets Block Editor supported post types
	 *
	 * @return \WP_Post_Type[]
	 */
	public static function get_supported_post_types(): array {
		$supported_post_types = [];
		/**
		 * Get Post Types that are set to Show in GraphQL and Show in REST.
		 * If it doesn't show in REST, it's not block-editor.
		 *
		 * @var \WP_Post_Type[] $block_editor_post_types
		 */
		$block_editor_post_types = \WPGraphQL::get_allowed_post_types( 'objects' );

		if ( empty( $block_editor_post_types ) || ! is_array( $block_editor_post_types ) ) {
			return $supported_post_types;
		}

		// Iterate over the post types
		foreach ( $block_editor_post_types as $block_editor_post_type ) {
			// If the post type doesn't support the editor, it's not block-editor enabled
			if ( ! post_type_supports( $block_editor_post_type->name, 'editor' ) ) {
				continue;
			}

			// If the post type is not set to show in REST then Gutenberg will not be enabled for the type.
			if ( ! $block_editor_post_type->show_in_rest ) {
				continue;
			}

			if ( ! isset( $block_editor_post_type->graphql_single_name ) ) {
				continue;
			}

			$supported_post_types[] = $block_editor_post_type;
		}

		return $supported_post_types;
	}

	/**
	 * Gets the get_block_editor_context of a specific Post Type
	 *
	 * @param string $post_type The Post Type to use.
	 * @param int    $id The Post ID to use.
	 *
	 * @return \WP_Block_Editor_Context The Block Editor Context
	 */
	public static function get_block_editor_context( string $post_type, $id = -99 ): WP_Block_Editor_Context {
		$post_id              = $id;
		$post                 = new stdClass();
		$post->ID             = $post_id;
		$post->post_author    = 1;
		$post->post_date      = current_time( 'mysql' );
		$post->post_date_gmt  = current_time( 'mysql', 1 );
		$post->post_title     = '';
		$post->post_content   = '';
		$post->post_status    = '';
		$post->comment_status = 'closed';
		$post->ping_status    = 'closed';
		$post->post_name      = 'fake-post-' . wp_rand( 1, 99999 );

		$post->post_type = $post_type;
		$post->filter    = 'raw';

		return new WP_Block_Editor_Context( [ 'post' => new WP_Post( $post ) ] );
	}
}
