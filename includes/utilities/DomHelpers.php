<?php

namespace WPGraphQL\ContentBlocks\Utilities;

use DiDom\Document;

final class DOMHelpers {
	/**
	 * Parses the given HTML string to extract the specified attribute selector.
	 *
	 * @param string  $html The HTML string to parse.
	 * @param string  $selector The selector to use.
	 * @param string  $attribute The attribute to extract.
	 * @param default $default The default value to return if the selector is not found.
	 *
	 * @return string|null extracted attribute
	 */
	public static function parseAttribute( $html, $selector, $attribute, $default = null ) {
		$value = null;
		$doc   = new Document();
		$doc->loadHTML( $html );
		$node    = $doc->find( $selector );
		$default = isset( $default ) ? $default : null;
		$value   = ( ! empty( $node ) && isset( $node[0] ) ) ? $node[0]->getAttribute( $attribute ) : $default;
		return $value;
	}

	/**
	 * Parses the given HTML string to extract the innerHTML contents.
	 *
	 * @param string  $html The HTML string to parse.
	 * @param string  $selector The selector to use.
	 * @param default $default The default value to return if the selector is not found.
	 *
	 * @return string|null extracted innerHTML of selector
	 */
	public static function parseHTML( $html, $selector, $default = null ) {
		$doc = new Document();
		$doc->loadHTML( $html );
		$node       = $doc->find( $selector );
		$inner_html = isset( $default ) ? $default : '';
		foreach ( $node as $elem ) {
			$inner_html .= $elem->innerHTML();
		}
		return $inner_html;
	}
}
