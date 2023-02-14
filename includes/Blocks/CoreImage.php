<?php

namespace WPGraphQL\ContentBlocks\Blocks;

class CoreImage extends Block {

	protected $additional_block_attributes = array(
		'style'     => array(
			'type'      => 'string',
			'selector'  => 'figure',
			'source'    => 'attribute',
			'attribute' => 'style',
		),
		'className' => array(
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
