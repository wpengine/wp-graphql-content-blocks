<?php
/**
 * Setup WPGraphQLContentBlocks
 *
 * @package WPGraphQL\ContentBlocks
 * @since   0.0.1
 */

// Global. - namespace WPGraphQL\ContentBlocks

/**
 * Main WPGraphQLContentBlocks Class.
 */
final class WPGraphQLContentBlocks {

	/**
	 * The one true WPGraphQLContentBlocks
	 *
	 * @var ?self
	 */
	private static ?WPGraphQLContentBlocks $instance;

	/**
	 * The instance of the WPGraphQLContentBlocks object
	 *
	 * @return object|WPGraphQLContentBlocks
	 * @since  0.0.1
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) || ! ( self::$instance instanceof WPGraphQLContentBlocks ) ) {
			self::$instance = new WPGraphQLContentBlocks();
			self::$instance->setup_constants();
			if ( self::$instance->includes() ) {
				self::$instance->actions();
				self::$instance->filters();
			}
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'The WPGraphQLContentBlocks class should not be cloned.', 'wp-graphql-content-blocks' ), '0.0.1' );
	}

	/**
	 * Disable deserializing of the class.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	public function __wakeup() {
		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WPGraphQLContentBlocks class is not allowed', 'wp-graphql-content-blocks' ), '0.0.1' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	private function setup_constants(): void {

		// Set main file path.
		$main_file_path = dirname( __DIR__ ) . '/wp-graphql.php';

		// Plugin version.
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_VERSION', '1.1.0' );
		// Plugin Folder Path.
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR', plugin_dir_path( $main_file_path ) );
		// Plugin Root File.
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE', $main_file_path );
		// Whether to autoload the files or not.
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD', true );
		// The minimum version of PHP this plugin requires to work properly
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_MIN_PHP_VERSION', '7.4' );
	}

	/**
	 * Include required files.
	 * Uses composer's autoload
	 *
	 * @since  0.0.1
	 * @return bool
	 */
	private function includes(): bool {
		/**
		 * WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD can be set to "false" to prevent the autoloader from running.
		 * In most cases, this is not something that should be disabled, but some environments
		 * may bootstrap their dependencies in a global autoloader that will autoload files
		 * before we get to this point, and requiring the autoloader again can trigger fatal errors.
		 */
		if ( defined( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD' ) && true === WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD ) {
			if ( file_exists( WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
				// Autoload Required Classes.
				require_once WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR . 'vendor/autoload.php';
			} else {
				add_action(
					'admin_notices',
					function () {
						if ( ! current_user_can( 'manage_options' ) ) {
							return;
						}

						echo sprintf(
							'<div class="notice notice-error">' .
								'<p>%s</p>' .
							'</div>',
							wp_kses_post(
								__( 'WPGraphQL Content Blocks appears to have been installed without its dependencies. If you meant to download the source code, you can run `composer install` to install dependencies. If you are looking for the production version of the plugin, you can download it from the <a target="_blank" href="https://github.com/wpengine/wp-graphql-content-blocks/releases">GitHub Releases tab.</a>', 'wp-graphql-content-blocks' )
							)
						);
					}
				);
			}//end if

			// If GraphQL class doesn't exist, then dependencies cannot be
			// detected. This likely means the user cloned the repo from GitHub
			// but did not run `composer install`
			if ( ! class_exists( 'WPGraphQL' ) ) {
				add_action(
					'admin_notices',
					function () {
						if ( ! current_user_can( 'manage_options' ) ) {
							return;
						}

						echo sprintf(
							'<div class="notice notice-error is-dismissible">' .
								'<p>%s</p>' .
							'</div>',
							esc_html__( 'WPGraphQL Content Blocks will not work without WPGraphQL installed and active.', 'wp-graphql-content-blocks' )
						);
					}
				);

				return false;
			}//end if
		}//end if

		return true;
	}

	/**
	 * Load required actions.
	 *
	 * @since 0.0.1
	 */
	public function actions(): void {
		add_action( 'graphql_register_types', array( $this, 'init_block_editor_registry' ) );
	}

	/**
	 * Load required filters.
	 *
	 * @since 0.0.1
	 */
	public function filters(): void {     }

	/**
	 * Initialize the Block Editor Registry
	 *
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry Type Registry.
	 */
	public function init_block_editor_registry( \WPGraphQL\Registry\TypeRegistry $type_registry ): void {
		$block_editor_registry = new \WPGraphQL\ContentBlocks\Registry\Registry( $type_registry, \WP_Block_Type_Registry::get_instance() );
		$block_editor_registry->init();
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 *
	 * @since 1.4.0
	 */
	private function define( string $name, $value ): void {
		if ( ! defined( $name ) ) {
			// phpcs:ignore
			define($name, $value);
		}
	}
}
