<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreHeadingTest extends PluginTestCase {
	public $post_id;

	public function setUp(): void {
		parent::setUp();

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Post with Heading',
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
			fragment CoreHeadingBlockFragment on CoreHeading {
				attributes {
					align
					anchor
					backgroundColor
					className
					content
					cssClassName
					fontFamily
					fontSize
					gradient
					level
					lock
					# metadata
					placeholder
					style
					textAlign
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
						...CoreHeadingBlockFragment
					}
				}
			}
		';
	}

	public function test_retrieve_core_heading_attributes() {
		$block_content = '
			<!-- wp:heading {"level":2,"textAlign":"center","style":{"typography":{"fontSize":"28px","fontStyle":"normal","fontWeight":"700"}}} -->
			<h2 class="wp-block-heading has-text-align-center" style="font-size:28px;font-style:normal;font-weight:700">Sample Heading</h2>
			<!-- /wp:heading -->
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

		// @todo this is not working
		// $this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );

		$this->assertEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/heading', $block['name'], 'The block name should be core/heading' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		$attributes = $block['attributes'];
		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => 'Sample Heading',
				'cssClassName'    => 'wp-block-heading has-text-align-center',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'level'           => 2.0, // @todo this should be an integer
				'lock'            => null,
				'placeholder'     => null,
				'style'           => wp_json_encode(
					[
						'typography' => [
							'fontSize'   => '28px',
							'fontStyle'  => 'normal',
							'fontWeight' => '700',
						],
					],
				),
				'textAlign'       => 'center',
				'textColor'       => null,
			],
			$attributes,
		);
	}

	public function test_retrieve_core_heading_with_colors_and_alignment() {
		$block_content = '
			<!-- wp:heading {"level":3,"textAlign":"right","align":"wide","style":{"color":{"background":"#cf2e2e","text":"#ffffff"}}} -->
			<h3 class="wp-block-heading has-text-align-right has-text-color has-background alignwide" style="background-color:#cf2e2e;color:#ffffff">Colored Heading</h3>
			<!-- /wp:heading -->
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$actual = graphql(
			[
				'query'     => $this->query(),
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$block      = $actual['data']['post']['editorBlocks'][0];
		$attributes = $block['attributes'];

		$this->assertEquals( 'Colored Heading', $attributes['content'] );
		$this->assertEquals( 3.0, $attributes['level'] );
		$this->assertEquals( 'right', $attributes['textAlign'] );
		$this->assertEquals( 'wide', $attributes['align'] );

		$style = json_decode( $attributes['style'], true );
		$this->assertEquals( '#cf2e2e', $style['color']['background'] );
		$this->assertEquals( '#ffffff', $style['color']['text'] );
	}

	public function test_retrieve_core_heading_with_font_and_anchor() {
		$block_content = '
			<!-- wp:heading {"anchor":"custom-id","style":{"typography":{"fontFamily":"Arial","fontSize":"32px"}}} -->
			<h2 class="wp-block-heading" id="custom-id" style="font-family:Arial;font-size:32px">Custom Font Heading</h2>
			<!-- /wp:heading -->
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$actual = graphql(
			[
				'query'     => $this->query(),
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$block      = $actual['data']['post']['editorBlocks'][0];
		$attributes = $block['attributes'];

		$this->assertEquals( 'Custom Font Heading', $attributes['content'] );
		$this->assertEquals( 'custom-id', $attributes['anchor'] );

		$style = json_decode( $attributes['style'], true );
		$this->assertEquals( 'Arial', $style['typography']['fontFamily'] );
		$this->assertEquals( '32px', $style['typography']['fontSize'] );
	}

	public function test_retrieve_core_heading_with_gradient() {
		$block_content = '
			<!-- wp:heading {"style":{"color":{"gradient":"linear-gradient(135deg,rgb(6,147,227) 0%,rgb(155,81,224) 100%)"}}} -->
			<h2 class="wp-block-heading has-background" style="background:linear-gradient(135deg,rgb(6,147,227) 0%,rgb(155,81,224) 100%)">Gradient Heading</h2>
			<!-- /wp:heading -->
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$actual = graphql(
			[
				'query'     => $this->query(),
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$block      = $actual['data']['post']['editorBlocks'][0];
		$attributes = $block['attributes'];

		$this->assertEquals( 'Gradient Heading', $attributes['content'] );

		$style = json_decode( $attributes['style'], true );
		$this->assertEquals( 'linear-gradient(135deg,rgb(6,147,227) 0%,rgb(155,81,224) 100%)', $style['color']['gradient'] );
	}

	public function test_retrieve_core_heading_with_background_color() {
		$block_content = '
			<!-- wp:heading {"backgroundColor":"vivid-red-background-color"} -->
			<h2 class="wp-block-heading has-vivid-red-background-color has-background">Heading with Background Color</h2>
			<!-- /wp:heading>
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$actual = graphql(
			[
				'query'     => $this->query(),
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$block      = $actual['data']['post']['editorBlocks'][0];
		$attributes = $block['attributes'];

		$this->assertEquals( 'Heading with Background Color', $attributes['content'] );
		$this->assertEquals( 'vivid-red-background-color', $attributes['backgroundColor'] );
	}

	public function test_retrieve_core_heading_with_text_color() {
		$block_content = '
			<!-- wp:heading {"textColor":"vivid-red"} -->
			<h2 class="wp-block-heading has-vivid-red-color has-text-color">Heading with Text Color</h2>
			<!-- /wp:heading>
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$actual = graphql(
			[
				'query'     => $this->query(),
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$block      = $actual['data']['post']['editorBlocks'][0];
		$attributes = $block['attributes'];

		$this->assertEquals( 'Heading with Text Color', $attributes['content'] );
		$this->assertEquals( 'vivid-red', $attributes['textColor'] );
	}

	public function test_retrieve_core_heading_with_font_size() {
		$block_content = '
			<!-- wp:heading {"fontSize":"large"} -->
			<h2 class="wp-block-heading has-large-font-size">Heading with Font Size</h2>
			<!-- /wp:heading>
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$actual = graphql(
			[
				'query'     => $this->query(),
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$block      = $actual['data']['post']['editorBlocks'][0];
		$attributes = $block['attributes'];

		$this->assertEquals( 'Heading with Font Size', $attributes['content'] );
		$this->assertEquals( 'large', $attributes['fontSize'] );
	}

	public function test_retrieve_core_heading_with_class_name() {
		$block_content = '
			<!-- wp:heading {"className":"custom-class"} -->
			<h2 class="wp-block-heading custom-class">Heading with Custom Class</h2>
			<!-- /wp:heading>
		';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$actual = graphql(
			[
				'query'     => $this->query(),
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$block      = $actual['data']['post']['editorBlocks'][0];
		$attributes = $block['attributes'];

		$this->assertEquals( 'Heading with Custom Class', $attributes['content'] );
		$this->assertEquals( 'custom-class', $attributes['className'] );
	}
}
