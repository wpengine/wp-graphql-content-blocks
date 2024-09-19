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
	 * Stores the instance of the WPGraphQLContentBlocks class
	 *
	 * @var ?\WPGraphQLContentBlocks The one true WPGraphQL
	 */
	private static $instance;

	/**
	 * The instance of the WPGraphQLContentBlocks object
	 *
	 * @return object|\WPGraphQLContentBlocks
	 * @since  0.0.1
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) || ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
			// @todo Remove this in a major version bump.
			self::$instance->deprecated_constants();

			if ( self::$instance->includes() ) {
				self::$instance->actions();
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
	 * Include required files.
	 * Uses composer's autoload
	 *
	 * @since  0.0.1
	 */
	private function includes(): bool {
		// Holds the status of whether the plugin is active or not so we can load the updater functions regardless.
		$success = true;

		// If GraphQL class doesn't exist, then that plugin is not active.
		if ( ! class_exists( 'WPGraphQL' ) ) {
			add_action(
				'admin_notices',
				static function () {
					if ( ! current_user_can( 'manage_options' ) ) {
						return;
					}

					printf(
						'<div class="notice notice-error is-dismissible">' .
							'<p>%s</p>' .
						'</div>',
						esc_html__( 'WPGraphQL Content Blocks will not work without WPGraphQL installed and active.', 'wp-graphql-content-blocks' )
					);
				}
			);

			$success = false;
		}

		// Include the updater functions.
		require_once WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR . '/includes/PluginUpdater/UpdateFunctions.php';
		require_once WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR . '/includes/PluginUpdater/UpdateCallbacks.php';

		// Bail if the Enforce SemVer class doesn't exist.
		if ( ! class_exists( 'EnforceSemVer\EnforceSemVer' ) ) {
			return false;
		}

		new \EnforceSemVer\EnforceSemVer( WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_PATH );

		return $success;
	}

	/**
	 * Load required actions.
	 *
	 * @since 0.0.1
	 */
	public function actions(): void {
		add_action( 'graphql_register_types', [ $this, 'init_block_editor_registry' ] );
	}

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
	 * Sets up deprecated constants.
	 *
	 * @deprecated 4.2.0 This can be removed in a major version bump.
	 */
	private function deprecated_constants(): void {
		if ( defined( 'WPGRAPHQL_CONTENT_BLOCKS_FILE' ) ) {
			_doing_it_wrong( 'WPGRAPHQL_CONTENT_BLOCKS_FILE', 'The WPGRAPHQL_CONTENT_BLOCKS_VERSION constant has been deprecated. Use the WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE constant instead.', '4.2.0' );
		} else {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_FILE', WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE );
		}

		if ( defined( 'WPGRAPHQL_CONTENT_BLOCKS_PATH' ) ) {
			_doing_it_wrong( 'WPGRAPHQL_CONTENT_BLOCKS_PATH', 'The WPGRAPHQL_CONTENT_BLOCKS_PATH constant has been deprecated. Use the WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_PATH constant instead.', '4.2.0' );
		} else {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_PATH', WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_PATH );
		}

		if ( defined( 'WPGRAPHQL_CONTENT_BLOCKS_MIN_PHP_VERSION' ) ) {
			_doing_it_wrong( 'WPGRAPHQL_CONTENT_BLOCKS_MIN_PHP_VERSION', 'The WPGRAPHQL_CONTENT_BLOCKS_VERSION constant has been deprecated, with no replacement. It will be removed in a future release.', '4.2.0' );
		} else {
			define( 'WPGRAPHQL_CONTENT_BLOCKS_MIN_PHP_VERSION', WPGRAPHQL_CONTENT_BLOCKS_VERSION );
		}
	}
}
