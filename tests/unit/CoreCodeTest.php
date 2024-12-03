<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreCodeTest extends PluginTestCase {
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
				'post_title'   => 'Post with Code Block',
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
			fragment CoreCodeBlockFragment on CoreCode {
				attributes {
					anchor
					backgroundColor
					borderColor
					className
					content
					cssClassName
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
						type
						...CoreCodeBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test the retrieval of core/code block attributes.
	 *
	 * Attributes covered:
	 * - content
	 * - cssClassName
	 * - backgroundColor
	 * - textColor
	 * - fontSize
	 * - fontFamily
	 *
	 * @return void
	 */
	public function test_retrieve_core_code_attributes() {
		$block_content = '
			<!-- wp:code {"backgroundColor":"pale-cyan-blue","textColor":"vivid-red","fontSize":"large","fontFamily":"monospace"} -->
			<pre class="wp-block-code has-vivid-red-color has-pale-cyan-blue-background-color has-text-color has-background has-large-font-size has-monospace-font-family"><code>function hello_world() {
				console.log("Hello, World!");
			}</code></pre>
			<!-- /wp:code -->
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
		$this->assertEquals( 'core/code', $block['name'], 'The block name should be core/code' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertEquals( 'CoreCode', $block['type'], 'The block type should be CoreCode' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => 'pale-cyan-blue', // previously untested
				'borderColor'     => null,
				'className'       => null,
				'content'         => "function hello_world() {\n\t\t\t\tconsole.log(\"Hello, World!\");\n\t\t\t}", // previously untested
				'cssClassName'    => 'wp-block-code has-vivid-red-color has-pale-cyan-blue-background-color has-text-color has-background has-large-font-size has-monospace-font-family', // previously untested
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
	 * Test retrieval of core/code block with custom styles and border color.
	 *
	 * Attributes covered:
	 * - borderColor
	 * - style
	 * - className
	 *
	 * @return void
	 */
	public function test_retrieve_core_code_with_custom_styles() {
		$block_content = '
			<!-- wp:code {"borderColor":"vivid-cyan-blue","className":"custom-class","align":"wide","style":{"border":{"width":"2px"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
			<pre class="wp-block-code alignwide custom-class has-border-color has-vivid-cyan-blue-border-color" style="border-width:2px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><code>const greeting = "Hello, styled code!";</code></pre>
			<!-- /wp:code -->
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

		$this->assertEquals( 'core/code', $block['name'], 'The block name should be core/code' );
		$this->assertEquals( 'CoreCode', $block['type'], 'The block type should be CoreCode' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => null,
				'borderColor'     => 'vivid-cyan-blue', // previously untested
				'className'       => 'custom-class', // previously untested
				'content'         => 'const greeting = "Hello, styled code!";',
				'cssClassName'    => 'wp-block-code alignwide custom-class has-border-color has-vivid-cyan-blue-border-color',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => null,
				'style'           => wp_json_encode( // previously untested
					[
						'border'  => [
							'width' => '2px',
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
	 * Test retrieval of core/code block with gradient and additional attributes.
	 *
	 * Attributes covered:
	 * - gradient
	 * - anchor
	 * - lock
	 *
	 * @return void
	 */
	public function test_retrieve_core_code_with_gradient_and_additional_attributes() {
		$block_content = '
			<!-- wp:code {"anchor":"test-anchor","gradient":"vivid-cyan-blue-to-vivid-purple","lock":{"move":true,"remove":true},"metadata":{"someKey":"someValue"}} -->
			<pre id="test-anchor" class="wp-block-code has-vivid-cyan-blue-to-vivid-purple-gradient-background"><code>console.log("Gradient and locked code block");</code></pre>
			<!-- /wp:code -->
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

		$this->assertEquals( 'core/code', $block['name'], 'The block name should be core/code' );
		$this->assertEquals( 'CoreCode', $block['type'], 'The block type should be CoreCode' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'anchor'          => 'test-anchor', // previously untested
				'backgroundColor' => null,
				'borderColor'     => null,
				'className'       => null,
				'content'         => 'console.log("Gradient and locked code block");',
				'cssClassName'    => 'wp-block-code has-vivid-cyan-blue-to-vivid-purple-gradient-background',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => 'vivid-cyan-blue-to-vivid-purple', // previously untested
				'lock'            => '{"move":true,"remove":true}', // previously untested
				'style'           => null,
				'textColor'       => null,
			],
			$attributes
		);
	}

	/**
	 * Test the retrieval of the align attribute for core/code block.
	 */
	public function test_retrieve_core_code_align_attribute(): void {
		// The align attribute is only supported in WP 6.3+
		if ( ! is_wp_version_compatible( '6.3' ) ) {
			$this->markTestSkipped( 'This test requires WP 6.3 or higher.' );
		}

		$block_content = '
        <!-- wp:code {"align":"wide","anchor":"test-anchor","gradient":"vivid-cyan-blue-to-vivid-purple","lock":{"move":true,"remove":true}} -->
        <pre id="test-anchor" class="wp-block-code alignwide has-vivid-cyan-blue-to-vivid-purple-gradient-background"><code>function test() { return "aligned code"; }</code></pre>
        <!-- /wp:code>
        ';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$query = '
        query CoreCodeAlignTest($id: ID!) {
            post(id: $id, idType: DATABASE_ID) {
                editorBlocks {
                    ... on CoreCode {
                        attributes {
                            align
                            anchor
                            backgroundColor
                            borderColor
                            className
                            content
                            cssClassName
                            fontFamily
                            fontSize
                            gradient
                            lock
                            style
                            textColor
                        }
                    }
                }
            }
        }
        ';

		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The response should have a data key' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The data should have a post key' );
		$this->assertArrayHasKey( 'editorBlocks', $actual['data']['post'], 'The post should have editorBlocks' );
		$this->assertCount( 1, $actual['data']['post']['editorBlocks'], 'There should be one editor block' );

		$block      = $actual['data']['post']['editorBlocks'][0];
		$attributes = $block['attributes'];

		$this->assertEquals(
			[
				'align'           => 'wide', // previously untested
				'anchor'          => 'test-anchor',
				'backgroundColor' => null,
				'borderColor'     => null,
				'className'       => null,
				'content'         => 'function test() { return "aligned code"; }',
				'cssClassName'    => 'wp-block-code alignwide has-vivid-cyan-blue-to-vivid-purple-gradient-background',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => 'vivid-cyan-blue-to-vivid-purple',
				'lock'            => '{"move":true,"remove":true}',
				'style'           => null,
				'textColor'       => null,
			],
			$attributes
		);
	}
}
