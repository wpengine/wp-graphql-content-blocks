<?php

namespace WPGraphQL\ContentBlocks\Blocks;

class CoreImage extends Block {

	protected ?array $additional_block_attributes = array(
		'cssClassName' => array(
			'type'      => 'string',
			'selector'  => 'figure',
			'source'    => 'attribute',
			'attribute' => 'class',
		),
		'src'       => array(
			'type'      => 'string',
			'selector'  => 'img',
			'source'    => 'attribute',
			'attribute' => 'src',
		),
	);
}
