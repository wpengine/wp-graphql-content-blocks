<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CorePostTermsTest extends PluginTestCase {

	/**
	 * The URI of the post created for the test.
	 *
	 * @var string
	 */
	public $post_uri;

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
				'post_title'   => 'Test Terms',
				'post_name'    => 'test-terms',
				'post_content' => '',
				'post_status'  => 'publish',
			]
		);

		$this->post_uri = get_permalink($this->post_id);

		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		wp_delete_post($this->post_id, true);
		\WPGraphQL::clear_schema();

		parent::tearDown();
	}

	/**
	 * Get the updated GraphQL query for the CorePostTerms block.
	 */
	public function query(): string {
		return '
			query TestPostTerms($uri: String! = "test-terms") {
				nodeByUri(uri: $uri) {
					id
					uri
					... on NodeWithPostEditorBlocks {
						editorBlocks {
							__typename
							... on CorePostTerms {
								prefix
								suffix
								taxonomy {
									__typename
									node {
										__typename
										id
										name
									}
								}
								terms {
									__typename
									nodes {
										__typename
										id
										name
									}
								}
							}
						}
					}
				}
			}
		';
	}

	/**
	 * Test that the CorePostTerms block retrieves attributes, taxonomy, and terms correctly.
	 */
	public function test_retrieve_core_post_terms(): void {
		$block_content = '<!-- wp:post-terms {"prefix":"Before","suffix":"After","term":"category"} /-->';

		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$variables = [ 'uri' => 'test-terms' ];
		$query = $this->query();
		$actual = graphql(compact('query', 'variables'));

		$node = $actual['data']['nodeByUri'];

		$this->assertArrayHasKey('editorBlocks', $node);
		$this->assertCount(1, $node['editorBlocks']);

		$block = $node['editorBlocks'][0];

		$this->assertEquals('CorePostTerms', $block['__typename']);
		$this->assertEquals('Before', $block['prefix']);
		$this->assertEquals('After', $block['suffix']);

		$this->assertArrayHasKey('taxonomy', $block);
		$this->assertArrayHasKey('node', $block['taxonomy']);
		$this->assertArrayHasKey('__typename', $block['taxonomy']['node']);
		$this->assertArrayHasKey('id', $block['taxonomy']['node']);
		$this->assertArrayHasKey('name', $block['taxonomy']['node']);

		$this->assertArrayHasKey('terms', $block);
		$this->assertArrayHasKey('nodes', $block['terms']);
		$this->assertIsArray($block['terms']['nodes']);
		$this->assertEquals('CorePostTermsToTermNodeConnection', $block['terms']['__typename']);
		$this->assertEquals('Category', $block['terms']['nodes'][0]['__typename']);

		$this->assertArrayHasKey('taxonomy', $block);
		$this->assertArrayHasKey('node', $block['taxonomy']);
		$this->assertIsArray($block['taxonomy']['node']);
		$this->assertEquals('CorePostTermsToTaxonomyConnectionEdge', $block['taxonomy']['__typename']);
		$this->assertEquals('category', $block['taxonomy']['node']['name']);


	}
}
