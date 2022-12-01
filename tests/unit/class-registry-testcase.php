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
        $type_registry = new TypeRegistry();
        $type_registry->init();
        $this->instance = new Registry($type_registry, \WP_Block_Type_Registry::get_instance());
        $this->instance->OnInit();
    }


    public function test_load_registered_editor_blocks_callback()
    {
        global $wp_filter;
        $this->assertTrue(isset($wp_filter['graphql_register_types']->callbacks));
        $config = $this->instance->load_registered_editor_blocks(array());
        $this->assertEquals($config['registered_editor_blocks'], $this->instance->registered_blocks);
    }
}
