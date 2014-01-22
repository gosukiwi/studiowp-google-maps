<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   StudioWP_Google_Maps
 * @author    Federico Ramírez <federico@studiowp.net>
 * @license   GPL-2.0+
 * @link      http://studiowp.net
 * @copyright 2014 Studio WP
 *
 * @wordpress-plugin
 * Plugin Name:       StudioWP Google Maps
 * Plugin URI:        http://studiowp.net
 * Description:       Easily display an address using google maps
 * Version:           1.0.0
 * Author:            Federico Ramírez
 * Author URI:        http://studiowp.net
 * Text Domain:       studiowp-google-maps
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/gosukiwi/studiowp-google-maps
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * Load main plugin class
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-studiowp-google-maps.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'StudioWP_Google_Maps', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'StudioWP_Google_Maps', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace StudioWP_Google_Maps with the name of the class defined in
 *   `class-plugin-name.php`
 */
add_action( 'plugins_loaded', array( 'StudioWP_Google_Maps', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-studiowp-google-maps-admin.php' );
	add_action( 'plugins_loaded', array( 'StudioWP_Google_Maps_Admin', 'get_instance' ) );

}
