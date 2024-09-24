<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CorePreformattedTest extends PluginTestCase {
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
				'post_title'   => 'Post with Preformatted Block',
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
            fragment CorePreformattedBlockFragment on CorePreformatted {
                attributes {
                    anchor
                    backgroundColor
                    className
                    content
                    fontFamily
                    fontSize
                    gradient
                    lock
                    metadata
                    style
                    textColor
                }
            }
            query Post( $id: ID! ) {
                post(id: $id, idType: DATABASE_ID) {
                    databaseId
                    editorBlocks {
                        name
                        ...CorePreformattedBlockFragment
                    }
                }
            }
        ';
	}

	/**
	 * Test the retrieval of core/preformatted block attributes.
	 *
	 * Attributes covered:
	 * - content
	 * - backgroundColor
	 * - textColor
	 * - fontSize
	 * - fontFamily
	 * - className
	 *
	 * @return void
	 */
	public function test_retrieve_core_preformatted_attributes() {
		$block_content = '
            <!-- wp:preformatted {"backgroundColor":"pale-cyan-blue","textColor":"vivid-red","fontSize":"large","fontFamily":"monospace","className":"custom-class"} -->
            <pre class="wp-block-preformatted has-vivid-red-color has-pale-cyan-blue-background-color has-text-color has-background has-large-font-size has-monospace-font-family custom-class">This is a
preformatted block
    with multiple lines
        and preserved spacing.</pre>
            <!-- /wp:preformatted -->
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

		$this->assertEquals( 'core/preformatted', $block['name'] );
		$this->assertEquals(
			[
				'anchor'          => null,
				'backgroundColor' => 'pale-cyan-blue',
				'className'       => 'custom-class',
				'content'         => "This is a\npreformatted block\n    with multiple lines\n        and preserved spacing.",
				'fontFamily'      => 'monospace',
				'fontSize'        => 'large',
				'gradient'        => null,
				'lock'            => null,
				'metadata'        => null,
				'style'           => null,
				'textColor'       => 'vivid-red',
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/preformatted block with custom styles and gradient.
	 *
	 * Attributes covered:
	 * - gradient
	 * - style
	 * - anchor
	 *
	 * @return void
	 */
	public function test_retrieve_core_preformatted_with_custom_styles() {
		$block_content = '
            <!-- wp:preformatted {"anchor":"custom-anchor","gradient":"vivid-cyan-blue-to-vivid-purple","style":{"border":{"width":"2px","style":"dashed"},"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}}}} -->
            <pre id="custom-anchor" class="wp-block-preformatted has-vivid-cyan-blue-to-vivid-purple-gradient-background" style="border-style:dashed;border-width:2px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">This preformatted block
has a gradient background
and custom border style.</pre>
            <!-- /wp:preformatted -->
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
				'anchor'          => 'custom-anchor',
				'backgroundColor' => null,
				'className'       => null,
				'content'         => "This preformatted block\nhas a gradient background\nand custom border style.",
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => 'vivid-cyan-blue-to-vivid-purple',
				'lock'            => null,
				'metadata'        => null,
				'style'           => wp_json_encode(
					[
						'border'  => [
							'width' => '2px',
							'style' => 'dashed',
						],
						'spacing' => [
							'padding' => [
								'top'    => '20px',
								'right'  => '20px',
								'bottom' => '20px',
								'left'   => '20px',
							],
						],
					]
				),
				'textColor'       => null,
			],
			$attributes
		);
	}

	/**
	 * Test retrieval of core/preformatted block with lock and metadata.
	 *
	 * Attributes covered:
	 * - lock
	 * - metadata
	 *
	 * @return void
	 */
	public function test_retrieve_core_preformatted_with_lock_and_metadata() {
		$block_content = '
            <!-- wp:preformatted {"lock":{"move":true,"remove":true},"metadata":{"key1":"value1","key2":"value2"}} -->
            <pre class="wp-block-preformatted">This is a locked preformatted block
with metadata.</pre>
            <!-- /wp:preformatted -->
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
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => "This is a locked preformatted block\nwith metadata.",
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'lock'            => '{"move":true,"remove":true}',
				'metadata'        => '{"key1":"value1","key2":"value2"}',
				'style'           => null,
				'textColor'       => null,
			],
			$attributes
		);
	}
}
