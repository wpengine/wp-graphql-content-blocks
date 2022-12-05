<?php

namespace WPGraphQL\ContentBlocks\Unit;

use \WPGraphQL\ContentBlocks\Utilities\DOMHelpers;

final class DOMHelpersTest extends PluginTestCase {
	public function testParseAttribute(): void {
		$html                 = '<p id="foo-id" class="foo-class" data="foo-data"><span >Bar</span></p>';
		$no_existant_selector = '#foo';
		$id_selector          = '#foo-id';
		$class_selector       = '.foo-class';
		$element_selector     = 'p';
		$data_attribute       = 'data';
		$class_attribute      = 'class';
		$id_attribute         = 'id';

		$this->assertNull( DOMHelpers::parseAttribute( $html, $no_existant_selector, $data_attribute ) );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $no_existant_selector, $data_attribute, 'Bar' ), 'Bar' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $id_selector, $data_attribute ), 'foo-data' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $id_selector, $class_attribute ), 'foo-class' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $id_selector, $id_attribute ), 'foo-id' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $class_selector, $data_attribute ), 'foo-data' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $class_selector, $class_attribute ), 'foo-class' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $class_selector, $id_attribute ), 'foo-id' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $element_selector, $data_attribute ), 'foo-data' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $element_selector, $class_attribute ), 'foo-class' );
		$this->assertEquals( DOMHelpers::parseAttribute( $html, $element_selector, $id_attribute ), 'foo-id' );
	}

	public function testParseHTML(): void {
		$html                 = '<p id="foo-id" class="foo-class" data="foo-data"><span >Bar</span></p>';
		$no_existant_selector = '#foo';
		$id_selector          = '#foo-id';
		$class_selector       = '.foo-class';
		$element_selector     = 'p';

		$this->assertEmpty( DOMHelpers::parseHTML( $html, $no_existant_selector ) );
		$this->assertEquals( DOMHelpers::parseHTML( $html, $no_existant_selector, 'Bar' ), 'Bar' );
		$this->assertEquals( DOMHelpers::parseHTML( $html, $id_selector ), '<span>Bar</span>' );
		$this->assertEquals( DOMHelpers::parseHTML( $html, $class_selector ), '<span>Bar</span>' );
		$this->assertEquals( DOMHelpers::parseHTML( $html, $element_selector ), '<span>Bar</span>' );
	}
}
