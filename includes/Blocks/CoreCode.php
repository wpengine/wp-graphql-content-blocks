<?php
/**
 * Add additional block attributes to the CorCode block to allow it to work properly with the WPGraphQL Schema.
 *
 * @package WPGraphQL\ContentBlocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Add additional block attributes to the CorCode block to allow it to work properly with the WPGraphQL Schema.
 *
 * @package WPGraphQL\ContentBlocks
 */
class CoreCode extends Block {

	/**
	 * Add block attributes so the block can work well with WPGraphQL.
	 *
	 * @var array|string[][]|null
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
