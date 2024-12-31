<?php

namespace WPGraphQL\ContentBlocks\Unit;

/**
 * @group block
 * @group core-group
 */
final class CoreGroupTest extends PluginTestCase {

	/**
	 * The ID of the post created for the test.
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * The ID of the attachment created for the test.
	 *
	 * @var int
	 */
	public $attachment_id;

	public function setUp(): void {
		parent::setUp();

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Post Title',
				'post_content' => '',
				'post_status'  => 'publish',
			]
		);

		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		// your tear down methods here
		wp_delete_post( $this->post_id, true );
		\WPGraphQL::clear_schema();

		parent::tearDown();
	}

	/**
	 * Get the query for the CoreGroup block.
	 *
	 * @param string $attributes The attributes to add to query.
	 */
	public function query(): string {
		return '
			fragment CoreGroupFragment on CoreGroup {
			  attributes {
			    align
			    backgroundColor
			    borderColor
			    className
			    cssClassName
			    fontFamily
			    fontSize
			    gradient
			    layout
			    lock
			    style
			    tagName
			    textColor	
			  }
			}
			
			query Post($id: ID!) {
			  post(id: $id, idType: DATABASE_ID) {
			    databaseId
			    editorBlocks(flat:true) {
			      apiVersion
			      blockEditorCategoryName
			      clientId
			      cssClassNames
			      innerBlocks {
			        name
			      }
			      name
			      parentClientId
			      renderedHtml
			      type
			      ...CoreGroupFragment
			    }
			  }
			}
		';
	}

	/**
	 * Test that the CoreGroup block is retrieved correctly.
	 *
	 * Covers the following attributes:
	 * - apiVersion
	 * - blockEditorCategoryName
	 * - clientId
	 * - cssClassNames
	 * - innerBlocks
	 * - name
	 * - parentClientId
	 * - renderedHtml
	 * - type
	 * - attributes
	 */

	public function test_retrieve_core_image_fields_attributes(): void {
		$block_content = <<<HTML
<!-- wp:group {"tagName":"header","className":"test-group-class is-style-default","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<header id="test-group-id" class="wp-block-group test-group-class is-style-default"><!-- wp:paragraph -->
<p>This is the left paragraph.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>This is the right paragraph</p>
<!-- /wp:paragraph --></header>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"example-class"} -->
<p class="example-class">This is an example page. It's different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:</p>
<!-- /wp:paragraph -->
HTML;

		$query = $this->query();

		// Update the post content with the block content.
		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );
		$node = $actual['data']['post'];


		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'], 'The post ID should match' );
		$this->assertEquals( 'core/group', $node['editorBlocks'][0]['name'], 'The block name should match core/group' );
		$this->assertEquals( 'CoreGroup', $node['editorBlocks'][0]['type'], 'The block type should match CoreGroup' );
		$this->assertNotEmpty( $node['editorBlocks'], 'The node should have an array with the key editorBlocks which is not empty' );

		// Check Block nodes
		$block = $node['editorBlocks'][0];
		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'design', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be media' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );
		$this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );
		$this->assertNotEmpty( $block['innerBlocks'], 'The innerBlocks should be an array' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );


		// Check child blocks
		$clientId = $block['clientId'];
		$childBlock1 = $node['editorBlocks'][1];
		$this->assertNotEmpty( $childBlock1, 'Child block 1 should be present' );
		$this->assertEquals( $clientId, $childBlock1['parentClientId'], 'Child block 1 parentClientId should match the parent block clientId' );
		$this->assertEquals('core/paragraph', $childBlock1['name'], 'Child block 1 should be a core/paragraph' );

		$childBlock2 = $node['editorBlocks'][1];
		$this->assertNotEmpty( $childBlock2, 'Child block 2 should be present' );
		$this->assertEquals( $clientId, $childBlock1['parentClientId'], 'Child block 2 parentClientId should match the parent block clientId' );
		$this->assertEquals('core/paragraph', $childBlock1['name'], 'Child block 2 should be a core/paragraph' );

		// Check attributes
		$this->assertEquals(
			[
				"align"           => null,
				"backgroundColor" => null,
				"borderColor"     => null,
				"className"       => "test-group-class is-style-default",
				"cssClassName"    => "wp-block-group test-group-class is-style-default is-content-justification-center is-layout-flex wp-container-7",
				"fontFamily"      => null,
				"fontSize"        => null,
				"gradient"        => null,
				"layout"          => "{\"type\":\"flex\",\"flexWrap\":\"wrap\",\"justifyContent\":\"center\"}",
				"lock"            => null,
				'style'           => null,
				"tagName"         => "header",
				"textColor"       => null
			],
			$node['editorBlocks'][0]['attributes']
		);
	}
}
