<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreImageTest extends PluginTestCase {
    public $instance;
	public $post_id;
	public $attachment_id;
    
    public function setUp(): void {
		parent::setUp();
		global $wpdb;
		$this->attachment_id = $this->factory->attachment->create_upload_object( WP_TEST_DATA_DIR . '/images/test-image.jpg' );

		$this->post_id = wp_insert_post(
			array(
				'post_title'   => 'Post Title',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim(
						'
                        <!-- wp:image {"width":500,"height":500,"sizeSlug":"full","linkDestination":"none", "id":' . $this->attachment_id . '} -->
                        <figure class="wp-block-image size-full is-resized"><img src="http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg" alt="" class="wp-image-1432" width="500" height="500"/></figure>
                        <!-- /wp:image -->
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

	public function test_retrieve_core_image_media_details() {
		$query  = '
		  fragment CoreImageBlockFragment on CoreImage {
			attributes {
			  id
			}
			mediaDetails {
			  height
			  width
			}
		  }
		  
		  query GetPosts {
			posts(first: 1) {
			  nodes {
				editorBlocks {
				  ...CoreImageBlockFragment
				}
			  }
			}
		  }
		';
		$actual = graphql( array( 'query' => $query ) );
		$node   = $actual['data']['posts']['nodes'][0];

		$this->assertEquals( $node['editorBlocks'][0]['mediaDetails'], [
			"width" => 50,
			"height" => 50,
		]);
	}


    public function test_retrieve_core_image_attributes() {
		$query  = '
		fragment CoreColumnBlockFragment on CoreColumn {
			attributes {
			  width
			}
		  }
		  
		  fragment CoreImageBlockFragment on CoreImage {
			attributes {
			  id
			  width
			  height
			  alt
			  src
			  style
			  sizeSlug
			  linkClass
			  linkTarget
			  linkDestination
			  align
			  caption
			  cssClassName
			}
		  }
		  
		  query GetPosts {
			posts(first: 1) {
			  nodes {
				databaseId
				editorBlocks {
				  name
				  ...CoreImageBlockFragment
				  ...CoreColumnBlockFragment
				}
			  }
			}
		  }
		';
		$actual = graphql( array( 'query' => $query ) );
		print_r($actual);
		$node   = $actual['data']['posts']['nodes'][0];
		
		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'] );
		// There should be only one block using that query when not using flat: true
		$this->assertEquals( count( $node['editorBlocks'] ), 1 );
		$this->assertEquals( $node['editorBlocks'][0]['name'], 'core/image' );

		$this->assertEquals( $node['editorBlocks'][0]['attributes'], [
			"width" => "500",
			"height" => 500.0,
			"alt" => "",
			"id" => $this->attachment_id,
			"src" => "http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg",
			"style" => NULL,
			"sizeSlug" => "full",
			"linkClass" => NULL,
			"linkTarget" => NULL,
			"linkDestination" => "none",
			"align" => NULL,
			"caption" => "",
			"cssClassName" => "wp-block-image size-full is-resized"   
		]); 
	}
}
