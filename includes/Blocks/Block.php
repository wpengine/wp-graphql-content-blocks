<?php

namespace WPGraphQL\ContentBlocks\Blocks;

use DiDom\Document;
use GraphQL\Type\Definition\ResolveInfo;
use WP_Block_Type;
use WPGraphQL\AppContext;
use WPGraphQL\ContentBlocks\Registry\Registry;
use WPGraphQL\Utils\Utils;

/**
 * Class Block
 *
 * Handles mapping a WP_Block_Type to the WPGraphQL Schema
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */
class Block {

	/**
	 * The Block Type
	 *
	 * @var WP_Block_Type
	 */
	protected WP_Block_Type $block;

	/**
	 * @var string
	 */
	protected string $type_name;

	/**
	 * @var Registry
	 */
	protected Registry $block_registry;

	/**
	 * The attributes of the block
	 *
	 * @var array|null
	 */
	protected ?array $block_attributes;

	/**
	 * Any Additional attributes of the block not defined in block.json
	 *
	 * @var array|null
	 */
	protected ?array $additional_block_attributes;

	/**
	 * Block constructor.
	 *
	 * @param WP_Block_Type $block
	 * @param Registry      $block_registry
	 */
	public function __construct( WP_Block_Type $block, Registry $block_registry ) {
		
		$this->block          = $block;
		$this->block_registry = $block_registry;
		$this->block_attributes = $this->block->attributes;

		// Format the type name for showing in the GraphQL Schema
		// @todo: WPGraphQL utility function should handle removing the '/' by default.
		$type_name       = lcfirst( ucwords( $block->name, '/' ) );
		$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
		$type_name = Utils::format_type_name( $type_name );
		$this->type_name = Utils::format_type_name( $type_name );
		$this->register_block_attributes_as_fields();
		$this->register_fields();
		$this->register_type();
	}

	public function register_block_attributes_as_fields() {
		if (isset( $this->additional_block_attributes)) {
			$block_attribute_fields = $this->get_block_attribute_fields(array_merge($this->block_attributes, $this->additional_block_attributes));
		} else {
			$block_attribute_fields = $this->get_block_attribute_fields($this->block_attributes);
		}

		if ( ! empty( $block_attribute_fields ) ) {

			$block_attribute_type_name = $this->type_name . 'Attributes';
			register_graphql_object_type( $block_attribute_type_name, [
				'description' => __( 'Attributes of the %s Block Type', 'wp-graphql-content-blocks' ),
				'fields' => $block_attribute_fields,
			]);

			register_graphql_field( $this->type_name, 'attributes', [
				'type' => $block_attribute_type_name,
				'description' => __( 'Attributes of the %s Block Type', 'wp-graphql-content-blocks' ),
				'resolve' => function( $block ) {
					return $block;
				}
			]);

		}
	}

	public function get_block_attribute_fields($block_attributes) {
		$block_attribute_fields = [];
		if ( isset( $block_attributes ) ) {
			foreach ( $block_attributes as $attribute_name => $attribute_config ) {
				$graphql_type = null;
				if ( ! isset( $attribute_config['type'] ) ) {
					return $block_attribute_fields;
				}

				switch ( $attribute_config['type'] ) {
					case 'string':
						$graphql_type = 'String';
						break;
					case 'number':
						$graphql_type = 'Int';
						break;
					case 'boolean':
						$graphql_type = 'Boolean';
						break;
				}


				if ( empty( $graphql_type ) ) {
					continue;
				}

				$block_attribute_fields[ Utils::format_field_name( $attribute_name ) ] = [
					'type' => $graphql_type,
					'description' => __( sprintf( 'The "%1$s" field on the "%2$s" block', $attribute_name, $this->type_name ), 'wp-graphql' ),
					'resolve' => function( $block, $args, $context, $info ) use ( $attribute_name, $attribute_config ) {
						if ( isset( $attribute_config['selector'], $attribute_config['source'] ) ) {
							
							$rendered_block = wp_unslash( render_block( $block ) );
							$value = null;
							switch ( $attribute_config['source'] ) {
								case 'attribute':

									if ( empty( $rendered_block ) ) {
										$value = null;
										break;
									}
									$doc = new Document();
									$doc->loadHTML( $rendered_block );
									$node = $doc->find( $attribute_config['selector'] );
									$default = isset( $attribute_config['default'] ) ? $attribute_config['default'] : null;
									$value = $node[0] ? $node[0]->getAttribute( $attribute_config['attribute'] ) : $default;
									break;
								case 'html':

									if ( empty( $rendered_block ) ) {
										$value = null;
										break;
									}

									$doc = new Document();
									$doc->loadHTML( $rendered_block );
									$node = $doc->find( $attribute_config['selector'] );
									$inner_html = isset( $attribute_config['default'] ) ? $attribute_config['default'] : '';
									foreach ( $node as $elem ) {
										$inner_html .= $elem->innerHTML();
									}			
									return $inner_html;

							}

							return $value;

						}

						return $block['attrs'][ $attribute_name ] ?? null;
					}
				];
			}
		}
		return $block_attribute_fields;
	}

	/**
	 * Register fields to the Block
	 *
	 * @return void
	 */
	public function register_fields() {

	}

	/**
	 * Register array type attributes to the block
	 *
	 * @return void
	 */
	public function register_array_attribute_fields($attribute_name, $attribute_config) {

	}

	/**
	 * Register the Type for the block
	 *
	 * @return void
	 */
	public function register_type() {

		/**
		 * Register the Block Object Type to the Schema
		 */
		register_graphql_object_type( $this->type_name, [
			'description' => __( 'A block used for editing the site', 'wp-graphql-content-blocks' ),
			'interfaces'  => [ 'ContentBlock' ],
			'eagerlyLoadType' => true,
			'fields'      => [
				'name' => [
					'type'        => [ 'non_null' => 'String' ],
					'description' => __( 'The name of the block', 'wp-graphql-content-blocks' ),
					'resolve'     => function( $block, array $args, AppContext $context, ResolveInfo $info ) {
						return $this->resolve( $block, $args, $context, $info );
					}
				]
			]
		] );

	}

	public function resolve( $block, array $args, AppContext $context, ResolveInfo $info ) {
		return isset( $block['blockName'] ) ? $block['blockName'] : '';
	}
}