<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreHeadingTest extends PluginTestCase {
	public $post_id;

	public function setUp(): void {
		parent::setUp();

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Post with Heading',
				'post_content' => '',
				'post_status'  => 'publish',
			]
		);
	}

	public function tearDown(): void {
		parent::tearDown();

		wp_delete_post( $this->post_id, true );
	}

	public function query(): string {
		return '
			fragment CoreHeadingBlockFragment on CoreHeading {
				attributes {
					align
					anchor
					backgroundColor
					className
					content
					cssClassName
					fontFamily
					fontSize
					gradient
					level
					lock
					# metadata
					placeholder
					style
					textAlign
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
						...CoreHeadingBlockFragment
					}
				}
			}
		';
	}

	public function test_retrieve_core_heading_attributes() {
		$block_content = '
			<!-- wp:heading {"level":2,"textAlign":"center","style":{"typography":{"fontSize":"28px","fontStyle":"normal","fontWeight":"700"}}} -->
			<h2 class="wp-block-heading has-text-align-center" style="font-size:28px;font-style:normal;font-weight:700">Sample Heading</h2>
			<!-- /wp:heading -->
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

		error_log( print_r( $actual, true ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		// There should be only one block using that query when not using flat: true
		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		// Verify the block data.
		error_log( print_r( $actual['data']['post']['editorBlocks'][0], true ) );
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'text', $actual['data']['post']['editorBlocks'][0]['blockEditorCategoryName'],  'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['clientId'], 'The clientId should be present' );

		// @todo this is not working
		// $this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['cssClassNames'], 'The cssClassNames should be present' );

		$this->assertEmpty( $actual['data']['post']['editorBlocks'][0]['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/heading', $actual['data']['post']['editorBlocks'][0]['name'], 'The block name should be core/heading' );
		$this->assertEmpty( $actual['data']['post']['editorBlocks'][0]['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $actual['data']['post']['editorBlocks'][0]['renderedHtml'], 'The renderedHtml should be present' );

		// Verify the attributes.
		$this->assertEquals(
			[
				'align'           => null,
				'anchor'          => null,
				'backgroundColor' => null,
				'className'       => null,
				'content'         => 'Sample Heading',
				'cssClassName'    => 'wp-block-heading has-text-align-center',
				'fontFamily'      => null,
				'fontSize'        => null,
				'gradient'        => null,
				'level'           => 2.0, // @todo this should be an integer
				'lock'            => null,
				'placeholder'     => null,
				'style'           => wp_json_encode(
					[
						'typography' => [
							'fontSize'   => '28px',
							'fontStyle'  => 'normal',
							'fontWeight' => '700',
						],
					],
				),
				'textAlign'       => 'center',
				'textColor'       => null,
			],
			$actual['data']['post']['editorBlocks'][0]['attributes'],
		);
	}
}
