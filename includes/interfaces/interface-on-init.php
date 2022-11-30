<?php
/**
 *  Contains OnInit interface
 *
 * @package WPGraphQLContentBlocks
 */

namespace WPGraphQL\ContentBlocks\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface OnInit {

	/**
	 * Executed onInit
	 *
	 * @return void
	 */
	public function onInit();
}
