<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class BlockScalarTest extends PluginTestCase {
	public $instance;
	public $post_id;

	public function setUp(): void {
		parent::setUp();
		global $wpdb;

		$this->post_id = wp_insert_post(
			array(
				'post_title'   => 'Post Title',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim(
						'
				<!-- wp:paragraph {"style":{"color":{"background":"#a62929"}}} -->
				<p class="has-background" style="background-color:#a62929">Test</p>
				<!-- /wp:paragraph -->'
					)
				),
				'post_status'  => 'publish',
			)
		);
	}

	public function tearDown(): void {
		// your tear down methods here
		parent::tearDown();
		wp_delete_post( $this->post_id, true );
	}

	public function test_retrieve_flatten_content_blocks() {
		$query = '
		{
			posts(first: 1) {
				nodes {
					databaseId
						contentBlocks(flat: true) {
							name
							parentId
						}
				}
			}
		}
		';

		$actual = graphql( array( 'query' => $query ) );
		$node   = $actual['data']['posts']['nodes'][0];

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'] );

		// There should more than one block using that query when using flat: true
		$this->assertEquals( count( $node['contentBlocks'] ), 5 );

		$this->assertEquals( $node['contentBlocks'][0]['name'], 'core/columns' );
		$this->assertNull( $node['contentBlocks'][0]['parentId'] );

		$this->assertEquals( $node['contentBlocks'][1]['name'], 'core/column' );
		$this->assertNotNull( $node['contentBlocks'][1]['parentId'] );

		$this->assertEquals( $node['contentBlocks'][2]['name'], 'core/paragraph' );
		$this->assertNotNull( $node['contentBlocks'][2]['parentId'] );

		$this->assertEquals( $node['contentBlocks'][3]['name'], 'core/column' );
		$this->assertNotNull( $node['contentBlocks'][3]['parentId'] );

		$this->assertEquals( $node['contentBlocks'][4]['name'], 'core/paragraph' );
		$this->assertNotNull( $node['contentBlocks'][4]['parentId'] );
	}

}
