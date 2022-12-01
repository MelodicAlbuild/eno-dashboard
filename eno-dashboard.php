<?php
/**
 * Plugin Name: ENO Dashboard
 * Version: 1.1.0-Pre1
 * Description: The Basic ENO Dashboard
 * Author: Alex Drum
 * Requires at least: 6.0
 * Tested up to: 6.1
 * Requires PHP: 8.0
 *
 * Text Domain: eno-dashboard
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Alex Drum
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load plugin class files.
require_once 'includes/class-eno-dashboard.php';
require_once 'includes/class-eno-dashboard-settings.php';

// Load plugin libraries.
require_once 'includes/lib/class-eno-dashboard-admin-api.php';
require_once 'includes/lib/class-eno-dashboard-post-type.php';
require_once 'includes/lib/class-eno-dashboard-taxonomy.php';

/**
 * Returns the main instance of eno-dashboard to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object eno-dashboard
 */
function eno_dashboard() {
	$instance = eno_dashboard::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = eno_dashboard_Settings::instance( $instance );
	}

	return $instance;
}

eno_dashboard();
