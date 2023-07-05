<?php
/**
 * Core Heading Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Class CoreHeading
 */
class CoreHeading extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'selector'  => 'h1, h2, h3, h4, h5, h6',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
	];
}
