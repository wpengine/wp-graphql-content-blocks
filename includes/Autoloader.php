<?php
/**
 * Includes the Composer Autoloader used for packages and classes in the includes/ directory.
 *
 * @package WPGraphQL\ContentBlocks
 * @since @todo
 */

declare( strict_types = 1 );

namespace WPGraphQL\ContentBlocks;

/**
 * Class - Autoloader
 *
 * @internal
 */
class Autoloader {
	/**
	 * Whether the autoloader has been loaded.
	 *
	 * @var bool
	 */
	protected static bool $is_loaded = false;

	/**
	 * Attempts to autoload the Composer dependencies.
	 */
	public static function autoload(): bool {
		// If we're not *supposed* to autoload anything, then return true.
		if ( defined( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD' ) && false === WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD ) {
			return true;
		}

		// If the autoloader has already been loaded, then return true.
		if ( self::$is_loaded ) {
			return self::$is_loaded;
		}

		// If the main class has already been loaded, then they must be using a different autoloader.
		if ( class_exists( 'WPGraphQLContentBlocks' ) ) {
			return true;
		}

		$autoloader      = dirname( __DIR__ ) . '/vendor/autoload.php';
		self::$is_loaded = self::require_autoloader( $autoloader );

		return self::$is_loaded;
	}

	/**
	 * Attempts to load the autoloader file, if it exists.
	 *
	 * @param string $autoloader_file The path to the autoloader file.
	 */
	private static function require_autoloader( string $autoloader_file ): bool {
		if ( ! is_readable( $autoloader_file ) ) {
			self::missing_autoloader_notice();
			return false;
		}

		return (bool) require_once $autoloader_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Autoloader is a Composer file.
	}

	/**
	 * Displays a notice if the autoloader is missing.
	 */
	private static function missing_autoloader_notice(): void {
		$error_message = __( 'WPGraphQL Content Blocks appears to have been installed without its dependencies. If you meant to download the source code, you can run `composer install` to install dependencies. If you are looking for the production version of the plugin, you can download it from the <a target="_blank" href="https://github.com/wpengine/wp-graphql-content-blocks/releases">GitHub Releases tab.</a>', 'wp-graphql-content-blocks' );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- This is a development notice.
				sprintf(
					wp_kses(
						$error_message,
						[
							'a' => [
								'href'   => [],
								'target' => [],
							],
						]
					)
				)
			);
		}

		$hooks = [
			'admin_notices',
			'network_admin_notices',
		];

		foreach ( $hooks as $hook ) {
			add_action(
				$hook,
				static function () use ( $error_message ) {

					// Only show the notice to admins.
					if ( ! current_user_can( 'manage_options' ) ) {
						return;
					}

					?>
					<div class="error notice">
						<p>
							<?php
							printf(
								wp_kses(
									$error_message,
									[
										'a' => [
											'href'   => [],
											'target' => [],
										],
									]
								)
							)
							?>
						</p>
					</div>
					<?php
				}
			);
		}
	}
}
