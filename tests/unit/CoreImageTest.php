<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreImageTest extends PluginTestCase {
	public $instance;

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

		parent::tearDown();

		\WPGraphQL::clear_schema();
	}

	public function test_retrieve_core_image_media_details(): void {
		$block_content = '<!-- wp:image {"lightbox":{"enabled":false},"align":"left","width":500,"height":500,"aspectRatio":"4/3","scale":"cover","sizeSlug":"full","linkDestination":"none", "id":' . $this->attachment_id . ',"className":"is-style-rounded", "style":{"color":{"duotone":"var:preset|duotone|purple-green"}},"borderColor":"vivid-red","lock":{"move":true,"remove":true},"className":"test-css-class-name"} -->
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
                        # databaseId
                        apiVersion
                        blockEditorCategoryName
                        clientId
                        cssClassNames
                        name
                        innerBlocks {
                            name
                        }
                        parentClientId
                        renderedHtml
                        ...CoreImageBlockFragment
                    }
                }
            }
        ';

		// Set post content.
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
		error_log( print_r( $actual, true ) );
		// $actual   = $actual['data']['post'];

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		// @todo : fix
		// $this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'media', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );

		// @todo this is not working
		$this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );

		$this->assertEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/image', $block['name'], 'The block name should be core/image' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		$this->assertEquals(
			[
				'width'  => 50,
				'height' => 50,
			],
			$block['mediaDetails']
		);
	}

	public function test_retrieve_core_image_attributes(): void {

		$block_content = '<!-- wp:image {"lightbox":{"enabled":false},"align":"left","width":500,"height":500,"aspectRatio":"4/3","scale":"cover","sizeSlug":"full","linkDestination":"none", "id":' . $this->attachment_id . ',"className":"is-style-rounded", "style":{"color":{"duotone":"var:preset|duotone|purple-green"}},"borderColor":"vivid-red","lock":{"move":true,"remove":true},"className":"test-css-class-name"} -->
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
                    width
                    height
                    alt
                    align
                    src
                    style
                    sizeSlug
                    # lightbox # not supported yet
                    # aspectRatio # not supported yet
                    # scale # not supported yet
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
                        parentClientId
                        renderedHtml
                        name
                        ...CoreImageBlockFragment
                    }
                }
            }
        ';

		// Set post content.
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

		error_log( print_r( $actual, true ) );
		$node = $actual['data']['post'];

		// Verify that the ID of the first post matches the one we just created.
		$this->assertEquals( $this->post_id, $node['databaseId'] );
		// There should be only one block using that query when not using flat: true
		$this->assertEquals( 1, count( $node['editorBlocks'] ) );
		$this->assertEquals( 'core/image', $node['editorBlocks'][0]['name'] );

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'media', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be media' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );

		// @todo this is not working
		// $this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );

		$this->assertEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/image', $block['name'], 'The block name should be core/image' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );
		$this->assertEquals(
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
				'cssClassName'    => ( ! is_wp_version_compatible( '6.3' ) ) ? 'wp-duotone-varpresetduotonepurple-green-19 wp-block-image size-full is-resized  wp-duotone-purple-green' : 'wp-block-image size-full is-resized wp-duotone-purple-green', // This uses the old class name for WP < 6.3 which is wp-duotone-varpresetduotonepurple-green-19.
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
			$node['editorBlocks'][0]['attributes']
		);
	}
}
