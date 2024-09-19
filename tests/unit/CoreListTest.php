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
                    ordered
                    cssClassName
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

	public function test_retrieve_core_list_attributes_basic() {
		$block_content = '
            <!-- wp:list {"ordered":true,"className":"custom-list-class"} -->
            <ol class="wp-block-list custom-list-class">
                <li>List item 1</li>
                <li>List item 2</li>
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

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// There should be only one block using that query when not using flat: true
		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );
		$this->assertEquals( 'core/list', $actual['data']['post']['editorBlocks'][0]['name'], 'The block name should be core/list' );

		// Verify the attributes.
		$this->assertEquals(
			[
				'ordered'      => true,
				'cssClassName' => 'wp-block-list custom-list-class',
			],
			$actual['data']['post']['editorBlocks'][0]['attributes'],
		);
	}

	public function test_retrieve_core_list_attributes_unordered() {
		$block_content = '
            <!-- wp:list {"ordered":false} -->
            <ul class="wp-block-list">
                <li>List item 1</li>
                <li>List item 2</li>
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

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// There should be only one block using that query when not using flat: true
		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );
		$this->assertEquals( 'core/list', $actual['data']['post']['editorBlocks'][0]['name'], 'The block name should be core/list' );

		// Verify the attributes.
		$this->assertEquals(
			[
				'ordered'      => false,
				'cssClassName' => 'wp-block-list',
			],
			$actual['data']['post']['editorBlocks'][0]['attributes'],
		);
	}
}
