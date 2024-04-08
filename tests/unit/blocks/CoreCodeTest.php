<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreCodeTest extends PluginTestCase {
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
                        <!-- wp:code -->
                        <pre class="wp-block-code"><code># Adding two nums
                        sum = num1 + num2</code></pre>
                        <!-- /wp:code -->
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

    public function test_retrieve_core_code_attributes() {
		$query  = '  
          fragment CoreCodeBlockFragment on CoreCode {
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
                  ...CoreCodeBlockFragment
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
		$this->assertEquals( count( $node['editorBlocks'] ), 1 );
        $this->assertEquals( $node['editorBlocks'][0]['name'], 'core/code' );
		$this->assertEquals( $node['editorBlocks'][0]['attributes'], [
            'content' => '# Adding two nums sum = num1 + num2',
		]);
	}
}
