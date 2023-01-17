<?php
namespace WPGraphQL\ContentBlocks\Blocks;

class CoreCode extends Block {

	protected ?array $additional_block_attributes = array(
		'style'     => array(
			'type'      => 'string',
			'selector'  => 'pre',
			'source'    => 'attribute',
			'attribute' => 'style',
		),
        'cssClassName' => array(
			'type'      => 'string',
			'selector'  => 'pre',
			'source'    => 'attribute',
			'attribute' => 'class',
		),
	);
}
