<?php

namespace WPGraphQL\ContentBlocks\Unit;

use \WPGraphQL\ContentBlocks\Type\InterfaceType\PostTypeBlockInterface;

final class PostTypeBlockInterfaceTest extends PluginTestCase {
	public $instance;
	public function setUp(): void {
		parent::setUp();

		$settings                                 = get_option( 'graphql_general_settings' );
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );

		$this->instance = new PostTypeBlockInterface();
		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		// your tear down methods here
		\WPGraphQL::clear_schema();
		parent::tearDown();
	}

	/**
	 * @covers PostTypeBlockInterface->register_type
	 */
	public function test_register_type() {
		$this->instance::register_type( 'post', array(), \WPGraphQL::get_type_registry() );

		// Verify NodeWithPostEditorBlocks fields registration
		$queryNodeWithPostEditorBlocksMeta = '
		query NodeWithPostEditorBlocksMeta {
            __type(name: "NodeWithPostEditorBlocks") {
              fields {
                name
              }
            }
          }
		';
		$response                          = graphql(
			array(
				'query'     => $queryNodeWithPostEditorBlocksMeta,
				'variables' => array(
					'name' => 'NodeWithPostEditorBlocks',
				),
			)
		);
		$expected                          = array(
			'fields' => array(
				array(
					'name' => 'editorBlocks',
				),
			),
		);
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertEquals( $response['data']['__type'], $expected );

		// Verify PostEditorBlock fields registration
		$queryContentBlockMeta = '
		query ContentBlockMeta {
		    __type(name: "PostEditorBlock") {
		        fields {
		            name
		        }
		    }
		}
		';

		// Verify ContentBlock fields registration
		$response = graphql(
			array(
				'query'     => $queryContentBlockMeta,
				'variables' => array(
					'name' => 'PostEditorBlock',
				),
			)
		);
		$expected = array(
			'apiVersion',
			'blockEditorCategoryName',
			'cssClassNames',
			'innerBlocks',
			'isDynamic',
			'name',
			'clientId',
			'parentClientId',
			'renderedHtml',
		);
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$actual = array_map(
			function ( $val ) {
				return $val['name'];
			},
			$response['data']['__type']['fields']
		);
		sort( $actual );
		sort( $expected );
		$this->assertEquals( $actual, $expected );
	}
}
