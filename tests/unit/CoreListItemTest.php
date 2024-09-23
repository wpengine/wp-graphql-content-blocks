<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreListItemTest extends PluginTestCase {
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
				'post_title'   => 'Post with List Items',
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
			fragment CoreListItemBlockFragment on CoreListItem {
				attributes {
                    className
                    content
                    fontFamily
                    fontSize
                    lock
                    # metadata
                    placeholder
                    style
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
						...CoreListItemBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test case for retrieving core list block fields and attributes.
	 *
	 * Covers : 'className', 'content', 'fontSize', 'fontFamily' and 'lock' attributes.
	 */
	public function test_retrieve_core_list_item_fields_and_attribute(): void {
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

		$query     = $this->query();
		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );
		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );
		$this->assertEquals( 2, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][1]; // Get the second block which is the list item block as the first block is the list block.
		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );
		$this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );
		$this->assertEmpty( $block['innerBlocks'], 'There should be ListItem should not have inner blocks' );
		$this->assertEquals( 'core/list-item', $block['name'], 'The block name should be core/list' );
		$this->assertNotEmpty( $block['parentClientId'], 'There should be some parentClientId for the block' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );
		$this->assertEquals(
			[
				'className'   => 'test-css-class-item-1',
				'content'     => 'List item 1',
				'fontFamily'  => 'heading',
				'fontSize'    => 'large',
				'lock'        => wp_json_encode(
					[
						'move'   => true,
						'remove' => true,
					]
				),
				'placeholder' => null,
				'style'       => null,
			],
			$block['attributes'],
		);
	}

	/**
	 * Test case for retrieving core list item block fields and attributes.
	 *
	 * Covers : 'placeholder' and 'style' attribute.
	 */
	public function test_retrieve_core_list_item_untested_attributes(): void {
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

		$query     = $this->query();
		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );
		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );
		$this->assertEquals( 2, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][1]; // Get the second block which is the list item block as the first block is the list block.
		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );
		$this->assertEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );
		$this->assertEmpty( $block['innerBlocks'], 'There should be ListItem should not have inner blocks' );
		$this->assertEquals( 'core/list-item', $block['name'], 'The block name should be core/list' );
		$this->assertNotEmpty( $block['parentClientId'], 'There should be some parentClientId for the block' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );
		$this->assertEquals(
			[
				'className'   => null,
				'content'     => 'List Item 2',
				'fontFamily'  => null,
				'fontSize'    => null,
				'lock'        => null,
				'placeholder' => null,
				'style'       => wp_json_encode( [ 'typography' => [ 'textDecoration' => 'underline' ] ] ), // Previously untested.
			],
			$block['attributes'],
		);
	}
}
