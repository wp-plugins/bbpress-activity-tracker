<?php
/**
 * Admin page
 *
 * @package bbpress-activity-tracker
 * @subpackage lib/admin
 * 
 * @author Ankit Pokhrel <info@ankitpokhrel.com.np, @ankitpokhrel>
 */
class BAT_Admin
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 0.0.0
	 *
	 * @see	 add_action()
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array($this, 'main_menu') );
	}
	
	/**
	 * Add main menu page
	 *
	 * @access public
	 * @see  add_menu_page()
	 * 
	 * @return void
	 */
	public function main_menu()
	{
		add_menu_page(
			BAT_Common_Helper::translate('bbPress Activity Tracker'),
			BAT_Common_Helper::translate('bbPress Tracker'),
			'manage_options',
			'bbpress-activity-tracker',
			array($this, 'main_menu_template'),
			'dashicons-chart-pie'
		);
	}

	/**
	 * Main settings page template
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @return void
	 */
	public function main_menu_template()
	{
		include "pages/main_menu_page.php";
	}
}

/** Initialize */
new BAT_Admin();
