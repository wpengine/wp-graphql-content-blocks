<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreParagraphTest extends PluginTestCase {
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
				'post_title'   => 'Post with Paragraph',
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
			fragment CoreParagraphBlockFragment on CoreParagraph {
				attributes {
					align
					anchor
					backgroundColor
					className
					content
					cssClassName
					dropCap
					direction
					fontFamily
					fontSize
					gradient
					lock
					# metadata
					placeholder
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
						type
						... on BlockWithSupportsAnchor {
							anchor
						}
						...CoreParagraphBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test the retrieval of core/paragraph block attributes.
	 *
	 * Attributes covered:
	 * - align
	 * - backgroundColor
	 * - textColor
	 * - fontSize
	 * - fontFamily
	 * - content
	 * - cssClassName
	 *
	 * @return void
	 */
	public function test_retrieve_core_paragraph_attributes() {
		$block_content = '
			<!-- wp:paragraph {"align":"center","backgroundColor":"pale-cyan-blue","textColor":"vivid-red","fontSize":"large","fontFamily":"helvetica-arial"} -->
			<p class="has-text-align-center has-vivid-red-color has-pale-cyan-blue-background-color has-text-color has-background has-large-font-size has-helvetica-arial-font-family">This is a test paragraph with various attributes.</p>
			<!-- /wp:paragraph -->
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
		$this->assertEquals( 'core/paragraph', $block['name'], 'The block name should be core/paragraph' );
		$this->assertEquals( 'CoreParagraph', $block['type'], 'The block type should be CoreParagraph' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'align'           => 'center', // previously untested
				'anchor'          => null,
				'backgroundColor' => 'pale-cyan-blue', // previously untested
				'className'       => null,
				'content'         => 'This is a test paragraph with various attributes.', // previously untested
				'cssClassName'    => 'has-text-align-center has-vivid-red-color has-pale-cyan-blue-background-color has-text-color has-background has-large-font-size has-helvetica-arial-font-family', // previously untested
				'dropCap'         => false,
				'direction'       => null,
				'fontFamily'      => 'helvetica-arial', // previously untested
				'fontSize'        => 'large', // previously untested
				'gradient'        => null,
				'lock'            => null,
				'placeholder'     => null,
				'style'           => null,
				'textColor'       => 'vivid-red', // previously untested
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/paragraph block with drop cap and custom styles.
	 *
	 * Attributes covered:
	 * - dropCap
	 * - style (typography and spacing)
	 * - content
	 * - cssClassName
	 *
	 * @return void
	 */
	public function test_retrieve_core_paragraph_with_drop_cap_and_custom_styles() {
		$block_content = '
			<!-- wp:paragraph {"dropCap":true,"style":{"typography":{"lineHeight":"2","textTransform":"uppercase"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
			<p class="has-drop-cap" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px;line-height:2;text-transform:uppercase">This is a paragraph with drop cap and custom styles.</p>
			<!-- /wp:paragraph -->
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

		$this->assertEquals( 'core/paragraph', $block['name'], 'The block name should be core/paragraph' );
		$this->assertEquals( 'CoreParagraph', $block['type'], 'The block type should be CoreParagraph' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => 'This is a paragraph with drop cap and custom styles.',
				'cssClassName'    => 'has-drop-cap',
				'dropCap'         => true, // previously untested
				'direction'       => null,
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => null,
				'placeholder'     => null,
				'style'           => wp_json_encode( // previously untested
					[
						'typography' => [
							'lineHeight'    => '2',
							'textTransform' => 'uppercase',
						],
						'spacing'    => [
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
	 * Test retrieval of core/paragraph block with direction and gradient.
	 *
	 * - Attributes covered:
	 * - align
	 * - direction
	 * - gradient
	 * - content
	 * - cssClassName
	 *
	 * @return void
	 */
	public function test_retrieve_core_paragraph_with_direction_and_gradient() {
		$block_content = '
			<!-- wp:paragraph {"align":"right","direction":"rtl","gradient":"vivid-cyan-blue-to-vivid-purple"} -->
			<p class="has-text-align-right has-vivid-cyan-blue-to-vivid-purple-gradient-background" style="direction:rtl">This is a right-aligned RTL paragraph with a gradient background.</p>
			<!-- /wp:paragraph -->
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

		$this->assertEquals( 'core/paragraph', $block['name'], 'The block name should be core/paragraph' );
		$this->assertEquals( 'CoreParagraph', $block['type'], 'The block type should be CoreParagraph' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'align'           => 'right',
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => 'This is a right-aligned RTL paragraph with a gradient background.',
				'cssClassName'    => 'has-text-align-right has-vivid-cyan-blue-to-vivid-purple-gradient-background',
				'dropCap'         => false,
				'direction'       => 'rtl', // previously untested
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => 'vivid-cyan-blue-to-vivid-purple', // previously untested
				'lock'            => null,
				'placeholder'     => null,
				'style'           => null,
				'textColor'       => null,
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/paragraph block with additional attributes.
	 *
	 * Attributes covered:
	 * - anchor
	 * - className
	 * - lock
	 * - placeholder
	 *
	 * @return void
	 */
	public function test_retrieve_core_paragraph_with_additional_attributes() {
		$block_content = '
			<!-- wp:paragraph {"anchor":"test-anchor","className":"custom-class","lock":{"move":true,"remove":true},"metadata":{"someKey":"someValue"},"placeholder":"Type here..."} -->
			<p class="custom-class" id="test-anchor">This is a paragraph with additional attributes.</p>
			<!-- /wp:paragraph -->
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

		$this->assertEquals( 'core/paragraph', $block['name'], 'The block name should be core/paragraph' );
		$this->assertEquals( 'CoreParagraph', $block['type'], 'The block type should be CoreParagraph' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => 'test-anchor', // previously untested
				'backgroundColor' => null,
				'className'       => 'custom-class', // previously untested
				'content'         => 'This is a paragraph with additional attributes.',
				'cssClassName'    => 'custom-class',
				'dropCap'         => false,
				'direction'       => null,
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => '{"move":true,"remove":true}', // previously untested
				'placeholder'     => 'Type here...', // previously untested
				'style'           => null,
				'textColor'       => null,
			],
			$attributes
		);
	}
}
