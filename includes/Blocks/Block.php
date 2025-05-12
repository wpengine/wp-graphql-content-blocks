<?php
/**
 *  Handles mapping a WP_Block_Type to the WPGraphQL Schema
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WPGraphQL\ContentBlocks\Data\BlockAttributeResolver;
use WPGraphQL\ContentBlocks\GraphQL\WPGraphQLRegisterConfig;
use WPGraphQL\ContentBlocks\Registry\Registry;
use WPGraphQL\ContentBlocks\Type\Scalar\Scalar;
use WPGraphQL\ContentBlocks\Utilities\WPGraphQLHelpers;
use WPGraphQL\Utils\Utils;
use WP_Block_Type;

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
	 * A filterable array of block object attributes that are typed.
	 * The keys could be the object attribute names of the block and the value is an associative array where the key is the property name and the value is the type.
	 *
	 * @var array<string, array<string, "array"|"boolean"|"number"|"integer"|"object"|"rich-text"|"string">>
	 */
	protected array $typed_object_attributes = [];

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

		$this->filter_typed_object_attributes();
		$this->register_block_type();
	}

	/**
	 * Filters the typed object attributes for the block.
	 *
	 * @return void
	 */
	private function filter_typed_object_attributes() {
		$block_name = str_replace( [ '/' ], '_', $this->block->name );

		if ( has_filter( 'wpgraphql_content_blocks_object_typing_' . $block_name ) ) {
			$this->typed_object_attributes = (array) apply_filters( 'wpgraphql_content_blocks_object_typing_' . $block_name, [] );
		}
	}

	/**
	 * Registers the Block Type to WPGraphQL.
	 *
	 * @return void
	 */
	private function register_block_type() {
		$this->register_block_attributes_as_fields();
		$this->register_type();
	}

	/**
	 * Registers the block attributes GraphQL type and adds it as a field on the Block.
	 */
	private function register_block_attributes_as_fields(): void {
		// Grab any additional block attributes attached into the class itself.
		$block_attributes = array_merge(
			$this->block_attributes ?? [],
			$this->additional_block_attributes ?? [],
		);

		$block_attribute_fields = $this->get_block_attribute_fields( $block_attributes, $this->type_name . 'Attributes' );

		// Bail early if no attributes are defined.
		if ( empty( $block_attribute_fields ) ) {
			return;
		}

		// For each attribute, register a new object type and attach it to the block type as a field
		$block_attribute_type_name = $this->type_name . 'Attributes';
		register_graphql_object_type(
			$block_attribute_type_name,
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'description' => sprintf(
					// translators: %s is the block type name.
						__( 'Attributes of the %s Block Type', 'wp-graphql-content-blocks' ),
						$this->type_name
					),
					'interfaces'  => $this->get_block_attributes_interfaces(),
					'fields'      => $block_attribute_fields,
				]
			)
		);
		register_graphql_field(
			$this->type_name,
			'attributes',
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'type'        => $block_attribute_type_name,
					'description' => sprintf(
						// translators: %s is the block type name.
						__( 'Attributes of the %s Block Type', 'wp-graphql-content-blocks' ),
						$this->type_name
					),
					'resolve'     => static function ( $block ) {
						return $block;
					},
				]
			)
		);
	}

	/**
	 * Returns the type of the block attribute.
	 *
	 * @param string              $name The block name
	 * @param array<string,mixed> $attribute The block attribute config
	 * @param string              $prefix Current prefix string to use for the get_query_type
	 *
	 * @return mixed
	 */
	private function get_attribute_type( $name, $attribute, $prefix ) {
		$type           = null;
		$attribute_type = $attribute['type'] ?? null;

		switch ( $attribute_type ) {
			case 'rich-text':
			case 'string':
				$type = 'String';
				break;
			case 'boolean':
				$type = 'Boolean';
				break;
			case 'number':
				$type = 'Float';
				break;
			case 'integer':
				$type = 'Int';
				break;
			case 'array':
				$type = $this->process_array_attributes( $name, $attribute, $prefix );
				break;
			case 'object':
				$type = $this->process_object_attributes( $name, $attribute, $prefix );
				break;
			case null:
				// Default to String if only 'source' is defined, otherwise return null.
				$type = isset( $attribute['source'] ) ? 'String' : null;
				break;
		}

		if ( null !== $type && isset( $attribute['default'] ) ) {
			$type = [ 'non_null' => $type ];
		}

		return $type;
	}

	/**
	 * Processes the array attributes as query, list of items or scalar array.
	 *
	 * @param string              $name The block name
	 * @param array<string,mixed> $attribute The block attribute config
	 * @param string              $prefix Current prefix string to use for the get_query_type
	 *
	 * @return array|string
	 */
	private function process_array_attributes( $name, $attribute, $prefix ) {
		if ( isset( $attribute['query'] ) ) {
			return [ 'list_of' => $this->register_inner_object_type( $name, $attribute['query'], $prefix ) ];
		}

		$of_type = null;
		if ( isset( $attribute['items'] ) ) {
			$of_type = $this->get_attribute_type( $name, $attribute['items'], $prefix );
		}

		return null !== $of_type ? [ 'list_of' => $of_type ] : Scalar::get_block_attributes_array_type_name();
	}

	/**
	 * Processes the object attributes as typed object if defined within filter, otherwise as scalar.
	 *
	 * @param string              $name The block name
	 * @param array<string,mixed> $attribute The block attribute config
	 * @param string              $prefix Current prefix string to use for the get_query_type
	 *
	 * @return string
	 */
	private function process_object_attributes( $name, $attribute, $prefix ) {
		// If there is no typing for this object attribute, return the default scalar type.
		if ( empty( $this->typed_object_attributes[ $name ] ) ) {
			return Scalar::get_block_attributes_object_type_name();
		}

		$typed = $this->build_typed_object_config(
			$attribute['default'] ?? [],
			$this->typed_object_attributes[ $name ]
		);

		return $typed ? $this->register_inner_object_type( $name, $typed, $prefix ) : Scalar::get_block_attributes_object_type_name();
	}

	/**
	 * Gets the WPGraphQL field registration config for the block attributes.
	 *
	 * @param ?array $block_attributes The block attributes.
	 * @param string $prefix The current prefix string to use for the get_query_type
	 */
	private function get_block_attribute_fields( ?array $block_attributes, string $prefix = '' ): array {
		// Bail early if no attributes are defined.
		if ( null === $block_attributes ) {
			return [];
		}

		$fields = [];
		foreach ( $block_attributes as $attribute_name => $attribute_config ) {
			$graphql_type = $this->get_attribute_type( $attribute_name, $attribute_config, $prefix );

			if ( empty( $graphql_type ) ) {
				continue;
			}

			// Create the field config.
			$fields[ Utils::format_field_name( $attribute_name ) ] = [
				'type'        => $graphql_type,
				'description' => sprintf(
					// translators: %1$s is the attribute name, %2$s is the block name.
					__( 'The "%1$s" field on the "%2$s" block or block attributes', 'wp-graphql-content-blocks' ),
					$attribute_name,
					$prefix
				),
				'resolve'     => function ( $block ) use ( $attribute_name, $attribute_config ) {
					$config = [
						$attribute_name => $attribute_config,
					];
					$result = $this->resolve_block_attributes_recursive( $block['attrs'], wp_unslash( render_block( $block ) ), $config );

					return $result[ $attribute_name ];
				},
			];
		}//end foreach

		return $fields;
	}

	/**
	 * Dynamically creates and registers a GraphQL object type for queries or typed object attributes.
	 *
	 * @param string $name The block name.
	 * @param array  $config The block config.
	 * @param string $prefix The current prefix string to use for registering the new attribute type.
	 */
	private function register_inner_object_type( string $name, array $config, string $prefix ): string {
		$type   = $prefix . ucfirst( $name );
		$fields = $this->create_attributes_fields( $config, $type );

		register_graphql_object_type(
			$type,
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'fields'      => $fields,
					'description' => sprintf(
						// translators: %1$s is the attribute name, %2$s is the block attributes field.
						__( 'The "%1$s" field on the "%2$s" block attribute field', 'wp-graphql-content-blocks' ),
						$type,
						$prefix
					),
				]
			)
		);

		return $type;
	}

	/**
	 * Generates typed object-type attribute config by merging the default values with the typed object configuration.
	 * When typing is specified for an attribute, only the typed properties are returned.
	 *
	 * @param array $default_values Default record of the attribute.
	 * @param array $typed Typed object configuration.
	 */
	private function build_typed_object_config( $default_values, $typed ): array {
		return array_combine(
			array_keys( $typed ),
			array_map(
				static fn ( $key ) => [
					'type'    => $typed[ $key ],
					'default' => $default_values[ $key ] ?? null,
				],
				array_keys( $typed )
			)
		) ?: [];
	}

	/**
	 * Creates the new attribute fields for query types
	 *
	 * @param array  $attributes The query attributes config
	 * @param string $prefix The current prefix string to use for registering the new query attribute type
	 */
	private function create_attributes_fields( $attributes, $prefix ): array {
		$fields = [];
		foreach ( $attributes as $name => $attribute ) {
			$type = $this->get_attribute_type( $name, $attribute, $prefix );

			if ( isset( $type ) ) {
				$default_value = $attribute['default'] ?? null;

				$fields[ Utils::format_field_name( $name ) ] = [
					'type'        => $type,
					'description' => sprintf(
						// translators: %1$s is the attribute name, %2$s is the block attributes field.
						__( 'The "%1$s" field on the "%2$s" block attribute field', 'wp-graphql-content-blocks' ),
						$name,
						$prefix
					),
					'resolve'     => function ( $attributes ) use ( $name, $default_value, $type ) {
						$value = $attributes[ $name ] ?? $default_value;

						return $this->normalize_attribute_value( $value, $type );
					},
				];
			}
		}

		return $fields;
	}

	/**
	 * Normalizes the value of the attribute
	 *
	 * @param array|string $value The value
	 * @param string       $type The type of the value
	 *
	 * @return array|string|int|float|bool
	 */
	private function normalize_attribute_value( $value, $type ) {
		// @todo use the `source` to normalize array/object values.
		if ( is_array( $value ) ) {
			return $value;
		}

		switch ( $type ) {
			case 'array':
				// If we're here, we want an array type, even though the value is not an array.
				// @todo This should return null if the value is empty.
				return ! empty( $value ) ? [ $value ] : [];
			case 'rich-text':
			case 'string':
				return (string) $value;
			case 'number':
				return (float) $value;
			case 'boolean':
				return (bool) $value;
			case 'integer':
				return (int) $value;
			default:
				return $value;
		}
	}

	/**
	 * Register the Type for the block. This happens after all other object types are already registered.
	 */
	private function register_type(): void {
		register_graphql_object_type(
			$this->type_name,
			// @TODO - Remove when WPGraphQL min version is 2.3.0
			WPGraphQLRegisterConfig::resolve_graphql_config(
				[
					'description'     => __( 'A block used for editing the site', 'wp-graphql-content-blocks' ),
					'interfaces'      => $this->get_block_interfaces(),
					'eagerlyLoadType' => true,
					'fields'          => [
						'name' => [
							'type'        => 'String',
							'description' => __( 'The name of the block', 'wp-graphql-content-blocks' ),
							'resolve'     => static function ( $block ) {
								return isset( $block['blockName'] ) ? (string) $block['blockName'] : null;
							},
						],
					],
				]
			)
		);
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
	 * Gets the GraphQL interfaces that should be implemented by the block attributes object.
	 *
	 * @return string[]
	 */
	private function get_block_attributes_interfaces(): array {
		return $this->block_registry->get_block_attributes_interfaces( $this->block->name );
	}

	/**
	 * Resolved the value of the block attributes based on the specified config
	 *
	 * @param array<string,mixed> $attribute_values The block current attributes value.
	 * @param string              $html The block rendered html.
	 * @param array<string,mixed> $attribute_configs The block current attribute configuration, keyed to the attribute name.
	 */
	private function resolve_block_attributes_recursive( $attribute_values, string $html, array $attribute_configs ): array {
		$result = [];

		// Clean up the html.
		$html = trim( $html );

		foreach ( $attribute_configs as $key => $config ) {
			$attribute_value = $attribute_values[ $key ] ?? null;

			$result[ $key ] = BlockAttributeResolver::resolve_block_attribute( $config, $html, $attribute_value );
		}

		return $result;
	}
}
