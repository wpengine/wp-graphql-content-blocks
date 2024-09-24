<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CorePreformattedTest extends PluginTestCase {
	/**
	 * The ID of the post created for the test.
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Post with Preformatted Block',
				'post_content' => '',
				'post_status'  => 'publish',
			]
		);

		\WPGraphQL::clear_schema();
	}

	/**
	 * Tear down the test environment.
	 */
	public function tearDown(): void {
		parent::tearDown();

		wp_delete_post( $this->post_id, true );

		\WPGraphQL::clear_schema();
	}

	/**
	 * Provide the GraphQL query for testing.
	 *
	 * @return string The GraphQL query.
	 */
	public function query(): string {
		return '
			fragment CorePreformattedBlockFragment on CorePreformatted {
				attributes {
					anchor
					backgroundColor
					className
					content
					fontFamily
					fontSize
					gradient
					lock
					# metadata
					style
					textColor
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
						... on BlockWithSupportsAnchor {
							anchor
						}
						...CorePreformattedBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test the retrieval of core/preformatted block attributes.
	 *
	 * Attributes covered:
	 * - content
	 * - backgroundColor
	 * - textColor
	 * - fontSize
	 * - fontFamily
	 * - className
	 *
	 * @return void
	 */
	public function test_retrieve_core_preformatted_attributes() {
		$block_content = '
			<!-- wp:preformatted {"backgroundColor":"pale-cyan-blue","textColor":"vivid-red","fontSize":"large","fontFamily":"monospace","className":"custom-class"} -->
			<pre class="wp-block-preformatted has-vivid-red-color has-pale-cyan-blue-background-color has-text-color has-background has-large-font-size has-monospace-font-family custom-class">This is a
preformatted block
    with multiple lines
        and preserved spacing.</pre>
			<!-- /wp:preformatted -->
		';

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

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );

		$this->assertEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/preformatted', $block['name'], 'The block name should be core/preformatted' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => 'pale-cyan-blue', // previously untested
				'className'       => 'custom-class', // previously untested
				'content'         => "This is a\npreformatted block\n    with multiple lines\n        and preserved spacing.", // previously untested
				'fontFamily'      => 'monospace', // previously untested
				'fontSize'        => 'large', // previously untested
				'gradient'        => null,
				'lock'            => null,
				'style'           => null,
				'textColor'       => 'vivid-red', // previously untested
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/preformatted block with custom styles and gradient.
	 *
	 * Attributes covered:
	 * - gradient
	 * - style
	 * - anchor
	 *
	 * @return void
	 */
	public function test_retrieve_core_preformatted_with_custom_styles() {
		$block_content = '
			<!-- wp:preformatted {"anchor":"custom-anchor","gradient":"vivid-cyan-blue-to-vivid-purple","style":{"border":{"width":"2px","style":"dashed"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
			<pre id="custom-anchor" class="wp-block-preformatted has-vivid-cyan-blue-to-vivid-purple-gradient-background" style="border-style:dashed;border-width:2px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">This preformatted block
has a gradient background
and custom border style.</pre>
			<!-- /wp:preformatted -->
		';

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

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertEquals( 'core/preformatted', $block['name'], 'The block name should be core/preformatted' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'anchor'          => 'custom-anchor', // previously untested
				'backgroundColor' => null,
				'className'       => null,
				'content'         => "This preformatted block\nhas a gradient background\nand custom border style.",
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => 'vivid-cyan-blue-to-vivid-purple', // previously untested
				'lock'            => null,
				'style'           => wp_json_encode( // previously untested
					[
						'border'  => [
							'width' => '2px',
							'style' => 'dashed',
						],
						'spacing' => [
							'padding' => [
								'top'    => '20px',
								'right'  => '20px',
								'bottom' => '20px',
								'left'   => '20px',
							],
						],
					]
				),
				'textColor'       => null,
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/preformatted block with lock.
	 *
	 * Attributes covered:
	 * - lock
	 *
	 * @return void
	 */
	public function test_retrieve_core_preformatted_with_lock() {
		$block_content = '
			<!-- wp:preformatted {"lock":{"move":true,"remove":true},"metadata":{"key1":"value1","key2":"value2"}} -->
			<pre class="wp-block-preformatted">This is a locked preformatted block
with metadata.</pre>
			<!-- /wp:preformatted -->
		';

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

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertEquals( 'core/preformatted', $block['name'], 'The block name should be core/preformatted' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => "This is a locked preformatted block\nwith metadata.",
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => '{"move":true,"remove":true}', // previously untested
				'style'           => null,
				'textColor'       => null,
			],
			$attributes
		);
	}
}
