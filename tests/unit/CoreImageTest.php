<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreImageTest extends PluginTestCase {

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

		$this->attachment_id = $this->factory->attachment->create_upload_object( WP_TEST_DATA_DIR . '/images/test-image.jpg' );

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
	 * Get the query for the CoreImage block.
	 *
	 * @param string $attributes The attributes to add to query.
	 */
	public function query(): string {
		return '
			fragment CoreImageBlockFragment on CoreImage {
				attributes {
					id
					width
					height
					alt
					align
					src
					style
					sizeSlug
					linkClass
					linkTarget
					linkDestination
					borderColor
					caption
					className
					cssClassName
					url
					rel
					href
					title
					lock
					anchor
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
						name
						parentClientId
						renderedHtml
						type
						...CoreImageBlockFragment
					}
				}
			}
		';
	}

	/**
	 * Test that the CoreImage block is retrieved correctly.
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
	 * - attributes
	 */
	public function test_retrieve_core_image_fields_attributes(): void {
		$block_content = '
			<!-- wp:image {"align":"left","id":' . $this->attachment_id . ',"className":"test-css-class-name"} -->
				<figure class="wp-block-image">
					<img src="http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg" class="wp-image-1432"/>
				</figure>
			<!-- /wp:image -->';

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

		$node = $actual['data']['post'];

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'] );
		$this->assertEquals( 1, count( $node['editorBlocks'] ) );
		$this->assertEquals( 'core/image', $node['editorBlocks'][0]['name'] );
		$this->assertEquals( 'CoreImage', $node['editorBlocks'][0]['type'] );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'media', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be media' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );
		$this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );
		$this->assertEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/image', $block['name'], 'The block name should be core/image' );
		$this->assertEquals( 'CoreImage', $block['type'], 'The block type should be CoreImage' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );
		$this->assertEquals(
			[
				'align'           => 'left',
				'id'              => $this->attachment_id,
				'className'       => 'test-css-class-name',
				'src'             => 'http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg',
				'url'             => 'http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg',
				'width'           => null,
				'height'          => null,
				'alt'             => '',
				'style'           => null,
				'sizeSlug'        => null,
				'linkClass'       => null,
				'linkTarget'      => null,
				'linkDestination' => null,
				'borderColor'     => null,
				'caption'         => '',
				'cssClassName'    => 'wp-block-image',
				'rel'             => null,
				'href'            => null,
				'title'           => null,
				'lock'            => null,
				'anchor'          => null,
			],
			$node['editorBlocks'][0]['attributes']
		);
	}

	/**
	 * Test that the CoreImage block mediaDetails are retrieved correctly.
	 *
	 * Covers the following attributes:
	 * - height
	 * - width
	 */
	public function test_retrieve_core_image_media_details(): void {
		$block_content = '
			<!-- wp:image {"lightbox":{"enabled":false},"align":"left","width":500,"height":500,"aspectRatio":"4/3","scale":"cover","sizeSlug":"full","linkDestination":"none", "id":' . $this->attachment_id . ',"className":"is-style-rounded", "style":{"color":{"duotone":"var:preset|duotone|purple-green"}},"borderColor":"vivid-red","lock":{"move":true,"remove":true},"className":"test-css-class-name"} -->
				<figure class="wp-block-image size-full is-resized" id="test-anchor">
					<a class="test-link-css-class" href="http://decoupled.local/dcf-1-0/" target="_blank" rel="https://www.youtube.com/ noreferrer noopener">
						<img src="http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg" alt="alt-text" class="wp-image-1432" width="500" height="500" title="test-title"/></figure>
					</a>
				<figcaption class="wp-element-caption">Align left</figcaption>
			<!-- /wp:image -->';

		$query = '
			fragment CoreImageBlockFragment on CoreImage {
				attributes {
					id
				}
				mediaDetails {
					height
					width
				}
			}
		
			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					editorBlocks {
						apiVersion
						blockEditorCategoryName
						name
						innerBlocks {
							name
						}
						...CoreImageBlockFragment
					}
				}
			}
		';

		// Update the post content with the block content.
		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$query     = $query;
		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertEquals(
			[
				'width'  => 50, // Previously untested.
				'height' => 50, // Previously untested.
			],
			$block['mediaDetails']
		);
	}

	/**
	 * Test that the CoreImage block attributes are retrieved correctly.
	 *
	 * Covers the following attributes:
	 * - width
	 * - height
	 * - alt
	 * - id
	 * - src
	 * - style
	 * - sizeSlug
	 * - linkClass
	 * - linkTarget
	 * - linkDestination
	 * - align
	 * - caption
	 * - className
	 * - url
	 * - borderColor
	 * - title
	 * - lock
	 * - anchor
	 * - rel
	 * - href
	 */
	public function test_retrieve_core_image_attributes(): void {

		$block_content = '
			<!-- wp:image {"lightbox":{"enabled":false},"align":"left","width":500,"height":500,"aspectRatio":"4/3","scale":"cover","sizeSlug":"full","linkDestination":"none", "id":' . $this->attachment_id . ',"className":"is-style-rounded", "style":{"color":{"duotone":"var:preset|duotone|purple-green"}},"borderColor":"vivid-red","lock":{"move":true,"remove":true},"className":"test-css-class-name"} -->
				<figure class="wp-block-image size-full is-resized" id="test-anchor">
					<a class="test-link-css-class" href="http://decoupled.local/dcf-1-0/" target="_blank" rel="https://www.youtube.com/ noreferrer noopener">
						<img src="http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg" alt="alt-text" class="wp-image-1432" width="500" height="500" title="test-title"/></figure>
					</a>
				<figcaption class="wp-element-caption">Align left</figcaption>
			<!-- /wp:image -->';

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

		$block = $actual['data']['post']['editorBlocks'][0];

		// WordPress 6.4+ adds layout styles, so `cssClassName` needs to be checked separately.
		$this->assertStringContainsString( 'wp-block-image', $block['attributes']['cssClassName'] );
		$this->assertStringContainsString( 'size-full', $block['attributes']['cssClassName'] );
		$this->assertStringContainsString( 'is-resized', $block['attributes']['cssClassName'] );
		unset( $block['attributes']['cssClassName'] );

		$this->assertEquals( // Previously untested.
			[
				'width'           => '500',
				'height'          => 500.0,
				'alt'             => 'alt-text',
				'id'              => $this->attachment_id,
				'src'             => 'http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg',
				'style'           => wp_json_encode(
					[
						'color' => [
							'duotone' => 'var:preset|duotone|purple-green',
						],
					]
				),
				'sizeSlug'        => 'full',
				'linkClass'       => 'test-link-css-class',
				'linkTarget'      => '_blank',
				'linkDestination' => 'none',
				'align'           => 'left',
				'caption'         => 'Align left',
				'className'       => 'test-css-class-name',
				'url'             => 'http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg',
				'borderColor'     => 'vivid-red',
				'title'           => 'test-title',
				'lock'            => wp_json_encode(
					[
						'move'   => true,
						'remove' => true,
					]
				),
				'anchor'          => 'test-anchor',

				'rel'             => 'https://www.youtube.com/ noreferrer noopener',
				'href'            => 'http://decoupled.local/dcf-1-0/',

			],
			$block['attributes']
		);
	}

	/**
	 * Test that the CoreImage block previously untested attributes are retrieved correctly.
	 *
	 * Covers the following attributes:
	 * - aspectRatio
	 * - scale
	 */
	public function test_retrieve_core_aspectratio_scale_attributes(): void {
		// `aspectRatio` and `scale` are only supported in WP 6.3+.
		if ( ! is_wp_version_compatible( '6.3' ) ) {
			$this->markTestSkipped( 'The aspectRatio and scale attributes are only supported in WP 6.3+' );
		}

		$block_content = '
			<!-- wp:image {"lightbox":{"enabled":false},"align":"left","width":500,"height":500,"aspectRatio":"4/3","scale":"cover","sizeSlug":"full","linkDestination":"none", "id":' . $this->attachment_id . ',"className":"is-style-rounded", "style":{"color":{"duotone":"var:preset|duotone|purple-green"}},"borderColor":"vivid-red","lock":{"move":true,"remove":true},"className":"test-css-class-name"} -->
				<figure class="wp-block-image size-full is-resized" id="test-anchor">
					<a class="test-link-css-class" href="http://decoupled.local/dcf-1-0/" target="_blank" rel="https://www.youtube.com/ noreferrer noopener">
						<img src="http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg" alt="alt-text" class="wp-image-1432" width="500" height="500" title="test-title"/></figure>
					</a>
				<figcaption class="wp-element-caption">Align left</figcaption>
			<!-- /wp:image -->';

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

		$query = '
		fragment CoreImageBlockFragment on CoreImage {
			attributes {
				aspectRatio
				scale
			}
		}

		query Post( $id: ID! ) {
			post(id: $id, idType: DATABASE_ID) {
				databaseId
				editorBlocks {
					name
					...CoreImageBlockFragment
				}
			}
		}';

		$actual = graphql( compact( 'query', 'variables' ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertEquals(
			[
				'aspectRatio' => '4/3', // Previously untested.
				'scale'       => 'cover', // Previously untested.

			],
			$block['attributes']
		);
	}

	/**
	 * Test that the CoreImage block previously untested attributes are retrieved correctly.
	 *
	 * Covers the following attributes:
	 * - lightbox
	 */
	public function test_retrieve_core_lightbox_attribute(): void {
		// `lightbox` is only supported in WP 6.4+.
		if ( ! is_wp_version_compatible( '6.4' ) ) {
			$this->markTestSkipped( 'The lightbox attribute is only supported in WP 6.4+' );
		}

		$block_content = '
			<!-- wp:image {"lightbox":{"enabled":false},"align":"left","width":500,"height":500,"aspectRatio":"4/3","scale":"cover","sizeSlug":"full","linkDestination":"none", "id":' . $this->attachment_id . ',"className":"is-style-rounded", "style":{"color":{"duotone":"var:preset|duotone|purple-green"}},"borderColor":"vivid-red","lock":{"move":true,"remove":true},"className":"test-css-class-name"} -->
				<figure class="wp-block-image size-full is-resized" id="test-anchor">
					<a class="test-link-css-class" href="http://decoupled.local/dcf-1-0/" target="_blank" rel="https://www.youtube.com/ noreferrer noopener">
						<img src="http://mysite.local/wp-content/uploads/2023/05/online-programming-course-hero-section-bg.svg" alt="alt-text" class="wp-image-1432" width="500" height="500" title="test-title"/></figure>
					</a>
				<figcaption class="wp-element-caption">Align left</figcaption>
			<!-- /wp:image -->';

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

		$query = '
		fragment CoreImageBlockFragment on CoreImage {
			attributes {
				lightbox
			}
		}

		query Post( $id: ID! ) {
			post(id: $id, idType: DATABASE_ID) {
				databaseId
				editorBlocks {
					name
					...CoreImageBlockFragment
				}
			}
		}';

		$actual = graphql( compact( 'query', 'variables' ) );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertEquals(
			[
				'lightbox' => wp_json_encode( // Previously untested.
					[
						'enabled' => false,
					]
				),

			],
			$block['attributes']
		);
	}
}
