<?php
/**
 * Used to resolve attributes from a block Model.
 *
 * @package WPGraphQL\ContentBlocks\Data
 */

namespace WPGraphQL\ContentBlocks\Data;

use WPGraphQL\ContentBlocks\Utilities\DOMHelpers;

/**
 * Class BlockAttributeResolver
 */
final class BlockAttributeResolver {
	/**
	 * Resolve the value of a single block attribute based on the specified config.
	 *
	 * @internal This method should only be used internally. There are no guarantees for backwards compatibility.
	 *
	 * @param array<string,mixed> $attribute The configuration for the specific attribute.
	 * @param string              $html The block rendered html.
	 * @param mixed               $attribute_value The value from the parsed block attributes.
	 *
	 * @return mixed
	 */
	public static function resolve_block_attribute( $attribute, string $html, $attribute_value ) {
		$value = null;

		if ( isset( $attribute['source'] ) ) {
			switch ( $attribute['source'] ) {
				case 'attribute':
					$value = self::parse_attribute_source( $html, $attribute );
					break;
				case 'html':
				case 'rich-text':
					// If there is no selector, we are dealing with single source.
					if ( ! isset( $attribute['selector'] ) ) {
						$value = self::parse_single_source( $html, $attribute['source'] );
						break;
					}
					$value = self::parse_html_source( $html, $attribute );
					break;
				case 'text':
					$value = self::parse_text_source( $html, $attribute );
					break;
				case 'query':
					$value = self::parse_query_source( $html, $attribute, $attribute_value );
					break;
				case 'meta':
					$value = self::parse_meta_source( $attribute );
					break;
			}

			// Sanitize the value type.
			if ( isset( $attribute['type'] ) ) {
				switch ( $attribute['type'] ) {
					case 'integer':
						$value = intval( $value );
						break;
					case 'boolean':
						$value = ! empty( $value );
						break;
				}
			}
		}

		// Fallback to the attributes or default value if the result is empty.
		if ( empty( $value ) ) {
			$default = $attribute['default'] ?? null;

			$value = $attribute_value ?? $default;
		}

		return $value;
	}

	/**
	 * Parses the block content of a source only block type
	 *
	 * @param string $html The html value
	 * @param string $source The source type
	 */
	private static function parse_single_source( string $html, $source ): ?string {
		if ( empty( $html ) ) {
			return null;
		}

		switch ( $source ) {
			case 'html':
				return DOMHelpers::find_nodes( $html )->innerHTML();
		}

		return null;
	}

	/**
	 * Parses the block content of an HTML source block type.
	 *
	 * Includes `multiline` handling.
	 *
	 * @param string              $html The html value.
	 * @param array<string,mixed> $config The value configuration.
	 */
	private static function parse_html_source( string $html, array $config ): ?string {
		if ( empty( $html ) || ! isset( $config['selector'] ) ) {
			return null;
		}

		$result = DOMHelpers::parse_html( $html, $config['selector'] );

		// Multiline values are located somewhere else.
		if ( isset( $config['multiline'] ) && ! empty( $result ) ) {
			$result = DOMHelpers::get_elements_from_html( $result, $config['multiline'] );
		}

		return $result;
	}

	/**
	 * Parses an attribute source block type.
	 *
	 * @param string              $html The html value.
	 * @param array<string,mixed> $config The value configuration.
	 */
	private static function parse_attribute_source( string $html, array $config ): ?string {
		if ( empty( $html ) || ! isset( $config['selector'] ) || ! isset( $config['attribute'] ) ) {
			return null;
		}

		return DOMHelpers::parse_attribute( $html, $config['selector'], $config['attribute'] );
	}

	/**
	 * Parses a text source block type.
	 *
	 * @param string              $html The html value.
	 * @param array<string,mixed> $config The value configuration.
	 */
	private static function parse_text_source( string $html, $config ): ?string {
		if ( ! isset( $config['selector'] ) ) {
			return null;
		}

		return DOMHelpers::parse_text( $html, $config['selector'] );
	}

	/**
	 * Parses a query source block type.
	 *
	 * @param string               $html The html value.
	 * @param array<string,mixed>  $config The value configuration.
	 * @param ?array<string,mixed> $attribute_values The attribute values for the block.
	 *
	 * @return ?mixed[]
	 */
	private static function parse_query_source( string $html, array $config, ?array $attribute_values ): ?array {
		if ( ! isset( $config['selector'] ) || ! isset( $config['query'] ) ) {
			return null;
		}

		$nodes = DOMHelpers::find_nodes( $html, $config['selector'] );

		// Coerce nodes to an array if it's not already.
		if ( ! is_array( $nodes ) ) {
			$nodes = [ $nodes ];
		}

		$results = [];
		foreach ( $nodes as $source_node ) {
			// Holds the results for each query.
			$temp = [];

			foreach ( $config['query'] as $q_key => $q_value ) {
				$attribute_value = $attribute_values[ $q_key ] ?? null;

				$res = self::resolve_block_attribute( $q_value, $source_node->html(), $attribute_value );

				$temp[ $q_key ] = $res;
			}

			$results[] = $temp;
		}

		return $results;
	}

	/**
	 * Parses a meta source block type.
	 *
	 * Note: Meta sources are considered deprecated but may still be used by legacy and third-party blocks.
	 *
	 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-attributes.md#meta-source-deprecated
	 *
	 * @param array<string,mixed> $config The attribute configuration.
	 */
	private static function parse_meta_source( array $config ): ?string {
		global $post_id;

		if ( empty( $post_id ) || empty( $config['meta'] ) ) {
			return null;
		}

		return get_post_meta( $post_id, $config['meta'], true );
	}
}
