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
			array(
				'post_title' => 'Reusable Block',
				'post_type' => 'wp_block',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim('
				<!-- wp:columns -->
				<div class="wp-block-columns"><!-- wp:column -->
				<div class="wp-block-column"><!-- wp:paragraph -->
				<p>Example paragraph in Column 1</p>
				<!-- /wp:paragraph --></div>
				<!-- /wp:column -->'
					)
				)
			)
		);

		$this->reusable_post_id  = wp_insert_post(
			array(
				'post_title'   => 'Post Title',
				'post_content' => '<!-- wp:block {"ref":' . $this->reusable_block_id . '} /-->',
				'post_status'  => 'publish',
			)
		);

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
		wp_delete_post( $this->reusable_post_id, true );
		wp_delete_post( $this->reusable_block_id, true );
	}

    public function test_resolve_content_blocks_resolves_reusable_blocks() {
        $post_model = new Post( get_post( $this->reusable_post_id ) );
        $actual     = $this->instance->resolve_content_blocks( $post_model, array( 'flat' => true ) );

        // There should return only the non-empty blocks
		$this->assertEquals( 3, count( $actual ) );
		$this->assertEquals( 'core/columns', $actual[0]['blockName'] );
    }

	public function test_resolve_content_blocks_filters_empty_blocks() {
		$post_model = new Post( get_post( $this->post_id ) );
		$actual     = $this->instance->resolve_content_blocks( $post_model, array( 'flat' => true ) );
		// There should return only the non-empty blocks
		$this->assertEquals( 6, count( $actual ) );
		$this->assertEquals( 'core/columns', $actual[0]['blockName'] );
	}

	public function test_resolve_content_blocks_resolves_classic_blocks() {
		$post_model = new Post( get_post( $this->post_id ) );
		$actual     = $this->instance->resolve_content_blocks( $post_model, array( 'flat' => true ) );

		$this->assertEquals( 'core/freeform', $actual[5]['blockName'] );
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
		$this->assertEquals( 4, count( $parsed_blocks ) );
		$this->assertEquals( $allowed, $actual_block_names  );
	}
}
