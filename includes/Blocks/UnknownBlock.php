<?php
namespace WPGraphQL\ContentBlocks\Blocks;

class UnknownBlock extends Block {
	protected ?array $additional_block_attributes = array(
		'style'        => array(
			'type'      => 'string',
			'selector'  => 'p',
			'source'    => 'attribute',
			'attribute' => 'style',
		),
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
