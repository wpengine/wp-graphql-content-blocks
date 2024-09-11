<?php

namespace WPGraphQL\ContentBlocks\Unit;

final class WPGraphQLContentBlocksTest extends PluginTestCase {


	public $instance;

	public function setUp(): void {
		parent::setUp();

		$this->instance = \WPGraphQLContentBlocks::instance();
	}

	public function tearDown(): void {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * Ensure that WPGraphQLContentBlocks::instance() returns an instance of WPGraphQLContentBlocks
	 *
	 * @covers WPGraphQLContentBlocks::instance()
	 */
	public function testInstance() {
		$this->assertTrue( $this->instance instanceof \WPGraphQLContentBlocks );
	}

	/**
	 * @covers WPGraphQLContentBlocks::__wakeup()
	 * @covers WPGraphQLContentBlocks::__clone()
	 */
	public function testCloneWPGraphQL() {
		$rc = new \ReflectionClass( $this->instance );
		$this->assertTrue( $rc->hasMethod( '__clone' ) );
		$this->assertTrue( $rc->hasMethod( '__wakeup' ) );
	}
}
