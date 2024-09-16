<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Utilities\WPHelpers;

final class WPHelpersTest extends PluginTestCase {
	public function setUp(): void {
		parent::setUp();

		register_post_type(
			'faq',
			[
				'show_in_graphql'     => true,
				'graphql_single_name' => 'faq',
				'graphql_plural_name' => 'faqs',
				'supports'            => [ 'title', 'author', 'thumbnail' ],
				'public'              => true,
			]
		);
		register_post_type(
			'blocks_enabled',
			[
				'show_in_graphql'     => true,
				'graphql_single_name' => 'blocksEnabled',
				'graphql_plural_name' => 'blocksEnabled',
				'supports'            => [ 'title', 'editor', 'author', 'thumbnail' ],
				'public'              => true,
				'show_in_rest'        => true,
			]
		);

		register_post_type(
			'blocks_disabled',
			[
				'show_in_graphql'     => true,
				'graphql_single_name' => 'blocksDisabled',
				'graphql_plural_name' => 'blocksDisabled',
				'supports'            => [ 'title', 'editor', 'author', 'thumbnail' ],
				'public'              => true,
				'show_in_rest'        => false,
			// post types that support the editor but do not show in rest will be prevented from using the Block editor
			]
		);

		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		unregister_post_type( 'faq' );
		unregister_post_type( 'blocks_enabled' );
		unregister_post_type( 'blocks_disabled' );
		\WPGraphQL::clear_schema();

		parent::tearDown();
	}

	/**
	 * @covers WPHelpers::get_supported_post_types
	 */
	public function test_get_supported_post_types() {
		$supported_post_types = WPHelpers::get_supported_post_types();

		$this->assertContains( get_post_type_object( 'post' ), $supported_post_types );
		$this->assertContains( get_post_type_object( 'page' ), $supported_post_types );
		$this->assertContains( get_post_type_object( 'blocks_enabled' ), $supported_post_types );
		$this->assertNotContains( get_post_type_object( 'blocks_disabled' ), $supported_post_types );
	}
}
