<?php

namespace WPGraphQL\ContentBlocks\Unit;

/**
 * @group block
 * @group core-list
 */
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

			fragment CoreListItemBlockFragment on CoreListItem {
				attributes {
					className
					content
					# fontFamily
					# fontSize
					lock
					# metadata
					placeholder
					# style
				}
			}

			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks( flat: false ) {
						apiVersion
						blockEditorCategoryName
						clientId
						cssClassNames
						innerBlocks {
							apiVersion
							blockEditorCategoryName
							clientId
							cssClassNames
							name
							parentClientId
							renderedHtml
							type
							... on CoreListItem {
								...CoreListItemBlockFragment
							}
						}
						isDynamic
						name
						parentClientId
						renderedHtml
						type
						...CoreListBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test case for retrieving core list block fields and attributes.
	 *
	 * Covers
	 * - CoreListAttributes: 'anchor', 'backgroundColor', 'className', 'cssClassName' and 'values'.
	 * - CoreListItemAttributes: 'content'.
	 */
	public function test_retrieve_core_list_fields_and_attributes(): void {
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

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		// Test the `CoreList` block.
		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );
		$this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );

		$this->assertEquals( 'core/list', $block['name'], 'The block name should be core/list' );
		$this->assertEquals( 'CoreList', $block['type'], 'The block type should be CoreList' );
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

		// Test the inner blocks.
		$this->assertNotEmpty( $block['innerBlocks'], 'There should be inner blocks' );
		$this->assertCount( 2, $block['innerBlocks'] );

		$block['innerBlocks'][0];
		$this->assertNotEmpty( $block['innerBlocks'][0]['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['innerBlocks'][0]['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertEmpty( $block['innerBlocks'][0]['cssClassNames'], 'The cssClassNames should be present' );
		$this->assertNotEmpty( $block['innerBlocks'][0]['clientId'], 'The clientId should be present' );
		$this->assertEquals( 'core/list-item', $block['innerBlocks'][0]['name'], 'The block name should be core/list-item' );
		$this->assertEquals( 'CoreListItem', $block['innerBlocks'][0]['type'], 'The block type should be CoreListItem' );
		$this->assertNotEmpty( $block['innerBlocks'][0]['renderedHtml'], 'The renderedHtml should be present' );

		$this->assertEquals(
			[
				'className'   => null,
				'content'     => 'Truck',
				'lock'        => null,
				'placeholder' => null,
			],
			$block['innerBlocks'][0]['attributes'],
			'The first inner block attributes should match'
		);

		$this->assertEquals(
			[
				'className'   => null,
				'content'     => 'Train',
				'lock'        => null,
				'placeholder' => null,
			],
			$block['innerBlocks'][1]['attributes'],
			'The second inner block attributes should match'
		);
	}

	/**
	 * Test case for retrieving core list block fields and attributes.
	 *
	 * Covers CoreListAttributes: 'fontFamily', 'fontSize', 'gradient', and 'lock'.
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

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertEquals( 'core/list', $block['name'], 'The block name should be core/list' );
		$this->assertEquals( 'CoreList', $block['type'], 'The block type should be CoreList' );

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
	 * Covers CoreListAttributes: 'ordered' and 'reversed'.
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

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertEquals( 'core/list', $block['name'], 'The block name should be core/list' );
		$this->assertEquals( 'CoreList', $block['type'], 'The block type should be CoreList' );

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
	 * Covers CoreListAttributes: 'start', 'style', 'textColor' and 'type'.
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

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertEquals( 'core/list', $block['name'], 'The block name should be core/list' );
		$this->assertEquals( 'CoreList', $block['type'], 'The block type should be CoreList' );

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

	/**
	 * Tests the retrieval of CoreListItem attributes.
	 * 
	 * `editorBlocks` is flat to test the `parentClientId` attribute.
	 *
	 * Covers : CoreListItemAttributes: 'className', 'lock'.
	 */
	public function test_retrieve_core_list_item_flat(): void {
		$block_content = '
			<!-- wp:list -->
				<ul class="wp-block-list">
					<!-- wp:list-item {"lock":{"move":true,"remove":true},"className":"test-css-class-item-1","fontSize":"large","fontFamily":"heading"} -->
						<li class="test-css-class-item-1 has-heading-font-family has-large-font-size">List item 1</li>
					<!-- /wp:list-item -->
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

		$query     = '
			fragment CoreListItemBlockFragment on CoreListItem {
				attributes {
					className
					content
					lock
					# metadata
				}
			}
			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks {
						clientId
						name
						parentClientId
						innerBlocks {
							... on CoreListItem {
								clientId
								name
								parentClientId
								...CoreListItemBlockFragment
							}
						}
						... on CoreListItem {
							...CoreListItemBlockFragment
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
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );
		$this->assertEquals( 2, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][1];

		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );
		$this->assertEquals( 'core/list-item', $block['name'], 'The block name should be core/list' );

		$this->assertNotEmpty( $block['parentClientId'], 'There should be some parentClientId for the block' );
		$this->assertEquals( $actual['data']['post']['editorBlocks'][0]['clientId'], $block['parentClientId'], 'The parentClientId should match' );
		$this->assertEquals( $block['clientId'], $actual['data']['post']['editorBlocks'][0]['innerBlocks'][0]['clientId'], 'The inner block clientId should match' );

		$this->assertEquals(
			[
				'className'   => 'test-css-class-item-1', // Previously untested.
				'content'     => 'List item 1',
				'lock'        => wp_json_encode( // Previously untested.
					[
						'move'   => true,
						'remove' => true,
					]
				),
			],
			$block['attributes'],
		);
	}

	/**
	 * Test case for retrieving core list block fields and attributes.
	 *
	 * Covers CoreListItemAttributes: 'fontSize', 'fontFamily'
	 */
	public function test_retrieve_core_list_item_font_family_size(): void {
		// fontFamily, fontSize and style are only supported in WP 6.2+.
		if ( ! is_wp_version_compatible( '6.2' ) ) {
			$this->markTestSkipped( 'This test requires WP 6.2 or higher.' );
		}

		$block_content = '
			<!-- wp:list -->
				<ul class="wp-block-list">
					<!-- wp:list-item {"lock":{"move":true,"remove":true},"className":"test-css-class-item-1","fontSize":"large","fontFamily":"heading"} -->
						<li class="test-css-class-item-1 has-heading-font-family has-large-font-size">List item 1</li>
					<!-- /wp:list-item -->
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

		$query     = '
			fragment CoreListItemBlockFragment on CoreListItem {
				attributes {
					className
					content
					lock
					fontFamily
					fontSize
					style
				}
			}
			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks( flat: false ) {
						clientId
						name
						parentClientId
						innerBlocks {
							... on CoreListItem {
								name
								...CoreListItemBlockFragment
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
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );
		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0]['innerBlocks'][0];
		$this->assertEquals( 'core/list-item', $block['name'], 'The block name should be core/list' );

		$this->assertEquals(
			[
				'className'   => 'test-css-class-item-1',
				'content'     => 'List item 1',
				'fontFamily'  => 'heading', // Previously untested.
				'fontSize'    => 'large', // Previously untested.
				'lock'        => wp_json_encode(
					[
						'move'   => true,
						'remove' => true,
					]
				),
				'style'       => null,
			],
			$block['attributes'],
		);
	}


	/**
	 * Test case for retrieving core list item block fields and attributes.
	 *
	 * Covers CoreListItemAttributes: 'style' attribute.
	 */
	public function test_retrieve_core_list_item_untested_attributes(): void {
		// fontFamily, fontSize and style are only supported in WP 6.2+.
		if ( ! is_wp_version_compatible( '6.2' ) ) {
			$this->markTestSkipped( 'This test requires WP 6.2 or higher.' );
		}

		$block_content = '
			<!-- wp:list -->
				<ul class="wp-block-list">
					<!-- wp:list-item {"style":{"typography":{"textDecoration":"underline"}}} -->
						<li style="text-decoration:underline">List Item 2</li>
					<!-- /wp:list-item -->
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

		$query     = '
			fragment CoreListItemBlockFragment on CoreListItem {
				attributes {
					className
					content
					lock
					fontFamily
					fontSize
					style
				}
			}
			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks( flat: false ) {
						clientId
						name
						type
						parentClientId
						innerBlocks {
							... on CoreListItem {
								name
								...CoreListItemBlockFragment
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
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );
		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0]['innerBlocks'][0];
		$this->assertEquals( 'core/list-item', $block['name'], 'The block name should be core/list' );

		$this->assertEquals(
			[
				'className'   => null,
				'content'     => 'List Item 2',
				'fontFamily'  => null,
				'fontSize'    => null,
				'lock'        => null,
				'style'       => wp_json_encode( [ 'typography' => [ 'textDecoration' => 'underline' ] ] ), // Previously untested.
			],
			$block['attributes'],
		);
	}


	public function test_retrieve_core_list_item_values(): void {
		$block_content = <<<HTML
<!-- wp:list {"ordered":true} -->
<ol class="wp-block-list"><!-- wp:list-item -->
<li>List item 1</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>List item 2<!-- wp:list {"className":"is-style-checkmark-list"} -->
<ul class="wp-block-list is-style-checkmark-list"><!-- wp:list-item -->
<li>Child list item 1<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Third level list item</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Child list item 2</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>List item 3</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>List item 4</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list -->
HTML;
		// Set post content.
		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$query  = '
		query postQuery($id: ID!) {
			  post(id: $id, idType: DATABASE_ID, asPreview: false) {
			    title
			    editorBlocks(flat: false) {
			      name
			      ... on CoreList {
			        type
			        name
			        renderedHtml
			        innerBlocks {
			          ... on CoreListItem {
			            type
			            name
			      
			            renderedHtml
			          }
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
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$editorBlocks = $actual['data']['post']['editorBlocks'];
		$this->assertEquals( 1, count($editorBlocks));

		$editorBlock = $editorBlocks[0];
		$this->assertEquals( 'core/list', $editorBlock['name'], 'The block name should be core/list' );
		$this->assertEquals( 'CoreList', $editorBlock['type'], 'The block type should be CoreList' );
		$this->assertArrayHasKey( 'renderedHtml', $editorBlock);
		$this->assertArrayHasKey( 'innerBlocks', $editorBlock);

		$innerBlocks = $editorBlock['innerBlocks'];
		$this->assertEquals( 4, count($innerBlocks));
		$firstBlock = $innerBlocks[0];
		$secondBlock = $innerBlocks[0];

		/**
		 * No child list items
		 */
		$this->assertEquals('CoreListItem', $firstBlock['type'], 'The block type should be CoreListItem');
		$this->assertEquals('core/list-item', $firstBlock['name'], 'The block name should be core/list-item');
		$this->assertEquals('<li>List item 1</li>', trim($firstBlock['renderedHtml']), 'The block should have valid HTML block');


		/**
		 * Child list items
		 */
		$html = <<<HTML
<li>List item 2
	<ul class="wp-block-list is-style-checkmark-list">
		<li>Child list item 1
			<ul class="wp-block-list">
				<li>Third level list item</li>
			</ul>
		</li>
		<li>Child list item 2</li>
	</ul>
</li>
HTML;

		$html = trim(preg_replace('/\s+/', ' ', $html));
		$renderedHtml = trim(preg_replace('/\s+/', ' ', $innerBlocks[1]['renderedHtml'] ));
		$this->assertEquals($html, $renderedHtml, 'The block should have valid HTML block');

	}
}
