<?php
/**
 * Highlight topic and forums with current status.
 *
 * @package  bbpress-activity-tracker
 * @subpackage lib
 * @author Ankit Pokhrel <info@ankitpokhrel.com.np, @ankitpokhrel>
 * @version 0.0.0
 */
class BAT_Highlight
{
	/** @var object Topic helper object */
	private $__topic_helper;

	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 * @see  add_action()
	 * @see  add_filter()
	 * @see  wp_enqueue_style()
	 * 
	 * @since  0.0.0
	 */
	public function __construct()
	{
		$this->__topic_helper = null;

		/** Before topic title in frontend */
		add_action( 'bbp_theme_before_topic_title', array( $this, 'bbp_theme_before_topic_title' ) );

		/** Topic custom column */
		add_action( 'manage_topic_posts_custom_column', array( $this, 'bbp_admin_topics_column_value' ), 10, 2 );
		add_filter( 'bbp_admin_topics_column_headers', array( $this, 'bbp_admin_topics_column_headers' ) );

		/** Forum custom column */
		add_action( 'manage_forum_posts_custom_column', array( $this, 'bbp_admin_forums_column_value' ), 10, 2 );
		add_filter( 'bbp_admin_forums_column_headers', array( $this, 'bbp_admin_forums_column_headers' ) );

		/** User custom column */
		add_action( 'manage_users_custom_column', array( $this, 'bbp_users_column_value' ), 10, 3 );
		add_filter( 'manage_users_columns', array( $this, 'bbp_users_column_headers' ) );
	}

	/**
	 * Create highlight icon based on different conditions.
	 *
	 * @access private
	 * 
	 * @see  BAT_Common_Helper::admin_highlight_html()
	 * @see  BAT_Common_Helper::is_authorized()
	 * 
	 * @return void
	 */
	private function __highlight()
	{
		if( !BAT_Common_Helper::is_authorized() ) {
			return;
		}

		$topicScore = $this->__topic_helper->get_score();

		//highlight
		echo BAT_Common_Helper::admin_highlight_html( $topicScore );

		//update score
		$this->__topic_helper->update_bat_topic_score( $topicScore );
	}

	/**
	 * Content for custom status field in admin.
	 *
	 * @access public
	 * @since  0.0.0
	 * 
	 * @param  string $column_name Name of the column
	 * @param  integer $post_id Post id
	 * 
	 * @return void
	 */
	public function bbp_admin_topics_column_value( $column_name, $post_id )
	{
		$this->__topic_helper = new BAT_Topic_Helper($post_id);

		if( $column_name == 'bbp_topic_status' ) {
			self::__highlight();
		}
	}

	/**
	 * Custom status field in admin.
	 *
	 * @access public
	 * @since  0.0.0
	 * @param  array $columns Column array
	 * 
	 * @return void
	 */
	public function bbp_admin_topics_column_headers( $columns )
	{
		$columns['bbp_topic_status'] = BAT_Common_Helper::translate('Status');
		return $columns;
	}

	/**
	 * Content for satisfaction score field in admin.
	 *
	 * @access public
	 * @since  0.0.0
	 * 
	 * @param  string $column_name Name of the column
	 * @param  integer $post_id Post id
	 * 
	 * @return void
	 */
	public function bbp_admin_forums_column_value( $column_name, $forum_id )
	{
		if( $column_name == 'bbp_forum_satisfaction_score' ) {
			echo BAT_Common_Helper::get_satisfaction_score( $forum_id );
		}
	}

	/**
	 * Custom satisfaction score field in admin.
	 *
	 * @access public
	 * @since  0.0.0
	 * @param  array $columns Column array
	 * 
	 * @return void
	 */
	public function bbp_admin_forums_column_headers( $columns )
	{
		$columns['bbp_forum_satisfaction_score'] = BAT_Common_Helper::translate('Satisfaction Score');
		return $columns;
	}

	/**
	 * Content for total forum posts and last post date
	 *
	 * @access public
	 * @since  0.1.1
	 * 
	 * @param  string $value Column value
	 * @param  string $column_name Name of the column
	 * @param  integer $user_id user id
	 * 
	 * @return void
	 */
	public function bbp_users_column_value( $value, $column_name, $user_id )
	{
		switch( $column_name ) {
			case 'bbp_user_topics':
				return BAT_Common_Helper::get_user_count_for( $user_id, 'topic' );

			case 'bbp_user_replies':
				return BAT_Common_Helper::get_user_count_for( $user_id, 'reply' );

			case 'bbp_user_last_post':
				$date = bbp_get_user_last_posted( $user_id );
				if( $date ) {
					return date('jS F Y', $date);
				}

				return '-';
		}
	}

	/**
	 * Forum posts and last post field in admin.
	 *
	 * @access public
	 * @since  0.1.1
	 * @param  array $columns Column array
	 * 
	 * @return void
	 */
	public function bbp_users_column_headers( $columns )
	{
		$columns['bbp_user_topics'] = BAT_Common_Helper::translate('Topics');
		$columns['bbp_user_replies'] = BAT_Common_Helper::translate('Replies');
		$columns['bbp_user_last_post'] = BAT_Common_Helper::translate('Last Post');
		
		return $columns;
	}

	/**
	 * Topic status in frontend.
	 *
	 * @access public
	 * @since  0.0.0
	 * @param  integer $post_id Post id
	 * 
	 * @return void
	 */
	public function bbp_theme_before_topic_title()
	{
		$this->__topic_helper = new BAT_Topic_Helper( get_the_ID() );
		self::__highlight();
	}

}

/** Initialize */
new BAT_Highlight();
