<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreListTest extends PluginTestCase {
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
				'post_title'   => 'Post with List',
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
			fragment CoreListBlockFragment on CoreList {
				attributes {
					anchor
					backgroundColor
					className
					cssClassName
					fontFamily
					fontSize
					gradient
					lock
					# metadata
					ordered
					placeholder
					reversed
					start
					style
					textColor
					type
					values
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
						...CoreListBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test case for retrieving core list block fields and attributes.
	 *
	 * Covers : 'anchor', 'backgroundColor', 'className', and 'cssClassName'.
	 */
	public function test_retrieve_core_list_fields_and_attribute(): void {
		$block_content = '
			<!-- wp:list {"className":"test-css-class-name","backgroundColor":"accent-4"} -->
				<ul id="test-anchor" class="wp-block-list test-css-class-name has-accent-4-background-color has-background">
					<!-- wp:list-item --><li>Truck</li><!-- /wp:list-item -->
					<!-- wp:list-item --><li>Train</li><!-- /wp:list-item -->
				</ul>
			<!-- /wp:list -->
		';

		// Set post content.
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
		$this->assertEquals( 3, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];
		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' ); 
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' ); 
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' ); 
		$this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' ); 
		$this->assertNotEmpty( $block['innerBlocks'], 'There should be ListItem inner blocks' ); 
		$this->assertEquals( 'core/list', $block['name'], 'The block name should be core/list' ); 
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' ); 
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' ); 

		$this->assertEquals(
			[
				'anchor'          => 'test-anchor',
				'backgroundColor' => 'accent-4',
				'className'       => 'test-css-class-name',
				'cssClassName'    => 'wp-block-list test-css-class-name has-accent-4-background-color has-background',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => null,
				'ordered'         => false,
				'placeholder'     => null,
				'reversed'        => null,
				'start'           => null,
				'style'           => null,
				'textColor'       => null,
				'type'            => null,
				'values'          => '<li>Truck</li><li>Train</li>',
			],
			$block['attributes'],
		);
	}

	/**
	 * Test case for retrieving core list block fields and attributes.
	 *
	 * Covers : 'fontFamily', 'fontSize', 'gradient', and 'lock'.
	 */
	public function test_retrieve_core_list_attributes_typography_and_lock(): void {
		$block_content = '
			<!-- wp:list {"lock":{"move":true,"remove":true},"gradient":"gradient-4","fontSize":"large","fontFamily":"heading"} -->
				<ul class="wp-block-list has-gradient-4-gradient-background has-background has-heading-font-family has-large-font-size">
					<!-- wp:list-item --><li>Car</li><!-- /wp:list-item -->
					<!-- wp:list-item --><li>Caterpillar</li><!-- /wp:list-item -->
				</ul>
			<!-- /wp:list -->
		';

		// Set post content.
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

		$block = $actual['data']['post']['editorBlocks'][0];
		$this->assertEquals( 'core/list', $block['name'], 'The block name should be core/list' );

		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'cssClassName'    => 'wp-block-list has-gradient-4-gradient-background has-background has-heading-font-family has-large-font-size',
				'fontFamily'      => 'heading', // Previously untested.
				'fontSize'        => 'large', // Previously untested.
				'gradient'        => 'gradient-4', // Previously untested.
				'lock'            => '{"move":true,"remove":true}', // Previously untested.
				'ordered'         => false,
				'placeholder'     => null,
				'reversed'        => null,
				'start'           => null,
				'style'           => null,
				'textColor'       => null,
				'type'            => null,
				'values'          => '<li>Car</li><li>Caterpillar</li>',
			],
			$block['attributes'],
		);
	}

	/**
	 * Test case for retrieving core list block fields and attributes.
	 * 
	 * Covers : 'ordered' and 'reversed'.
	 *
	 * @todo : The 'placeholder' attribute is not tested as it is getting returned as null.
	 */
	public function test_retrieve_core_list_attributes_ordered_and_reversed(): void {
		$block_content = '
			<!-- wp:list {"ordered":true,"reversed":true} -->
				<ol reversed class="wp-block-list">
					<!-- wp:list-item --><li>Polo</li><!-- /wp:list-item -->
					<!-- wp:list-item --><li>Nano</li><!-- /wp:list-item -->
					<!-- wp:list-item --><li></li><!-- /wp:list-item -->
				</ol>
			<!-- /wp:list -->
		';

		// Set post content.
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
		$this->assertEquals( 4, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];
		$this->assertEquals( 'core/list', $block['name'], 'The block name should be core/list' );

		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'cssClassName'    => 'wp-block-list',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => null,
				'ordered'         => true, // Previously untested.
				'placeholder'     => null,
				'reversed'        => true, // Previously untested.
				'start'           => null,
				'style'           => null,
				'textColor'       => null,
				'type'            => null,
				'values'          => '<li>Polo</li><li>Nano</li><li>',
			],
			$block['attributes'],
		);
	}

	/**
	 * Test case for retrieving core list block fields and attributes.
	 *
	 * Covers : 'start', 'style', and 'textColor'.
	 */
	public function test_retrieve_core_list_attributes_start_and_styles(): void {
		$block_content = '
			<!-- wp:list {"ordered":true,"type":"upper-alpha","start":5,"className":"is-style-checkmark-list","style":{"typography":{"textTransform":"uppercase"},"spacing":{"margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"textColor":"accent-3"} -->
				<ol start="5" style="list-style-type:upper-alpha" class="wp-block-list is-style-checkmark-list has-accent-3-color has-text-color">
					<!-- wp:list-item --><li>Pizza</li><!-- /wp:list-item -->
					<!-- wp:list-item --><li>Pasta</li><!-- /wp:list-item -->
				</ol>
			<!-- /wp:list -->
		';

		// Set post content.
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
		$this->assertEquals( 3, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];
		$this->assertEquals( 'core/list', $block['name'], 'The block name should be core/list' );

		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => 'is-style-checkmark-list',
				'cssClassName'    => 'wp-block-list is-style-checkmark-list has-accent-3-color has-text-color',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => null,
				'ordered'         => true,
				'placeholder'     => null,
				'reversed'        => null,
				'start'           => 5.0, // Previously untested.
				'style'           => wp_json_encode(  // Previously untested.
					[
						'typography' => [
							'textTransform' => 'uppercase',
						],
						'spacing'    => [
							'margin' => [
								'top'    => 'var:preset|spacing|30',
								'bottom' => 'var:preset|spacing|30',
								'left'   => 'var:preset|spacing|30',
								'right'  => 'var:preset|spacing|30',
							],
						],
					]
				),
				'textColor'       => 'accent-3',
				'type'            => 'upper-alpha',
				'values'          => '<li>Pizza</li><li>Pasta</li>',
			],
			$block['attributes'],
		);
	}
}