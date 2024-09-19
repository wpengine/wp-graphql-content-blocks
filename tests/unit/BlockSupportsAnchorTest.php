<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Field\BlockSupports\Anchor;

final class BlockSupportsAnchorTest extends PluginTestCase {
	public $instance;
	public $post_id;

	public function setUp(): void {
		parent::setUp();

		$settings                                 = get_option( 'graphql_general_settings', [] );
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );

		$this->post_id  = wp_insert_post(
			[
				'post_title'   => 'Post Title',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim(
						'
							<!-- wp:paragraph -->
							<p id="example">Example paragraph with Anchor</p>
							<!-- /wp:paragraph -->
							<!-- wp:paragraph -->
							<p>Example paragraph without Anchor</p>
							<!-- /wp:paragraph -->

							<!-- wp:group -->
							<div class="wp-block-group">
								<!-- wp:paragraph -->
								<p id="example-inner">Example inner block</p>
								<!-- /wp:paragraph -->
							<!-- /wp:group -->				
						'
					)
				),
				'post_status'  => 'publish',
			]
		);
		$this->instance = new Anchor();
		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		// your tear down methods here
		parent::tearDown();

		wp_delete_post( $this->post_id, true );
		\WPGraphQL::clear_schema();
	}

	/**
	 * @covers Anchor->register schema fields
	 */
	public function test_register_anchor_schema_fields() {
		$block = \WP_Block_Type_Registry::get_instance()->get_registered( 'core/paragraph' );
		$this->instance::register( $block );

		// Verify BlockWithSupportsAnchor fields registration
		$query    = '
		query BlockWithSupportsAnchorMeta {
			__type(name: "BlockWithSupportsAnchor") {
				fields {
					name
				}
				possibleTypes {
					name
				}
			}
		}
		';

		$actual   = graphql( [ 'query' => $query ] );
		$expected = [
			'fields'        => [
				[
					'name' => 'anchor',
				],
			],
			'possibleTypes' => [
				[
					'name' => 'CoreParagraph',
				],
				[
					'name' => 'CoreParagraphAttributes',
				],
			],
		];
		$this->assertArrayHasKey( 'data', $actual, json_encode( $actual ) );
		$this->assertEquals( $actual['data']['__type']['fields'], $expected['fields'] );
		$this->assertContains( $expected['possibleTypes'][0], $actual['data']['__type']['possibleTypes'] );
		$this->assertContains( $expected['possibleTypes'][1], $actual['data']['__type']['possibleTypes'] );
	}

	/**
	 * @covers Anchor->register querying for field data
	 */
	public function test_register_anchor_query_field() {
		$block = \WP_Block_Type_Registry::get_instance()->get_registered( 'core/paragraph' );
		$this->instance::register( $block );

		// Verify BlockWithSupportsAnchor returns data
		$query  = '
		{
			posts(first: 1) {
				nodes {
					editorBlocks {
						name
						... on BlockWithSupportsAnchor {
							anchor
						}
					}
				}
			}
		}';
		$actual = graphql( [ 'query' => $query ] );
		$node   = $actual['data']['posts']['nodes'][0];

		$this->assertEquals( count( $node['editorBlocks'] ), 4 );
		$this->assertEquals( $node['editorBlocks'][0]['name'], 'core/paragraph' );
		$this->assertEquals( $node['editorBlocks'][0]['anchor'], 'example' );

		$this->assertEquals( $node['editorBlocks'][1]['name'], 'core/paragraph' );
		$this->assertNull( $node['editorBlocks'][1]['anchor'] );

		$this->assertEquals( $node['editorBlocks'][2]['name'], 'core/group' );
		$this->assertNull( $node['editorBlocks'][2]['anchor'] );

		$this->assertEquals( $node['editorBlocks'][3]['name'], 'core/paragraph' );
		$this->assertEquals( $node['editorBlocks'][3]['anchor'], 'example-inner' );
	}
}
