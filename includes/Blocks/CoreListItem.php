<?php
/**
 * Core List Item Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

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
}
