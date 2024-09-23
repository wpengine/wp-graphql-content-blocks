<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreHeadingTest extends PluginTestCase {
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
				'post_title'   => 'Post with Heading',
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

	/**
	 * Test the retrieval of core/heading block attributes.
	 *
	 * @return void
	 */
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
					]
				),
				'textAlign'       => 'center',
				'textColor'       => null,
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/heading block with colors and alignment.
	 *
	 * @return void
	 */
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

		$this->assertEquals(
			[
				'align'           => 'wide', // Previously untested
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => 'Colored Heading',
				'cssClassName'    => 'wp-block-heading has-text-align-right has-text-color has-background alignwide',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'level'           => 3.0,
				'lock'            => null,
				'placeholder'     => null,
				'style'           => wp_json_encode(
					[
						'color' => [
							'background' => '#cf2e2e',
							'text'       => '#ffffff',
						],
					]
				),
				'textAlign'       => 'right',
				'textColor'       => null,
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/heading block with background and text color.
	 *
	 * @return void
	 */
	public function test_retrieve_core_heading_with_background_text_color() {
		$block_content = '
		<!-- wp:heading {"textAlign":"right","level":3,"align":"wide","backgroundColor":"accent-4","textColor":"accent-3"} -->
		<h3 class="wp-block-heading alignwide has-text-align-right has-accent-3-color has-accent-4-background-color has-text-color has-background">Colored Heading</h3>
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

		$this->assertEquals(
			[
				'align'           => 'wide',
				'anchor'          => null,
				'backgroundColor' => 'accent-4', // Previously untested
				'className'       => null,
				'content'         => 'Colored Heading',
				'cssClassName'    => 'wp-block-heading alignwide has-text-align-right has-accent-3-color has-accent-4-background-color has-text-color has-background',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'level'           => 3.0,
				'lock'            => null,
				'placeholder'     => null,
				'style'           => null,
				'textAlign'       => 'right',
				'textColor'       => 'accent-3', // Previously untested
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/heading block with font and anchor.
	 *
	 * @return void
	 */
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

		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => 'custom-id', // Previously untested
				'backgroundColor' => null,
				'className'       => null,
				'content'         => 'Custom Font Heading',
				'cssClassName'    => 'wp-block-heading',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'level'           => 2.0,
				'lock'            => null,
				'placeholder'     => null,
				'style'           => wp_json_encode(
					[
						'typography' => [
							'fontFamily' => 'Arial', // Previously untested
							'fontSize'   => '32px', // Previously untested
						],
					]
				),
				'textAlign'       => null,
				'textColor'       => null,
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/heading block with className, font family and font size.
	 *
	 * @return void
	 */
	public function test_retrieve_core_heading_with_font_family_and_size() {
		$block_content = '
		<!-- wp:heading {"className":"is-style-default","backgroundColor":"accent","textColor":"contrast-2","fontSize":"xx-large","fontFamily":"system-sans-serif"} -->
<h2 class="wp-block-heading is-style-default has-contrast-2-color has-accent-background-color has-text-color has-background has-system-sans-serif-font-family has-xx-large-font-size">hurrah</h2>
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

		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => 'accent',
				'className'       => 'is-style-default', // Previously untested
				'content'         => 'hurrah',
				'cssClassName'    => 'wp-block-heading is-style-default has-contrast-2-color has-accent-background-color has-text-color has-background has-system-sans-serif-font-family has-xx-large-font-size',
				'fontFamily'      => 'system-sans-serif', // Previously untested
				'fontSize'        => 'xx-large', // Previously untested
				'gradient'        => null,
				'level'           => 2.0,
				'lock'            => null,
				'placeholder'     => null,
				'style'           => null,
				'textAlign'       => null,
				'textColor'       => 'contrast-2',
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/heading block with font size & gradient.
	 *
	 * @return void
	 */
	public function test_retrieve_core_heading_with_gradient() {
		$block_content = '
		<!-- wp:heading {"gradient":"gradient-3","fontSize":"medium"} -->
		<h2 class="wp-block-heading has-gradient-3-gradient-background has-background has-medium-font-size">hello</h2>
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

		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => 'hello',
				'cssClassName'    => 'wp-block-heading has-gradient-3-gradient-background has-background has-medium-font-size',
				'fontFamily'      => null,
				'fontSize'        => 'medium', // Previously untested
				'gradient'        => 'gradient-3', // Previously untested
				'level'           => 2.0,
				'lock'            => null,
				'placeholder'     => null,
				'style'           => null,
				'textAlign'       => null,
				'textColor'       => null,
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/heading block with lock attribute.
	 *
	 * @return void
	 */
	public function test_retrieve_core_heading_with_lock() {
		$block_content = '
        <!-- wp:heading {"lock":{"move":true,"remove":true},"level":2} -->
        <h2 class="wp-block-heading">Locked Heading</h2>
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

		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => 'Locked Heading',
				'cssClassName'    => 'wp-block-heading',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'level'           => 2.0,
				'lock'            => wp_json_encode(
					[
						'move'   => true,
						'remove' => true,
					]
				),
				'placeholder'     => null,
				'style'           => null,
				'textAlign'       => null,
				'textColor'       => null,
			],
			$attributes
		);
	}
}
