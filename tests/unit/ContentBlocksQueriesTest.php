<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class ContentBlocksQueriesTest extends PluginTestCase {
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
            <!-- /wp:columns -->'
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

	public function test_retrieve_non_flatten_content_blocks() {
		$query = '
		{
			posts(first: 1) {
				nodes {
					databaseId
                    contentBlocks {
                        name
                    }
				}
			}
		}
		';

		$actual = graphql( array( 'query' => $query ) );

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $actual['data']['posts']['nodes'][0]['databaseId'] );

		// There should be only one block using that query when not using flat: true
		$this->assertEquals( count( $actual['data']['posts']['nodes'][0]['contentBlocks'] ), 1 );

		$this->assertEquals( $actual['data']['posts']['nodes'][0]['contentBlocks'][0]['name'], 'core/columns' );
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

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $actual['data']['posts']['nodes'][0]['databaseId'] );

		// There should more than one block using that query when using using flat: true
		$this->assertEquals( count( $actual['data']['posts']['nodes'][0]['contentBlocks'] ), 5 );

		$this->assertEquals( $actual['data']['posts']['nodes'][0]['contentBlocks'][0]['name'], 'core/columns' );
		$this->assertNull( $actual['data']['posts']['nodes'][0]['contentBlocks'][0]['parentId'] );

		$this->assertEquals( $actual['data']['posts']['nodes'][0]['contentBlocks'][1]['name'], 'core/column' );
		$this->assertNotNull( $actual['data']['posts']['nodes'][0]['contentBlocks'][1]['parentId'] );

		$this->assertEquals( $actual['data']['posts']['nodes'][0]['contentBlocks'][2]['name'], 'core/paragraph' );
		$this->assertNotNull( $actual['data']['posts']['nodes'][0]['contentBlocks'][2]['parentId'] );

		$this->assertEquals( $actual['data']['posts']['nodes'][0]['contentBlocks'][3]['name'], 'core/column' );
		$this->assertNotNull( $actual['data']['posts']['nodes'][0]['contentBlocks'][3]['parentId'] );

		$this->assertEquals( $actual['data']['posts']['nodes'][0]['contentBlocks'][4]['name'], 'core/paragraph' );
		$this->assertNotNull( $actual['data']['posts']['nodes'][0]['contentBlocks'][4]['parentId'] );
	}

}
