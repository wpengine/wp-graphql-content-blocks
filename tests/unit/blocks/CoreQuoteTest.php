<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreQuoteTest extends PluginTestCase {
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
                        <!-- wp:quote -->
                        <blockquote class="wp-block-quote"><!-- wp:paragraph -->
                        <p>To be, or not to be, that is the question</p>
                        <!-- /wp:paragraph --><cite>WILLIAM SHAKESPEARE</cite></blockquote>
                        <!-- /wp:quote -->
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

    public function test_retrieve_core_quote_attributes() {
		$query  = '  
          fragment CoreQuoteBlockFragment on CoreQuote {
			attributes {
              citation
              value
            }
		  }
		  
		  query GetPosts {
			posts(first: 1) {
			  nodes {
				databaseId
				editorBlocks {
				  name
                  ...CoreQuoteBlockFragment
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
		$this->assertEquals( count( $node['editorBlocks'] ), 2 );
        $this->assertEquals( $node['editorBlocks'][0]['name'], 'core/quote' );
		$this->assertEquals( $node['editorBlocks'][0]['attributes'], [
            'citation' => 'WILLIAM SHAKESPEARE',
            'value' => '<p>To be, or not to be, that is the question</p>',
		]);
	}
}
