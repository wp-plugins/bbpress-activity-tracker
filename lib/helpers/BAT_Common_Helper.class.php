<?php
/**
 * Common Helper class: Contain globally required modules
 *
 * @static
 * @package  bbpress-activity-tracker
 * @subpackage lib/helpers
 * @author Ankit Pokhrel <info@ankitpokhrel.com.np, @ankitpokhrel>
 * @version 0.0.0
 */
class BAT_Common_Helper
{
	/** Current version of the plugin */
	const VERSION = '0.3.0';

	/** @var string Plugin text domain */
	public static $textDomain = 'bbpress-activity-tracker';

	/** @var array Icon metadata */
	protected static $_highlightMarkup = array(
			0 => array(
					'color' => 'purple',
					'icon' => 'dashicons-clock',
					'title' => 'New topic',
					'colorHex' => '#cc00cc'
				),
			1 => array(
					'color' => 'red',
					'icon' => 'dashicons-welcome-comments',
					'title' => 'Require immediate attention',
					'colorHex' => '#ff0000'
				),
			2 => array(
					'color' => 'dark-orange',
					'icon' => 'dashicons-flag',
					'title' => 'Needs attention',
					'colorHex' => '#ff6600'
				),
			3 => array(
					'color' => 'light-green',
					'icon' => 'dashicons-format-status',
					'title' => 'Waiting for response',
					'colorHex' => '#a6cb45'
				),
			4 => array(
					'color' => 'mild-green',
					'icon' => 'dashicons-heart',
					'title' => 'Good but unexpected',
					'colorHex' => '#42db3d'
				),
			5 => array(
					'color' => 'green',
					'icon' => 'dashicons-heart',
					'title' => 'All is well',
					'colorHex' => '#009a31'
				)
		);

	/**
	 * Generate html for post status.
	 *
	 * @access public
	 * @since  0.0.0
	 * @static
	 * 
	 * @param  string $color Icon color
	 * @param  string $icon Dashicon icon class
	 * @param  string $title Image title
	 * 
	 * @return string
	 */
	public static function admin_highlight_html( $score )
	{
		$markup = BAT_Common_Helper::get_highlight_markup( $score );
		return "<span class='bat-icon {$markup['color']} dashicons {$markup['icon']}' title='" . self::translate($markup['title']) . "'></span>";
	}

	/**
	 * Check if current logged in user is authorized forum user.
	 *
	 * @access public
	 * @since 0.0.0
	 * @static
	 * 
	 * @return boolean
	 */
	public static function is_authorized()
	{
		return ( user_can( get_current_user_id(), 'bbp_keymaster' ) ||
				 user_can( get_current_user_id(), 'bbp_moderator' ) );
	}

	/**
	 * Get background color for satisfaction score.
	 *
	 * @access public
	 * @since 0.0.0
	 * @static
	 * 
	 * @return string
	 */
	public static function get_satisfaction_score_color( $score )
	{
		switch( $score ) {			
			case 0:
				return 'bg-gray';

			case ($score >= 80):
				return 'bg-green';

			case ($score >= 70):
				return 'bg-light-green';

			case ($score >= 50):
				return 'bg-dark-orange';

			default:
				return 'bg-red';
		}
	}

	/**
	 * Fetches highlight icon meta data
	 *
	 * @access public
	 * @since 0.0.0
	 * @static
	 * 
	 * @param  float $score Satisfaction score
	 * 
	 * @return array
	 */
	public static function get_highlight_markup( $score )
	{
		if( !isset( self::$_highlightMarkup[$score] ) ) {
			return;
		}

		return self::$_highlightMarkup[$score];
	}

	/**
	 * Get total number of forums
	 *
	 * @access public
	 * @since 0.0.0
	 * @static
	 * 
	 * @return integer
	 */
	public static function get_forum_count()
	{
		global $wpdb;
		$query = "SELECT COUNT(*) AS total_forum FROM $wpdb->posts WHERE post_type = %s AND post_status = %s";
		$row = $wpdb->get_row( $wpdb->prepare($query, 'forum', 'publish') );

		if( !empty($row) ) {
			return $row->total_forum;
		}

		return 0;
	}

	/**
	 * Get all forums
	 *
	 * @access public
	 * @since 0.3.0
	 * @static
	 *
	 * @return array|null
	 */
	public static function get_forums()
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->posts WHERE post_type = %s AND post_status = %s";
		$row = $wpdb->get_results( $wpdb->prepare($query, 'forum', 'publish') );

		if( !empty($row) ) {
			return $row;
		}

		return null;
	}


	/**
	 * Get total number of topics
	 *
	 * @access public
	 * @since 0.0.0
	 * @static
	 * 
	 * @return integer
	 */
	public static function get_topic_count()
	{
		global $wpdb;
		$query = "SELECT COUNT(*) AS total_topic FROM $wpdb->posts WHERE post_type = %s AND post_status IN (%s, %s, %s)";
		$row = $wpdb->get_row( $wpdb->prepare($query, 'topic', 'publish', 'closed', 'private') );

		if( !empty($row) ) {
			return $row->total_topic;
		}

		return 0;
	}

	/**
	 * Get new topics count for current day
	 *
	 * @access public
	 * @since 0.2.1
	 * @static
	 * 
	 * @return integer
	 */
	public static function get_new_topics_count()
	{
		global $wpdb;
		$query = "SELECT COUNT(*) AS total_topic FROM $wpdb->posts WHERE post_type = %s AND post_status IN (%s, %s, %s) AND DATE(post_date) = '" . date('Y-m-d') . "'";
		$row = $wpdb->get_row( $wpdb->prepare($query, 'topic', 'publish', 'closed', 'private') );

		if( !empty($row) ) {
			return $row->total_topic;
		}

		return 0;
	}

	/**
	 * Get new topics for current day
	 *
	 * @access public
	 * @since 0.2.1
	 * @static
	 * 
	 * @return array
	 */
	public static function get_new_topics()
	{
		global $wpdb;
		$query = "SELECT * FROM $wpdb->posts WHERE post_type = %s AND post_status IN (%s, %s) AND DATE(post_date) = '" . date('Y-m-d') . "'";
		$row = $wpdb->get_results( $wpdb->prepare($query, 'topic', 'publish', 'private') );

		if( !empty($row) ) {
			return $row;
		}

		return null;
	}

	/**
	 * Get total number of replies
	 *
	 * @access public
	 * @since 0.2.1
	 * @static
	 * 
	 * @return integer
	 */
	public static function get_replies_count()
	{
		global $wpdb;
		$query = "SELECT COUNT(*) AS total_replies FROM $wpdb->posts WHERE post_type = %s AND post_status = %s";
		$row = $wpdb->get_row( $wpdb->prepare($query, 'reply', 'publish') );

		if( !empty($row) ) {
			return $row->total_replies;
		}

		return 0;
	}

	/**
	 * Get new replies count for current day
	 *
	 * @access public
	 * @since 0.0.0
	 * @static
	 * 
	 * @return integer
	 */
	public static function get_new_replies_count()
	{
		global $wpdb;
		$query = "SELECT COUNT(*) AS total_replies FROM $wpdb->posts WHERE post_type = %s AND post_status = %s AND DATE(post_date) = '" . date('Y-m-d') . "'";
		$row = $wpdb->get_row( $wpdb->prepare($query, 'reply', 'publish') );

		if( !empty($row) ) {
			return $row->total_replies;
		}

		return 0;
	}

	/**
	 * Get active user count for current day
	 *
	 * @access public
	 * @since 0.2.1
	 * @static
	 * 
	 * @return integer
	 */
	public static function get_active_users_count()
	{
		global $wpdb;
		$query = "SELECT COUNT( DISTINCT post_author ) AS active_users FROM $wpdb->posts WHERE post_type IN (%s, %s) AND post_status IN (%s, %s, %s) AND DATE(post_modified) = '" . date('Y-m-d') . "'";
		$row = $wpdb->get_row( $wpdb->prepare($query, 'topic', 'reply', 'publish', 'closed', 'private') );

		if( !empty($row) ) {
			return $row->active_users;
		}

		return 0;
	}

	/**
	 * Calculate satisfaction score for a forum.
	 *
	 * @access private
	 * @since  0.0.0
	 * @static
	 * 
	 * @param  integer Forum id
	 * @return string
	 */
	public static function get_satisfaction_score( $forum_id )
	{
		//get topics
		global $wpdb;

		$query = "SELECT SUM(meta_value) as totalSum, COUNT(meta_value) as totalCount 
					FROM $wpdb->postmeta wpm
					INNER JOIN $wpdb->posts wp
					ON wpm.`post_id` = wp.`ID`
					WHERE wpm.`meta_key` = %s
					AND wp.`post_status` IN (%s, %s, %s)";
		$actualScore = $wpdb->get_row( $wpdb->prepare($query, '_bat_score-' . $forum_id, 'publish', 'closed', 'private') );

		$totalSum = (float) $actualScore->totalSum;
		$totalCount = (float) $actualScore->totalCount;

		if( $totalCount == 0 ) {
			update_post_meta($forum_id, '_bat_satisfaction_score', 0);
			return '-';
		}

		$satisfactionScore = $totalSum/($totalCount * 5);
		$satisfactionScore = round( $satisfactionScore * 100, 2 );

		//save satisfaction score
		update_post_meta($forum_id, '_bat_satisfaction_score', $satisfactionScore);

		//display color
		$color = BAT_Common_Helper::get_satisfaction_score_color( $satisfactionScore );

		return "<span class='bat-score {$color}'>" . $satisfactionScore . '% ' . self::translate('Satisfied') . '</span>';
	}

	/**
	 * Localize text strings
	 *
	 * @access public
	 * @since  0.0.0
	 *
	 * @see  __()
	 * 
	 * @return string
	 */
	public static function translate( $string )
	{
		return __($string, self::$textDomain);
	}

	/**
	 * Forum keymasters and moderators
	 *
	 * @access public
	 * @since 0.0.0
	 * @static
	 *
	 * @return array
	 */
	public static function get_forum_maintainers()
	{
		$keymasters = get_users( array(
			'role' => 'bbp_keymaster'
		) );

		$moderators = get_users( array(
			'role' => 'bbp_moderator'
		) );

		return array_merge( $keymasters, $moderators );
	}

	/**
	 * Get count for different user activity like topic and reply count.
	 * 
	 * @access public
	 * @since  0.0.0
	 * @static
	 * 
	 * @param  integer $user_id  User id
	 * @param  String $activity Activity like topic or reply
	 * 
	 * @return integer
	 */
	public static function get_user_count_for( $user_id, $activity )
	{
		global $wpdb;

		$query = "SELECT COUNT(*) as total FROM $wpdb->posts 
					WHERE post_type = %s 
					AND post_status = %s 
					AND post_author = %d";

		$count = $wpdb->get_results( $wpdb->prepare($query, $activity, 'publish', $user_id) );

		return $count[0]->total ? $count[0]->total : 0;
	}

	/**
	 * Calculates average satisfaction score
	 *
	 * @access public
	 * @since  0.2.1
	 * @static
	 * 
	 * @return float
	 */
	public static function get_average_satisfaction_score()
	{
		global $wpdb;

		$query = "SELECT ( SUM(meta_value)/COUNT(meta_value) ) AS average_satisfaction_score,
					CONCAT( (SELECT COUNT(*) 
						FROM $wpdb->posts 
						WHERE post_type = %s 
						AND post_status IN (%s, %s, %s) ), '' ) AS topic_count
					FROM $wpdb->postmeta wpm
					INNER JOIN $wpdb->posts wp
					ON wpm.`post_id` = wp.`ID`
					WHERE wpm.`meta_key` = %s
					AND wpm.`meta_value` > %d
					AND wp.`post_status` IN (%s, %s, %s)
					GROUP BY topic_count
					HAVING topic_count > %d";

		$satisfactionScore = $wpdb->get_row( $wpdb->prepare($query, 'topic', 'publish', 'closed', 'private', '_bat_satisfaction_score', 0, 'publish', 'closed', 'private', 0) );

		return !empty($satisfactionScore) ? round($satisfactionScore->average_satisfaction_score, 2) : 0;
	}

	/**
	 * Get topic score for today
	 *
	 * @access public
	 * @since  0.2.1
	 * @static
	 * 
	 * @return array|null
	 */
	public static function get_topic_scores_for_today()
	{
		global $wpdb;

		$query = "SELECT * FROM $wpdb->postmeta wpm 
					INNER JOIN $wpdb->posts wp 
					ON wpm.`post_id` = wp.`ID`
					WHERE wpm.`meta_key` LIKE %s
					AND wp.`post_status` IN (%s, %s, %s)
					AND DATE(wp.`post_modified`) = '" . date('Y-m-d') . "'
					";
		$scores = $wpdb->get_results( $wpdb->prepare($query, '%_bat_score-%', 'publish', 'closed', 'private') );

		if( !empty($scores) ) {
			return $scores;
		}

		return null;

	}

	/**
	 * Displays error message with WordPress default theme.
	 * 
	 * @param  string $message Message to display
	 * @return void
	 */
	public static function error_notice( $message )
	{
		echo "<div class='error'>";
		echo "<p>" . $message . "</p>";
		echo "</div>";
	}

	/**
	 * Displays success message with WordPress default theme.
	 * 
	 * @param  string $message Message to display
	 * @return void
	 */
	public static function success_notice( $message )
	{
		echo "<div class='updated'>";
		echo "<p>" . $message . "</p>";
		echo "</div>";
	}

}
