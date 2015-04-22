<div class="wrap bat">
	<h2><?php echo BAT_Common_Helper::translate('bbPress Activity Tracker Settings') ?></h2>
	
	<div class="bat-danger-zone">
		<p>
			<?php 
				echo BAT_Common_Helper::translate('Reset and rebuild bbPress Activity Tracker plugin data. This will delete all previous data ');
				echo BAT_Common_Helper::translate('and create new one. Use this option with caution.');
			?>
			<br/>
			<form name="bat-data-builder-form" action="admin-post.php" method="post">
				<input type="hidden" name="action" value="bat_build_data">
				<input type="submit" value="<?php echo BAT_Common_Helper::translate('Rebuild Data'); ?>" class="button button-primary" title="Rebuild Plugin Data">
			</form>
		</p>
	</div>
</div>
