<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;
use WPGraphQL\Model\Post;

final class ContentBlocksResolverTest extends PluginTestCase {
	public $instance;
	public $post_id;

	public function setUp(): void {
		parent::setUp();
		$this->post_id  = wp_insert_post(
			array(
				'post_title'   => 'Post Title',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim(
						'
                <!--  -->
                <!--  -->
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
			)
		);
		$this->instance = new ContentBlocksResolver();
	}

	public function tearDown(): void {
		// your tear down methods here
		parent::tearDown();
		wp_delete_post( $this->post_id, true );
	}

	public function test_resolve_content_blocks_filters_empty_blocks() {
		$post_model = new Post( get_post( $this->post_id ) );
		$actual     = $this->instance->resolve_content_blocks( $post_model, array( 'flat' => true ) );
		// There should return only the non-empty blocks
		$this->assertEquals( count( $actual ), 6 );
		$this->assertEquals( $actual[0]['blockName'], 'core/columns' );
	}

	public function test_resolve_content_blocks_resolves_classic_blocks() {
		$post_model = new Post( get_post( $this->post_id ) );
		$actual     = $this->instance->resolve_content_blocks( $post_model, array( 'flat' => true ) );

		$this->assertEquals( $actual[5]['blockName'], 'core/freeform' );
	}

	public function test_resolve_content_blocks_filters_blocks_not_from_allow_list() {
		$post_model         = new Post( get_post( $this->post_id ) );
		$allowed            = array( 'core/column', 'core/paragraph' );
		$parsed_blocks      = $this->instance->resolve_content_blocks( $post_model, array( 'flat' => true ), $allowed );
		$actual_block_names = array_values(
			array_unique(
				array_map(
					function ( $parsed_block ) {
						return $parsed_block['blockName'];
					},
					$parsed_blocks,
				)
			)
		);
		// There should return only blocks from the allow list
		$this->assertEquals( count( $parsed_blocks ), 4 );
		$this->assertEquals( $actual_block_names, $allowed );
	}
}
