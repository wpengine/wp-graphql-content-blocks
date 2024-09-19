<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;
use WPGraphQL\Model\Post;

final class ContentBlocksResolverTest extends PluginTestCase {
	public $instance;
	public $post_id;
	public $reusable_post_id;
	public $reusable_block_id;

	public function setUp(): void {
		parent::setUp();
		// Test comment.
		$this->reusable_block_id = wp_insert_post(
			[
				'post_title'   => 'Reusable Block',
				'post_type'    => 'wp_block',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim(
						'
							<!-- wp:columns -->
							<div class="wp-block-columns"><!-- wp:column -->
							<div class="wp-block-column"><!-- wp:paragraph -->
							<p>Example paragraph in Column 1</p>
							<!-- /wp:paragraph --></div>
							<!-- /wp:column -->
						'
					)
				),
			]
		);

		$this->reusable_post_id = wp_insert_post(
			[
				'post_title'   => 'Post Title',
				'post_content' => '<!-- wp:block {"ref":' . $this->reusable_block_id . '} /-->',
				'post_status'  => 'publish',
			]
		);

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Post Title',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim(
						'
							<!--	-->
							<!--	-->
							<!-- wp -->
							<!-- /wp -->
							
							<!-- wp: -->
							<!-- /wp: -->

							<!-- wp:columns -->
							<div class="wp-block-columns"><!-- wp:column -->
							<div class="wp-block-column"><!-- wp:paragraph -->
							<p>Example paragraph in Column 1</p>
							<!-- /wp:paragraph --></div>
							<!-- /wp:column -->

							<!-- wp:column -->
							<div class="wp-block-column"><!-- wp:paragraph -->
							<p>Example paragraph in Column 2</p>
							<!-- /wp:paragraph --></div>
							<!-- /wp:column --></div>
							<!-- /wp:columns -->

							<!-- Classic Block -->
							<p>Hello Classic Block</p>
						'
					)
				),
				'post_status'  => 'publish',
			]
		);

		$this->instance = new ContentBlocksResolver();
	}

	public function tearDown(): void {
		// your tear down methods here
		parent::tearDown();

		wp_delete_post( $this->post_id, true );
		wp_delete_post( $this->reusable_post_id, true );
		wp_delete_post( $this->reusable_block_id, true );

		// Clean up the filter.
		remove_all_filters( 'wpgraphql_content_blocks_pre_resolve_blocks' );
		remove_all_filters( 'wpgraphql_content_blocks_resolve_blocks' );
	}

	public function test_resolve_content_blocks_resolves_reusable_blocks() {
		$post_model = new Post( get_post( $this->reusable_post_id ) );
		$actual     = $this->instance->resolve_content_blocks( $post_model, [ 'flat' => true ] );

		// There should return only the non-empty blocks
		$this->assertEquals( 3, count( $actual ) );
		$this->assertEquals( 'core/columns', $actual[0]['blockName'] );
	}

	public function test_resolve_content_blocks_filters_empty_blocks() {
		$post_model = new Post( get_post( $this->post_id ) );
		$actual     = $this->instance->resolve_content_blocks( $post_model, [ 'flat' => true ] );
		// There should return only the non-empty blocks
		$this->assertEquals( 6, count( $actual ) );
		$this->assertEquals( 'core/columns', $actual[0]['blockName'] );
	}

	public function test_resolve_content_blocks_resolves_classic_blocks() {
		$post_model = new Post( get_post( $this->post_id ) );
		$actual     = $this->instance->resolve_content_blocks( $post_model, [ 'flat' => true ] );

		$this->assertEquals( 'core/freeform', $actual[5]['blockName'] );
	}

	public function test_resolve_content_blocks_filters_blocks_not_from_allow_list() {
		$post_model         = new Post( get_post( $this->post_id ) );
		$allowed            = [ 'core/column', 'core/paragraph' ];
		$parsed_blocks      = $this->instance->resolve_content_blocks( $post_model, [ 'flat' => true ], $allowed );
		$actual_block_names = array_values(
			array_unique(
				array_map(
					static function ( $parsed_block ) {
						return $parsed_block['blockName'];
					},
					$parsed_blocks,
				)
			)
		);
		// There should return only blocks from the allow list
		$this->assertEquals( 4, count( $parsed_blocks ) );
		$this->assertEquals( $allowed, $actual_block_names );
	}

	/**
	 * Test the wpgraphql_content_blocks_pre_resolve_blocks filter.
	 */
	public function test_pre_resolved_blocks_filter_returns_non_null() {
		add_filter(
			'wpgraphql_content_blocks_pre_resolve_blocks',
			static function ( $blocks, $node, $args, $allowed_block_names ) {
				return [
					[
						'blockName' => 'core/paragraph',
						'attrs'     => [ 'content' => 'Test content' ],
					],
				];
			},
			10,
			4
		);

		$post_id = self::factory()->post->create( [ 'post_content' => '' ] );
		$post    = new Post( get_post( $post_id ) );

		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [] );
		// The filter should return a block.
		$this->assertCount( 1, $resolved_blocks );
		$this->assertEquals( 'core/paragraph', $resolved_blocks[0]['blockName'] );
		$this->assertEquals( 'Test content', $resolved_blocks[0]['attrs']['content'] );

		// Clean up by deleting the created post.
		wp_delete_post( $post_id, true );
	}

	/**
	 * Tests content retrieval from a post node.
	 */
	public function test_content_retrieved_from_post_node() {
		$post_id         = self::factory()->post->create(
			[
				'post_content' => '<!-- wp:paragraph --><p>Content</p><!-- /wp:paragraph -->',
			]
		);
		$post            = new Post( get_post( $post_id ) );
		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [] );

		// The block should be resolved from the post node.
		$this->assertCount( 1, $resolved_blocks );
		$this->assertEquals( 'core/paragraph', $resolved_blocks[0]['blockName'] );
	}

	/**
	 * Tests that an empty array is returned when the post content is empty.
	 */
	public function test_returns_empty_array_for_empty_content() {
		$post_id = self::factory()->post->create( [ 'post_content' => '' ] );
		$post    = new Post( get_post( $post_id ) );

		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [] );

		// The block should be resolved from the post node.
		$this->assertIsArray( $resolved_blocks );
		$this->assertEmpty( $resolved_blocks );
	}

	/**
	 * Tests that the wpgraphql_content_blocks_allowed_blocks filter is applied.
	 */
	public function test_filters_allowed_blocks() {
		$post_id         = self::factory()->post->create(
			[
				'post_content' => '<!-- wp:paragraph --><p>Content</p><!-- /wp:paragraph -->' .
									'<!-- wp:heading --><h2>Heading</h2><!-- /wp:heading -->',
			]
		);
		$post            = new Post( get_post( $post_id ) );
		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [], [ 'core/paragraph' ] );

		// The block should be resolved from the post node.
		$this->assertCount( 1, $resolved_blocks );
		$this->assertEquals( 'core/paragraph', $resolved_blocks[0]['blockName'] );
	}

	/**
	 * Tests that the wpgraphql_content_blocks_resolve_blocks filter is applied.
	 */
	public function test_filters_after_resolving_blocks() {
		add_filter(
			'wpgraphql_content_blocks_resolve_blocks',
			static function ( $blocks, $node, $args, $allowed_block_names ) {
				return [ [ 'blockName' => 'core/test-filter' ] ];
			},
			10,
			4
		);

		$post = new Post( get_post( $this->post_id ) );

		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [] );

		// The block should be resolved from the post node.
		$this->assertCount( 1, $resolved_blocks );
		$this->assertEquals( 'core/test-filter', $resolved_blocks[0]['blockName'] );
	}

	public function test_inner_blocks_have_correct_parent_client_id() {
		// Set up post content with nested blocks
		$post_id = self::factory()->post->create(
			[
				'post_content' => '
					<!-- wp:columns -->
					<div class="wp-block-columns">
						<!-- wp:column -->
						<div class="wp-block-column">
							<!-- wp:heading -->
							<h2>Heading</h2>
							<!-- /wp:heading -->
							<!-- wp:paragraph -->
							<p>Paragraph</p>
							<!-- /wp:paragraph -->
						</div>
						<!-- /wp:column -->
						<!-- wp:column -->
						<div class="wp-block-column">
							<!-- wp:heading -->
							<h2>Heading</h2>
							<!-- /wp:heading -->
							<!-- wp:paragraph -->
							<p>Paragraph</p>
							<!-- /wp:paragraph -->
						</div>
						<!-- /wp:column -->
					</div>
					<!-- /wp:columns -->
				',
			]
		);
		$post = new Post( get_post( $post_id ) );
	
		// Resolve blocks in flat mode
		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => true ] );

		// Create a mapping of clientIds to parentClientIds
		$parent_map = [];
	
		// Loop through resolved blocks to populate parent map.
		foreach ( $resolved_blocks as $block ) {
			if ( isset( $block['parentClientId'] ) ) {
				$parent_map[ $block['clientId'] ] = $block['parentClientId'];
			}
			else {
				$parent_map[ $block['clientId'] ] = null;
			}
		}
	
		// Validate parent-child relationships
		foreach ( $resolved_blocks as $block ) {
			if ( isset( $block['parentClientId'] ) ) {

				// Ensure that the parentClientId points to a valid block
				$this->assertArrayHasKey( $block['parentClientId'], $parent_map );
			}
		}
	
		// Now compare nested and flat structures
		$nested_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => false ] );

		// Create a helper function to compare nested structure with flat
		// $this->assertNestedBlocksMatchFlat($nested_blocks, $resolved_blocks);

		// Resolve blocks in nested mode
		// $nested_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => false ] );
		// // Resolve blocks in flat mode
		// $flat_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => true ] );
	
		// // Convert nested blocks to flat structure for comparison
		// $transformed_nested_blocks = $this->transformNestedToFlat($nested_blocks);
	
		// // Assert that the transformed nested structure matches the flat structure
		// $this->assertEquals($transformed_nested_blocks, $flat_blocks);

		// Resolve blocks in nested mode
		$nested_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => false ] );
		// Resolve blocks in flat mode
		$flat_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => true ] );
	
		// Convert nested blocks to flat structure for comparison
		$transformed_nested_blocks = $this->transformNestedToFlat($nested_blocks);
	
		// Normalize both block arrays for comparison
		$normalized_nested_blocks = $this->normalizeBlocks($transformed_nested_blocks);
		$normalized_flat_blocks = $this->normalizeBlocks($flat_blocks);
	
		// Assert that the normalized structures match
		$this->assertEquals($normalized_nested_blocks, $normalized_flat_blocks);
	}


	// Helper function to transform nested blocks into flat structure
	protected function transformNestedToFlat(array $nested_blocks) {
		$flat_blocks = [];
		
		foreach ($nested_blocks as $block) {
			$flat_blocks[] = $block;
			if (!empty($block['innerBlocks'])) {
				$flat_blocks = array_merge($flat_blocks, $this->transformNestedToFlat($block['innerBlocks']));
			}
		}
		
		return $flat_blocks;
	}


// Helper function to normalize blocks by removing 'clientId'
protected function normalizeBlocks(array $blocks) {
    return array_map(function($block) {
        // Remove 'clientId' and 'parentClientId' from comparison
        unset($block['clientId']);
        unset($block['parentClientId']);

        if (!empty($block['innerBlocks'])) {
            $block['innerBlocks'] = $this->normalizeBlocks($block['innerBlocks']);
        }

        return $block;
    }, $blocks);
}

	
	// Helper function to validate nested blocks
	// protected function assertNestedBlocksMatchFlat(array $nested_blocks, array $flat_blocks) {


	// 	foreach ($nested_blocks as $block) {
	// 		if (!empty($block['innerBlocks'])) {
	// 			foreach ($block['innerBlocks'] as $inner_block) {

	// 				// Find the corresponding flat block
	// 				$flat_block = array_filter($flat_blocks, function ($flat) use ($inner_block) {
						
	// 					return $flat['clientId'] === $inner_block['clientId'];
	// 				});


	// 				// Assert that the parentClientId in flat block matches the parent's clientId
	// 				$this->assertNotEmpty($flat_block);
	// 				$flat_block = array_shift($flat_block); // Get the first matched block
	// 				$this->assertEquals($block['clientId'], $flat_block['parentClientId']);
	
	// 				// Recursively check nested blocks
	// 				$this->assertNestedBlocksMatchFlat($inner_block['innerBlocks'], $flat_blocks);
	// 			}
	// 		}
	// 	}
	// }
	

	// public function test_inner_blocks_have_correct_parent_client_id_x() {
	// 	$post_id = self::factory()->post->create(
	// 		[
	// 			'post_content' => '
	// 				<!-- wp:columns -->
	// 				<div class="wp-block-columns">
	// 					<!-- wp:column -->
	// 					<div class="wp-block-column">
	// 						<!-- wp:heading -->
	// 						<h2>Heading</h2>
	// 						<!-- /wp:heading -->
	// 						<!-- wp:paragraph -->
	// 						<p>Paragraph</p>
	// 						<!-- /wp:paragraph -->
	// 					</div>
	// 					<!-- /wp:column -->
	// 					<!-- wp:column -->
	// 					<div class="wp-block-column">
	// 						<!-- wp:heading -->
	// 						<h2>Heading</h2>
	// 						<!-- /wp:heading -->
	// 						<!-- wp:paragraph -->
	// 						<p>Paragraph</p>
	// 						<!-- /wp:paragraph -->
	// 					</div>
	// 					<!-- /wp:column -->
	// 				</div>
	// 				<!-- /wp:columns -->
	// 			',
	// 		]
	// 	);
	// 	$post = new Post( get_post( $post_id ) );
	
	// 	// Resolving blocks in "flat" mode.
	// 	$resolved_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => true ] );
	
	// 	// Checking that the are blocks resolved.
	// 	$this->assertNotEmpty( $resolved_blocks );
	
	// 	// Creating a mapping of clientIds to block structures for flat checking.
	// 	$client_id_map = [];
	
	// 	// Looping through resolved blocks to verify parentClientId assignment.
	// 	foreach ( $resolved_blocks as $block ) {
	// 		if ( ! empty( $block['innerBlocks'] ) ) {
	// 			foreach ( $block['innerBlocks'] as $inner_block ) {
	// 				// Verify that inner blocks have the correct parentClientId.
	// 				$this->assertEquals( $block['clientId'], $inner_block['parentClientId'] );
	
	// 				// Add to client_id_map for later comparison.
	// 				$client_id_map[ $inner_block['clientId'] ] = $inner_block;
	// 			}
	// 		}
	// 	}
	
	// 	// Resolve blocks in "non-flat" mode.
	// 	$nested_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => false ] );
	
	// 	// Compare flat and non-flat structures.
	// 	foreach ( $nested_blocks as $block ) {
	// 		if ( ! empty( $block['innerBlocks'] ) ) {
	// 			foreach ( $block['innerBlocks'] as $inner_block ) {
	// 				// Compare non-flat inner block with the corresponding flat block.
	// 				$this->assertArrayHasKey( $inner_block['clientId'], $client_id_map );
	// 				$this->assertEquals( $client_id_map[ $inner_block['clientId'] ], $inner_block );
	// 			}
	// 		}
	// 	}
	// }
	
}
