<?php

namespace WPGraphQL\ContentBlocks\Blocks;

use GraphQL\Type\Definition\ResolveInfo;
use WP_Block_Type;
use WPGraphQL\AppContext;
use WPGraphQL\ContentBlocks\Registry\Registry;
use WPGraphQL\ContentBlocks\Utilities\DOMHelpers;
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
		$this->block            = $block;
		$this->block_registry   = $block_registry;
		$this->block_attributes = $this->block->attributes;
		$this->type_name        = $this->format_type_name( $block->name );
		$this->update_scalars();
		$this->register_block_type();
	}

	/**
	 * Update scaler types
	 * 
	 * @return void
	 */
	public function update_scalars() {
		Registry::register_graphql_scalar( 'Block', [ 
			'serialize' => function ( $value ) {
				return json_encode( $value );
			}
		]);
	}

	/**
	 * Template Method to Register fields to the Block
	 *
	 * @return void
	 */
	public function register_fields() {     }
	/**
	 * Formats the name of the block for the GraphQL registry
	 *
	 * @param String $name The name of the block
	 * @return String
	 */

	private function format_type_name( $name ) {
		// Format the type name for showing in the GraphQL Schema
		// @todo: WPGraphQL utility function should handle removing the '/' by default.
		$type_name = lcfirst( ucwords( $name, '/' ) );
		$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
		$type_name = Utils::format_type_name( $type_name );
		return Utils::format_type_name( $type_name );
	}

	private function register_block_type() {
		$this->register_block_attributes_as_fields();
		$this->register_fields();
		$this->register_type();
	}

	private function register_block_attributes_as_fields() {
		if ( isset( $this->additional_block_attributes ) ) {
			$block_attribute_fields = $this->get_block_attribute_fields( array_merge( $this->block_attributes, $this->additional_block_attributes ) );
		} else {
			$block_attribute_fields = $this->get_block_attribute_fields( $this->block_attributes );
		}

		if ( ! empty( $block_attribute_fields ) ) {
			$block_attribute_type_name = $this->type_name . 'Attributes';
			register_graphql_object_type(
				$block_attribute_type_name,
				array(
					'description' => __( 'Attributes of the %s Block Type', 'wp-graphql-content-blocks' ),
					'fields'      => $block_attribute_fields,
				)
			);

			register_graphql_field(
				$this->type_name,
				'attributes',
				array(
					'type'        => $block_attribute_type_name,
					'description' => __( 'Attributes of the %s Block Type', 'wp-graphql-content-blocks' ),
					'resolve'     => function ( $block ) {
						return $block;
					},
				)
			);
		}//end if
	}

	private function get_block_attribute_fields( $block_attributes ) {
		$block_attribute_fields = array();
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
						$graphql_type = 'Float';
						break;
					case 'integer':
						$graphql_type = 'Int';
						break;
					case 'boolean':
						$graphql_type = 'Boolean';
						break;
					case 'object':
						$graphql_type = 'Object';
						break;
				}

				if ( empty( $graphql_type ) ) {
					continue;
				}

				$block_attribute_fields[ Utils::format_field_name( $attribute_name ) ] = array(
					'type'        => $graphql_type,
					'description' => __( sprintf( 'The "%1$s" field on the "%2$s" block', $attribute_name, $this->type_name ), 'wp-graphql' ),
					'resolve'     => function ( $block, $args, $context, $info ) use ( $attribute_name, $attribute_config ) {
						return $this->resolve_block_attributes( $block, $attribute_name, $attribute_config );
					},
				);
			}//end foreach
		}//end if
		return $block_attribute_fields;
	}

	/**
	 * Register the Type for the block
	 *
	 * @return void
	 */
	private function register_type() {
		/**
		 * Register the Block Object Type to the Schema
		 */
		register_graphql_object_type(
			$this->type_name,
			array(
				'description'     => __( 'A block used for editing the site', 'wp-graphql-content-blocks' ),
				'interfaces'      => array( 'ContentBlock' ),
				'eagerlyLoadType' => true,
				'fields'          => array(
					'name' => array(
						'type'        => 'String',
						'description' => __( 'The name of the block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block, array $args, AppContext $context, ResolveInfo $info ) {
							return $this->resolve( $block, $args, $context, $info );
						},
					),
				),
			)
		);
	}

	private function resolve( $block, array $args, AppContext $context, ResolveInfo $info ) {
		return isset( $block['blockName'] ) ? $block['blockName'] : '';
	}

	private function resolve_block_attributes( $block, $attribute_name, $attribute_config ) {
		if ( isset( $attribute_config['selector'], $attribute_config['source'] ) ) {
			$rendered_block = wp_unslash( render_block( $block ) );
			$value          = null;
			if ( empty( $rendered_block ) ) {
				return $value;
			}
			switch ( $attribute_config['source'] ) {
				case 'attribute':
					$value = DOMHelpers::parseAttribute( $rendered_block, $attribute_config['selector'], $attribute_config['attribute'], $attribute_config['default'] );
					break;
				case 'html':
					$value = DOMHelpers::parseHTML( $rendered_block, $attribute_config['selector'], $attribute_config['default'] );
					break;
			}//end switch

			return $value;
		}//end if
		$default = isset( $attribute_config['default'] ) ? $attribute_config['default'] : null;
		return $block['attrs'][ $attribute_name ] ?? $default;
	}
}
