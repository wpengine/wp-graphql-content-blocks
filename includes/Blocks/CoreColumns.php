<?php
/**
 * Core Columns Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Class CoreColumns
 */
class CoreColumns extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'selector'  => 'div',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
	];
}
