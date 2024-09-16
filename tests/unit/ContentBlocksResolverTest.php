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

		$post_id = self::factory()->post->create(
			[
				'post_content' => '<!-- wp:paragraph --><p>Content</p><!-- /wp:paragraph -->',
			]
		);
		$post    = new Post( get_post( $post_id ) );

		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [] );

		// The block should be resolved from the post node.
		$this->assertCount( 1, $resolved_blocks );
		$this->assertEquals( 'core/test-filter', $resolved_blocks[0]['blockName'] );
	}
}
