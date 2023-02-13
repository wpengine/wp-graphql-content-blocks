<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class UnknownBlockTest extends PluginTestCase
{
  public $instance;
  public $post_id;

  public function setUp(): void
  {
    parent::setUp();
    global $wpdb;

    $this->post_id = wp_insert_post([
      'post_title'   => 'Post Title',
      'post_content' => preg_replace('/\s+/', ' ', trim('
        <!-- wp:paragraph -->
        <p>Testing</p>
        <!-- /wp:paragraph -->
        
        <!-- wp:heading -->
        <h2>My Heading</h2>
        <!-- /wp:heading -->
        
        <!-- wp:testing -->
        <p>Testing</p>
        <!-- /wp:testing -->
      ')),
      'post_status'  => 'publish'
    ]);
  }

  public function tearDown(): void
  {
    // your tear down methods here
    parent::tearDown();
    wp_delete_post($this->post_id, true);
  }

  public function test_retrieve_content_blocks()
  {
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

    $actual = graphql(['query' => $query]);
    $node = $actual['data']['posts']['nodes'][0];

    // Verify that the ID of the first post matches the one we just created.
    $this->assertEquals($this->post_id, $node['databaseId']);

    // Verify 3 blocks are returned
    $this->assertEquals(count($node['contentBlocks']), 3);

    $this->assertEquals($node['contentBlocks'][0]['name'], 'core/paragraph');
    $this->assertEquals($node['contentBlocks'][1]['name'], 'core/heading');
    $this->assertEquals($node['contentBlocks'][2]['name'], 'UnknownBlock');
    $this->assertEquals($node['contentBlocks'][2]['renderedHtml'], '\r\n<p>Testing</p>\r\n');
  }
}
