<?php
/**
 * Core Group Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Class CoreGroup
 */
class CoreGroup extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * Note that no selector is set as it can be a variety of selectors
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
	];
}
