<?php
/**
 * Core List Item Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WPGraphQL\ContentBlocks\Registry\Registry;
use WP_Block_Type;

/**
 * Class CoreListItem
 */
class CoreListItem extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'selector'  => 'li',
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
			'value',
			[
				'type'        => 'String',
				'description' => sprintf(
					__( 'The content of the list item', 'wp-graphql-content-blocks' ),
					$this->type_name
				),
			]
		);

		register_graphql_field(
			$this->type_name,
			'children',
			[
				'type'        => [ 'list_of' => 'CoreListItem' ],
				'description' => sprintf(
					__( 'The content of the list item', 'wp-graphql-content-blocks' ),
					$this->type_name
				),
			]
		);
	}
}
