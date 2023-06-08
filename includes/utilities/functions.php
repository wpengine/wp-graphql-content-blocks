<?php
/**
 * Various utility functions used through the plugin.
 *
 * @package WPGraphQL\ContentBlocks
 */

namespace WPGraphQL\ContentBlocks\Utilities;

/**
 * Converts string to camelCase. Added to ensure that fields are compliant with the GraphQL spec.
 *
 * @param string $str The string to be converted to camelCase.
 * @param array  $preserved_chars The characters to preserve.
 *
 * @credit http://www.mendoweb.be/blog/php-convert-string-to-camelcase-string/
 *
 * @return string camelCase'd string
 */
function camelcase( string $str, array $preserved_chars = array() ): string {
	/* Convert non-alpha and non-numeric characters to spaces. */
	$str = preg_replace( '/[^a-z0-9' . implode( '', $preserved_chars ) . ']+/i', ' ', $str );
	$str = trim( $str ?? '' );

	/* Uppercase the first character of each word. */
	$str = ucwords( $str );
	$str = str_replace( ' ', '', $str );

	return lcfirst( $str );
}
