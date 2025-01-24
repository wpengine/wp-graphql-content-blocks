<?php
/**
 * Core Button Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Class CoreButton
 */
class CoreButton extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array
	 */
	protected array $additional_block_attributes = [
		'cssClassName'  => [
			'type'      => 'string',
			'selector'  => 'div',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
		'linkClassName' => [
			'type'      => 'string',
			'selector'  => 'a',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
	];
}
