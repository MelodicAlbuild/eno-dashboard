<?php
/**
 * Plugin Name: ENO Dashboard
 * Version: 1.2.1
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

if (!defined('ABSPATH')) {
    exit;
}

// Load plugin class files.
require_once 'includes/class-eno-dashboard.php';
require_once 'includes/class-eno-dashboard-settings.php';

// Load plugin libraries.
require_once 'includes/lib/class-eno-dashboard-admin-api.php';
require_once 'includes/lib/class-eno-dashboard-post-type.php';
require_once 'includes/lib/class-eno-dashboard-taxonomy.php';

global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
    global $wpdb;
    global $jal_db_version;

    $table_name = $wpdb->prefix . 'eno_assets';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `$table_name` (
	`idTag` INT(6) NOT NULL UNIQUE,
	`brand` VARCHAR(255),
	`serialNumber` VARCHAR(255),
	`checkedOut` BOOLEAN NOT NULL,
	`checkedOutUser` VARCHAR(255),
	`checkedOutDate` DATE,
	`checkedOutFrom` VARCHAR(255),
	PRIMARY KEY (`idTag`)
        ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    add_option( 'jal_db_version', $jal_db_version );

    //Our class extends the WP_List_Table class, so we need to make sure that it's there
    if(!class_exists('WP_List_Table')){
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }
}

register_activation_hook( __FILE__, 'jal_install' );

/**
 * Returns the main instance of eno-dashboard to prevent the need to use globals.
 *
 * @return object eno-dashboard
 * @since  1.0.0
 */
function eno_dashboard()
{
    $instance = eno_dashboard::instance(__FILE__, '1.0.0');

    if (is_null($instance->settings)) {
        $instance->settings = eno_dashboard_Settings::instance($instance);
    }

    return $instance;
}

eno_dashboard();
