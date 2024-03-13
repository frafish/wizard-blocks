<?php
/**
 *
 * @wordpress-plugin
 * Plugin Name:       Wizard Blocks Manager for Gutenberg 
 * Plugin URI:        https://wiz.farm
 * Description:       Easy create, edit and manage Guternberg Blocks
 * Version:           1.0.1
 * Author:            FraFish
 * Text Domain:       wizard-blocks
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Wizard Blocks is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Wizard Blocks is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WIZARD_BLOCKS_URL', plugins_url(DIRECTORY_SEPARATOR, __FILE__));
define('WIZARD_BLOCKS_PATH', str_replace('/', DIRECTORY_SEPARATOR, plugin_dir_path(__FILE__)));

/**
 * Load plugin
 *
 * @since 1.0.1
 */
add_action('plugins_loaded', function () {
    // Load localization file
    load_plugin_textdomain('wizard-blocks');
    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'autoload.php');
});

// Require the main plugin file
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'plugin.php' );
$plugin = \WizardBlocks\Plugin::instance();
do_action('wizard-blocks/loaded');



/*
$wp_uploads_dir = wp_get_upload_dir();
$autoload = $wp_uploads_dir['basedir'].DIRECTORY_SEPARATOR.'blocks'.DIRECTORY_SEPARATOR.'autoload.php';
$autoload = str_replace('/', DIRECTORY_SEPARATOR, $autoload);
include_once($autoload);
*/