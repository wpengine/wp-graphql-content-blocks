<?php
/**
 * Core Post Terms Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WPGraphQL\AppContext;
use WPGraphQL\ContentBlocks\Registry\Registry;
use WPGraphQL\Data\Connection\TermObjectConnectionResolver;
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
		register_graphql_connection(
			[
				'fromType'      => $this->type_name,
				'toType'        => 'TermNode',
				'fromFieldName' => 'terms',
				'resolve'       => static function ( $block, array $args, AppContext $context, $info ) {
					$term = $block['attrs']['term'] ?? null;
					if ( empty( $term ) ) {
						return null;
					}

					$post_id = get_the_ID();
					if ( ! $post_id ) {
						return null;
					}

					$resolver = new TermObjectConnectionResolver( $block, $args, $context, $info, $term );
					$resolver->set_query_arg( 'object_ids', $post_id );

					return $resolver->get_connection();
				},
			]
		);
	}
}
