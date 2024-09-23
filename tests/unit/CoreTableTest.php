<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreTableTest extends PluginTestCase {
	private $post_id;

	public function setUp(): void {
		parent::setUp();

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Table',
				'post_content' => '',
				'post_status'  => 'publish',
			]
		);

		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		wp_delete_post( $this->post_id, true );
		\WPGraphQL::clear_schema();

		parent::tearDown();
	}

	private function query(): string {
		return '
			fragment CoreTableBlockFragment on CoreTable {
				attributes {
					align
					anchor
					backgroundColor
					body {
						cells {
							align
							colspan
							content
							rowspan
							scope
							tag
						}
					}
					borderColor
					caption
					className
					fontFamily
					fontSize
					foot {
						cells {
							align
							colspan
							content
							rowspan
							scope
							tag
						}
					}
					gradient
					hasFixedLayout
					head {
						cells {
							align
							colspan
							content
							rowspan
							scope
							tag
						}
					}
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
						...CoreTableBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test that the CoreTable block can be retrieved and basic fields are present.
	 *
	 * Tested attributes:
	 *  - caption
	 *  - hasFixedLayout
	 *  - body > cells > tag (Rest are not working)
	 */
	public function test_retrieve_core_table_attribute_fields() {
		$block_content = '
			<!-- wp:table -->
			<figure class="wp-block-table"><table><tbody><tr><td>Cell 1</td><td>Cell 2</td></tr><tr><td>Cell 3</td><td>Cell 4</td></tr></tbody></table><figcaption class="wp-element-caption">Caption</figcaption></figure>
			<!-- /wp:table -->
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
		$this->assertEquals( 'core/table', $block['name'], 'The block name should be core/table' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		// We'll test the body cells separately.
		$body = $block['attributes']['body'];
		unset( $block['attributes']['body'] );

		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => null,
				'borderColor'     => null,
				'caption'         => 'Caption',
				'className'       => null,
				'fontFamily'      => null,
				'fontSize'        => null,
				'foot'            => [],
				'gradient'        => null,
				'hasFixedLayout'  => is_wp_version_compatible( '6.6' ) ? true : false, // WP 6.6 changes the unset default value to true.
				'head'            => [],
				'lock'            => null,
				'style'           => null,
				'textColor'       => null,
			],
			$block['attributes']
		);

		// Test the body cells.
		$this->assertNotEmpty( $body, 'The body should have cells' );
		$this->assertCount( 2, $body, 'There should be 2 rows' );

		// Test the first row
		$this->assertCount( 2, $body[0]['cells'], 'There should be 2 cells in the first row' );
		$this->assertEquals(
			[ // @todo These should be filled in
				'align'   => null,
				'colspan' => null,
				'content' => null,
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'td',
			],
			$body[0]['cells'][0],
			'The first cell in the first row does not match'
		);
		$this->assertEquals(
			[ // @todo These should be filled in
				'align'   => null,
				'colspan' => null,
				'content' => null,
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'td',
			],
			$body[0]['cells'][1],
			'The second cell in the first row does not match'
		);

		// Test the second row
		$this->assertCount( 2, $body[1]['cells'], 'There should be 2 cells in the second row' );
		$this->assertEquals(
			[ // @todo These should be filled in
				'align'   => null,
				'colspan' => null,
				'content' => null,
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'td',
			],
			$body[1]['cells'][0],
			'The first cell in the second row does not match'
		);
		$this->assertEquals(
			[ // @todo These should be filled in
				'align'   => null,
				'colspan' => null,
				'content' => null,
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'td',
			],
			$body[1]['cells'][1],
			'The second cell in the second row does not match'
		);
	}

	/**
	 * Tests additional attributes of the CoreTable block along with the header and footer cells.
	 *
	 * Tested attributes:
	 *  - align
	 *  - borderColor
	 *  - fontFamily
	 *  - fontSize
	 *  - style
	 *  - head > cells > tag (Rest are not working)
	 *  - foot > cells > tag (Rest are not working)
	 */
	public function test_retrieve_core_table_attribute_fields_header_footer() {
		$block_content = '
			<!-- wp:table {"hasFixedLayout":false,"align":"wide","style":{"border":{"width":"1px"}},"fontSize":"medium","fontFamily":"system-serif","borderColor":"accent-4"} -->
			<figure class="wp-block-table alignwide has-system-serif-font-family has-medium-font-size"><table class="has-border-color has-accent-4-border-color" style="border-width:1px"><thead><tr><th class="has-text-align-left" data-align="left">Header label</th><th class="has-text-align-right" data-align="right">Header label</th></tr></thead><tbody><tr><td class="has-text-align-left" data-align="left">This column has "align column left"</td><td class="has-text-align-right" data-align="right">This column has "align column center"</td></tr><tr><td class="has-text-align-left" data-align="left">Cell 3</td><td class="has-text-align-right" data-align="right">Cell 4</td></tr></tbody><tfoot><tr><td class="has-text-align-left" data-align="left">Footer label</td><td class="has-text-align-right" data-align="right">Footer label</td></tr></tfoot></table><figcaption class="wp-element-caption">Caption</figcaption></figure>
			<!-- /wp:table -->
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

		$block = $actual['data']['post']['editorBlocks'][0];

		// We'll test the cells separately.
		$body = $block['attributes']['body'];
		$head = $block['attributes']['head'];
		$foot = $block['attributes']['foot'];
		unset( $block['attributes']['body'] );
		unset( $block['attributes']['head'] );
		unset( $block['attributes']['foot'] );

		$this->assertEquals(
			[
				'align'           => 'wide', // Previously untested
				'anchor'          => null,
				'backgroundColor' => null,
				'borderColor'     => 'accent-4', // Previously untested
				'caption'         => 'Caption',
				'className'       => null,
				'fontFamily'      => 'system-serif', // Previously untested
				'fontSize'        => 'medium', // Previously untested
				'gradient'        => null,
				'hasFixedLayout'  => false,
				'lock'            => null,
				'style'           => wp_json_encode(
					[ // Previously untested
						'border' => [
							'width' => '1px',
						],
					]
				),
				'textColor'       => null,
			],
			$block['attributes'],
			'The block attributes do not match'
		);

		// Test the head cells.
		$this->assertNotEmpty( $head, 'The head should have cells' );
		$this->assertCount( 1, $head, 'There should be 1 row in the head' );
		$this->assertCount( 2, $head[0]['cells'], 'There should be 2 cells in the head' );

		// Test the foot cells.
		// @todo This is duplicated after the `markTestIncomplete` call and should be removed once fixed.
		$this->assertNotEmpty( $foot, 'The foot should have cells' );
		$this->assertCount( 1, $foot, 'There should be 1 row in the foot' );
		$this->assertCount( 2, $foot[0]['cells'], 'There should be 2 cells in the foot' );

		$this->markTestIncomplete( 'Cell attributes are not returned' );

		$this->assertEquals(
			[
				'align'   => 'left',
				'colspan' => null,
				'content' => 'Header label',
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'th',
			],
			$head[0]['cells'][0],
			'The first cell in the head does not match'
		);
		$this->assertEquals(
			[
				'align'   => 'right',
				'colspan' => null,
				'content' => 'Header label',
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'th',
			],
			$head[0]['cells'][1],
			'The second cell in the head does not match'
		);

		// Test the body cells.
		$this->assertNotEmpty( $body, 'The body should have cells' );
		$this->assertCount( 2, $body, 'There should be 2 rows' );

		// Test the left cell.
		$this->assertEquals(
			[
				'align'   => 'left',
				'colspan' => null,
				'content' => 'This column has "align column left"',
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'td',
			],
			$body[0]['cells'][0],
			'The first cell in the first row does not match'
		);

		// Test right cell.
		$this->assertEquals(
			[
				'align'   => 'right',
				'colspan' => null,
				'content' => 'This column has "align column center"',
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'td',
			],
		);

		// Test the foot cells.
		$this->assertNotEmpty( $foot, 'The foot should have cells' );
		$this->assertCount( 1, $foot, 'There should be 1 row in the foot' );
		$this->assertCount( 2, $foot[0]['cells'], 'There should be 2 cells in the foot' );

		$this->assertEquals(
			[
				'align'   => 'left',
				'colspan' => null,
				'content' => 'Footer label',
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'td',
			],
			$foot[0]['cells'][0],
			'The first cell in the foot does not match'
		);

		$this->assertEquals(
			[
				'align'   => 'right',
				'colspan' => null,
				'content' => 'Footer label',
				'rowspan' => null,
				'scope'   => null,
				'tag'     => 'td',
			],
			$foot[0]['cells'][1],
			'The second cell in the foot does not match'
		);
	}

	/**
	 * Tests additional style attributes of the CoreTable block.
	 *
	 * Tested attributes:
	 *  - backgroundColor
	 *  - className
	 *  - textColor
	 */
	public function test_retrieve_core_table_attribute_styles() {
		$block_content = '
			<!-- wp:table {"hasFixedLayout":false,"className":"is-style-stripes","style":{"elements":{"link":{"color":{"text":"var:preset|color|vivid-red"}}}},"backgroundColor":"base","textColor":"vivid-red"} -->
			<figure class="wp-block-table is-style-stripes"><table class="has-vivid-red-color has-base-background-color has-text-color has-background has-link-color"><thead><tr><th>Header label</th><th>Header label</th></tr></thead><tbody><tr><td>Cell 1</td><td>Cell 2</td></tr><tr><td>Cell 3</td><td>Cell 4</td></tr></tbody><tfoot><tr><td></td><td></td></tr></tfoot></table><figcaption class="wp-element-caption">Caption</figcaption></figure>
			<!-- /wp:table -->
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

		$block = $actual['data']['post']['editorBlocks'][0];

		// No need to test the cells again.
		unset( $block['attributes']['body'] );
		unset( $block['attributes']['head'] );
		unset( $block['attributes']['foot'] );

		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => 'base', // Previously untested
				'borderColor'     => null,
				'caption'         => 'Caption',
				'className'       => 'is-style-stripes', // Previously untested
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'hasFixedLayout'  => false,
				'lock'            => null,
				'style'           => wp_json_encode(
					[
						'elements' => [
							'link' => [
								'color' => [
									'text' => 'var:preset|color|vivid-red',
								],
							],
						],
					]
				),
				'textColor'       => 'vivid-red', // Previously untested
			],
			$block['attributes'],
			'The block attributes do not match'
		);
	}

	/**
	 * Tests the `lock` and gradient attributes of the CoreTable block.
	 *
	 * Tested attributes:
	 *  - lock
	 *  - gradient
	 */
	public function test_retrieve_core_table_attribute_lock_gradient(): void {
		$block_content = '
			<!-- wp:table {"hasFixedLayout":false,"lock":{"move":true,"remove":false},"style":{"typography":{"letterSpacing":"7px"}},"gradient":"gradient-3"} -->
			<figure style="letter-spacing:7px" class="wp-block-table"><table class="has-gradient-3-gradient-background has-background"><thead><tr><th>Header label</th><th>Header label</th></tr></thead><tbody><tr><td>Cell 1</td><td>Cell 2</td></tr><tr><td>Cell 3</td><td>Cell 4</td></tr></tbody><tfoot><tr><td>Footer label</td><td>Footer label</td></tr></tfoot></table><figcaption class="wp-element-caption">Caption</figcaption></figure>
			<!-- /wp:table -->
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

		$block = $actual['data']['post']['editorBlocks'][0];

		// No need to test the cells again.
		unset( $block['attributes']['body'] );
		unset( $block['attributes']['head'] );
		unset( $block['attributes']['foot'] );

		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => null,
				'borderColor'     => null,
				'caption'         => 'Caption',
				'className'       => null,
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => 'gradient-3', // Previously untested
				'hasFixedLayout'  => false,
				'lock'            => wp_json_encode(
					[ // Previously untested
						'move'   => true,
						'remove' => false,
					]
				),
				'style'           => wp_json_encode(
					[
						'typography' => [
							'letterSpacing' => '7px',
						],
					]
				),
				'textColor'       => null,
			],
			$block['attributes'],
			'The block attributes do not match'
		);
	}
}
