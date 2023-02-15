<?php
namespace WPGraphQL\ContentBlocks\Blocks;

class CoreParagraph extends Block {
	protected ?array $additional_block_attributes = array(
		'cssClassName' => array(
			'type'      => 'string',
			'selector'  => 'p',
			'source'    => 'attribute',
			'attribute' => 'class',
		),
		'content'      => array(
			'type'     => 'string',
			'selector' => 'p',
			'source'   => 'html',
		),
	);
}
