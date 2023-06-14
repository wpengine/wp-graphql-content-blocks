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
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = array(
		'cssClassName'  => array(
			'type'      => 'string',
			'selector'  => 'div',
			'source'    => 'attribute',
			'attribute' => 'class',
		),
		'linkClassName' => array(
			'type'      => 'string',
			'selector'  => 'a',
			'source'    => 'attribute',
			'attribute' => 'class',
		),
	);
}
