<?php

namespace WPGraphQL\ContentBlocks\Unit;

use \WPGraphQL\ContentBlocks\Type\InterfaceType\ContentBlockInterface;

final class ContentBlockInterfaceTest extends PluginTestCase
{
    public $instance;
    public function setUp(): void
    {
        parent::setUp();

        $settings                                 = get_option('graphql_general_settings');
        $settings['public_introspection_enabled'] = 'on';
        update_option('graphql_general_settings', $settings);

        $this->instance = new ContentBlockInterface();
    }

    public function tearDown(): void
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    /**
     * @covers ContentBlockInterface->get_block
     */
    public function test_get_block()
    {
        $block_exists = [
            'blockName' => 'core/paragraph'
        ];
        $block_does_not_exist = [
            'blockName' => 'core/block_does_not_exist'
        ];
        $block_has_wrong_type = [
            'blockName' => 'core/block_has_wrong_type'
        ];
        $context = \WPGraphQL::get_app_context();
        $context->config['registered_editor_blocks'] = \WP_Block_Type_Registry::get_instance()->get_all_registered();
        $context->config['registered_editor_blocks']['core/block_has_wrong_type'] = ['core/block_has_wrong_type'];

        $this->assertNull($this->instance->get_block($block_does_not_exist, $context));
        $this->assertNull($this->instance->get_block($block_has_wrong_type, $context));
        $this->assertNotNull($this->instance->get_block($block_exists, $context));
    }

    /**
     * @covers ContentBlockInterface->register_type
     */
    public function test_register_type()
    {
        $queryNodeWithContentBlocksMeta = '
		query NodeWithContentBlocksMeta {
            __type(name: "NodeWithContentBlocks") {
              fields {
                name
              }
            }
          }
		';

        // Verify NodeWithContentBlocks fields registration
        $response = graphql(['query' => $queryNodeWithContentBlocksMeta, 'variables' => [
            'name' => 'NodeWithContentBlocks',
        ]]);
        $expected =  [
            'fields' => [
                [
                    "name" => "contentBlocks"
                ]
            ]
        ];
        $this->assertArrayHasKey('data', $response, json_encode($response));
        $this->assertEquals($response['data']['__type'], $expected);

        $queryContentBlockMeta = '
		query ContentBlockMeta {
            __type(name: "ContentBlock") {
              fields {
                name
              }
            }
          }
		';

        // Verify ContentBlock fields registration
        $response = graphql(['query' => $queryContentBlockMeta, 'variables' => [
            'name' => 'ContentBlock',
        ]]);
        $expected =  [
            'fields' => [
                [
                    "name" => "apiVersion"
                ],
                [
                    "name" => "blockEditorCategoryName"
                ],
                [
                    "name" => "cssClassNames"
                ],
                [
                    "name" => "innerBlocks"
                ],
                [
                    "name" => "isDynamic"
                ],
                [
                    "name" => "name"
                ],
                [
                    "name" => "nodeId"
                ],
                [
                    "name" => "parentId"
                ],
                [
                    "name" => "renderedHtml"
                ]
            ]
        ];
        $this->assertArrayHasKey('data', $response, json_encode($response));
        $this->assertEquals($response['data']['__type'], $expected);
    }
}
