<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreSeparatorTest extends PluginTestCase {
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
				'post_title'   => 'Post with Separator',
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
            fragment CoreSeparatorBlockFragment on CoreSeparator {
                attributes {
                    align
                    anchor
                    backgroundColor
                    className
                    cssClassName
                    gradient
                    opacity
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
                        ...CoreSeparatorBlockFragment
                    }
                }
            }
        ';
	}

	public function test_retrieve_core_separator_attributes() {
		$block_content = '
        <!-- wp:separator {"align":"wide"} -->
        <hr class="wp-block-separator alignwide has-alpha-channel-opacity"/>
        <!-- /wp:separator -->
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
		$this->assertEquals( 'core/separator', $actual['data']['post']['editorBlocks'][0]['name'], 'The block name should be core/separator' );

		// Verify the attributes.
		$this->assertEquals(
			[
				'cssClassName'    => 'wp-block-separator alignwide has-alpha-channel-opacity',
				'align'           => 'wide',
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'gradient'        => null,
				'opacity'         => 'alpha-channel',
				'style'           => null,
			],
			$actual['data']['post']['editorBlocks'][0]['attributes'],
		);
	}

	public function test_retrieve_core_separator_attributes_two() {
		$block_content = '
            <!-- wp:separator -->
            <hr class="wp-block-separator has-alpha-channel-opacity"/>
            <!-- /wp:separator -->
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
		$this->assertEquals( 'core/separator', $actual['data']['post']['editorBlocks'][0]['name'], 'The block name should be core/separator' );

		// Verify the attributes.
		$this->assertEquals(
			[
				'cssClassName'    => 'wp-block-separator has-alpha-channel-opacity',
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'gradient'        => null,
				'opacity'         => 'alpha-channel',
				'style'           => null,
			],
			$actual['data']['post']['editorBlocks'][0]['attributes'],
		);
	}
}
