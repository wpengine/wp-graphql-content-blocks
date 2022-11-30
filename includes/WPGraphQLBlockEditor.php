<?php
// Global. - namespace WPGraphQL\ContentBlocks

class WPGraphQLBlockEditor {

	private static $instance;

	/**
	 * The instance of the WPGraphQLBlockEditor object
	 *
	 * @return object|WPGraphQLBlockEditor - The one true WPGraphQL
	 * @since  0.0.1
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) || ! ( self::$instance instanceof WPGraphQLBlockEditor ) ) {
			self::$instance = new WPGraphQLBlockEditor();
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'The WPGraphQLBlockEditor class should not be cloned.', 'wp-graphql' ), '0.0.1' );

	}

	/**
	 * Disable deserializing of the class.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	public function __wakeup() {

		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WPGraphQLBlockEditor class is not allowed', 'wp-graphql' ), '0.0.1' );

	}

	/**
	 * Setup plugin constants.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	private function setup_constants() {

		// Set main file path.
		$main_file_path = dirname( __DIR__ ) . '/wp-graphql.php';

		// Plugin version.
		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_VERSION' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_VERSION', '0.0.1' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR', plugin_dir_path( $main_file_path ) );
		}

		// Plugin Root File.
		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE', $main_file_path );
		}

		// Whether to autoload the files or not.
		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD', true );
		}

		// The minimum version of PHP this plugin requires to work properly
		if ( ! defined( 'WPGRAPHQL_CONTENT_BLOCKS_MIN_PHP_VERSION' ) ) {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_MIN_PHP_VERSION', '7.1' );
		}

	}

	/**
	 * Include required files.
	 * Uses composer's autoload
	 *
	 * @since  0.0.1
	 * @return bool
	 */
	private function includes() {

		/**
		 * WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD can be set to "false" to prevent the autoloader from running.
		 * In most cases, this is not something that should be disabled, but some environments
		 * may bootstrap their dependencies in a global autoloader that will autoload files
		 * before we get to this point, and requiring the autoloader again can trigger fatal errors.
		 *
		 * The codeception tests are an example of an environment where adding the autoloader again causes issues
		 * so this is set to false for tests.
		 */
		if ( defined( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD' ) && true === WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD ) {

			if ( file_exists( WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
				// Autoload Required Classes.
				require_once WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR . 'vendor/autoload.php';

			}

			// If GraphQL class doesn't exist, then dependencies cannot be
			// detected. This likely means the user cloned the repo from Github
			// but did not run `composer install`
			if ( ! class_exists( 'WPGraphQL' ) ) {

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
							__( 'WPGraphQL Content Blocks will not work without WPGraphQL installed and active.', 'wp-graphql' )
						);
					}
				);

				return false;
			}
		}

		return true;

	}

	public function actions() {

		add_action( 'graphql_register_types', function( \WPGraphQL\Registry\TypeRegistry $type_registry ) {
			
			$block_editor_registry = new \WPGraphQL\ContentBlocks\Registry\Registry( $type_registry );
			$block_editor_registry->init();

		} );
	}

	public function filters() {

	}



}
