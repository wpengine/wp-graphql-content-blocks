<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class CoreVideoTest extends PluginTestCase {
	public $instance;

	/**
	 * The post ID.
	 *
	 * @var int
	 */
	public $post_id;

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

	public function query(): string {
		return '
			fragment CoreVideoBlockFragment on CoreVideo {
				attributes {
					align
					anchor
					autoplay
					className
					lock
					tracks
					muted
					caption
					preload
					src
					style
					playsInline
					controls
					loop
					poster
					id
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
						name
						innerBlocks{
							name
						}
						parentClientId
						renderedHtml
						type
						...CoreVideoBlockFragment
					}
				}
			}';
	}

	/**
	 * Test to retrieve core video block attributes.
	 *
	 * Covers:
	 * - align
	 * - anchor
	 * - autoplay
	 * - caption
	 * - className
	 * - controls
	 * - id
	 * - lock
	 * - loop
	 * - muted
	 * - playsInline
	 * - poster
	 * - preload
	 * - src
	 * - style
	 */
	public function test_retrieve_core_video_attributes(): void {
		$block_content = '
			<!-- wp:video {"id":1636,"align":"wide","lock":{"move":true,"remove":true},"className":"test-css-class","style":{"spacing":{"margin":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|10","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}}} -->
			<figure id="test-anchor" class="wp-block-video" style="margin-top:var(--wp--preset--spacing--50);margin-right:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--50);margin-left:var(--wp--preset--spacing--50)">
			<video autoplay controls loop muted poster="http://mysite.local/wp-content/uploads/2023/05/pexels-egor-komarov-14420089-scaled.jpg" preload="auto" src="http://mysite.local/wp-content/uploads/2023/07/pexels_videos_1860684-1440p.mp4" playsinline></video>
			<figcaption class="wp-element-caption">Sample caption</figcaption></figure>
			<!-- /wp:video -->';

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
		$actual    = graphql( compact( 'query', 'variables' ) );

		$block = $actual['data']['post'];

		$this->assertArrayNotHasKey( 'errors', $actual, 'There should not be any errors' );
		$this->assertArrayHasKey( 'data', $actual, 'The data key should be present' );
		$this->assertArrayHasKey( 'post', $actual['data'], 'The post key should be present' );

		$this->assertEquals( $this->post_id, $actual['data']['post']['databaseId'], 'The post ID should match' );

		$this->assertEquals( 1, count( $actual['data']['post']['editorBlocks'] ) );

		$block = $actual['data']['post']['editorBlocks'][0];

		$this->assertNotEmpty( $block['apiVersion'], 'The apiVersion should be present' );
		$this->assertEquals( 'media', $block['blockEditorCategoryName'], 'The blockEditorCategoryName should be text' );
		$this->assertNotEmpty( $block['clientId'], 'The clientId should be present' );
		$this->assertNotEmpty( $block['cssClassNames'], 'The cssClassNames should be present' );
		$this->assertEmpty( $block['innerBlocks'], 'There should be no inner blocks' );
		$this->assertEquals( 'core/video', $block['name'], 'The block name should be core/video' );
		$this->assertEquals( 'CoreVideo', $block['type'], 'The block type should be CoreVideo' );
		$this->assertEmpty( $block['parentClientId'], 'There should be no parentClientId' );
		$this->assertNotEmpty( $block['renderedHtml'], 'The renderedHtml should be present' );

		$this->assertEquals(
			[
				'align'       => 'wide',
				'anchor'      => 'test-anchor',
				'autoplay'    => true,
				'tracks'      => [],
				'muted'       => true,
				'caption'     => 'Sample caption',
				'className'   => 'test-css-class',
				'preload'     => 'auto',
				'src'         => 'http://mysite.local/wp-content/uploads/2023/07/pexels_videos_1860684-1440p.mp4',
				'style'       => wp_json_encode(
					[
						'spacing' => [
							'margin' => [
								'top'    => 'var:preset|spacing|10',
								'bottom' => 'var:preset|spacing|10',
								'left'   => 'var:preset|spacing|20',
								'right'  => 'var:preset|spacing|20',
							],
						],
					]
				),
				'playsInline' => true,
				'controls'    => true,
				'loop'        => true,
				'lock'        => wp_json_encode(
					[
						'move'   => true,
						'remove' => true,
					]
				),
				'poster'      => 'http://mysite.local/wp-content/uploads/2023/05/pexels-egor-komarov-14420089-scaled.jpg',
				'id'          => 1636.0,
			],
			$block['attributes']
		);
	}

	/**
	 * Test to retrieve core video 'tracks' attribute.
	 *
	 * Covers `tracks`
	 */
	public function test_retrieve_core_video_tracks_attribute(): void {
		$block_content = '
			<!-- wp:video {"id":1636,"tracks":[{"src":"https://example.com/subtitles.vtt","kind":"subtitles","label":"English","srclang":"en"}],"lock":{"move":true,"remove":true}} -->
				<figure class="wp-block-video">
					<video src="http://mysite.local/wp-content/uploads/2023/07/pexels_videos_1860684-1440p.mp4" playsinline></video>
				</figure>
			<!-- /wp:video -->';

		// Update the post content with the block content.
		wp_update_post(
			[
				'ID'           => $this->post_id,
				'post_content' => $block_content,
			]
		);

		$query = '
			fragment CoreVideoBlockFragment on CoreVideo {
				attributes {
						tracks
					}
				}

			query Post( $id: ID! ) {
				post(id: $id, idType: DATABASE_ID) {
					databaseId
					editorBlocks {
						name
						...CoreVideoBlockFragment
					}
				}
			}';

		$actual    = graphql( [ 'query' => $query ] );
		$variables = [
			'id' => $this->post_id,
		];

		$actual = graphql( compact( 'query', 'variables' ) );

		$block  = $actual['data']['post']['editorBlocks'][0];
		$tracks = $block['attributes']['tracks'];

		$this->assertCount( 1, $tracks );
		$this->assertEquals(
			wp_json_encode( // Previously untested.
				[
					'src'     => 'https://example.com/subtitles.vtt',
					'kind'    => 'subtitles',
					'label'   => 'English',
					'srclang' => 'en',
				]
			),
			$tracks[0]
		);
	}
}
