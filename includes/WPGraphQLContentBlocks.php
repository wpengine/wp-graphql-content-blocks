<?php

/**
 * Setup WPGraphQLContentBlocks
 *
 * @package WPGraphQL\ContentBlocks
 * @since   0.0.1
 */
// Global. - namespace WPGraphQL\ContentBlocks
use WPGraphQL\ContentBlocks\Data\ContentBlocksResolver;
use WPGraphQL\Utils\Utils;
final class WPGraphQLContentBlocks {


	private static $instance;

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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'The WPGraphQLContentBlocks class should not be cloned.', 'wp-graphql' ), '0.0.1' );
	}

	/**
	 * Disable deserializing of the class.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	public function __wakeup() {
		// De-serializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WPGraphQLContentBlocks class is not allowed', 'wp-graphql' ), '0.0.1' );
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
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_VERSION', '0.0.1' );
		// Plugin Folder Path.
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_DIR', plugin_dir_path( $main_file_path ) );
		// Plugin Root File.
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_PLUGIN_FILE', $main_file_path );
		// Whether to autoload the files or not.
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_AUTOLOAD', true );
		// The minimum version of PHP this plugin requires to work properly
		$this->define( 'WPGRAPHQL_CONTENT_BLOCKS_MIN_PHP_VERSION', '7.1' );
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
							'<div class="notice notice-error is-dismissible">' .
								'<p>%s</p>' .
								'</div>',
							__( 'WPGraphQL Content Blocks will not work without WPGraphQL installed and active.', 'wp-graphql' )
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
	public function actions() {
		 add_action( 'graphql_register_types', array( $this, 'init_block_editor_registry' ) );
	}

	public function filters() {     }

	public function init_block_editor_registry( \WPGraphQL\Registry\TypeRegistry $type_registry ) {
		$block_editor_registry = new \WPGraphQL\ContentBlocks\Registry\Registry( $type_registry, \WP_Block_Type_Registry::get_instance() );
		$block_editor_registry->onInit();
		register_graphql_interface_type(
			"postBlock",
			array(
				'interfaces' => array( 'EditorBlock' ),
				'fields'     => array(
					'name' => array(
						'type' => 'String',
					),
				),
				'resolveType'     => function ( $block ) use ( $type_registry ) {
					if ( empty( $block['blockName'] ) ) {
						$block['blockName'] = 'core/html';
					}

					$type_name = lcfirst( ucwords( $block['blockName'], '/' ) );
					$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
					$type_name = Utils::format_type_name( $type_name );

					return $type_registry->get_type( $type_name ) ?? $type_registry->get_type( 'UnknownBlock' );
				},
			)
		);

		register_graphql_interfaces_to_types( array( "postBlock" ), ["CoreParagraph", "CoreImage", "CoreColumn", "CoreColumns", "UnknownBlock"] );
		register_graphql_interface_type(
			"pageBlock",
			array(
				'interfaces' => array( 'EditorBlock' ),
				'fields'     => array(
					'name' => array(
						'type' => 'String',
					),
				),
				'resolveType'     => function ( $block ) use ( $type_registry ) {
					if ( empty( $block['blockName'] ) ) {
						$block['blockName'] = 'core/html';
					}

					$type_name = lcfirst( ucwords( $block['blockName'], '/' ) );
					$type_name = preg_replace( '/\//', '', lcfirst( ucwords( $type_name, '/' ) ) );
					$type_name = Utils::format_type_name( $type_name );

					return $type_registry->get_type( $type_name ) ?? $type_registry->get_type( 'UnknownBlock' );
				},
			)
		);

		register_graphql_interfaces_to_types( array( "pageBlock" ), ["CoreParagraph", "CoreImage", "CoreColumn", "CoreColumns", "UnknownBlock"] );
		deregister_graphql_type("NodeWithEditorBlocks");
		register_graphql_interface_type(
			'NodeWithPostEditorBlocks',
			array(
				'description'     => __( 'Node that has content blocks associated with it', 'wp-graphql-content-blocks' ),
				'eagerlyLoadType' => true,
				'fields'          => array(
					'editorBlocks' => array(
						'type'        => array(
							'list_of' => "postBlock",
						),
						'args'        => array(
							'flat' => array(
								'type' => 'Boolean',
							),
						),
						'description' => __( 'List of editor blocks', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $node, $args ) {
							return ContentBlocksResolver::resolve_content_blocks( $node, $args );
						},
					),
				),
			)
		);
		register_graphql_interface_type(
			'NodeWithPageEditorBlocks',
			array(
				'description'     => __( 'Node that has content blocks associated with it', 'wp-graphql-content-blocks' ),
				'eagerlyLoadType' => true,
				'fields'          => array(
					'editorBlocks' => array(
						'type'        => array(
							'list_of' => "pageBlock",
						),
						'args'        => array(
							'flat' => array(
								'type' => 'Boolean',
							),
						),
						'description' => __( 'List of editor blocks', 'wp-graphql-content-blocks' ),
						'resolve'     => function ( $node, $args ) {
							return ContentBlocksResolver::resolve_content_blocks( $node, $args );
						},
					),
				),
			)
		);
		register_graphql_interfaces_to_types( array( 'NodeWithPageEditorBlocks' ), ["page"] );
		register_graphql_interfaces_to_types( array( 'NodeWithPostEditorBlocks' ), ["post"] );

		// $post_id = -99; // negative ID, to avoid clash with a valid post
		// $post = new stdClass();
		// $post->ID = $post_id;
		// $post->post_author = 1;
		// $post->post_date = current_time( 'mysql' );
		// $post->post_date_gmt = current_time( 'mysql', 1 );
		// $post->post_title = 'Some title or other';
		// $post->post_content = 'Whatever you want here. Maybe some cat pictures....';
		// $post->post_status = 'publish';
		// $post->comment_status = 'closed';
		// $post->ping_status = 'closed';
		// $post->post_name = 'fake-post-' . rand( 1, 99999 ); // append random number to avoid clash
		// $post->post_type = 'post';
		// $post->filter = 'raw'; // important!
		// $block_editor_context = new WP_Block_Editor_Context( array( 'post' => new WP_Post( $post ) ) );
		// $blocks = get_allowed_block_types($block_editor_context);
		// print_r($blocks);
		// // $res = "";
		// // foreach ($blocks as $block) {
		// // 	$res += $block;
		// // }
		// // print_r($res);
	}

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
