<?php
/**
 *  Handles mapping a WP_Block_Type to the WPGraphQL Schema
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WP_Block_Type;
use WPGraphQL\ContentBlocks\Registry\Registry;
use WPGraphQL\ContentBlocks\Utilities\DOMHelpers;
use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;
use WPGraphQL\ContentBlocks\Type\Scalar\Scalar;
use WPGraphQL\ContentBlocks\Field\BlockSupports\Anchor;
use WPGraphQL\Utils\Utils;

/**
 * Class Block
 */
class Block {
	/**
	 * The Block Type
	 *
	 * @var \WP_Block_Type
	 */
	protected WP_Block_Type $block;

	/**
	 * The GraphQL type name of the block.
	 *
	 * @var string
	 */
	protected string $type_name;

	/**
	 * The instance of the WPGraphQL block registry.
	 *
	 * @var \WPGraphQL\ContentBlocks\Registry\Registry
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
	 * @param \WP_Block_Type                             $block The Block Type.
	 * @param \WPGraphQL\ContentBlocks\Registry\Registry $block_registry The instance of the WPGraphQL block registry.
	 */
	public function __construct( WP_Block_Type $block, Registry $block_registry ) {
		$this->block            = $block;
		$this->block_registry   = $block_registry;
		$this->block_attributes = $this->block->attributes;
		$this->type_name        = WPGraphQLHelpers::format_type_name( $block->name );
		$this->register_block_type();
	}

	/**
	 * Template Method to Register fields to the Block
	 *
	 * @return void
	 */
	public function register_fields() {     }


	/**
	 * Registers the Block Type to WPGraphQL.
	 *
	 * @return void
	 */
	private function register_block_type() {
		$this->register_block_attributes_as_fields();
		$this->register_block_support_fields();
		$this->register_fields();
		$this->register_type();
	}

	/**
	 * Registers the block attributes GraphQL type and adds it as a field on the Block.
	 *
	 * @return void
	 */
	private function register_block_attributes_as_fields() {
		if ( isset( $this->additional_block_attributes ) ) {
			$block_attributes       = ! empty( $this->block_attributes ) ? array_merge( $this->block_attributes, $this->additional_block_attributes ) : $this->additional_block_attributes;
			$block_attribute_fields = $this->get_block_attribute_fields( $block_attributes );
		} else {
			$block_attribute_fields = $this->get_block_attribute_fields( $this->block_attributes );
		}

		if ( ! empty( $block_attribute_fields ) ) {
			$block_attribute_type_name = $this->type_name . 'Attributes';
			register_graphql_object_type(
				$block_attribute_type_name,
				array(
					'description' => sprintf(
						// translators: %s is the block type name.
						__( 'Attributes of the %s Block Type', 'wp-graphql-content-blocks' ),
						$this->type_name
					),
					'fields'      => $block_attribute_fields,
				)
			);

			register_graphql_field(
				$this->type_name,
				'attributes',
				array(
					'type'        => $block_attribute_type_name,
					'description' => sprintf(
						// translators: %s is the block type name.
						__( 'Attributes of the %s Block Type', 'wp-graphql-content-blocks' ),
						$this->type_name
					),
					'resolve'     => function ( $block ) {
						return $block;
					},
				)
			);
		}//end if
	}

	/**
	 * Registers fields for the block supports.
	 */
	private function register_block_support_fields() {
		Anchor::register( $this->block );
	}

	/**
	 * Gets the WPGraphQL field registration config for the block attributes.
	 *
	 * @param array $block_attributes The block attributes.
	 */
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
					case 'array':
					case 'object':
						$graphql_type = Scalar::get_block_attributes_object_type_name();
						break;
				}

				if ( empty( $graphql_type ) ) {
					continue;
				}

				$block_attribute_fields[ Utils::format_field_name( $attribute_name ) ] = array(
					'type'        => $graphql_type,
					'description' => sprintf(
						// translators: %1$s is the attribute name, %2$s is the block name.
						__( 'The "%1$s" field on the "%2$s" block', 'wp-graphql-content-blocks' ),
						$attribute_name,
						$this->type_name
					),
					'resolve'     => function ( $block ) use ( $attribute_name, $attribute_config ) {
						return $this->resolve_block_attributes( $block, $attribute_name, $attribute_config );
					},
				);
			}//end foreach
		}//end if
		return $block_attribute_fields;
	}

	/**
	 * Gets the GraphQL interfaces that should be implemented by the block.
	 *
	 * @return string[]
	 */
	private function get_block_interfaces(): array {
		return $this->block_registry->get_block_interfaces( $this->block->name );
	}

	/**
	 * Register the Type for the block
	 *
	 * @return void
	 */
	private function register_type(): void {
		/**
		 * Register the Block Object Type to the Schema
		 */
		register_graphql_object_type(
			$this->type_name,
			array(
				'description'     => __( 'A block used for editing the site', 'wp-graphql-content-blocks' ),
				'interfaces'      => $this->get_block_interfaces(),
				'eagerlyLoadType' => true,
				'fields'          => array(
					'name' => array(
						'type'        => 'String',
						'description' => __( 'The name of the block', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $block ) {
							return $this->resolve( $block );
						},
					),
				),
			)
		);
	}

	/**
	 * Returns the necessary block data to resolve the block field.
	 *
	 * @param mixed $block The block data passed to the resolver.
	 */
	private function resolve( $block ) {
		return isset( $block['blockName'] ) ? $block['blockName'] : '';
	}

	/**
	 * Returns the necessary block data to resolve the block attributes.
	 *
	 * @param array  $block            The block data passed to the resolver.
	 * @param string $attribute_name   The name of the attribute to resolve.
	 * @param array  $attribute_config The config for the attribute.
	 */
	private function resolve_block_attributes( $block, $attribute_name, $attribute_config ) {
		// Get default value.
		$default = isset( $attribute_config['default'] ) ? $attribute_config['default'] : null;
		// Case when only source defined: Classic Blocks
		if ( isset( $attribute_config['source'] ) && ! isset( $attribute_config['selector'] ) ) {
			$rendered_block = wp_unslash( render_block( $block ) );
			$value          = null;
			if ( empty( $rendered_block ) ) {
				return $value;
			}
			switch ( $attribute_config['source'] ) {
				case 'html':
					$value = $rendered_block;
					break;
			}
			return $value;
		}
		// Case when both selector and source are defined
		if ( isset( $attribute_config['selector'], $attribute_config['source'] ) ) {
			$rendered_block = wp_unslash( render_block( $block ) );
			$value          = null;
			if ( empty( $rendered_block ) ) {
				return $value;
			}

			switch ( $attribute_config['source'] ) {
				case 'attribute':
					$value = DOMHelpers::parseAttribute( $rendered_block, $attribute_config['selector'], $attribute_config['attribute'], $default );
					break;
				case 'html':
					$value = DOMHelpers::parseHTML( $rendered_block, $attribute_config['selector'], $default );

					if ( isset( $attribute_config['multiline'] ) && ! empty( $value ) ) {
						$value = DOMHelpers::getElementsFromHTML( $value, $attribute_config['multiline'] );
					}

					break;
				case 'text':
					$value = DOMHelpers::getTextFromSelector( $rendered_block, $attribute_config['selector'], $default );

					break;
			}//end switch

			// if type is set to integer, get the integer value of the attribute.
			if ( 'integer' === $attribute_config['type'] ) {
				$value = intval( $value );
			}

			return $value;
		}//end if

		return $block['attrs'][ $attribute_name ] ?? $default;
	}
}
