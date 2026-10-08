<?php
/**
 * Derives block attribute configs from a block type's `supports` declaration.
 *
 * Gutenberg progressively migrates named block attributes into block supports.
 * When that happens the value stops being serialized as a top-level attribute
 * and is written into the generic `style` object instead, so the named
 * attribute disappears from `WP_Block_Type::$attributes` and the corresponding
 * GraphQL field disappears with it.
 *
 * Reading `supports` alongside `attributes` keeps those fields in the schema
 * and points them at the location the value actually moved to.
 *
 * @package WPGraphQL\ContentBlocks\Blocks
 */

namespace WPGraphQL\ContentBlocks\Blocks;

use WP_Block_Type;

/**
 * Class BlockSupportsAttributes
 */
final class BlockSupportsAttributes {
	/**
	 * Maps a block support flag to the attribute config it implies.
	 *
	 * Each entry is keyed by the attribute name to expose. `support` is the
	 * path to the flag within `WP_Block_Type::$supports`, and `config` is the
	 * attribute definition handed to the resolver.
	 *
	 * @var array<string,array{support:string[],config:array<string,mixed>}>
	 */
	private const SUPPORTS_ATTRIBUTES = [
		'textAlign' => [
			'support' => [ 'typography', 'textAlign' ],
			'config'  => [
				'type'   => 'string',
				'source' => 'attrs',
				'path'   => [ 'style', 'typography', 'textAlign' ],
			],
		],
	];

	/**
	 * Gets the attribute configs implied by the block type's supports.
	 *
	 * Attributes already declared by the block type are never overridden, so a
	 * block that still declares the attribute itself (such as `core/quote` and
	 * its `textAlign`) keeps its own definition.
	 *
	 * @param \WP_Block_Type $block_type The block type.
	 *
	 * @return array<string,array<string,mixed>> The derived attribute configs.
	 */
	public static function get_attributes( WP_Block_Type $block_type ): array {
		$supports = $block_type->supports;

		if ( empty( $supports ) || ! is_array( $supports ) ) {
			return [];
		}

		$declared = is_array( $block_type->attributes ) ? $block_type->attributes : [];
		$derived  = [];

		foreach ( self::SUPPORTS_ATTRIBUTES as $attribute_name => $definition ) {
			// Don't shadow an attribute the block already declares.
			if ( array_key_exists( $attribute_name, $declared ) ) {
				continue;
			}

			if ( ! self::has_support( $supports, $definition['support'] ) ) {
				continue;
			}

			$derived[ $attribute_name ] = $definition['config'];
		}

		return $derived;
	}

	/**
	 * Checks whether a supports path is enabled on the block type.
	 *
	 * @param array<string,mixed> $supports The block type supports.
	 * @param string[]            $path The path to the support flag.
	 */
	private static function has_support( array $supports, array $path ): bool {
		$value = $supports;

		foreach ( $path as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return false;
			}

			$value = $value[ $segment ];
		}

		return true === $value;
	}
}
