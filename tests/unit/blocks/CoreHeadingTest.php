<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreHeadingTest extends PluginTestCase {
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
                        <!-- wp:heading -->
                        <h2 class="wp-block-heading">Heading 1</h2>
                        <!-- /wp:heading -->

                        <!-- wp:heading {"level":3} -->
                        <h3 class="wp-block-heading">Heading 2</h3>
                        <!-- /wp:heading -->

                        <!-- wp:heading {"level":4} -->
                        <h4 class="wp-block-heading">Heading 3</h4>
                        <!-- /wp:heading -->
                        '
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

    public function test_retrieve_core_heading_attributes() {
		$query  = '  
          fragment CoreHeadingBlockFragment on CoreHeading {
			attributes {
              level
              content
            }
		  }
		  
		  query GetPosts {
			posts(first: 1) {
			  nodes {
				databaseId
				editorBlocks {
				  name
                  ...CoreHeadingBlockFragment
				}
			  }
			}
		  }
		';
		$actual = graphql( array( 'query' => $query ) );
		$node   = $actual['data']['posts']['nodes'][0];
		
		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'] );
		// There should be only one block using that query when not using flat: true
		$this->assertEquals( count( $node['editorBlocks'] ), 3 );
        $this->assertEquals( $node['editorBlocks'][0]['name'], 'core/heading' );
        $this->assertEquals( $node['editorBlocks'][1]['name'], 'core/heading' );
        $this->assertEquals( $node['editorBlocks'][2]['name'], 'core/heading' );
		$this->assertEquals( $node['editorBlocks'][0]['attributes'], [
            'level'   => 2,
            'content' => 'Heading 1',
		]);
        $this->assertEquals( $node['editorBlocks'][1]['attributes'], [
            'level'   => 3,
            'content' => 'Heading 2',
		]);
        $this->assertEquals( $node['editorBlocks'][2]['attributes'], [
            'level'   => 4,
            'content' => 'Heading 3',
		]);
	}
}
