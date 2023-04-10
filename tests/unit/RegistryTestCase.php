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

		\WPGraphQL::clear_schema();
		$type_registry  = \WPGraphQL::get_type_registry();
		$this->instance = new Registry( $type_registry, \WP_Block_Type_Registry::get_instance() );
	}

	public function tearDown(): void {
		// your tear down methods here
		\WPGraphQL::clear_schema();
		parent::tearDown();
	}

	/**
	 * This test ensures that the `register_interface_types()` method
	 * works as expected when no get_allowed_block_types is used
	 */
	public function test_add_block_fields_to_schema_no_get_allowed_block_types() {
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
					'name' => 'Post',
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

	/**
	 * This test ensures that the `register_interface_types()` method
	 * works as expected when the get_allowed_block_types is used
	 */
	public function test_add_block_fields_to_schema_with_get_allowed_block_types() {
		add_filter(
			'allowed_block_types_all',
			function ( $allowed_blocks, $editor_context ) {
				if ( isset( $editor_context->post ) && $editor_context->post instanceof \WP_Post && 'post' === $editor_context->post->post_type ) {
					return array(
						'core/image',
						'core/paragraph',
					);
				}
				return $allowed_blocks;
			},
			10,
			2
		);

		$query = '
		query GetType($name:String!) {
			__type(name: $name) {
				name
				description
				interfaces {
					name
                    description
				}
				possibleTypes {
					name
				}
			}
		}
		';

		$this->instance->OnInit();

		// Verify Post meta
		$response           = graphql(
			array(
				'query'     => $query,
				'variables' => array(
					'name' => 'Post',
				),
			)
		);
		$contains_interface = array(
			'name'        => 'NodeWithPostEditorBlocks',
			'description' => 'Node that has post content blocks associated with it',
		);

		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertTrue( in_array( $contains_interface, $response['data']['__type']['interfaces'] ) );

		// Verify PostEditorBlock meta
		$response                = graphql(
			array(
				'query'     => $query,
				'variables' => array(
					'name' => 'PostEditorBlock',
				),
			)
		);
		$contains_interface      = array(
			'name'        => 'EditorBlock',
			'description' => 'Blocks that can be edited to create content and layouts',
		);
		$contains_detail         = array(
			'name'        => 'PostEditorBlock',
			'description' => '',
		);
		$contains_possible_types = array(
			array(
				'name' => 'CoreImage',
			),
			array(
				'name' => 'CoreParagraph',
			),
		);

		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertTrue( in_array( $contains_interface, $response['data']['__type']['interfaces'] ) );
		$this->assertEquals( array_intersect_key( $contains_detail, $response['data']['__type'] ), $contains_detail );
		$this->assertEquals( $contains_possible_types, $response['data']['__type']['possibleTypes'] );

		// Verify NodeWithPostEditorBlocks meta
		$response           = graphql(
			array(
				'query'     => $query,
				'variables' => array(
					'name' => 'NodeWithPostEditorBlocks',
				),
			)
		);
		$contains_interface = array(
			'name'        => 'NodeWithEditorBlocks',
			'description' => 'Node that has content blocks associated with it',
		);
		$contains_detail    = array(
			'name'        => 'NodeWithPostEditorBlocks',
			'description' => 'Node that has post content blocks associated with it',
		);

		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertTrue( in_array( $contains_interface, $response['data']['__type']['interfaces'] ) );
		$this->assertEquals( array_intersect_key( $contains_detail, $response['data']['__type'] ), $contains_detail );
	}
}
