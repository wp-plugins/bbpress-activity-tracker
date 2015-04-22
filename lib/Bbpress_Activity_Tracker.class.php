<?php
/**
 * Main plugin class
 *
 * @package  bbpress-activity-tracker
 * @subpackage lib
 * @author Ankit Pokhrel <info@ankitpokhrel.com.np, @ankitpokhrel>
 * @version 0.3.0
 */
class Bbpress_Activity_Tracker
{	
	/**
	 * Constructor
	 *
	 * @see  add_action()
	 * @since  0.0.0
	 *
	 * @see  add_action()
	 */
	public function __construct()
	{
		//check if bbPress plugin is installed and activated
		add_action( 'plugins_loaded', array( $this, 'bbp_validate_installation' ) );
		
		//load plugin textdomain
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		//hook topic and reply save action
		add_action( 'save_post_topic', array( $this, 'bbp_topic_updated' ) );
		add_action( 'save_post_reply', array( $this, 'bbp_reply_updated' ) );

		//enqueue necessary scripts and styles
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_global_scripts') );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_global_scripts') );

		//build plugin data
		add_action( 'admin_post_bat_build_data', array($this, 'bat_build_plugin_data') );
	}

	/**
	 * Initial data build script
	 *
	 * @access public
	 * @since  0.0.0
	 * @global  $wpdb
	 * 
	 * @return void
	 */
	public function bat_build_plugin_data()
	{
		global $wpdb;
		
		//get all topics
		$query = "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status IN(%s, %s)";
		$topics = $wpdb->get_results( $wpdb->prepare($query, 'topic', 'publish', 'closed') );

		//update topic score
		if( !empty($topics) ) {
			foreach( $topics as $topic ) {
				$topicHelper = new BAT_Topic_Helper( $topic->ID );
				$topicHelper->update_bat_topic_score( $topicHelper->get_score() );
			}
		}

		//update forum satisfaction score
		$query = "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = %s";
		$forums = $wpdb->get_results( $wpdb->prepare($query, 'forum', 'publish') );

		//update forum score
		if( !empty($forums) ) {
			foreach( $forums as $forum ) {
				BAT_Common_Helper::get_satisfaction_score( $forum->ID );
			}
		}

		header( 'location:' . get_admin_url() );
		exit;
	}

	/**
	 * Enqueue required global styles and scirpts
	 *
	 * @access public
	 * @since  0.0.0
	 *
	 * @see  user_can()
	 * @see  wp_enqueue_style()
	 * 
	 * @return void
	 */
	public function enqueue_global_scripts()
	{		
		$loggedInUserId = get_current_user_id();
		if( user_can( $loggedInUserId, 'bbp_keymaster' ) ||
				 user_can( $loggedInUserId, 'bbp_moderator' ) ) {

			wp_enqueue_style( 'bat-styles', plugins_url( '/css/bat-styles.css', dirname(__FILE__) ) );

			//required for wp3.8 and wp3.9
			wp_enqueue_style( 'dashicons' );

		}
	}

	/**
	 * Add notice if bbPress plugin is not activated
	 *
	 * @since  0.0.0
	 * @access public
	 *
	 * @see  add_action()
	 * 
	 * @return void
	 */
	public function bbp_validate_installation()
	{
		 if( !class_exists('bbPress') ) {
		 	add_action( 'admin_notices', array($this, 'bbp_plugin_required_notice') );
		 }
	}

	/**
	 * Error notice: bbPress Plugin is required for this plugin to work
	 *
	 * @access public
	 * @since  0.0.0
	 * 
	 * @return void
	 */
	public function bbp_plugin_required_notice() 
	{
?>
	<div class="error">
		<p>
			<?php 
				echo BAT_Common_Helper::translate( 
					'bbPress Activity Tracker plugin requires 
					<a href="https://wordpress.org/plugins/bbpress/">bbPress</a> 
					plugin to work. Please make sure that bbPress is installed and activated.'
				); 
			?>
		</p>
	</div>
<?php
	}

	/**
	 * Fired on topic save/update. Save BAT details.
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @param  integer $topic_id Topic that is updated
	 * @return void
	 */
	public function bbp_topic_updated( $topic_id )
	{
		$topicHelper = new BAT_Topic_Helper( $topic_id );
		$topicHelper->update_bat_topic_score( $topicHelper->get_score() );

		BAT_Common_Helper::get_satisfaction_score( $topicHelper->get_forum_id() );

		//update last post date
		global $table_prefix;
		$key = $table_prefix . '_bbp_last_posted';
		update_user_meta( get_current_user_id(), $key, time() );
	}

	/**
	 * Fired on reply save/update. Save BAT details.
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @param  integer $reply_id Reply that is updated
	 * @return void
	 */
	public function bbp_reply_updated( $reply_id )
	{
		//get topic id
		$reply = get_post($reply_id);
		$topic_id = $reply->post_parent;

		//last_reply_id and topic_reply_count is not updated
		//in this state, so update it manually
		bbp_update_topic_last_reply_id( $topic_id, $reply_id );
		bbp_update_topic_reply_count( $topic_id );

		//update score
		$this->bbp_topic_updated( $topic_id );
	}

	/**
	 * Load the plugin's textdomain hooked to 'plugins_loaded'.
	 *
	 * @since 0.0.0
	 * @access public
	 *
	 * @see	load_plugin_textdomain()
	 * @see	plugin_basename()
	 * @action	plugins_loaded
	 *
	 * @return	void
	 */
	public function load_plugin_textdomain()
	{
		load_plugin_textdomain(
			BAT_Common_Helper::$textDomain,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

}
