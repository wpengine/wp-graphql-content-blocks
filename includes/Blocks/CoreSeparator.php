<?php
/**
 * Core Separator Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Class Separator
 */
class CoreSeparator extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array
	 */
	protected array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'selector'  => 'hr',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
	];
}
