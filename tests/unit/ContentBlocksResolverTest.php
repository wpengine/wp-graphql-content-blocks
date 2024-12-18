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

		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		// your tear down methods here
		wp_delete_post( $this->post_id, true );
		wp_delete_post( $this->reusable_post_id, true );
		wp_delete_post( $this->reusable_block_id, true );
		\WPGraphQL::clear_schema();

		parent::tearDown();
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
			static function () {
				return [
					[
						'blockName' => 'core/paragraph',
						'attrs'     => [ 'content' => 'Test content' ],
					],
				];
			},
			10,
		);

		$post = new Post( get_post( $this->post_id ) );

		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [] );
		// The filter should return a block.
		$this->assertCount( 1, $resolved_blocks );
		$this->assertEquals( 'core/paragraph', $resolved_blocks[0]['blockName'] );
		$this->assertEquals( 'Test content', $resolved_blocks[0]['attrs']['content'] );

		// Cleanup.
		remove_all_filters( 'wpgraphql_content_blocks_pre_resolve_blocks' );
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

		// Cleanup.
		wp_delete_post( $post_id, true );
	}

	/**
	 * Tests that the wpgraphql_content_blocks_resolve_blocks filter is applied.
	 */
	public function test_filters_wpgraphql_content_blocks_resolve_blocks() {
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

		// Cleanup.
		remove_all_filters( 'wpgraphql_content_blocks_resolve_blocks' );
	}

	/**
	 * Tests that flat and nested blocks are resolved correctly.
	 */
	public function test_inner_blocks() {
		$post_content = '
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
			<!-- /wp:columns -->';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			]
		);

		$post = new Post( get_post( $this->post_id ) );

		// Resolve blocks as nested.
		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => false ] );

		$this->assertCount( 1, $resolved_blocks, 'There should be only one top-level block (columns).' );
		$this->assertEquals( 'core/columns', $resolved_blocks[0]['blockName'] );
		$this->assertNotEmpty( $resolved_blocks[0]['clientId'], 'The clientId should be set.' );
		$this->assertArrayNotHasKey( 'parentClientId', $resolved_blocks[0], 'The parentClientId should be empty.' );

		$this->assertCount( 2, $resolved_blocks[0]['innerBlocks'], 'There should be two inner blocks (columns).' );

		// Check the inner blocks.
		$expected_parent_client_id = $resolved_blocks[0]['clientId'];

		foreach ( $resolved_blocks[0]['innerBlocks'] as $inner_block ) {
			$this->assertEquals( 'core/column', $inner_block['blockName'] );
			$this->assertCount( 2, $inner_block['innerBlocks'], 'There should be two inner blocks (column).' );
			$this->assertNotEmpty( $inner_block['clientId'], 'The clientId should be set.' );
			$this->assertArrayNotHasKey( 'parentClientId', $resolved_blocks[0], 'The parentClientId should only be set when flattening.' ); // @todo This is incorrect, the parentClientId should be set for nested blocks.

			// Check the inner inner blocks.
			$expected_parent_client_id = $inner_block['clientId'];

			foreach ( $inner_block['innerBlocks'] as $inner_inner_block ) {
				$this->assertNotEmpty( $inner_inner_block['clientId'], 'The clientId should be set.' );
				$this->assertArrayNotHasKey( 'parentClientId', $resolved_blocks[0], 'The parentClientId should only be set when flattening.' ); // @todo This is incorrect, the parentClientId should be set for nested blocks.
			}
		}

		// Resolve blocks as flat.
		$expected_parent_client_id = null;
		$expected_blocks           = $resolved_blocks;

		$resolved_blocks = $this->instance->resolve_content_blocks( $post, [ 'flat' => true ] );

		$this->assertCount( 7, $resolved_blocks, 'There should be five blocks when flattened.' );

		// Check the top-level block (columns).
		$this->assertNotEmpty( $resolved_blocks[0]['clientId'], 'The clientId should be set.' );
		$this->assertEqualBlocks( $expected_blocks[0], $resolved_blocks[0], 'The top-level block should match.' );

		// Check first inner block (column).
		$expected_parent_client_id = $resolved_blocks[0]['clientId'];
		$this->assertNotEmpty( $resolved_blocks[1]['clientId'], 'The clientId should be set.' );
		$this->assertEquals( $expected_parent_client_id, $resolved_blocks[1]['parentClientId'], 'The parentClientId should match.' );
		$this->assertEqualBlocks( $expected_blocks[0]['innerBlocks'][0], $resolved_blocks[1], 'The first inner block should match.' );

		// Check first inner block children.
		$expected_parent_client_id = $resolved_blocks[1]['clientId'];
		$this->assertNotEmpty( $resolved_blocks[2]['clientId'], 'The clientId should be set.' );
		$this->assertEquals( $expected_parent_client_id, $resolved_blocks[2]['parentClientId'], 'The parentClientId should match.' );
		$this->assertEqualBlocks( $expected_blocks[0]['innerBlocks'][0]['innerBlocks'][0], $resolved_blocks[2], 'The first inner inner block should match.' );

		$this->assertNotEmpty( $resolved_blocks[3]['clientId'], 'The clientId should be set.' );
		$this->assertEquals( $expected_parent_client_id, $resolved_blocks[3]['parentClientId'], 'The parentClientId should match.' );
		$this->assertEqualBlocks( $expected_blocks[0]['innerBlocks'][0]['innerBlocks'][1], $resolved_blocks[3], 'The second inner inner block should match.' );

		// Check second inner block (column).
		$expected_parent_client_id = $resolved_blocks[0]['clientId'];
		$this->assertNotEmpty( $resolved_blocks[4]['clientId'], 'The clientId should be set.' );
		$this->assertEquals( $expected_parent_client_id, $resolved_blocks[4]['parentClientId'], 'The parentClientId should match.' );
		$this->assertEqualBlocks( $expected_blocks[0]['innerBlocks'][1], $resolved_blocks[4], 'The first inner block should match.' );

		// Check second inner block children.
		$expected_parent_client_id = $resolved_blocks[4]['clientId'];
		$this->assertNotEmpty( $resolved_blocks[5]['clientId'], 'The clientId should be set.' );
		$this->assertEquals( $expected_parent_client_id, $resolved_blocks[5]['parentClientId'], 'The parentClientId should match.' );
		$this->assertEqualBlocks( $expected_blocks[0]['innerBlocks'][1]['innerBlocks'][0], $resolved_blocks[5], 'The first inner inner block should match.' );

		$this->assertNotEmpty( $resolved_blocks[6]['clientId'], 'The clientId should be set.' );
		$this->assertEquals( $expected_parent_client_id, $resolved_blocks[6]['parentClientId'], 'The parentClientId should match.' );
		$this->assertEqualBlocks( $expected_blocks[0]['innerBlocks'][1]['innerBlocks'][1], $resolved_blocks[6], 'The second inner inner block should match.' );
	}

	/**
	 * Asserts two blocks are equal, ignoring clientId and parentClientId.
	 *
	 * @param array<string,mixed> $expected The expected block.
	 * @param array<string,mixed> $actual   The actual block.
	 * @param string              $message  The message to display if the assertion fails.
	 */
	protected function assertEqualBlocks( $expected, $actual, $message = '' ) {
		// Remove clientId and parentClientId from comparison.
		unset( $expected['clientId'] );
		unset( $expected['parentClientId'] );
		unset( $actual['clientId'] );
		unset( $actual['parentClientId'] );

		$expected_inner_blocks = $expected['innerBlocks'] ?? [];
		$actual_inner_blocks   = $actual['innerBlocks'] ?? [];

		unset( $expected['innerBlocks'] );
		unset( $actual['innerBlocks'] );

		$this->assertEquals( $expected, $actual, $message );

		foreach ( $expected_inner_blocks as $index => $expected_inner_block ) {
			$this->assertEqualBlocks( $expected_inner_block, $actual_inner_blocks[ $index ], $message );
		}
	}

	/**
	 * Tests that pattern inner blocks are resolved correctly.
	 */
	public function test_resolve_content_blocks_resolves_pattern_inner_blocks() {
		// Skip if pattern resolution functionality is not supported.
		if ( ! function_exists( 'resolve_pattern_blocks' ) ) {
			$this->markTestSkipped( 'Pattern block resolution not supported in this WordPress version.' );
		}

		// Create a pattern
		$pattern_name = 'test/pattern-blocks';
		$pattern_content = '
			<!-- wp:paragraph -->
			<p>Pattern Paragraph</p>
			<!-- /wp:paragraph -->
			<!-- wp:heading -->
			<h2>Pattern Heading</h2>
			<!-- /wp:heading -->
		';

		// Register the pattern.
		register_block_pattern(
			$pattern_name,
			[
				'title'    => 'Test Pattern',
				'content'  => $pattern_content,
			]
		);

		// Update post content to include pattern block.
		$post_content = '
			<!-- wp:pattern {"slug":"test/pattern-blocks"} /-->
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			]
		);

		$post_model = new Post( get_post( $this->post_id ) );
		
		// Resolve blocks as nested.
		$resolved_blocks = $this->instance->resolve_content_blocks( $post_model, [ 'flat' => false ] );

		$this->assertCount( 1, $resolved_blocks, 'There should be only one top-level block (pattern).' );
		$this->assertEquals( 'core/pattern', $resolved_blocks[0]['blockName'] );
		$this->assertCount( 2, $resolved_blocks[0]['innerBlocks'], 'There should be two inner blocks in the pattern.' );
		$this->assertEquals( 'core/paragraph', $resolved_blocks[0]['innerBlocks'][0]['blockName'] );
		$this->assertEquals( 'core/heading', $resolved_blocks[0]['innerBlocks'][1]['blockName'] );

		// Resolve blocks as flat.
		$resolved_flat_blocks = $this->instance->resolve_content_blocks( $post_model, [ 'flat' => true ] );

		$this->assertCount( 3, $resolved_flat_blocks, 'There should be three blocks when flattened.' );
		$this->assertEquals( 'core/pattern', $resolved_flat_blocks[0]['blockName'] );
		$this->assertEquals( 'core/paragraph', $resolved_flat_blocks[1]['blockName'] );
		$this->assertEquals( 'core/heading', $resolved_flat_blocks[2]['blockName'] );

		// Cleanup: Unregistering the pattern.
		unregister_block_pattern( $pattern_name );
	}

	/**
	 * Tests that template part inner blocks are resolved correctly.
	 */
	public function test_resolve_content_blocks_resolves_template_part_inner_blocks() {
		// Skip if template part functionality is not supported
		if ( ! function_exists( 'get_block_templates' ) ) {
			$this->markTestSkipped( 'Template part functionality not supported in this WordPress version.' );
		}

		// Mock the get_block_templates function to control the output.
		$mock_template = (object) [
			'content' => '<!-- wp:paragraph /--><!-- wp:heading /-->',
		];

		add_filter(
			'get_block_templates',
			function () use ( $mock_template ) {
				return [ $mock_template ];
			}
		);

		// Update post content to include template part block
		$post_content = '
			<!-- wp:template-part {"slug":"test-template-part"} /-->
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			]
		);

		$post_model = new Post( get_post( $this->post_id ) );
		
		// Resolve blocks as nested
		$resolved_blocks = $this->instance->resolve_content_blocks( $post_model, [ 'flat' => false ] );

		// Assertions
		$this->assertCount( 1, $resolved_blocks, 'There should be only one top-level block (template-part).' );
		$this->assertEquals( 'core/template-part', $resolved_blocks[0]['blockName'] );
		$this->assertCount( 2, $resolved_blocks[0]['innerBlocks'], 'There should be two inner blocks in the template part.' );
		$this->assertEquals( 'core/paragraph', $resolved_blocks[0]['innerBlocks'][0]['blockName'] );
		$this->assertEquals( 'core/heading', $resolved_blocks[0]['innerBlocks'][1]['blockName'] );

		// Resolve blocks as flat
		$resolved_flat_blocks = $this->instance->resolve_content_blocks( $post_model, [ 'flat' => true ] );

		$this->assertCount( 3, $resolved_flat_blocks, 'There should be three blocks when flattened.' );
		$this->assertEquals( 'core/template-part', $resolved_flat_blocks[0]['blockName'] );
		$this->assertEquals( 'core/paragraph', $resolved_flat_blocks[1]['blockName'] );
		$this->assertEquals( 'core/heading', $resolved_flat_blocks[2]['blockName'] );
	}
}
