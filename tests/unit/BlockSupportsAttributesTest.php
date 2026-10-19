<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;

final class BlockSupportsAttributesTest extends PluginTestCase {
	/**
	 * A block that opts into `supports.typography.textAlign` without declaring a
	 * `textAlign` attribute, the way core blocks do from WordPress 7.0.
	 */
	private const BLOCK_NAME = 'wpgraphql-content-blocks-test/supports-text-align';

	public $post_id;

	public function setUp(): void {
		parent::setUp();

		$settings                                 = get_option( 'graphql_general_settings', [] );
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );

		register_block_type(
			self::BLOCK_NAME,
			[
				'supports' => [
					'typography' => [
						'textAlign' => true,
					],
				],
			]
		);

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Supports Attributes',
				'post_content' => trim(
					'
					<!-- wp:wpgraphql-content-blocks-test/supports-text-align {"style":{"typography":{"textAlign":"center"}}} -->
					<p class="has-text-align-center">Aligned from style</p>
					<!-- /wp:wpgraphql-content-blocks-test/supports-text-align -->

					<!-- wp:wpgraphql-content-blocks-test/supports-text-align -->
					<p>Unaligned</p>
					<!-- /wp:wpgraphql-content-blocks-test/supports-text-align -->

					<!-- wp:wpgraphql-content-blocks-test/supports-text-align {"textAlign":"left"} -->
					<p class="has-text-align-left">Saved before the attribute moved to supports</p>
					<!-- /wp:wpgraphql-content-blocks-test/supports-text-align -->

					<!-- wp:quote {"textAlign":"right"} -->
					<blockquote class="wp-block-quote has-text-align-right"><!-- wp:paragraph --><p>Quoted</p><!-- /wp:paragraph --></blockquote>
					<!-- /wp:quote -->
					'
				),
				'post_status'  => 'publish',
			]
		);

		\WPGraphQL::clear_schema();
	}

	public function tearDown(): void {
		wp_delete_post( $this->post_id, true );
		unregister_block_type( self::BLOCK_NAME );
		delete_option( 'graphql_general_settings' );
		\WPGraphQL::clear_schema();

		parent::tearDown();
	}

	/**
	 * Returns the `textAlign` values of the test blocks in the test post, in order.
	 *
	 * @return array<int,mixed>
	 */
	private function get_test_block_text_aligns(): array {
		$type_name = WPGraphQLHelpers::format_type_name( self::BLOCK_NAME );

		$query = '
		query GetPost( $id: ID! ) {
			post( id: $id, idType: DATABASE_ID ) {
				editorBlocks {
					__typename
					... on ' . $type_name . ' {
						attributes {
							textAlign
						}
					}
				}
			}
		}
		';

		$actual = graphql(
			[
				'query'     => $query,
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$this->assertArrayNotHasKey( 'errors', $actual );

		$blocks = array_filter(
			$actual['data']['post']['editorBlocks'],
			static fn ( $block ) => $type_name === $block['__typename']
		);

		return array_values( wp_list_pluck( wp_list_pluck( $blocks, 'attributes' ), 'textAlign' ) );
	}

	/**
	 * The block declares no `textAlign` attribute, but opts into
	 * `supports.typography.textAlign`, so the field must still be present.
	 */
	public function test_supports_derived_field_is_registered() {
		$query = '
		query GetType( $name: String! ) {
			__type( name: $name ) {
				fields {
					name
				}
			}
		}
		';

		$actual = graphql(
			[
				'query'     => $query,
				'variables' => [ 'name' => WPGraphQLHelpers::format_type_name( self::BLOCK_NAME ) . 'Attributes' ],
			]
		);

		$this->assertArrayNotHasKey( 'errors', $actual );

		$field_names = wp_list_pluck( $actual['data']['__type']['fields'], 'name' );

		$this->assertContains( 'textAlign', $field_names, 'The attributes type should expose textAlign via block supports.' );
	}

	/**
	 * The value lives at `style.typography.textAlign`, not at the top level.
	 */
	public function test_supports_derived_value_resolves_from_style() {
		$text_aligns = $this->get_test_block_text_aligns();

		$this->assertSame( 'center', $text_aligns[0] );
		$this->assertNull( $text_aligns[1], 'A block with no alignment should resolve null, not a default.' );
	}

	/**
	 * Content saved before the attribute moved to supports keeps a top-level
	 * `textAlign` until it is re-saved in the editor, and should still resolve.
	 */
	public function test_supports_derived_value_falls_back_to_top_level_attribute() {
		$text_aligns = $this->get_test_block_text_aligns();

		$this->assertSame( 'left', $text_aligns[2] );
	}

	/**
	 * `core/quote` still declares its own `textAlign` attribute, so the
	 * supports-derived config must not shadow it.
	 */
	public function test_declared_attribute_is_not_shadowed_by_supports() {
		$query = '
		query GetPost( $id: ID! ) {
			post( id: $id, idType: DATABASE_ID ) {
				editorBlocks {
					__typename
					... on CoreQuote {
						attributes {
							textAlign
						}
					}
				}
			}
		}
		';

		$actual = graphql(
			[
				'query'     => $query,
				'variables' => [ 'id' => $this->post_id ],
			]
		);

		$this->assertArrayNotHasKey( 'errors', $actual );

		$quotes = array_values(
			array_filter(
				$actual['data']['post']['editorBlocks'],
				static fn ( $block ) => 'CoreQuote' === $block['__typename']
			)
		);

		$this->assertSame( 'right', $quotes[0]['attributes']['textAlign'] );
	}
}
