<?php

namespace WPGraphQL\ContentBlocks\Data;

use WPGraphQL\Model\Post;

/**
 * Class ContentBlocksResolver
 *
 * @package WPGraphQL\ContentBlocks
 */
final class ContentBlocksResolver {
    /**
	 * Retrieves a list of content blocks
	 *
     * @param mixed       $source  The object the connection is coming from
	 * @param array       $args    Query args to pass to the connection resolver
     * 
     * @throws Exception
	 */
    public static function resolve_content_blocks($node, $args) {
        $content = null;
		if ( $node instanceof Post ) {

            // @todo: this is restricted intentionally.
            // $content = $node->contentRaw;

            // This is the unrestricted version, but we need to
            // probably have a "Block" Model that handles
            // determining what fields should/should not be
            // allowed to be returned?
            $post    = get_post( $node->databaseId );
            $content = $post->post_content;
        }

        if ( empty( $content ) ) {
            return array();
        }

        // Parse the blocks from HTML comments to an array of blocks
        $parsed_blocks = parse_blocks( $content );
        if ( empty( $parsed_blocks ) ) {
            return array();
        }

        // Filter out blocks that have no name
        $parsed_blocks = array_filter(
            $parsed_blocks,
            function ( $parsed_block ) {
                return isset( $parsed_block['blockName'] ) && ! empty( $parsed_block['blockName'] );
            },
            ARRAY_FILTER_USE_BOTH
        );

        $parsed_blocks = array_map(
            function ( $parsed_block ) {
                $parsed_block['nodeId'] = uniqid();
                return $parsed_block;
            },
            $parsed_blocks
        );

        // Flatten block list here if requested
        if ( isset( $args['flat'] ) && 'true' == $args['flat'] ) {
            return self::flatten_block_list( $parsed_blocks );
        }
        return $parsed_blocks;
    }

    private static function flatten_block_list( $blocks ) {
        $result = array();
        foreach ( $blocks as $block ) {
            $result = array_merge( $result, self::flatten_inner_blocks( $block ) );
        }
        return $result;
    } 
    
    private static function flatten_inner_blocks( $block ) {
        $result          = array();
        $block['nodeId'] = isset( $block['nodeId'] ) ? $block['nodeId'] : uniqid();
        array_push( $result, $block );
        foreach ( $block['innerBlocks'] as $child ) {
            $child['parentId'] = $block['nodeId'];
            $result            = array_merge( $result, self::flatten_inner_blocks( $child ) );
        }
        return $result;
    }
}