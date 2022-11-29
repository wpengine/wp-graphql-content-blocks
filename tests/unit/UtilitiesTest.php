<?php

namespace WPGraphQLContentBlocks\Unit;

final class UtilitiesTest extends PluginTestCase {

	public function testCamelcase(): void {
		$string_with_spaces = 'Hello World';
		$string_with_lowercase = 'hello     world';
		$string_with_line_breaks = '
			Hello
			World
		';

		$string1 = \WPGraphQLContentBlocks\Utilities\camelcase( $string_with_spaces );
		$string2 = \WPGraphQLContentBlocks\Utilities\camelcase( $string_with_lowercase );
		$string3 = \WPGraphQLContentBlocks\Utilities\camelcase( $string_with_line_breaks );

		// differently formatted versions of the same string should
		// all produce the same camel case result
		$this->assertSame( $string1, $string2 );
		$this->assertSame( $string2, $string3 );
		$this->assertSame( $string1, $string3 );

		$not_a_string = 8675309;

		// if a non-string is passed, we should get a null response
		$this->assertNull( \WPGraphQLContentBlocks\Utilities\camelcase( $not_a_string ) );

	}
}
