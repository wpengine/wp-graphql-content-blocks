<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreButtonTest extends PluginTestCase {
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
                        <!-- wp:buttons -->
                        <div class="wp-block-buttons"><!-- wp:button -->
                        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Button 1</a></div>
                        <!-- /wp:button -->

                        <!-- wp:button -->
                        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Button 2</a></div>
                        <!-- /wp:button --></div>
                        <!-- /wp:buttons -->
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

    public function test_retrieve_core_button_attributes() {
		$query  = '  
          fragment CoreButtonBlockFragment on CoreButton {
			attributes {
              text
            }
		  }
		  
		  query GetPosts {
			posts(first: 1) {
			  nodes {
				databaseId
				editorBlocks {
				  name
                  ...CoreButtonBlockFragment
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
        $this->assertEquals( $node['editorBlocks'][0]['name'], 'core/buttons' );
		$this->assertEquals( $node['editorBlocks'][1]['name'], 'core/button' );
        $this->assertEquals( $node['editorBlocks'][2]['name'], 'core/button' );

		$this->assertEquals( $node['editorBlocks'][1]['attributes'], [
            'text' => 'Button 1',
		]);
        $this->assertEquals( $node['editorBlocks'][2]['attributes'], [
            'text' => 'Button 2',
		]);
	}
}
