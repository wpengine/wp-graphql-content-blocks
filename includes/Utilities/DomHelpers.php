<?php
/**
 * Helper functions for manipulating DOM
 *
 * @package WPGraphQL\ContentBlocks\Utilities
 */

namespace WPGraphQL\ContentBlocks\Utilities;

use DiDom\Document;

/**
 * Class DOMHelpers
 */
final class DOMHelpers {
	/**
	 * Parses the given HTML string to extract the specified attribute selector.
	 *
	 * @param string $html The HTML string to parse.
	 * @param string $selector The selector to use.
	 * @param string $attribute The attribute to extract.
	 * @param mixed  $default_value The default value to return if the selector is not found.
	 *
	 * @return string|null extracted attribute
	 */
	public static function parseAttribute( $html, $selector = null, $attribute, $default_value = null ): ?string {
		$doc = new Document();
		$doc->loadHTML( $html );
		if ( '*' === $selector ) {
			$selector = '*[' . $attribute . ']';
		}

		if ( empty( $selector ) ) {
			$selector = '*';
		}
		$node          = $doc->find( $selector );
		$default_value = isset( $default_value ) ? $default_value : null;
		return ( ! empty( $node ) && isset( $node[0] ) ) ? $node[0]->getAttribute( $attribute ) : $default_value;
	}

	/**
	 * Parses the given HTML string to extract the specified attribute selector of the first node.
	 *
	 * @param string $html The HTML string to parse.
	 * @param string $attribute The attribute to extract of the first node.
	 *
	 * @return string|null extracted attribute
	 */
	public static function parseFirstNodeAttribute( $html, $attribute ) {
		$value = null;

		// Bail early if there's no html to parse.
		if ( empty( trim( $html ) ) ) {
			return $value;
		}

		$doc = new Document( $html );
		// <html><body>$html</body></html>
		$elem = $doc->find( '*' )[2];
		if ( $elem ) {
			$value = $elem->getAttribute( $attribute );
		}

		return $value;
	}

	/**
	 * Parses the given HTML string to extract the innerHTML contents.
	 *
	 * @param string $html The HTML string to parse.
	 * @param string $selector The selector to use.
	 * @param mixed  $default_value The default value to return if the selector is not found.
	 *
	 * @return string|null extracted innerHTML of selector
	 */
	public static function parseHTML( $html, $selector, $default_value = null ) {
		$doc = new Document();
		$doc->loadHTML( $html );
		$node       = $doc->find( $selector );
		$inner_html = isset( $default_value ) ? $default_value : '';

		foreach ( $node as $elem ) {
			$inner_html .= $elem->innerHTML();
		}
		return $inner_html;
	}

	/**
	 * Parses the given HTML string and extracts the specified elements.
	 *
	 * @param string $html The HTML string to parse.
	 * @param string $selector The element (selector) to extract.
	 *
	 * @return string|null the HTML string of the extracted elements
	 */
	public static function getElementsFromHTML( $html, $selector ) {
		$doc = new Document();
		$doc->loadHTML( $html );
		$elements = $doc->find( $selector );

		$output = '';

		foreach ( $elements as $element ) {
			$output .= $element->html();
		}

		return $output;
	}

	/**
	 * Gets the text content of a given selector. If multiple selectors exist,
	 * the first result will be used.
	 *
	 * @param string $html The HTML string to parse.
	 * @param string $selector The selector to get the text content from.
	 *
	 * @return string|null The text content of the selector if found.
	 */
	public static function parseText( $html, $selector ) {
		$doc = new Document();
		$doc->loadHTML( $html );
		$nodes = $doc->find( $selector );

		if ( count( $nodes ) === 0 ) {
			return null;
		}

		// Returns the element's "textContent"
		// https://developer.mozilla.org/en-US/docs/Web/API/Node/textContent
		return $nodes[0]->text();
	}

	/**
	 * Parses the html into DOMElement and searches the DOM tree for a given XPath expression or CSS selector.
	 *
	 * @param string      $html The HTML string to parse.
	 * @param string|null $selector The selector to use.
	 *
	 * @return \DOMElement[]
	 */
	public static function findNodes( $html, $selector = null ) {
		$value = null;
		// Bail early if there's no html to parse.
		if ( empty( trim( $html ) ) ) {
			return $value;
		}

		$doc = new Document( $html );
		// <html><body>$html</body></html>
		$elem = $doc->find( '*' )[2];
		if ( $selector ) {
			$elem = $doc->find( $selector );
		}
		return $elem;
	}
}
