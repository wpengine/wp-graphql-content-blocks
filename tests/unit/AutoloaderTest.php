<?php

namespace WPGraphQL\ContentBlocks\Unit;

use WPGraphQL\ContentBlocks\Autoloader;

class MockAutoloader extends Autoloader {
	public static function reset() {
		self::$is_loaded = false;
	}
}

/**
 * Tests the main class.
 */
final class AutoloaderTest extends PluginTestCase {
	protected $autoloader;

	public function setUp(): void {
		$this->autoloader = new MockAutoloader();
		MockAutoloader::reset();

		parent::setUp();
	}

	public function tearDown(): void {
		unset( $this->autoloader );

		parent::tearDown();
	}

	public function testAutoload() {
		$this->assertTrue( $this->autoloader->autoload() );
	}

	public function testRequireAutoloader() {
		$reflection = new \ReflectionClass( $this->autoloader );
		$is_loaded_property   = $reflection->getProperty( 'is_loaded' );
		$is_loaded_property->setAccessible( true );
		$is_loaded_property->setValue( $this->autoloader, false );

		$method = $reflection->getMethod( 'require_autoloader' );
		$method->setAccessible( true );


		$this->assertTrue( $method->invokeArgs( $this->autoloader, [ WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR . '/vendor/autoload.php' ] ) );

		$is_loaded_property->setValue( $this->autoloader, false );
		$this->assertFalse( $method->invokeArgs( $this->autoloader, [ '/path/to/invalid/autoload.php' ] ) );

		// Capture the admin notice output

		$admin_id = $this->factory->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $admin_id );

		set_current_screen( 'dashboard' );
		ob_start();
		do_action( 'admin_notices' );

		$output = ob_get_clean();
		$this->assertStringContainsString( 'WPGraphQL Content Blocks appears to have been installed without its dependencies.', $output );

		// Cleanup
		wp_delete_user( $admin_id );
		remove_all_actions( 'admin_notices' );
	}
}
