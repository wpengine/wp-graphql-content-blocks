<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CorePostTermsTest extends PluginTestCase {

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
				'post_title'   => 'Test Post',
				'post_content' => '',
				'post_status'  => 'publish',
			]
		);

		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		wp_delete_post($this->post_id, true);
		\WPGraphQL::clear_schema();

		parent::tearDown();
	}

	/**
	 * Get the GraphQL query for the CorePostTerms block.
	 */
	public function query(): string {
		return '
			query TestPostTerms($id: ID!) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks {
						__typename
						... on CorePostTerms {
							prefix
							suffix
							taxonomySlug
							terms {
								__typename
								nodes {
									id
									name
								}
							}
						}
					}
				}
			}
		';
	}

	/**
	 * Test that the CorePostTerms block retrieves attributes and terms correctly.
	 */
	public function test_retrieve_core_post_terms(): void {
		$block_content = '<!-- wp:post-terms {"prefix":"Before","suffix":"After","term":"category"} /-->';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$variables = [ 'id' => $this->post_id ];
		$query = $this->query();
		$actual = graphql(compact('query', 'variables'));

		$node = $actual['data']['post'];

		$this->assertEquals($this->post_id, $node['databaseId']);
		$this->assertArrayHasKey('editorBlocks', $node);
		$this->assertCount(1, $node['editorBlocks']);

		$block = $node['editorBlocks'][0];

		$this->assertEquals('CorePostTerms', $block['__typename']);
		$this->assertEquals('Before', $block['prefix']);
		$this->assertEquals('After', $block['suffix']);
		$this->assertEquals('category', $block['taxonomySlug']);

		$this->assertArrayHasKey('terms', $block);
		$this->assertArrayHasKey('nodes', $block['terms']);
		$this->assertIsArray($block['terms']['nodes']);

		$this->assertEquals('CorePostTermsToTermNodeConnection', $block['terms']['__typename']);
	}
}
