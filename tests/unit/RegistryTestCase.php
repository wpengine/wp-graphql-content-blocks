<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Registry\Registry;

final class RegistryTestCase extends PluginTestCase {
	/**
	 * @var ?\WPGraphQL\ContentBlocks\Registry\Registry
	 */
	public $instance;

	public function setUp(): void {
		parent::setUp();

		$settings                                 = get_option( 'graphql_general_settings' );
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}
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

		$this->instance->init();

		// Verify the response contains what we put in cache
		$response           = graphql(
			[
				'query'     => $query,
				'variables' => [
					'name' => 'Post',
				],
			]
		);
		$contains_interface = [
			'name'        => 'NodeWithEditorBlocks',
			'description' => 'Node that has content blocks associated with it',
		];
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertTrue( in_array( $contains_interface, $response['data']['__type']['interfaces'] ) );
	}

	/**
	 * This test ensures that when disabling the block editor for post types then
	 * no additional interfaces are included in them.
	 */
	public function test_no_additional_interfaces_on_block_editor_disabled_block_types() {
		add_filter( 'use_block_editor_for_post_type', '__return_false' );
		$query = '
		query GetType($name:String!) {
			__type(name: $name) {
				interfaces {
					name
				}
			}
		}
		';

		$this->instance->init();

		// Verify the response contains what we put in cache
		$response     = graphql(
			[
				'query'     => $query,
				'variables' => [
					'name' => 'Post',
				],
			]
		);
		$not_included = [
			'name' => 'NodeWithEditorBlocks',
		];
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertNotContains( $not_included, $response['data']['__type']['interfaces'] );
		remove_filter( 'use_block_editor_for_post_type', '__return_false' );
	}

	/**
	 * This test ensures that the `register_interface_types()` method
	 * works as expected when the get_allowed_block_types is used
	 */
	public function test_add_block_fields_to_schema_with_get_allowed_block_types() {
		add_filter(
			'allowed_block_types_all',
			static function ( $allowed_blocks, $editor_context ) {
				if ( isset( $editor_context->post ) && $editor_context->post instanceof \WP_Post && 'post' === $editor_context->post->post_type ) {
					return [
						'core/image',
						'core/paragraph',
					];
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

		$this->instance->init();

		// Verify Post meta
		$response           = graphql(
			[
				'query'     => $query,
				'variables' => [
					'name' => 'Post',
				],
			]
		);
		$contains_interface = [
			'name'        => 'NodeWithPostEditorBlocks',
			'description' => 'Node that has Post content blocks associated with it',
		];
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertTrue( in_array( $contains_interface, $response['data']['__type']['interfaces'] ) );

		// Verify PostEditorBlock meta
		$response                = graphql(
			[
				'query'     => $query,
				'variables' => [
					'name' => 'PostEditorBlock',
				],
			]
		);
		$contains_interface      = [
			'name'        => 'EditorBlock',
			'description' => 'Blocks that can be edited to create content and layouts',
		];
		$contains_detail         = [
			'name'        => 'PostEditorBlock',
			'description' => '',
		];
		$contains_possible_types = [
			[
				'name' => 'CoreImage',
			],
			[
				'name' => 'CoreParagraph',
			],
		];

		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertTrue( in_array( $contains_interface, $response['data']['__type']['interfaces'] ) );
		$this->assertEquals( array_intersect_key( $contains_detail, $response['data']['__type'] ), $contains_detail );
		$this->assertEquals( $contains_possible_types, $response['data']['__type']['possibleTypes'] );

		// Verify NodeWithPostEditorBlocks meta
		$response           = graphql(
			[
				'query'     => $query,
				'variables' => [
					'name' => 'NodeWithPostEditorBlocks',
				],
			]
		);
		$contains_interface = [
			'name'        => 'NodeWithEditorBlocks',
			'description' => 'Node that has content blocks associated with it',
		];
		$contains_detail    = [
			'name'        => 'NodeWithPostEditorBlocks',
			'description' => 'Node that has post content blocks associated with it',
		];

		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertNotEmpty( $response['data']['__type']['interfaces'] );
		$this->assertTrue( in_array( $contains_interface, $response['data']['__type']['interfaces'] ) );
		$this->assertEquals( array_intersect_key( $contains_detail, $response['data']['__type'] ), $contains_detail );
	}
}
