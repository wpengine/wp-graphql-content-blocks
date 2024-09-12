<?php
/**
 * Class TestUpdateCallbacks
 */
class UpdateCallbacksTest extends WP_UnitTestCase {
	public function test_pre_set_site_transient_update_plugins_has_filter_added(): void {
		self::assertSame( 10, has_action( 'pre_set_site_transient_update_plugins', 'WPGraphQL\ContentBlocks\PluginUpdater\check_for_plugin_updates' ) );
	}

	public function test_plugins_api_has_filter_added(): void {
		self::assertSame( 10, has_action( 'plugins_api', 'WPGraphQL\ContentBlocks\PluginUpdater\custom_plugin_api_request' ) );
	}

	public function test_admin_notices_has_actions_added(): void {
		self::assertSame( 10, has_action( 'admin_notices', 'WPGraphQL\ContentBlocks\PluginUpdater\delegate_plugin_row_notice' ) );
		self::assertSame( 10, has_action( 'admin_notices', 'WPGraphQL\ContentBlocks\PluginUpdater\display_update_page_notice' ) );
	}

	public function test_semantic_versioning_notice_text_has_filter_added(): void {
		self::assertSame( 10, has_filter( 'semantic_versioning_notice_text', 'WPGraphQL\ContentBlocks\PluginUpdater\filter_semver_notice_text', 10, 2 ) );
	}
}
