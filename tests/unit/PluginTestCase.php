<?php

namespace WPGraphQL\ContentBlocks\Unit;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

abstract class PluginTestCase extends \WP_UnitTestCase {
	// Adds Mockery expectations to the PHPUnit assertions count.
	use MockeryPHPUnitIntegration;

	public function setUp(): void {
		parent::setUp();

		Monkey\setUp();
	}

	public function tearDown(): void {
		Monkey\tearDown();

		parent::tearDown();
	}
}
