<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WP_Block_Type_Registry;
use \WPGraphQL\ContentBlocks\Field\BlockSupports\Anchor;

final class BlockSupportsAnchorTest extends PluginTestCase {
	public $instance;
	public $post_id;
	public function setUp(): void {
		parent::setUp();
		global $wpdb;
		$settings                                 = get_option( 'graphql_general_settings' );
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );

		$this->post_id  = wp_insert_post(
			array(
				'post_title'   => 'Post Title',
				'post_content' => preg_replace(
					'/\s+/',
					' ',
					trim(
						'
                        <!-- wp:paragraph -->
                        <p id="example">Example paragraph with Anchor</p>
                        <!-- /wp:paragraph --></div>
                        <!-- wp:paragraph -->
                        <p>Example paragraph without Anchor</p>
                        <!-- /wp:paragraph --></div>
			        '
					)
				),
				'post_status'  => 'publish',
			)
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
		$queryBlockWithSupportsAnchor = '
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
		$response                     = graphql(
			array(
				'query' => $queryBlockWithSupportsAnchor,
			)
		);
		$expected                     = array(
			'fields'        => array(
				array(
					'name' => 'anchor',
				),
			),
			'possibleTypes' => array(
				array(
					'name' => 'CoreParagraph',
				),
				array(
					'name' => 'CoreParagraphAttributes',
				),
			),
		);
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertEquals( $response['data']['__type']['fields'], $expected['fields'] );
		$this->assertContains( $expected['possibleTypes'][0], $response['data']['__type']['possibleTypes'] );
		$this->assertContains( $expected['possibleTypes'][1], $response['data']['__type']['possibleTypes'] );
	}
}
