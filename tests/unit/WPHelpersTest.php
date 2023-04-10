<?php

namespace WPGraphQL\ContentBlocks\Unit;

use \WPGraphQL\ContentBlocks\Utilities\WPHelpers;

final class WPHelpersTest extends PluginTestCase {
	public function setUp(): void {
		parent::setUp();
		register_post_type(
			'faq',
			array(
				'show_in_graphql'     => true,
				'graphql_single_name' => 'faq',
				'graphql_plural_name' => 'faqs',
				'supports'            => array( 'title', 'author', 'thumbnail' ),
				'public'              => true,
			)
		);
		register_post_type(
			'recipe',
			array(
				'show_in_graphql'     => true,
				'graphql_single_name' => 'recipe',
				'graphql_plural_name' => 'recipes',
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail' ),
				'public'              => true,
			)
		);
		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		unregister_post_type( 'faq' );
		unregister_post_type( 'recipe' );
		\WPGraphQL::clear_schema();
		parent::tearDown();
	}
	/**
	 * @covers WPHelpers::get_supported_post_types
	 */
	public function test_get_supported_post_types() {
		$expected_post_types = array(
			get_post_type_object( 'post' ),
			get_post_type_object( 'page' ),
			get_post_type_object( 'recipe' ),
		);
		$this->assertEquals( WPHelpers::get_supported_post_types(), $expected_post_types );
	}
}
