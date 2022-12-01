<?php

namespace WPGraphQL\ContentBlocks\Unit;

use \WPGraphQL\ContentBlocks\Registry\Registry;
use \WPGraphQL\Registry\TypeRegistry;

final class RegistryTests extends PluginTestCase
{
    public $instance;

    public function setUp(): void
    {
        parent::setUp();

        $settings                                 = get_option('graphql_general_settings');
        $settings['public_introspection_enabled'] = 'on';
        update_option('graphql_general_settings', $settings);

        $type_registry = new TypeRegistry();
        $type_registry->init();
        $this->instance = new Registry($type_registry, \WP_Block_Type_Registry::get_instance());
    }

    /**
     * @covers Registry->load_registered_editor_blocks
     */
    public function test_load_registered_editor_blocks_callback()
    {
        $this->instance->OnInit();
        global $wp_filter;
        $this->assertTrue(isset($wp_filter['graphql_register_types']->callbacks));
        $config = $this->instance->load_registered_editor_blocks(array());
        $this->assertEquals($config['registered_editor_blocks'], $this->instance->registered_blocks);
    }

    /**
     * @covers Registry->get_supported_post_types
     */
    public function test_get_supported_post_types()
    {
        $this->instance->OnInit();
        $expected_post_types = [
            "Post", "Page"
        ];
        $this->assertEquals($this->instance->get_supported_post_types(), $expected_post_types);
    }

    /**
     * This test ensures that the `add_block_fields_to_schema()` method
     * works as expected.
     *
     */
    public function test_add_block_fields_to_schema()
    {
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
        $response = graphql(['query' => $query, 'variables' => [
            'name' => $post,
        ]]);
        $contains_interface =  [
            'name' => "NodeWithContentBlocks",
            'description' => "Node that has content blocks associated with it"
        ];
        $this->assertArrayHasKey('data', $response, json_encode($response));
        $this->assertNotEmpty($response['data']['__type']['interfaces']);
        $this->assertTrue(in_array($contains_interface, $response['data']['__type']['interfaces']));
    }
}
