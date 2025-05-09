<?php
/**
 * Core Post Terms Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WPGraphQL\AppContext;
use WPGraphQL\ContentBlocks\Registry\Registry;
use WPGraphQL\Data\Connection\TaxonomyConnectionResolver;
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

		$this->register_fields();
		$this->register_connections();
	}

	/**
	 * Registers custom fields for the block.
	 */
	private function register_fields(): void {
		register_graphql_fields(
			$this->type_name,
			[
				'prefix' => [
					'type'        => 'String',
					'description' => __( 'Prefix to display before the post terms', 'wp-graphql-content-blocks' ),
					'resolve'     => static fn ( $block ) => isset( $block['attrs']['prefix'] ) ? (string) $block['attrs']['prefix'] : null,
				],
				'suffix' => [
					'type'        => 'String',
					'description' => __( 'Suffix to display after the post terms', 'wp-graphql-content-blocks' ),
					'resolve'     => static fn ( $block ) => isset( $block['attrs']['suffix'] ) ? (string) $block['attrs']['suffix'] : null,
				],
			]
		);
	}

	/**
	 * Registers a list of terms field for the block.
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function register_connections() {
		// Register connection to terms.
		register_graphql_connection(
			[
				'fromType'      => $this->type_name,
				'toType'        => 'TermNode',
				'fromFieldName' => 'terms',
				'resolve'       => static function ( $block, array $args, AppContext $context, $info ) {
					$taxonomy = $block['attrs']['term'] ?? null;
					if ( empty( $taxonomy ) ) {
						return null;
					}

					$post_id = get_the_ID();
					if ( ! $post_id ) {
						return null;
					}

					$args['where']['objectIds'] = $post_id;
					$resolver                   = new TermObjectConnectionResolver( $block, $args, $context, $info, $taxonomy );

					return $resolver->get_connection();
				},
			]
		);

		// Register connection to the taxonomy.
		register_graphql_connection(
			[
				'fromType'      => $this->type_name,
				'toType'        => 'Taxonomy',
				'fromFieldName' => 'taxonomy',
				'oneToOne'      => true,
				'resolve'       => static function ( $block, array $args, AppContext $context, $info ) {
					$taxonomy = $block['attrs']['term'] ?? null;
					if ( empty( $taxonomy ) ) {
						return null;
					}

					$resolver = new TaxonomyConnectionResolver( $block, $args, $context, $info );
					$resolver->set_query_arg( 'name', $taxonomy );

					return $resolver->one_to_one()->get_connection();
				},
			]
		);
	}
}
