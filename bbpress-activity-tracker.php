<?php
/***
 Plugin Name: bbPress Activity Tracker
 Plugin URI: http://wordpress.org/plugins/bbpress-activity-tracker/
 Description: Track bbPress forum activities. Never miss a single thread.
 Version: 0.0.0
 Author: Ankit Pokhrel
 Author URI: http://ankitpokhrel.com.np
 Text Domain: bbpress-activity-tracker
 Domain Path: /languages

 Copyright (c) 2015 Ankit Pokhrel <info@ankitpokhrel.com.np, http://ankitpokhrel.com.np>.
*/

//Avoid direct calls to this file
if ( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die( 'Access Forbidden' );
}

include "lib/helpers/BAT_Common_Helper.class.php";
include "lib/helpers/BAT_Topic_Helper.class.php";
include "lib/helpers/BAT_Settings_Helper.class.php";
include "lib/BAT_Highlight.class.php";
include "lib/BAT_Widget.class.php";
include "lib/admin/BAT_Admin.class.php";
include "lib/Bbpress_Activity_Tracker.class.php";

/** Initialize the awesome */
new Bbpress_Activity_Tracker();
