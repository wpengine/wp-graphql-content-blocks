<?php
/**
 * Setup WPGraphQL_Content_Blocks
 *
 * @package WPGraphQLContentBlocks
 */

namespace WPGraphQL\ContentBlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WPGraphQL_Content_Blocks Class.
 *
 * @since 0.0.1
 */
final class WPGraphQL_Content_Blocks {

	/**
	 * The instance of the WPGraphQL_Content_Blocks object.
	 *
	 * @var WPGraphQL_Content_Blocks
	 */
	private static $instance;

	/**
	 * The instance of the WPGraphQLContentBlocks object
	 *
	 * @return object|WPGraphQLContentBlocks
	 * @since  0.0.1
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) || ! ( self::$instance instanceof WPGraphQL_Content_Blocks ) ) {
			self::$instance = new WPGraphQL_Content_Blocks();
			self::$instance->setup_constants();
			self::$instance->actions();
			self::$instance->filters();
		}

		/**
		 * Return the WPGraphQL Instance
		 */
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 * The whole idea of the singleton design pattern is that there is a single object
	 * therefore, we don't want the object to be cloned.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'The WPGraphQL_Content_Blocks class should not be cloned.', 'wp-graphql-content-blocks' ), '0.0.1' );
	}

	/**
	 * Disable deserializing of the class.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	public function __wakeup() {
		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WPGraphQL_Content_Blocks class is not allowed', 'wp-graphql-content-blocks' ), '0.0.1' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	private function setup_constants() {

		// The minimum version of PHP this plugin requires to work properly
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_MIN_PHP_VERSION', '7.1' );
		// Plugin version.
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_VERSION', '0.0.1' );
	}
	/**
	 * Load required actions.
	 */
	public function actions() {
	}

	/**
	 * Include required files.
	 */
	public function filters() {     }
	/**
	 * Define constant if not already set.
	 *
	 * @since 1.4.0
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			// phpcs:ignore
			define($name, $value);
		}
	}
}
