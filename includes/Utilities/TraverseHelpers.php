<?php
/**
 * Helper functions for traversing the list of blocks
 *
 * @package WPGraphQL\ContentBlocks\Utilities
 */

namespace WPGraphQL\ContentBlocks\Utilities;

/**
 * Class TraverseHelpers
 * 
 * Provides utility functions to traverse and manipulate blocks.
 */
final class TraverseHelpers {
	/**
	 * Traverse blocks and apply a callback with optional depth limit.
	 *
	 * @param array    &$blocks The blocks to traverse.
	 * @param callable $callback The callback function to apply to each block.
	 * @param int      $depth The current depth of traversal.
	 * @param int      $max_depth The maximum depth to traverse.
	 */
	public static function traverse_blocks( &$blocks, $callback, $depth = 0, $max_depth = PHP_INT_MAX ) {
		foreach ( $blocks as &$block ) {
			$callback( $block );
			if ( ! empty( $block['innerBlocks'] ) && $depth < $max_depth ) {
				self::traverse_blocks( $block['innerBlocks'], $callback, $depth + 1, $max_depth );
			}
		}
	}

	/**
	 * Example callback function to replace reusable blocks.
	 *
	 * @param array $block The block to potentially replace.
	 */
	public static function replace_reusable_blocks( &$block ) {
		if ( 'core/block' === $block['blockName'] && isset( $block['attrs']['ref'] ) ) {
			$post            = get_post( $block['attrs']['ref'] );
			$reusable_blocks = ! empty( $post->post_content ) ? parse_blocks( $post->post_content ) : null;

			if ( ! empty( $reusable_blocks ) ) {
				$block = array_merge( ...$reusable_blocks );
			}
		}
	}
}
