<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreQuoteTest extends PluginTestCase {
	/**
	 * The ID of the post created for the test.
	 *
	 * @var int
	 */
	public $post_id;

	public function setUp(): void {
		parent::setUp();

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Post with Quote',
				'post_content' => '',
				'post_status'  => 'publish',
			]
		);

		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		parent::tearDown();

		wp_delete_post( $this->post_id, true );

		\WPGraphQL::clear_schema();
	}

	public function query(): string {
		return '
			fragment CoreQuoteBlockFragment on CoreQuote {
				innerBlocks {
					... on CoreParagraph{
						attributes {
							content
						}
					}
				}
				attributes {
					anchor
					backgroundColor
					citation
					className
					cssClassName
					fontFamily
					fontSize
					gradient
					lock
					# metadata
					style
					textColor
					value
				}
			}

			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks {
						apiVersion
						blockEditorCategoryName
						clientId
						cssClassNames
						innerBlocks {
							name
						}
						isDynamic
						name
						parentClientId
						renderedHtml
						...CoreQuoteBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test case for retrieving core quote block fields and attributes.
	 *
	 * Covers : 'citation', 'className', 'cssClassName' and 'value'.
	 */
	public function test_retrieve_core_quote_fields_and_attributes(): void {
		$block_content = '
			<!-- wp:quote {"className":"custom-quote-class"} -->
			<blockquote class="wp-block-quote custom-quote-class"><p>This is a sample quote block content.</p><cite>Author Name</cite></blockquote>
			<!-- /wp:quote -->
		';

		// Update the post content with the block content.
		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$query     = $this->query();
		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		error_log( print_r( $actual, true ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ), 'There should be only one block' );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );

		$this->assertEquals( $block['cssClassNames'][0], 'custom-quote-class' );

		$this->assertEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/quote', $block['name'], 'The block name should be core/quote' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		// Verify the attributes..

		// WordPress 6.4+ adds layout styles, so `cssClassName` needs to be checked separately.
		$this->assertStringContainsString( 'wp-block-quote', $block['attributes']['cssClassName'] );
		$this->assertStringContainsString( 'custom-quote-class', $block['attributes']['cssClassName'] );
		unset( $block['attributes']['cssClassName'] );

		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => null,
				'citation'        => 'Author Name',
				'className'       => 'custom-quote-class',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => null,
				'style'           => null,
				'textColor'       => null,
				'value'           => '<p>This is a sample quote block content.</p>',
				// 'layout'          => null,
				// 'textAlign'       => null,
			],
			$block['attributes'],
		);
	}

	/**
	 * Test case for retrieving core quote block untested attributes.
	 *
	 * Covers : 'anchor', 'backgroundColor', 'fontFamily', 'fontSize', 'gradient', 'lock', 'style', 'textColor' and 'value'.
	 */
	public function test_retrieve_core_quote_additional_attributes(): void {
		$block_content = '
			<!-- wp:quote {"lock":{"move":true,"remove":true},"fontFamily":"body","fontSize":"small","backgroundColor":"pale-cyan-blue","style":{"elements":{"heading":{"color":{"text":"var:preset|color|vivid-cyan-blue","background":"var:preset|color|cyan-bluish-gray"}}}},"textColor":"vivid-red","gradient":"pale-ocean"} -->
			<blockquote class="wp-block-quote" id="test-anchor"><!-- wp:paragraph -->
			<p>Sample Quote</p>
			<!-- /wp:paragraph --><cite>Citation</cite></blockquote>
			<!-- /wp:quote -->
		';

		// Update the post content with the block content.
		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$query     = $this->query();
		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		error_log( print_r( $actual, true ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// Verify the block data.
		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );

		$this->assertNotEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 1, count( $block['innerBlocks'] ), 'There should be only one inner block' );
		$this->assertEquals( 'core/paragraph', $block['innerBlocks'][0]['name'], 'The inner block name should be core/paragraph' );

		$this->assertEquals( 'core/quote', $block['name'], 'The block name should be core/quote' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		unset( $block['attributes']['citation'] ); // Tested above.
		unset( $block['attributes']['className'] ); // Tested above.
		unset( $block['attributes']['cssClassName'] ); // Tested above.

		// Verify the attributes.
		$this->assertEquals(
			[
				'anchor'          => 'test-anchor', // Previously untested.
				'backgroundColor' => 'pale-cyan-blue', // Previously untested.
				'fontFamily'      => 'body', // Previously untested.
				'fontSize'        => 'small', // Previously untested.
				'gradient'        => 'pale-ocean', // Previously untested.
				'lock'            => wp_json_encode( // Previously untested.
					[
						'move'   => true,
						'remove' => true,
					]
				),
				'style'           => wp_json_encode( // Previously untested.
					[
						'elements' => [
							'heading' => [
								'color' => [
									'text'       => 'var:preset|color|vivid-cyan-blue',
									'background' => 'var:preset|color|cyan-bluish-gray',
								],
							],
						],
					]
				),
				'textColor'       => 'vivid-red', // Previously untested.
				'value'           => '<p>Sample Quote</p>', // Previously untested.
			],
			$block['attributes'],
		);
	}


	/**
	 * Test case for retrieving core quote block layout attribute.
	 */
	public function test_retrieve_core_quote_layout_attributes(): void {
		// layout are only supported in WP 6.5+.
		if ( ! is_wp_version_compatible( '6.5' ) ) {
			$this->markTestSkipped( 'This test requires WP 6.5 or higher.' );
		}
		$block_content = '
			<!-- wp:quote {"layout":{"type":"flex","flexWrap":"nowrap"},"textAlign":"center"} -->
			<blockquote class="wp-block-quote" id="test-anchor"><!-- wp:paragraph -->
			<p>Sample Quote</p>
			<!-- /wp:paragraph --><cite>Citation</cite></blockquote>
			<!-- /wp:quote -->
		';

		// Update the post content with the block content.
		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$query     = '
			fragment CoreQuoteBlockFragment on CoreQuote {
				innerBlocks {
					... on CoreParagraph{
						attributes {
							content
						}
					}
				}
				attributes {
					anchor
					backgroundColor
					citation
					className
					cssClassName
					fontFamily
					fontSize
					gradient
					layout
					lock
					# metadata
					style
					textColor
					value
				}
			}

			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks {
						apiVersion
						blockEditorCategoryName
						clientId
						cssClassNames
						innerBlocks {
							name
						}
						isDynamic
						name
						parentClientId
						renderedHtml
						...CoreQuoteBlockFragment
					}
				}
			}
		';
		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		error_log( print_r( $actual, true ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// Verify the block data.
		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );

		$this->assertNotEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 1, count( $block['innerBlocks'] ), 'There should be only one inner block' );
		$this->assertEquals( 'core/paragraph', $block['innerBlocks'][0]['name'], 'The inner block name should be core/paragraph' );

		$this->assertEquals( 'core/quote', $block['name'], 'The block name should be core/quote' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		unset( $block['attributes']['citation'] ); // Tested above.
		unset( $block['attributes']['className'] ); // Tested above.
		unset( $block['attributes']['cssClassName'] ); // Tested above.

		unset( $block['attributes']['anchor'] ); // Tested above.
		unset( $block['attributes']['backgroundColor'] ); // Tested above.
		unset( $block['attributes']['fontFamily'] ); // Tested above.
		unset( $block['attributes']['fontSize'] ); // Tested above.
		unset( $block['attributes']['gradient'] ); // Tested above.
		unset( $block['attributes']['lock'] ); // Tested above.
		unset( $block['attributes']['style'] ); // Tested above.
		unset( $block['attributes']['textColor'] ); // Tested above.


		// Verify the attributes.
		$this->assertEquals(
			[
				'value'           => '<p>Sample Quote</p>',
				'textAlign'       => 'center', // Previously untested.
			],
			$block['attributes'],
		);
	}


	/**
	 * Test case for retrieving core quote block 'textAlign' attribute.
	 */
	public function test_retrieve_core_quote_text_align_attributes(): void {
		// textAlign is only supported in WP 6.6+.
		if ( ! is_wp_version_compatible( '6.6' ) ) {
			$this->markTestSkipped( 'This test requires WP 6.6 or higher.' );
		}
		$block_content = '
			<!-- wp:quote {"layout":{"type":"flex","flexWrap":"nowrap"},"textAlign":"center"} -->
			<blockquote class="wp-block-quote" id="test-anchor"><!-- wp:paragraph -->
			<p>Sample Quote</p>
			<!-- /wp:paragraph --><cite>Citation</cite></blockquote>
			<!-- /wp:quote -->
		';

		// Update the post content with the block content.
		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$query     = '
			fragment CoreQuoteBlockFragment on CoreQuote {
				innerBlocks {
					... on CoreParagraph{
						attributes {
							content
						}
					}
				}
				attributes {
					anchor
					backgroundColor
					citation
					className
					cssClassName
					fontFamily
					fontSize
					gradient
					layout
					lock
					# metadata
					style
					textAlign
					textColor
					value
				}
			}

			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks {
						apiVersion
						blockEditorCategoryName
						clientId
						cssClassNames
						innerBlocks {
							name
						}
						isDynamic
						name
						parentClientId
						renderedHtml
						...CoreQuoteBlockFragment
					}
				}
			}
		';
		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		error_log( print_r( $actual, true ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// Verify the block data.
		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );

		$this->assertNotEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 1, count( $block['innerBlocks'] ), 'There should be only one inner block' );
		$this->assertEquals( 'core/paragraph', $block['innerBlocks'][0]['name'], 'The inner block name should be core/paragraph' );

		$this->assertEquals( 'core/quote', $block['name'], 'The block name should be core/quote' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		unset( $block['attributes']['citation'] ); // Tested above.
		unset( $block['attributes']['className'] ); // Tested above.
		unset( $block['attributes']['cssClassName'] ); // Tested above.

		unset( $block['attributes']['anchor'] ); // Tested above.
		unset( $block['attributes']['backgroundColor'] ); // Tested above.
		unset( $block['attributes']['fontFamily'] ); // Tested above.
		unset( $block['attributes']['fontSize'] ); // Tested above.
		unset( $block['attributes']['gradient'] ); // Tested above.
		unset( $block['attributes']['lock'] ); // Tested above.
		unset( $block['attributes']['style'] ); // Tested above.
		unset( $block['attributes']['textColor'] ); // Tested above.


		// Verify the attributes.
		$this->assertEquals(
			[
				'value'           => '<p>Sample Quote</p>',
				'layout'          => wp_json_encode( // Previously untested.
					[
						'type'     => 'flex',
						'flexWrap' => 'nowrap',
					]
				),
				'textAlign'       => 'center',
			],
			$block['attributes'],
		);
	}
}
