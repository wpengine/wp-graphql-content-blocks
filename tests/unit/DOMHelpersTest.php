<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Utilities\DOMHelpers;

final class DOMHelpersTest extends PluginTestCase {
	public function testParseAttribute(): void {
		$html                 = '<p id="foo-id" class="foo-class" data="foo-data"><span >Bar</span></p>';
		$html2                = '<td class="has-text-align-center" data-align="center">Content 1</td>
		<td class="has-text-align-right" data-align="right">Content 2</td>';
		$html3                = '<div class="container"><span data-align="left">Left</span><span data-align="right">Right</span></div>';
		$no_existent_selector = '#foo';
		$id_selector          = '#foo-id';
		$class_selector       = '.foo-class';
		$element_selector     = 'p';
		$data_attribute       = 'data';
		$class_attribute      = 'class';
		$id_attribute         = 'id';

		// $html
		$this->assertNull( DOMHelpers::parse_attribute( '', $no_existent_selector, $data_attribute ) );
		$this->assertNull( DOMHelpers::parse_attribute( $html, $no_existent_selector, $data_attribute ) );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $no_existent_selector, $data_attribute, 'Bar' ), 'Bar' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $id_selector, $data_attribute ), 'foo-data' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $id_selector, $class_attribute ), 'foo-class' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $id_selector, $id_attribute ), 'foo-id' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $class_selector, $data_attribute ), 'foo-data' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $class_selector, $class_attribute ), 'foo-class' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $class_selector, $id_attribute ), 'foo-id' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $element_selector, $data_attribute ), 'foo-data' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $element_selector, $class_attribute ), 'foo-class' );
		$this->assertEquals( DOMHelpers::parse_attribute( $html, $element_selector, $id_attribute ), 'foo-id' );

		// $html2
		$this->assertEquals( 'center', DOMHelpers::parse_attribute( $html2, '*', 'data-align' ) );
		$this->assertEquals( 'right', DOMHelpers::parse_attribute( $html2, '.has-text-align-right', 'data-align' ) );
		$this->assertNull( DOMHelpers::parse_attribute( $html2, '.non-existent-class', 'data-align' ) );
		$this->assertEquals( 'default', DOMHelpers::parse_attribute( $html2, '.non-existent-class', 'data-align', 'default' ) );

		// $htm3
		$this->assertEquals( 'left', DOMHelpers::parse_attribute( $html3, 'span', 'data-align' ) );
		$this->assertEquals( 'left', DOMHelpers::parse_attribute( $html3, '*', 'data-align' ) );
	}

	public function testParseHTML(): void {
		$html                 = '<p id="foo-id" class="foo-class" data="foo-data"><span >Bar</span></p>';
		$no_existent_selector = '#foo';
		$id_selector          = '#foo-id';
		$class_selector       = '.foo-class';
		$element_selector     = 'p';

		$this->assertNull( DOMHelpers::parse_html( '', $no_existent_selector ) );
		$this->assertEmpty( DOMHelpers::parse_html( $html, $no_existent_selector ) );
		$this->assertEquals( DOMHelpers::parse_html( $html, $no_existent_selector, 'Bar' ), 'Bar' );
		$this->assertEquals( DOMHelpers::parse_html( $html, $id_selector ), '<span>Bar</span>' );
		$this->assertEquals( DOMHelpers::parse_html( $html, $class_selector ), '<span>Bar</span>' );
		$this->assertEquals( DOMHelpers::parse_html( $html, $element_selector ), '<span>Bar</span>' );
	}

	public function testGetElementsFromHTML(): void {
		$html                 = '<blockquote><p>First paragraph</p><div>My div</div><p>Second paragraph</p></blockquote>';
		$element_selector     = 'p';
		$no_existent_selector = 'span';

		$this->assertNull( DOMHelpers::get_elements_from_html( '', $no_existent_selector ) );
		$this->assertEquals( DOMHelpers::get_elements_from_html( $html, $element_selector ), '<p>First paragraph</p><p>Second paragraph</p>' );
		$this->assertEmpty( DOMHelpers::get_elements_from_html( $html, $no_existent_selector ) );
	}

	public function getTextFromSelector(): void {
		$html = '<blockquote><p>First paragraph</p><div>My div</div><p>Second paragraph</p></blockquote>';

		$blockquote_element   = 'blockquote';
		$p_element            = 'p';
		$div_element          = 'div';
		$no_existent_selector = 'span';

		// getTextFromSelector should get all text (even descendents) according to "textContent"
		// https://developer.mozilla.org/en-US/docs/Web/API/Node/textContent
		$this->assertEquals( DOMHelpers::getTextFromSelector( $html, $blockquote_element ), 'First paragraphMy divSecond paragraph' );

		$this->assertEquals( DOMHelpers::getTextFromSelector( $html, $p_element ), 'First paragraph' );
		$this->assertEquals( DOMHelpers::getTextFromSelector( $html, $div_element ), 'My div' );
		$this->assertEmpty( DOMHelpers::get_elements_from_html( $html, $no_existent_selector ) );
	}
}
