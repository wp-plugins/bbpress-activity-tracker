<?php
/**
 * bbPress Activity Tracker Uninstall script
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

//cleanup plugin data
global $wpdb;
$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s", '%bat_score-%') );
delete_post_meta_by_key('_bat_satisfaction_score');
