<?php
/**
 * Get all user settings and cache it for future use.
 *
 * @package bbpress-activity-tracker
 * @subpackage lib/helpers
 *
 * @static
 * 
 * @author Ankit Pokhrel <info@ankitpokhrel.com.np, @ankitpokhrel>
 */
class BAT_Settings_Helper
{
	/* Option key prefix */
	const PREFIX = '_bat_';

	/* Flag to check if the setting is already fetched */
	private static $__initialized = false;

	/* Hold all settings */
	protected static $_settings = array();

	/**
	 * Prevent the instantiation of class using 
	 * private constructor
	 */
	private function __construct() {}

	/**
	 * Fetch settings from database if not already fetched.
	 *
	 * @access protected
	 * @static
	 * @see  get_option()
	 * 
	 * @return void
	 */
	protected static function __init()
	{
		if( self::$__initialized ) {
			return;
		}

		//fetch settings
		$settings = get_option( self::PREFIX . 'settings');
		if( $settings !== false ) {
			self::$_settings = unserialize($settings);
		}

		self::$__initialized = true;
	}

	/**
	 * Forcefully reinitialze settings
	 *
	 * @access public
	 * @static
	 * 
	 * @return void
	 */
	public static function force_init()
	{
		self::$__initialized = false;
	}

	/**
	 * Check if setting is available.
	 *
	 * @access public
	 * @static
	 * 
	 * @return boolean
	 */
	public static function has_settings()
	{
		self::__init();
		return !empty( self::$_settings );
	}

	/**
	 * Check settings array and return setting if available.
	 *
	 * @access public
	 * @static
	 * 
	 * @return string|boolean
	 */
	public static function get( $key, $bool = false )
	{
		self::__init();

		$key = self::PREFIX . $key;
		if( isset(self::$_settings[$key]) ) {
			return $bool ? (bool) self::$_settings[$key] : self::$_settings[$key];
		}

		return false;
	}
	
}
