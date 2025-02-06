<?php
/**
 * Core Image Block
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WPGraphQL\ContentBlocks\Registry\Registry;
use WP_Block_Type;

/**
 * Class CoreImage
 */
class CoreImage extends Block {
	/**
	 * {@inheritDoc}
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes = [
		'cssClassName' => [
			'type'      => 'string',
			'selector'  => 'figure',
			'source'    => 'attribute',
			'attribute' => 'class',
		],
		'src'          => [
			'type'      => 'string',
			'selector'  => 'img',
			'source'    => 'attribute',
			'attribute' => 'src',
		],
	];

	/**
	 * Block constructor.
	 *
	 * @param \WP_Block_Type                             $block The Block Type.
	 * @param \WPGraphQL\ContentBlocks\Registry\Registry $block_registry The instance of the WPGraphQL block registry.
	 */
	public function __construct( WP_Block_Type $block, Registry $block_registry ) {
		parent::__construct( $block, $block_registry );
		register_graphql_field(
			$this->type_name,
			'mediaDetails',
			[
				'type'        => 'MediaDetails',
				'description' => sprintf(
					// translators: %s is the block type name.
					__( 'Media Details of the %s Block Type', 'wp-graphql-content-blocks' ),
					$this->type_name
				),
				'resolve'     => static function ( $block ) {
					$attrs = $block['attrs'];
					$id    = $attrs['id'] ?? null;
					if ( $id ) {
						$media_details = wp_get_attachment_metadata( $id );
						if ( ! empty( $media_details ) ) {
							$media_details['ID'] = $id;

							return $media_details;
						}
					}
					return null;
				},
			]
		);
	}
}
