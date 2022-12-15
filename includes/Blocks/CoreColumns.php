<?php
namespace WPGraphQL\ContentBlocks\Blocks;

class CoreColumns extends Block {

	protected ?array $additional_block_attributes = array(
		'cssClassName' => array(
			'type'      => 'string',
			'selector'  => 'div',
			'source'    => 'attribute',
			'attribute' => 'class',
		),
	);
}
