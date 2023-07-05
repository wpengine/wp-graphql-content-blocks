<?php
/**
 * Core Paragraph Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

/**
 * Class CoreParagraph
 */
class CoreParagraph extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'selector'  => 'p',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
		'content'      => [
			'type'     => 'string',
			'selector' => 'p',
			'source'   => 'html',
		],
	];
}
