<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class BlockQueriesTest extends PluginTestCase {
	public $instance;
	public $post_id;

	public function setUp(): void {
		parent::setUp();

		global $wpdb;

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Post Title',
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

							<!-- wp:column -->
							<div class="wp-block-column"><!-- wp:paragraph -->
							<p>Example paragraph in Column 2</p>
							<!-- /wp:paragraph --></div>
							<!-- /wp:column --></div>
							<!-- /wp:columns -->
						'
					)
				),
				'post_status'  => 'publish',
			]
		);
	}

	public function tearDown(): void {
		// your tear down methods here
		parent::tearDown();

		wp_delete_post( $this->post_id, true );
	}

	public function test_retrieve_non_flatten_editor_blocks() {
		$query  = '
		{
			posts(first: 1) {
				nodes {
					databaseId
					editorBlocks(flat: false) {
						name
					}
				}
			}
		}
		';
		$actual = graphql( [ 'query' => $query ] );
		$node   = $actual['data']['posts']['nodes'][0];

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'] );

		// There should be only one block using that query when not using flat: true
		$this->assertEquals( 1, count( $node['editorBlocks'] ) );
		$this->assertEquals( 'core/columns', $node['editorBlocks'][0]['name'] );
	}

	public function test_retrieve_flatten_editor_blocks() {
		$query = '
		{
			posts(first: 1) {
				nodes {
					databaseId
					editorBlocks(flat: true) {
							name
							parentClientId
					}
				}
			}
		}
		';

		$actual = graphql( [ 'query' => $query ] );
		$node   = $actual['data']['posts']['nodes'][0];

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'] );

		// There should more than one block using that query when using flat: true
		$this->assertEquals( 5, count( $node['editorBlocks'] ) );

		$this->assertEquals( 'core/columns', $node['editorBlocks'][0]['name'] );
		$this->assertNull( $node['editorBlocks'][0]['parentClientId'] );

		$this->assertEquals( 'core/column', $node['editorBlocks'][1]['name'] );
		$this->assertNotNull( $node['editorBlocks'][1]['parentClientId'] );

		$this->assertEquals( 'core/paragraph', $node['editorBlocks'][2]['name'] );
		$this->assertNotNull( $node['editorBlocks'][2]['parentClientId'] );

		$this->assertEquals( 'core/column', $node['editorBlocks'][3]['name'] );
		$this->assertNotNull( $node['editorBlocks'][3]['parentClientId'] );

		$this->assertEquals( 'core/paragraph', $node['editorBlocks'][4]['name'] );
		$this->assertNotNull( $node['editorBlocks'][4]['parentClientId'] );
	}
}
