<?php
/**
 * Core List Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WPGraphQL\ContentBlocks\Registry\Registry;
use WP_Block_Type;

/**
 * Class CoreList
 */
class CoreList extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'selector'  => 'ul,ol',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
	];

	/**
	 * Block constructor.
	 *
	 * @param \WP_Block_Type                             $block The Block Type.
	 * @param \WPGraphQL\ContentBlocks\Registry\Registry $block_registry The instance of the WPGraphQL block registry.
	 */
	public function __construct( WP_Block_Type $block, Registry $block_registry ) {
		parent::__construct( $block, $block_registry );

		register_graphql_field(
			$this->type_name,
			'ordered',
			[
				'type'        => 'Boolean',
				'description' => sprintf(
					__( 'Whether the list is ordered or unordered', 'wp-graphql-content-blocks' ),
					$this->type_name
				),
				'resolve'     => static function ( $block ) {
					return $block['attrs']['ordered'] ?? false;
				},
			]
		);

		register_graphql_field(
			$this->type_name,
			'items',
			[
				'type'        => [ 'list_of' => 'CoreListItem' ],
				'description' => sprintf(
					__( 'Whether list items', 'wp-graphql-content-blocks' ),
					$this->type_name
				),
				'resolve'     => static function ( $block ) {
					return self::resolveInnerBlocks( $block['innerBlocks'] );
				},
			]
		);
	}

	/**
	 * @param array $inner_blocks An array of inner blocks.
	 *
	 * @return array An array of parsed inner blocks.
	 */
	public static function resolveInnerBlocks( array $inner_blocks ): array {

		$items = [];

		foreach ( $inner_blocks as $block ) {
			switch ( $block['blockName'] ) {
				case 'core/list-item':
					$items[] = [
						'value'    => trim( $block['innerHTML'], "\n" ),
						'children' => self::resolveInnerBlocks( $block['innerBlocks'] ),
					];
					break;

				case 'core/list':
					$nested_items = self::resolveInnerBlocks( $block['innerBlocks'] );
					$items        = array_merge( $items, $nested_items );
					break;
			}
		}

		return $items;
	}
}
