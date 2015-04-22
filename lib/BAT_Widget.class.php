<?php
/**
 * Widget class: Add dashboard widget with graph.
 *
 * @package  bbpress-activity-tracker
 * @subpackage lib
 * @author Ankit Pokhrel <info@ankitpokhrel.com.np, @ankitpokhrel>
 * @version 0.0.0
 */
class BAT_Widget
{
	/** @var array Holds dashboard chart data */
	private $__chartData;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since  0.0.0
	 *
	 * @see  add_action()
	 */
	public function __construct()
	{
		$this->__chartData = array();

		//hook script
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts') );

		//hook widget
		add_action( 'wp_dashboard_setup', array($this, 'bat_add_dashboard_widget') );
	}

	/**
	 * Enqueue required admin scripts
	 *
	 * @access public
	 * @since  0.0.0
	 *
	 * @see  wp_enqueue_script()
	 * 
	 * @return void
	 */
	public function enqueue_admin_scripts()
	{
		wp_enqueue_script( 'bat-chart-js', plugins_url( '/js/chart.min.js', dirname(__FILE__) ), array('jquery'), true );
		wp_enqueue_script( 'bat-scripts', plugins_url( '/js/bat-scripts.js', dirname(__FILE__) ), array('jquery', 'bat-chart-js'), true );
	}

	/**
	 * Hook frontend dashboard widget and reorder priority
	 *
	 * @access public
	 * @since  0.0.0
	 *
	 * @see  wp_add_dashboard_widget()
	 * @global $wp_meta_boxes
	 * 
	 * @return void
	 */
	public function bat_add_dashboard_widget()
	{
		//add widget
		if( BAT_Common_Helper::is_authorized() ) {
			wp_add_dashboard_widget( 'bat-forum-status', BAT_Common_Helper::translate('bbPress Activity Tracker'), array($this, 'bat_dashboard_widget') );

			//move widget to the top
			global $wp_meta_boxes;

			$normalDashboard = $wp_meta_boxes['dashboard']['normal']['core'];

			$batWidget = array( 'bat-forum-status' => $normalDashboard['bat-forum-status'] );
			unset( $normalDashboard['bat-forum-status'] );

			$sortedDashboard = array_merge( $batWidget, $normalDashboard );

			$wp_meta_boxes['dashboard']['normal']['core'] = $sortedDashboard;
		}
	}

	/**
	 * Dashboard widget
	 *
	 * @access public
	 * @since  0.0.0
	 *
	 * @see  get_results()
	 * @global  $wpdb
	 * 
	 * @return void
	 */
	public function bat_dashboard_widget()
	{
		//get topic scores
		global $wpdb;
		$query = "SELECT * FROM $wpdb->postmeta wpm 
					INNER JOIN $wpdb->posts wp 
					ON wpm.`post_id` = wp.`ID`
					WHERE wpm.`meta_key` LIKE %s
					AND wp.`post_status` IN (%s, %s, %s)";
		$scores = $wpdb->get_results( $wpdb->prepare($query, '%_bat_score-%', 'publish', 'closed', 'private') );

		$topicStatusScore = array();
		$legends = array();
		if( !empty($scores) ) {
			foreach( $scores as $score ) {
				if( isset($topicStatusScore[ $score->meta_value ]) ) {
					$topicStatusScore[ $score->meta_value ] += 1;
				} else {
					$topicStatusScore[ $score->meta_value ] = 1;
				}
			}

			ksort($topicStatusScore);
			foreach( $topicStatusScore as $score => $count ) {
				$topicMetaData = BAT_Common_Helper::get_highlight_markup( $score );
				$this->__chartData[] = array(
						'value' => $count,
						'color' => $topicMetaData['colorHex'],
						'highlight' => $topicMetaData['colorHex'],
						'label' => BAT_Common_Helper::translate($topicMetaData['title'])
					);

				//line break
				if( $score == 3 ) {
					$legends[] = "<div class='clear'></div>";
				}

				$legends[] = "<div class='{$topicMetaData['color']}'><span class='dashicons {$topicMetaData['icon']} {$topicMetaData['color']}'></span><span class='legend-title'>" . BAT_Common_Helper::translate($topicMetaData['title']) . " ({$count})</span></div>";
			}
		}

		$averageSatisfactionScore = BAT_Common_Helper::get_average_satisfaction_score();
		$averageSatisfactionScoreColor = BAT_Common_Helper::get_satisfaction_score_color( $averageSatisfactionScore );
?>
<div class="bat-forum-details">
	<a href="edit.php?post_type=forum" class="bat-bbp-image-forums left dashicons-before">
		<span>
			<?php
				echo BAT_Common_Helper::translate('Forums');
				echo ' (' . BAT_Common_Helper::get_forum_count() . ')';
			?>
		</span>
	</a>
	<a href="edit.php?post_type=topic" class="bat-bbp-image-topics left dashicons-before">
		<span>
			<?php
				echo BAT_Common_Helper::translate('Topics');
				echo ' (' . BAT_Common_Helper::get_topic_count() . ')';
			?>
		</span>
	</a>
	<a href="edit.php?post_type=reply" class="bat-bbp-image-replies left dashicons-before">
		<span>
			<?php
				echo BAT_Common_Helper::translate('Replies');
				echo ' (' . BAT_Common_Helper::get_replies_count() . ')';
			?>
		</span>
	</a>
	<div class="clear"></div>
</div>

<?php if( !empty($this->__chartData) ): ?>
	<canvas id='bat-chart'></canvas>

	<div class="bat-legends">
		<?php echo implode('', $legends); ?>
		<div class="clear"></div>
	</div>
<?php else: ?>
		<div class="bat-nothing-found">
			<b><?php echo BAT_Common_Helper::translate('Sorry, Nothing to show!'); ?></b>
			<p>
				<?php 
					echo BAT_Common_Helper::translate('If you have just installed the plugin you need to rebuild your data first. ');
					echo BAT_Common_Helper::translate('Make sure');
					echo " <a href='https://wordpress.org/plugins/bbpress/' title='bbPress'>bbPress</a> ";
					echo BAT_Common_Helper::translate(' is installed, activated and you have some forums, topics and replies.');
					echo "<br/><br/>";
					echo BAT_Common_Helper::translate('If you are existing user and seeing this page then your data might be damaged for some reason.');
					echo BAT_Common_Helper::translate(' Try rebuidling the data again.');
				?>
				<br/><br/>
				<form action="admin-post.php" method="post">
					<input type="hidden" name="action" value="bat_build_data">
					<input type="submit" value="<?php echo BAT_Common_Helper::translate('Build Data Now'); ?>" class="button button-primary" title="Build Plugin Data">
				</form>
			</p>
		</div>
<?php endif; ?>

<div class="bat-average-score">
	<label>
		<?php echo BAT_Common_Helper::translate('Your Average Customer Satisfaction Score is') ?>&nbsp;
		<span class='bat-score <?php echo $averageSatisfactionScoreColor ?>'>
			<?php echo $averageSatisfactionScore ?>%
		</span>
	</label>
</div>
<script>
	/* <![CDATA[ */
		var BAT_CHART_DATA = <?php echo json_encode( $this->__chartData ); ?>;
	/* ]]> */
</script>
<?php
	}
}

/** Initialize */
new BAT_Widget();
