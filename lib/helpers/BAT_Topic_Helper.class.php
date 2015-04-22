<?php
/**
 * Topic Helper class: Fetch and set metadata related to topic and forum.
 *
 * @package  bbpress-activity-tracker
 * @subpackage lib/helpers
 * @author Ankit Pokhrel <info@ankitpokhrel.com.np, @ankitpokhrel>
 * @version 0.0.0
 */
class BAT_Topic_Helper
{
	/** Score for New Forum Topic */
	const NEW_TOPIC = 0;

	/** Score for topic that needs immediate attention */
	const NEEDS_IMMEDIATE_ATTENTION = 1;

	/** Score for topic that needs attention */
	const NEEDS_ATTENTION = 2;

	/** Score for topic waiting for response from poster */
	const WAITING_FOR_RESPONSE = 3;

	/** Score for topic that is closed  */
	const SATISFIED = 4;

	/** Score for topic where all is well */
	const FULLY_SATISFIED = 5;

	/** @var integer Post id */
	private $__topic_id;

	/** @var array Current initialized post data */
	protected $_current_post;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since  0.0.0
	 * @param integer $post_id Post id to initialize
	 */
	public function __construct( $post_id )
	{
		$this->__topic_id = $post_id;
		$this->_current_post = get_post( $this->__topic_id );;
	}

	/**
	 * Check if current topic has reply.
	 *
	 * @access public
	 * @since 0.0.0
	 *
	 * @see  get_post_meta()
	 * 
	 * @return boolean
	 */
	public function has_reply()
	{
		$replyCount = get_post_meta( $this->__topic_id, '_bbp_reply_count', true );
		return $replyCount > 0;
	}

	/**
	 * Get last reply id for current topic.
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @return integer|boolean
	 */
	public function get_last_reply_id()
	{
		return get_post_meta( $this->__topic_id, '_bbp_last_reply_id', true );
	}

	/**
	 * Get current topic status.
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @return string
	 */
	public function get_topic_status()
	{
		return !empty($this->_current_post) ? $this->_current_post->post_status : false;
	}

	/**
	 * Get last reply time of the topic.
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @return datetime
	 */
	public function get_last_reply_time()
	{
		$lastReplyDate = !empty($this->_current_post) ? $this->_current_post->post_date : false;
		if( $this->has_reply() ) {		
			$lastReplyId = $this->get_last_reply_id();
			$lastReplyDate = get_the_date( 'Y-m-d H:i:s', $this->get_last_reply_id() );
		}

		return $lastReplyDate;
	}

	/**
	 * Get last reply time of topic in hours.
	 *
	 * @access public
	 * @since 0.0.0
	 *
	 * @return integer
	 */
	public function get_last_reply_time_in_hours()
	{
		$created = new DateTime( get_gmt_from_date($this->get_last_reply_time()) );
		$now = new DateTime( date('Y-m-d H:i:s'), new DateTimeZone('UTC') );
		$timeElapsed = $now->diff( $created );

		return (int) $timeElapsed->format('%a') * 24 + $timeElapsed->format('%h');
	}

	/**
	 * Check if latest response in post is by admin.
	 *
	 * @access public
	 * @since 0.0.0
	 *
	 * @see  get_post()
	 * @see  user_can()
	 * 
	 * @return boolean
	 */
	public function has_latest_response_by_admin()
	{
		$lastReplyId = $this->get_last_reply_id();
		if( empty($lastReplyId) ) {
			return false;
		}

		$lastReply = get_post( $lastReplyId );

		if( !empty($lastReply) ) {
			return ( user_can( $lastReply->post_author, 'bbp_keymaster' ) ||
					 user_can( $lastReply->post_author, 'bbp_moderator' ) );
		}

		return false;
	}

	/**
	 * Update satisfaction score of current topic
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @param  integer $score Score to update
	 * 
	 * @return void
	 */
	public function update_bat_topic_score( $score )
	{
		if( in_array($this->_current_post->post_status, array('publish', 'closed')) ) {
			//delete previous records
			global $wpdb;
			$query = "DELETE FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s";
			$wpdb->query( $wpdb->prepare($query, $this->__topic_id, '%_bat_score-%') );

			//add new entry
			add_post_meta($this->__topic_id, '_bat_score-' . $this->_current_post->post_parent , $score);
		}
	}

	/**
	 * Get topic score based on different conditions
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @return integer
	 */
	public function get_score()
	{
		$latestResponseByAdmin = $this->has_latest_response_by_admin();
		$topicStatus = $this->get_topic_status();
		$lastReplyTime = $this->get_last_reply_time_in_hours();
		$has_reply = $this->has_reply();
		$topicScore = 0;
		$averageResponseTime = BAT_Settings_Helper::get('average_response_time');
		$averageResponseTime = is_numeric($averageResponseTime) ? $averageResponseTime : 8;

		if( $topicStatus === 'closed' && !$has_reply ) {

			$topicScore = self::SATISFIED;

		} elseif( $topicStatus === 'closed' ) {

			$topicScore = self::FULLY_SATISFIED;

		} elseif( !$latestResponseByAdmin && ($lastReplyTime >= $averageResponseTime && $lastReplyTime <= 48) ) {

			$topicScore = self::NEEDS_ATTENTION;

		} elseif( $latestResponseByAdmin ) {

			$topicScore = self::WAITING_FOR_RESPONSE;

		} elseif( $lastReplyTime < $averageResponseTime && !$has_reply ) {

			$topicScore = self::NEW_TOPIC;

		} else {

			$topicScore = self::NEEDS_IMMEDIATE_ATTENTION;

		}

		return $topicScore;
	}

	/**
	 * Fetch forum id of current topic
	 *
	 * @access public
	 * @since 0.0.0
	 * 
	 * @return integer
	 */
	public function get_forum_id()
	{
		return !empty($this->_current_post) ? $this->_current_post->post_parent : null;
	}

}
