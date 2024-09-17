<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreQuoteTest extends PluginTestCase {
	public $post_id;

	public function setUp(): void {
		parent::setUp();

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Post with Quote',
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
			fragment CoreQuoteBlockFragment on CoreQuote {
				attributes {
					citation
					className
					cssClassName
					value
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
						...CoreQuoteBlockFragment
					}
				}
			}
		';
	}

	public function test_retrieve_core_quote_attributes() {
		$block_content = '
			<!-- wp:quote {"className":"custom-quote-class"} -->
			<blockquote class="wp-block-quote custom-quote-class"><p>This is a sample quote block content.</p><cite>Author Name</cite></blockquote>
			<!-- /wp:quote -->
		';

		// Update the post content with the block content.
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

		// Test the query.
		$actual = graphql( compact( 'query', 'variables' ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// There should be only one block using that query when not using flat: true
		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ), 'There should be only one block' );

		// Verify the block data.
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $actual['data']['post']['editorBlocks'][0]['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['clientId'], 'The clientId should be present' );

		$this->assertEmpty( $actual['data']['post']['editorBlocks'][0]['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/quote', $actual['data']['post']['editorBlocks'][0]['name'], 'The block name should be core/quote' );
		$this->assertEmpty( $actual['data']['post']['editorBlocks'][0]['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['renderedHtml'], 'The renderedHtml should be present' );

		// Verify the attributes.
		$this->assertEquals(
			[
				'citation'     => 'Author Name',
				'className'    => 'custom-quote-class',
				'cssClassName' => 'wp-block-quote custom-quote-class',
				'value'        => '<p>This is a sample quote block content.</p>',
			],
			$actual['data']['post']['editorBlocks'][0]['attributes'],
		);
	}
}
