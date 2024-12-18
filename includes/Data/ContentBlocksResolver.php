<?php
/**
 * Used to resolve content blocks from a node.
 *
 * @package WPGraphQL\ContentBlocks\Data
 */

namespace WPGraphQL\ContentBlocks\Data;

use WPGraphQL\Model\Post;

/**
 * Class ContentBlocksResolver
 */
final class ContentBlocksResolver {
	/**
	 * Retrieves a list of content blocks
	 *
	 * @param \WPGraphQL\Model\Model|mixed $node The node we are resolving.
	 * @param array<string,mixed>          $args GraphQL query args to pass to the connection resolver.
	 * @param string[]                     $allowed_block_names The list of allowed block names to filter.
	 *
	 * @return array<string,mixed> The resolved parsed blocks.
	 */
	public static function resolve_content_blocks( $node, $args, $allowed_block_names = [] ): array {
		/**
		 * When this filter returns a non-null value, the content blocks resolver will use that value
		 *
		 * @param ?array                 $content_blocks The content blocks to parse.
		 * @param \WPGraphQL\Model\Model $node           The node we are resolving.
		 * @param array                  $args           GraphQL query args to pass to the connection resolver.
		 * @param array                  $allowed_block_names The list of allowed block names to filter.
		 */
		$pre_resolved_blocks = apply_filters( 'wpgraphql_content_blocks_pre_resolve_blocks', null, $node, $args, $allowed_block_names );

		if ( null !== $pre_resolved_blocks && is_array( $pre_resolved_blocks ) ) {
			return $pre_resolved_blocks;
		}

		$content = null;
		if ( $node instanceof Post ) {

			// @todo: this is restricted intentionally.
			// $content = $node->contentRaw;

			// This is the unrestricted version, but we need to
			// probably have a "Block" Model that handles
			// determining what fields should/should not be
			// allowed to be returned?
			$post    = get_post( $node->databaseId );
			$content = ! empty( $post->post_content ) ? $post->post_content : null;
		}

		/**
		 * Filters the content retrieved from the node used to parse the blocks.
		 *
		 * @param ?string                $content The content to parse.
		 * @param \WPGraphQL\Model\Model $node    The node we are resolving.
		 * @param array                  $args    GraphQL query args to pass to the connection resolver.
		 */
		$content = apply_filters( 'wpgraphql_content_blocks_resolver_content', $content, $node, $args );

		if ( empty( $content ) ) {
			return [];
		}

		// Parse the blocks from HTML comments to an array of blocks.
		$parsed_blocks = self::parse_blocks( $content );
		if ( empty( $parsed_blocks ) ) {
			return [];
		}

		// Flatten block list here if requested or if 'flat' value is not selected (default).
		if ( ! isset( $args['flat'] ) || 'true' == $args['flat'] ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			$parsed_blocks = self::flatten_block_list( $parsed_blocks );
		}

		// Final level of filtering out blocks not in the allowed list.
		$parsed_blocks = self::filter_allowed_blocks( $parsed_blocks, $allowed_block_names );

		/**
		 * Filters the content blocks after they have been resolved.
		 *
		 * @param array                  $parsed_blocks The parsed blocks.
		 * @param \WPGraphQL\Model\Model $node          The node we are resolving.
		 * @param array                  $args          GraphQL query args to pass to the connection resolver.
		 * @param array                  $allowed_block_names The list of allowed block names to filter.
		 */
		$parsed_blocks = apply_filters( 'wpgraphql_content_blocks_resolve_blocks', $parsed_blocks, $node, $args, $allowed_block_names );

		return is_array( $parsed_blocks ) ? $parsed_blocks : [];
	}

	/**
	 * Get blocks from html string.
	 *
	 * @param string $content Content to parse.
	 *
	 * @return array<string,mixed> List of blocks.
	 */
	private static function parse_blocks( $content ): array {
		$blocks = parse_blocks( $content );

		return self::handle_do_blocks( $blocks );
	}

	/**
	 * Recursively process blocks.
	 *
	 * This mirrors the `do_blocks` function in WordPress which is responsible for hydrating certain block attributes and supports, but without the forced rendering.
	 *
	 * @param array<string,mixed>[] $blocks Blocks data.
	 *
	 * @return array<string,mixed>[] The processed blocks.
	 */
	private static function handle_do_blocks( array $blocks ): array {
		$parsed = [];
		foreach ( $blocks as $block ) {
			$block_data = self::handle_do_block( $block );

			if ( $block_data ) {
				$parsed[] = $block_data;
			}
		}

		// Remove empty blocks.
		return array_values( array_filter( $parsed ) );
	}

	/**
	 * Process a block, getting all extra fields.
	 *
	 * @param array<string,mixed> $block Block data.
	 *
	 * @return ?array<string,mixed> The processed block.
	 */
	private static function handle_do_block( array $block ): ?array {
		if ( self::is_block_empty( $block ) ) {
			return null;
		}

		// Since Gutenberg assigns an empty blockName for Classic block, we define it here.
		if ( empty( $block['blockName'] ) ) {
			$block['blockName'] = 'core/freeform';
		}

		// Assign a unique clientId to the block.
		$block['clientId'] = uniqid();

		// @todo apply more hydrations.
		$block = self::populate_template_part_inner_blocks( $block );
		$block = self::populate_post_content_inner_blocks( $block );
		$block = self::populate_reusable_blocks( $block );
		$block = self::populate_pattern_inner_blocks( $block );

		// Prepare innerBlocks.
		if ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = self::handle_do_blocks( $block['innerBlocks'] );
		}

		return $block;
	}

	/**
	 * Checks whether a block is really empty, and not just a `core/freeform`.
	 *
	 * @param array<string,mixed> $block The block to check.
	 */
	private static function is_block_empty( array $block ): bool {
		// If we have a blockName, no need to check further.
		if ( ! empty( $block['blockName'] ) ) {
			return false;
		}

		// If there is no innerHTML and no innerContent, we can consider it empty.
		if ( empty( $block['innerHTML'] ) && empty( $block['innerContent'] ) ) {
			return true;
		}

		// Strip empty comments and spaces to see if `innerHTML` is truly empty.
		$stripped = preg_replace( '/<!--(.*)-->/Uis', '', $block['innerHTML'] );

		return empty( trim( $stripped ?? '' ) );
	}

	/**
	 * Populates the innerBlocks of a template part block with the blocks from the template part.
	 *
	 * @param array<string,mixed> $block The block to populate.
	 *
	 * @return array<string,mixed> The populated block.
	 */
	private static function populate_template_part_inner_blocks( array $block ): array {
		// Bail if not WP 5.8 or later.
		if ( ! function_exists( 'get_block_templates' ) ) {
			return $block;
		}

		if ( 'core/template-part' !== $block['blockName'] || ! isset( $block['attrs']['slug'] ) ) {
			return $block;
		}

		$matching_templates = get_block_templates( [ 'slug__in' => [ $block['attrs']['slug'] ] ], 'wp_template_part' );

		$template_blocks = ! empty( $matching_templates[0]->content ) ? self::parse_blocks( $matching_templates[0]->content ) : null;

		if ( empty( $template_blocks ) ) {
			return $block;
		}

		$block['innerBlocks'] = $template_blocks;

		return $block;
	}

	/**
	 * Populates the innerBlocks of a core/post-content block with the blocks from the post content.
	 *
	 * @param array<string,mixed> $block The block to populate.
	 *
	 * @return array<string,mixed> The populated block.
	 */
	private static function populate_post_content_inner_blocks( array $block ): array {
		if ( 'core/post-content' !== $block['blockName'] ) {
			return $block;
		}

		$post = get_post();

		if ( ! $post ) {
			return $block;
		}

		$parsed_blocks = ! empty( $post->post_content ) ? self::parse_blocks( $post->post_content ) : null;

		if ( empty( $parsed_blocks ) ) {
			return $block;
		}

		$block['innerBlocks'] = $parsed_blocks;

		return $block;
	}

	/**
	 * Populates reusable blocks with the blocks from the reusable ref ID.
	 *
	 * @param array<string,mixed> $block The block to populate.
	 *
	 * @return array<string,mixed> The populated block.
	 */
	private static function populate_reusable_blocks( array $block ): array {
		if ( 'core/block' !== $block['blockName'] || ! isset( $block['attrs']['ref'] ) ) {
			return $block;
		}

		$reusable_block = get_post( $block['attrs']['ref'] );

		if ( ! $reusable_block ) {
			return $block;
		}

		$parsed_blocks = ! empty( $reusable_block->post_content ) ? self::parse_blocks( $reusable_block->post_content ) : null;

		if ( empty( $parsed_blocks ) ) {
			return $block;
		}

		return array_merge( ...$parsed_blocks );
	}

	/**
	 * Populates the pattern innerBlocks with the blocks from the pattern.
	 *
	 * @param array<string,mixed> $block The block to populate.
	 * @return array<string,mixed> The populated block.
	 */
	private static function populate_pattern_inner_blocks( array $block ): array {
		// Bail if not WP 6.6 or later.
		if ( ! function_exists( 'resolve_pattern_blocks' ) ) {
			return $block;
		}

		if ( 'core/pattern' !== $block['blockName'] || ! isset( $block['attrs']['slug'] ) ) {
			return $block;
		}

		$resolved_patterns = resolve_pattern_blocks( [ $block ] );

		if ( empty( $resolved_patterns ) ) {
			return $block;
		}

		$block['innerBlocks'] = $resolved_patterns;
		return $block;
	}

	/**
	 * Flattens a list blocks into a single array
	 *
	 * @param array<string,mixed> $blocks A list of blocks to flatten.
	 *
	 * @return array<string,mixed> The flattened list of blocks.
	 */
	private static function flatten_block_list( $blocks ): array {
		$result = [];
		foreach ( $blocks as $block ) {
			$result = array_merge( $result, self::flatten_inner_blocks( $block ) );
		}
		return $result;
	}

	/**
	 * Flattens a block and its inner blocks into a single while attaching unique clientId's
	 *
	 * @param array<string,mixed> $block A parsed block.
	 *
	 * @return array<string,mixed> The flattened block.
	 */
	private static function flatten_inner_blocks( $block ): array {
		$result = [];

		// Assign a unique clientId to the block if it doesn't already have one.
		$block['clientId'] = isset( $block['clientId'] ) ? $block['clientId'] : uniqid();
		array_push( $result, $block );

		foreach ( $block['innerBlocks'] as $child ) {
			$child['parentClientId'] = $block['clientId'];

			// Flatten the child, and merge with the result.
			$result = array_merge( $result, self::flatten_inner_blocks( $child ) );
		}

		/** @var array<string,mixed> $result */
		return $result;
	}

	/**
	 * Filters out disallowed blocks from the list of blocks
	 *
	 * @param array<string,mixed> $blocks A list of blocks to filter.
	 * @param string[]            $allowed_block_names The list of allowed block names to filter.
	 *
	 * @return array<string,mixed> The filtered list of blocks.
	 */
	private static function filter_allowed_blocks( array $blocks, array $allowed_block_names ): array {
		if ( empty( $allowed_block_names ) ) {
			return $blocks;
		}

		return array_filter(
			$blocks,
			static function ( $block ) use ( $allowed_block_names ) {
				return in_array( $block['blockName'], $allowed_block_names, true );
			}
		);
	}
}
