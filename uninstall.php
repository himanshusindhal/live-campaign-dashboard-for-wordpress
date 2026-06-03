<?php
/**
 * Uninstall script for Live Campaign Dashboard.
 *
 * Runs when the plugin is deleted from the WordPress admin.
 * Removes all plugin-specific options from the database.
 *
 * @package LiveCampaignDashboard
 */

// If uninstall.php is not called by WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove plugin options.
delete_option( 'lcd_settings' );

// Clean up any transients.
delete_transient( 'lcd_cache' );
