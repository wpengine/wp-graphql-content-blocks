<?php

/**
 * OnInit interface
 *
 * @package WPGraphQL\ContentBlocks
 * @since   0.0.1
 */

namespace WPGraphQL\ContentBlocks\Interfaces;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * OnInit interface.
 *
 * @since 0.0.1
 */
interface OnInit
{
    public function onInit();
}
