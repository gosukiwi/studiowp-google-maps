<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   StudioWP
 * @author    Federico Ramírez <federico@studiowp.net>
 * @license   GPL-2.0+
 * @link      http://studiowp.net
 * @copyright 2014 Studio WP
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// No uninstall action so far
