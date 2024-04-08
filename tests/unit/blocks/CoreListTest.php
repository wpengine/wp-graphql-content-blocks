<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreListTest extends PluginTestCase {
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
                        <!-- wp:list -->
                        <ul><!-- wp:list-item -->
                        <li>List Item 1</li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li>List Item 2</li>
                        <!-- /wp:list-item --></ul>
                        <!-- /wp:list -->

                        <!-- wp:list {"ordered":true} -->
                        <ol><!-- wp:list-item -->
                        <li>List Item 4</li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li>List Item 5</li>
                        <!-- /wp:list-item --></ol>
                        <!-- /wp:list -->
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

    public function test_retrieve_core_list_attributes() {
		$query  = '  
          fragment CoreListItemBlockFragment on CoreListItem {
			attributes {
              content
            }
		  }
		  
		  query GetPosts {
			posts(first: 1) {
			  nodes {
				databaseId
				editorBlocks {
				  name
                  ...CoreListItemBlockFragment
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
		$this->assertEquals( count( $node['editorBlocks'] ), 6 );
        $this->assertEquals( $node['editorBlocks'][0]['name'], 'core/list' );
		$this->assertEquals( $node['editorBlocks'][1]['name'], 'core/list-item' );
        $this->assertEquals( $node['editorBlocks'][2]['name'], 'core/list-item' );
        $this->assertEquals( $node['editorBlocks'][3]['name'], 'core/list' );
        $this->assertEquals( $node['editorBlocks'][4]['name'], 'core/list-item' );
        $this->assertEquals( $node['editorBlocks'][5]['name'], 'core/list-item' );

		$this->assertEquals( $node['editorBlocks'][1]['attributes'], [
            'content' => 'List Item 1',
		]);
        $this->assertEquals( $node['editorBlocks'][2]['attributes'], [
            'content' => 'List Item 2',
		]);
        $this->assertEquals( $node['editorBlocks'][4]['attributes'], [
            'content' => 'List Item 4',
		]);
        $this->assertEquals( $node['editorBlocks'][5]['attributes'], [
            'content' => 'List Item 5',
		]);
	}
}
