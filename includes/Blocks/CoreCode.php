<?php
/**
 * Core Code Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Class CoreCode
 */
class CoreCode extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'selector'  => 'pre',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
		'content'      => [
			'type'     => 'string',
			'selector' => 'code',
			'source'   => 'html',
			'default'  => '',
		],
	];
}
