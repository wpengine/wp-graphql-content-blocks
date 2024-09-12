<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class BlockAttributesObjectTest extends PluginTestCase {
	public $instance;
	public $post_id;

	public function setUp(): void {
		parent::setUp();

		global $wpdb;

		$this->post_id = wp_insert_post(
			[
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
			]
		);
	}

	public function tearDown(): void {
		// your tear down methods here
		parent::tearDown();

		wp_delete_post( $this->post_id, true );
	}

	public function test_resolve_attribute_object_type() {
		$query = '
		{
			posts(first: 1) {
				nodes {
					databaseId
					editorBlocks(flat: true) {
						...on CoreParagraph {
							attributes {
								style
							}
						}
					}
				}
			}
		}
		';

		$actual = graphql( [ 'query' => $query ] );
		$node   = $actual['data']['posts']['nodes'][0];

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'] );
		// There should be only 1 block
		$this->assertEquals( count( $node['editorBlocks'] ), 1 );
		// There should be a style attribute that matches the json for the content of the attribute
		$this->assertEquals( $node['editorBlocks'][0]['attributes']['style'], '{"color":{"background":"#a62929"}}' );
	}
}
