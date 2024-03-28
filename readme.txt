=== WPGraphQL Content Blocks ===
Contributors: blakewpe, chriswiegman, joefusco, matthewguywright, TeresaGobble, thdespou, wpengine
Tags: faustjs, faust, headless, decoupled, gutenberg
Requires at least: 5.7
Tested up to: 6.5
Stable tag: 3.1.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extends WPGraphQL to support querying (Gutenberg) Blocks as data.

== Description ==

Extends WPGraphQL to support querying (Gutenberg) Blocks as data.

== Installation ==

1. Search for the plugin in WordPress under "Plugins -> Add New".
2. Click the “Install Now” button, followed by "Activate".

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 3.1.3 =

### Patch Changes

- d62e8db: chore: remove `squizlabs/php_codesniffer` from Composer's direct dependencies.
- 05b21b5: fix: Update parameter type for `$supported_blocks_for_post_type_context` in `wpgraphql_content_blocks_should_apply_post_type_editor_blocks_interfaces` to support boolean values
- 7b49863: chore: Bump PHPStan.neon.dist to level 8 and generate baseline of existing tech debt.
- 0c8e2c7: fix: check for `post_content` before attempting to parse them.
- 2d4a218: fix: : rename `WPGraphQLHelpers` file to match class casing. The file name has been changed from `includes/Utilities/WPGraphqlHelpers.php` to `includes/Utilities/WPGraphQLHelpers.php`.
- 66f74fb: chore: stub WP_Post_Type and boostrap wp-graphql-content-blocks.php when scanning with PHPStan
- 43791db: chore: update PHPStan ruleset for stricter linting, and address newly-discovered tech debt.

= 3.1.2 =

### Patch Changes

- 1117a18: Fixed issue with updater functionality.

= 3.1.1 =

### Patch Changes

- bc32b94: No functional changes between 3.1.0 and 3.1.1. This was tagged due to pipeline issues during the 3.1.0 release.

[View the full changelog](https://github.com/wpengine/wp-graphql-content-blocks/blob/main/CHANGELOG.md)