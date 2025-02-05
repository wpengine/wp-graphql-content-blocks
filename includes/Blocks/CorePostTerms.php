<?php
/**
 * Core Post Terms Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WPGraphQL\ContentBlocks\Registry\Registry;
use WPGraphQL\Model\Term;
use WP_Block_Type;

/**
 * Class CorePostTerms.
 */
class CorePostTerms extends Block {
	/**
	 * {@inheritDoc}
	 */
	public function __construct( WP_Block_Type $block, Registry $block_registry ) {
		parent::__construct( $block, $block_registry );

		$this->register_list_of_terms_field();
	}

	/**
	 * Registers a list of terms field for the block.
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function register_list_of_terms_field() {
		register_graphql_field(
			$this->type_name,
			'terms',
			[
				'type'        => [ 'list_of' => 'TermNode' ],
				'description' => __( 'The terms associated with the post.', 'wp-graphql-content-blocks' ),
				'resolve'     => static function ( $block ) {
					$term = $block['attrs']['term'] ?? null;
					if ( empty( $term ) ) {
						return null;
					}

					$id = get_the_ID();
					if ( ! $id ) {
						return null;
					}

					$terms = get_the_terms( $id, $term );
					if ( empty( $terms ) || is_wp_error( $terms ) ) {
						return null;
					}

					return array_map( static fn ( $term ) => new Term( $term ), $terms );
				},
			]
		);
	}
}
