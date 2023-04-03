<?php

namespace WPGraphQL\ContentBlocks\Unit;

use \WPGraphQL\ContentBlocks\Registry\Registry;

final class RegistryTest extends PluginTestCase {

	public $instance;

	public function setUp(): void {
		parent::setUp();

		$settings                                 = get_option( 'graphql_general_settings' );
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );

		$type_registry  = \WPGraphQL::get_type_registry();
		$this->instance = new Registry( $type_registry, \WP_Block_Type_Registry::get_instance() );
	}

	/**
	 * This test ensures that the `add_block_fields_to_schema()` method
	 * works as expected.
	 */
	public function test_add_block_fields_to_schema() {
		$post = 'Post';

		$query = '
		query GetType($name:String!) {
			__type(name: $name) {
				interfaces {
					name
                    description
				}
			}
		}
		';

		$this->instance->OnInit();

		// Verify the response contains what we put in cache
		$response           = graphql(
			array(
				'query'     => $query,
				'variables' => array(
					'name' => $post,
				),
			)
		);
		$contains_interface = array(
			'name'        => 'NodeWithEditorBlocks',
			'description' => 'Node that has content blocks associated with it',
		);
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertTrue( in_array( $contains_interface, $response['data']['__type']['interfaces'] ) );
	}
}
