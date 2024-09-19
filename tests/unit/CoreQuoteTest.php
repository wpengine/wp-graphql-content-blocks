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

		// Test the query.
		$actual = graphql( compact( 'query', 'variables' ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// There should be only one block using that query when not using flat: true
		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ), 'There should be only one block' );

		$block = $actual['data']['post']['editorBlocks'][0];

		// Verify the block data.
		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );
		$this->assertEmpty( $block['cssClassNames'], 'There should be no cssClassNames' );
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

	public function test_retrieve_core_quote_untested_attributes() {
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

		// Test the query.
		$actual = graphql( compact( 'query', 'variables' ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// Verify the block data.
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $actual['data']['post']['editorBlocks'][0]['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['clientId'], 'The clientId should be present' );

		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/quote', $actual['data']['post']['editorBlocks'][0]['name'], 'The block name should be core/quote' );
		$this->assertEmpty( $actual['data']['post']['editorBlocks'][0]['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['renderedHtml'], 'The renderedHtml should be present' );

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
				'lock'            => '{"move":true,"remove":true}',
				'style'           => '{"elements":{"heading":{"color":{"text":"var:preset|color|vivid-cyan-blue","background":"var:preset|color|cyan-bluish-gray"}}}}', // @todo : use wp_json_encode here.
				'textColor'       => 'vivid-red',
				'value'           => '',
			],
			$actual['data']['post']['editorBlocks'][0]['attributes'],
		);
	}
}
