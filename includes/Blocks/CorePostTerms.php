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
	 * String fields for the block.
	 */
	public const STRING_FIELDS = [ 'prefix', 'suffix', 'term' ];

	/**
	 * {@inheritDoc}
	 */
	public function __construct( WP_Block_Type $block, Registry $block_registry ) {
		parent::__construct( $block, $block_registry );

		foreach ( self::STRING_FIELDS as $field ) {
			$this->register_string_field( $field );
		}

		$this->register_list_of_terms_field();
	}

	/**
	 * Registers a string field for the block.
	 *
	 * @param string $name The name of the field.
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function register_string_field( $name ) {
		register_graphql_field(
			$this->type_name,
			$name,
			[
				'type'        => 'String',
				'description' => sprintf(
					// translators: %1$s is the field name, %2$s is the block type name.
					__( '%1$s of the "%2$s" Block Type', 'wp-graphql-content-blocks' ),
					ucfirst( $name ),
					$this->type_name
				),
				'resolve'     => static function ( $block ) use ( $name ) {
					return isset( $block['attrs'][ $name ] ) ? (string) $block['attrs'][ $name ] : null;
				},
			]
		);
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
