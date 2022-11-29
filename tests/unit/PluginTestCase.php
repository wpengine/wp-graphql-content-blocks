<?php

namespace WPGraphQLContentBlocks\Unit;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

abstract class PluginTestCase extends \PHPUnit\Framework\TestCase {

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
