<?php
/**
 * Core Quote Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Class CoreQuote
 */
class CoreQuote extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = array(
		'cssClassName' => array(
			'type'      => 'string',
			'selector'  => 'pre',
			'source'    => 'attribute',
			'attribute' => 'class',
		),
	);
}
