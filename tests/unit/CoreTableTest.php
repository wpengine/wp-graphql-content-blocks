<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreTableTest extends PluginTestCase {
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
						<!-- wp:table {"hasFixedLayout":true} -->
						<figure class="wp-block-table"><table class="has-fixed-layout">
						<thead><tr><th>Header 1</th><th>Header 2</th></tr></thead>
						<tbody><tr><td></td><td></td></tr><tr><td></td><td></td></tr></tbody><tfoot><tr><td>Footer 1</td><td>Footer 2</td></tr></tfoot></table>
						<figcaption class="wp-element-caption">Caption</figcaption></figure>
						<!-- /wp:table -->
						'
					)
				),
				'post_status'  => 'publish',
			)
		);
	}

	public function tearDown(): void {
		parent::tearDown();
		wp_delete_post( $this->post_id, true );
	}

	public function test_retrieve_core_table_attribute_fields() {
		$query  = '
		  query GetPosts {
			posts(first: 1) {
			  nodes {
				editorBlocks(flat: true) {
				  name
				  ...on CoreTable {
					attributes {
						caption
						align
						anchor
					}
				  }
				}
			  }
			}
		  }
		';
		$actual = graphql( array( 'query' => $query ) );
		print_r($actual);
		$node   = $actual['data']['posts']['nodes'][0];
		$this->assertEquals( $node['editorBlocks'][0]['name'], 'core/table' );
		// There should be only one block using that query when not using flat: true
		$this->assertEquals( count( $node['editorBlocks'] ), 1 );
		$this->assertEquals( $node['editorBlocks'][0]['attributes'], [
			'caption' => "Caption",
			'align' => null,
			'anchor' => null
		]);
	}
}
