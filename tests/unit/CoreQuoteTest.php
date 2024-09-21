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
				attributes {
					anchor
					backgroundColor
					citation
					className
					cssClassName
					fontFamily
					fontSize
					gradient
					# layout
					lock
					# metadata
					style
					# textAlign
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
	 * The following aspects are tested:
	 * - The absence of errors in the GraphQL response.
	 * - Presence of the 'data' and 'post' keys in the response.
	 * - Matching post ID.
	 * - Correct block name ('core/quote').
	 * - Correct retrieval of the block's attributes, especially 'citation', 'className', 'cssClassName' and 'value'.
	 *
	 * @return void
	 */
	public function test_retrieve_core_quote_fields_and_attributes() {
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

		// Verify the attributes.
		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => null,
				'citation'        => 'Author Name',
				'className'       => 'custom-quote-class',
				'cssClassName'    => 'wp-block-quote custom-quote-class',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => null,
				'style'           => null,
				'textColor'       => null,
				'value'           => '<p>This is a sample quote block content.</p>',
			],
			$block['attributes'],
		);
	}

	/**
	 * Test case for retrieving core quote block untested attributes.
	 *
	 * The following aspects are tested:
	 * - The absence of errors in the GraphQL response.
	 * - Presence of the 'data' and 'post' keys in the response.
	 * - Matching post ID.
	 * - Correct block name ('core/quote').
	 * - Correct retrieval of the block's attributes, especially:
	 *   - 'anchor'
	 *   - 'backgroundColor'
	 *   - 'fontFamily'
	 *   - 'fontSize'
	 *   - 'gradient'
	 *   - 'lock'
	 *   - 'style'
	 *   - 'textColor'
	 *
	 * @return void
	 */
	public function test_retrieve_core_quote_attributes() {
		$block_content = '
			<!-- wp:quote {"lock":{"move":true,"remove":true},"fontFamily":"body","fontSize":"small","backgroundColor":"pale-cyan-blue","style":{"elements":{"heading":{"color":{"text":"var:preset|color|vivid-cyan-blue","background":"var:preset|color|cyan-bluish-gray"}}}},"textColor":"vivid-red","gradient":"pale-ocean"} -->
			<blockquote class="wp-block-quote" id="test-anchor"><!-- wp:heading -->
			<h2 class="wp-block-heading">Quote, with heading color</h2>
			<!-- /wp:heading --><cite>Citation</cite></blockquote>
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
		$this->assertEquals( 'core/quote', $block['name'], 'The block name should be core/quote' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		$style = wp_json_encode(
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
		);

		// Verify the attributes.
		$this->assertEquals(
			[
				'anchor'          => 'test-anchor',
				'backgroundColor' => 'pale-cyan-blue',
				'citation'        => 'Citation',
				'className'       => null,
				'cssClassName'    => 'wp-block-quote',
				'fontFamily'      => 'body',
				'fontSize'        => 'small',
				'gradient'        => 'pale-ocean',
				'lock'            => wp_json_encode(
					[
						'move'   => true,
						'remove' => true,
					]
				),
				'style'           => $style,
				'textColor'       => 'vivid-red',
				'value'           => '',
			],
			$block['attributes'],
		);
	}
}
