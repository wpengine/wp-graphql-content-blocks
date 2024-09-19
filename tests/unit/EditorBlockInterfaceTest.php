<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Type\InterfaceType\EditorBlockInterface;

final class EditorBlockInterfaceTest extends PluginTestCase {
	public function setUp(): void {
		parent::setUp();

		$settings                                 = get_option( 'graphql_general_settings', [] );
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );
	}

	public function tearDown(): void {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @covers EditorBlockInterface->get_block
	 */
	public function test_get_block() {
		$block_exists         = [
			'blockName' => 'core/paragraph',
		];
		$block_does_not_exist = [
			'blockName' => 'core/block_does_not_exist',
		];

		$this->assertNull( EditorBlockInterface::get_block( $block_does_not_exist ) );
		$this->assertNotNull( EditorBlockInterface::get_block( $block_exists ) );
	}

	/**
	 * @covers EditorBlockInterface->register_type
	 */
	public function test_register_type() {
		$queryNodeWithEditorBlocksMeta = '
			query NodeWithEditorBlocksMeta {
				__type(name: "NodeWithEditorBlocks") {
					fields {
						name
					}
				}
			}
		';

		// Verify NodeWithEditorBlocks fields registration
		$response = graphql(
			[
				'query'     => $queryNodeWithEditorBlocksMeta,
				'variables' => [
					'name' => 'NodeWithEditorBlocks',
				],
			]
		);
		$expected = [
			'fields' => [
				[
					'name' => 'editorBlocks',
				],
			],
		];
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$this->assertEquals( $response['data']['__type'], $expected );

		$queryContentBlockMeta = '
		query ContentBlockMeta {
				__type(name: "EditorBlock") {
					fields {
						name
					}
				}
			}
		';

		// Verify ContentBlock fields registration
		$response = graphql(
			[
				'query'     => $queryContentBlockMeta,
				'variables' => [
					'name' => 'EditorBlock',
				],
			]
		);
		$expected = [
			'apiVersion',
			'blockEditorCategoryName',
			'cssClassNames',
			'innerBlocks',
			'isDynamic',
			'name',
			'clientId',
			'parentClientId',
			'renderedHtml',
		];
		$this->assertArrayHasKey( 'data', $response, json_encode( $response ) );
		$actual = array_map(
			static function ( $val ) {
				return $val['name'];
			},
			$response['data']['__type']['fields']
		);
		sort( $actual );
		sort( $expected );
		$this->assertEquals( $actual, $expected );
	}
}
