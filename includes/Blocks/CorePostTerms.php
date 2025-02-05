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
	 * {@inheritDoc}
	 */
	public function __construct( WP_Block_Type $block, Registry $block_registry ) {
		parent::__construct( $block, $block_registry );

		register_graphql_fields(
			$this->type_name,
			[
				'prefix' => $this->get_string_field_config( 'prefix' ),
				'suffix' => $this->get_string_field_config( 'suffix' ),
				'term'   => $this->get_string_field_config( 'term' ),
			]
		);

		$this->register_list_of_terms_field();
	}

	/**
	 * Gets a string field for the block.
	 *
	 * @param string $name The name of the field.
	 */
	private function get_string_field_config( string $name ): array {
		return [
			'type'        => 'String',
			'description' => sprintf(
				// translators: %1$s is the field name, %2$s is the block type name.
				__( '%1$s of the "%2$s" Block Type', 'wp-graphql-content-blocks' ),
				ucfirst( $name ),
				$this->type_name
			),
			'resolve'     => static fn ( $block ) => isset( $block['attrs'][ $name ] ) ? (string) $block['attrs'][ $name ] : null,
		];
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
