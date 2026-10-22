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
		wp_delete_post( $this->post_id, true );
		delete_option( 'graphql_general_settings' );
		\WPGraphQL::clear_schema();

		parent::tearDown();
	}

	/**
	 * @covers Anchor->register schema fields
	 */
	public function test_register_anchor_schema_fields() {
		$block = \WP_Block_Type_Registry::get_instance()->get_registered( 'core/paragraph' );
		$this->instance::register( $block );

		// Verify BlockWithSupportsAnchor fields registration
		$query = '
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
		$this->assertEquals( $expected['fields'], $actual['data']['__type']['fields'] );
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
						type
						... on BlockWithSupportsAnchor {
							anchor
						}
					}
				}
			}
		}';
		$actual = graphql( [ 'query' => $query ] );
		$node   = $actual['data']['posts']['nodes'][0];

		$this->assertEquals( 4, count( $node['editorBlocks'] ) );
		$this->assertEquals( 'core/paragraph', $node['editorBlocks'][0]['name'] );
		$this->assertEquals( 'CoreParagraph', $node['editorBlocks'][0]['type'] );
		$this->assertEquals( 'example', $node['editorBlocks'][0]['anchor'] );

		$this->assertEquals( 'core/paragraph', $node['editorBlocks'][1]['name'] );
		$this->assertEquals( 'CoreParagraph', $node['editorBlocks'][1]['type'] );
		$this->assertNull( $node['editorBlocks'][1]['anchor'] );

		$this->assertEquals( 'core/group', $node['editorBlocks'][2]['name'] );
		$this->assertEquals( 'CoreGroup', $node['editorBlocks'][2]['type'] );
		$this->assertNull( $node['editorBlocks'][2]['anchor'] );

		$this->assertEquals( 'core/paragraph', $node['editorBlocks'][3]['name'] );
		$this->assertEquals( 'CoreParagraph', $node['editorBlocks'][3]['type'] );
		$this->assertEquals( 'example-inner', $node['editorBlocks'][3]['anchor'] );
	}

	/**
	 * Dynamic blocks (such as ACF blocks) save the anchor as an attribute and
	 * may not render it as an `id`.
	 *
	 * @see https://github.com/wpengine/wp-graphql-content-blocks/issues/268
	 */
	public function test_anchor_resolves_from_dynamic_block_attributes() {
		register_block_type(
			'test/dynamic-anchor',
			[
				'supports'        => [ 'anchor' => true ],
				'attributes'      => [ 'anchor' => [ 'type' => 'string' ] ],
				'render_callback' => static fn () => '<div class="no-id">Rendered without an id</div>',
			]
		);
		\WPGraphQL::clear_schema();

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => '<!-- wp:test/dynamic-anchor {"anchor":"dynamic-anchor"} /--><!-- wp:test/dynamic-anchor /-->',
			]
		);

		$query  = '
		query GetPost( $id: ID! ) {
			post( id: $id, idType: DATABASE_ID ) {
				editorBlocks {
					name
					... on BlockWithSupportsAnchor {
						anchor
					}
				}
			}
		}';
		$actual = graphql(
			[
				'query'     => $query,
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		unregister_block_type( 'test/dynamic-anchor' );

		$this->assertArrayNotHasKey( 'errors', $actual, json_encode( $actual ) );
		$blocks = $actual['data']['post']['editorBlocks'];

		$this->assertEquals( 'test/dynamic-anchor', $blocks[0]['name'] );
		$this->assertEquals( 'dynamic-anchor', $blocks[0]['anchor'] );
		$this->assertNull( $blocks[1]['anchor'] );
	}
}
