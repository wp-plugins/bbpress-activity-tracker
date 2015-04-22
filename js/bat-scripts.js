/*!
 * @file bat-scripts.js
 *
 * Scripts for bbPress Activity Tracker plugin.
 *
 * Copyright (c) 2015, Ankit Pokhrel <info@ankitpokhrel.com.np, @ankitpokhrel>
 */
jQuery(document).ready(function($) {  
	if( $('#bat-chart').length ) {		
		var ctx = document.getElementById('bat-chart').getContext("2d");

		var chart = new Chart(ctx).Doughnut(BAT_CHART_DATA, {
			animateScale: true,
		});	
	}
});
