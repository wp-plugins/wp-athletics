/*
 * Javascript functions for WPA stats plugin
 */

String.prototype.startsWith = function(needle) {
    return(this.indexOf(needle) == 0);
};

WPA.Stats = {

		/**
		 * Gathers stat criteria and loads the stats
		 */
		loadStats: function(isDialog) {
			var params = WPA.Stats.gatherParams(isDialog);
			console.log('** loading stats' + (isDialog ? ' in dialog' : ''));
			for(param in params) {
				console.log(param + ':' + params[param]);
			}
			
			// if type is events and event not yet chosen, do not load stats
			if(!(params['stat'] == 'events' && params['eventCat'] == 'all') ) {
				
				// if type is runner, ensure mode is always club
				if(params['stat'] == 'runner') {
					params['mode'] = 'club';
				}
				
				WPA.Ajax.getStatistics(params, function(result) {
					if(result) {
						var suffix = isDialog ? '-dialog' : '';
						
						jQuery('#wpa-stats-type-container' + suffix).show();
						
						// hide show club wide stats
						if(params.mode == 'club') {
							jQuery('.wpa-stats-club-only').show();
						}
						else {
							jQuery('.wpa-stats-club-only').hide();
						}
						
						if(params.stat == 'summary') {
							WPA.Stats.displaySummaryStats(result, params, suffix);
							WPA.Stats.displaySummaryCharts(result, params, suffix);
						}
						
						if(params.stat == 'events') {
							WPA.Stats.displayEventStats(result, params, suffix);
							WPA.Stats.displayEventRecentResultsChart(result, params, suffix);
						}
						
						if(params.stat == 'runner') {
							jQuery('#wpa-stats-type-container' + suffix).hide();
							WPA.Stats.displayRunnersChart(result, params, suffix);
						}
					}
				});
			}
		},
		
		/**
		 * Processes and displays the event statistics
		 */
		displayEventStats: function(results, params, suffix) {
			jQuery('#wpa-stats-events-no-results' + suffix).hide();
			jQuery('#wpa-stats-events-content' + suffix).hide();
			jQuery('#wpa-stats-events-default' + suffix).hide();
			
			var count = results.length;
			var event = jQuery('#wpa-stats-event-select' + suffix).combobox('getLabel');
			if(count > 0) {
				
				var best = 0;
				var worst = 0;
				var average = 0;
				var totalTime = 0;
				var totalDistance = 0;
				var top10s = 0;
				var bestResult, worstResult;
				var timeFormat = WPA.getEventTimeFormat(params['eventCat']);
				
				// loops the results and generates stats
				jQuery.each(results, function(index, result) {
					if(parseInt(result.position) <= 10 && parseInt(result.position) > 0) {
						top10s++;
					}
					
					totalTime += parseFloat(result.time);
					totalDistance += parseFloat(result.distance_meters);
					
					// worst result
					if(parseFloat(result.time) > worst || index == 0) {
						worst = parseFloat(result.time);
						worstResult = result;
					}
					
					// best result
					if(parseFloat(result.time) < best || index == 0) {
						best = parseFloat(result.time);
						bestResult = result;
					}
				});
				
				// for displaying the results in the generic results window
				
				var avgTime = totalTime / count;
				
				jQuery('#wpa-stats-events-content' + suffix).show();
				jQuery('#wpa-stats-event-name' + suffix).html(event);
				jQuery('#wpa-stats-event-count' + suffix).html(WPA.commaSeparateNumber(count)).off('click').addClass('wpa-link').click(function() {
					var genericParams = WPA.Stats.toGenericParams(params);
					WPA.displayGenericResultsDialog(genericParams);
				});
				jQuery('#wpa-stats-event-best' + suffix).html('<span title="' + bestResult.event_name + ' (' + bestResult.event_date +  ')" class="wpa-link" onclick="WPA.displayEventResultsDialog(' + bestResult.event_id + ')">' + WPA.displayEventTime(best, timeFormat) + '</span>');
				jQuery('#wpa-stats-event-worst' + suffix).html('<span title="' + worstResult.event_name + ' (' + worstResult.event_date +  ')" class="wpa-link" onclick="WPA.displayEventResultsDialog(' + worstResult.event_id + ')">' + WPA.displayEventTime(worst, timeFormat) + '</span>');
				jQuery('#wpa-stats-event-total-distance' + suffix).html(WPA.formatMeterDisplay(totalDistance));
				jQuery('#wpa-stats-event-total-time' + suffix).html(WPA.displayEventTime(totalTime, 'd:h:m:s'));
				jQuery('#wpa-stats-event-top10s' + suffix).html(WPA.commaSeparateNumber(top10s)).removeClass('wpa-link').off('click');
				jQuery('#wpa-stats-event-avg' + suffix).html(WPA.displayEventTime(avgTime, timeFormat));
				
				if(top10s > 0) {
					jQuery('#wpa-stats-event-top10s' + suffix).off('click').click(function() {
						var genericParams = WPA.Stats.toGenericParams(params);
						genericParams.showTop10 = 'true';
						WPA.displayGenericResultsDialog(genericParams);
					}).addClass('wpa-link');
				} 
			}
			else {
				jQuery('#wpa-stats-events-no-results' + suffix).show();
			}
		},
		
		/**
		 * Converts the stats params into a format readable by the generic results dialog
		 */
		toGenericParams: function(params) {
			var newParams = {
				ageCat: params['ageCat'],
				eventCat: params['eventCat'],
				period: params['period'],
				type: params['type']
			}
			
			if(params['mode'] == 'user') {
				newParams.userId = params['userId']
			}
			
			return newParams;
		},
		
		/**
		 * Determines if the X axis should be displayed in minutes or seconds
		 */
		calculateTimeForEvent: function(result) {
			var time = WPA.millisecondsToTime(result.time);
			var timeFormat = WPA.getEventTimeFormat(result.event_cat_id);
			if(timeFormat.startsWith('s')) {
				// display in seconds
				WPA.Stats.label = WPA.getProperty('add_result_event_time_seconds');
				return parseFloat(time.seconds + '.' + time.milliseconds);
			}
			else {
				// display in hours/minutes or minutes/seconds
				if(time.hours > 0) {
					WPA.Stats.label = WPA.getProperty('add_result_event_time_hours');
					return parseFloat(time.hours + '.' + time.minutes);
				}
				else {
					WPA.Stats.label = WPA.getProperty('add_result_event_time_minutes');
					return parseFloat(time.minutes + '.' + time.seconds);
				}
			}
		},
		
		/**
		 * Displays chart of recent results
		 */
		displayEventRecentResultsChart: function(results, params, suffix) {
			
			if(results.length > 1) {
				
				var resultArray = [];
				var base = results.length > 10 ? results.length-10 : 0;
				
				for(i = base; i < results.length; i++) {
					var result = results[i];
					resultArray.push([result.event_date, WPA.Stats.calculateTimeForEvent(result)]);
				}
				
				var data = new google.visualization.DataTable();
				data.addColumn('string', 'Date');
		        data.addColumn('number', 'Time');
		        data.addRows(resultArray);
	
	            var options = {
	            	legend: 'none',
	            	theme: 'maximized',
	            	vAxis: {title: (WPA.getProperty('column_time') + ' (' + WPA.Stats.label + ')') },
	            	pointSize: 5,
	            	height: 250,
	            	width: 600,
	            	title: WPA.getProperty('stats_event_chart_title')
	            };
	
	            jQuery('#wpa-stats-event-results-chart' + suffix).show();
	            var chart = new google.visualization.LineChart(document.getElementById('wpa-stats-event-results-chart' + suffix));
	            chart.draw(data, options);	
			}
			else {
				jQuery('#wpa-stats-event-results-chart' + suffix).hide();
			}
		},
		
		/**
		 * Displays chart of most frequent runners for an event
		 */
		displayRunnersChart: function(results, params, suffix) {
			
			if(results.length > 1) {
				
				var resultArray = [];
				
		        var data = new google.visualization.DataTable();
		        data.addColumn('string', 'name');
		        data.addColumn('number', 'count');

		        jQuery.each(results, function(id, result) {
		        	resultArray.push( [result.name, parseInt(result.count) ] );
		        })
		        
		        data.addRows(resultArray);

		        // Set chart options
		        var options = {
		        	legend: 'none',
		        	theme: 'maximized',
		        	height: 300,
		        	width: 700,
		        	title: WPA.getProperty('stats_runners_chart_title')
		        	
		        	//height: 400,
		        	//width: 600
		        };		     
		        
		        WPA.Stats.runnerChart = new google.visualization.BarChart(document.getElementById('wpa-stats-runner' + suffix));
		        WPA.Stats.runnerChart.draw(data, options);	
			}
		},
		
		/**
		 * Processes and displays the summary statistics
		 */
		displaySummaryStats: function(result, params, suffix) {
			var detail = result.summary[0];
			jQuery('#wpa-stats-total-races' + suffix).html(WPA.commaSeparateNumber(detail.count));
			jQuery('#wpa-stats-total-distance' + suffix).html(WPA.formatMeterDisplay(detail.total_distance));
			jQuery('#wpa-stats-total-time' + suffix).html(WPA.displayEventTime(detail.total_time, 'd:h:m:s'));
			jQuery('#wpa-stats-total-wins' + suffix).html(WPA.commaSeparateNumber(detail.wins));
			jQuery('#wpa-stats-total-runner-up' + suffix).html(WPA.commaSeparateNumber(detail.runner_up));
			jQuery('#wpa-stats-total-top-10' + suffix).html(WPA.commaSeparateNumber(detail.top_tens));
			jQuery('#wpa-stats-total-athletes' + suffix).html(WPA.commaSeparateNumber(detail.athletes));
		},
		
		/**
		 * Displays the summary pie charts for event category and type distribution
		 */
		displaySummaryCharts: function(results, params, suffix) {
			var types = results.types;
			var events = results.events;	
			
			// Terrain chart
			if(types && types.length > 1) {
		        // Create the data table.
		        var data = new google.visualization.DataTable();
		        data.addColumn('string', 'terrain');
		        data.addColumn('number', 'count');
		        
		        var typeArray = [];
		        jQuery.each(types, function(id, type) {
		        	typeArray.push( [WPA.getEventSubTypeDescription(type.type), parseInt(type.count) ] );
		        })
		        
		        data.addRows(typeArray);

		        // Set chart options
		        var options = {
		        	legend: 'none',
		        	chartArea: {left:20,right:20,top:0,width:"100%",height:"100%"},
		        	pieSliceText: 'label',
		        	height: 180,
		        	width: 165
		        };		     
		        
		        // Instantiate and draw our chart, passing in some options
		        jQuery('#wpa-stats-summary-terrain-chart' + suffix).show();
		        WPA.Stats.typeChart = new google.visualization.PieChart(document.getElementById('wpa-stats-summary-terrain-chart' + suffix));
		        WPA.Stats.typeChart.draw(data, options);
			}
			else {
				jQuery('#wpa-stats-summary-terrain-chart' + suffix).hide();
			}
			
			// Event summary Chart
			if(events && events.length > 1) {
		        // Create the data table.
		        var data = new google.visualization.DataTable();
		        data.addColumn('string', 'event');
		        data.addColumn('number', 'count');
		        
		        var eventArray = [];
		        jQuery.each(events, function(id, event) {
		        	eventArray.push( [event.name, parseInt(event.count) ] );
		        })
		        
		        data.addRows(eventArray);
		        
		        // Set chart options
		        var options = {
		        	chartArea: {left:20,right:20,top:0,width:"100%",height:"100%"},
		        	pieSliceText: 'label',
		        	height: 180,
		        	width: 250
		        };

		        // Instantiate and draw our chart, passing in some options
		        jQuery('#wpa-stats-summary-event-chart' + suffix).show();
		        WPA.Stats.eventChart = new google.visualization.PieChart(document.getElementById('wpa-stats-summary-event-chart' + suffix));
		        WPA.Stats.eventChart.draw(data, options);
			}
			else {
				jQuery('#wpa-stats-summary-event-chart' + suffix).hide();
			}
		},

		/**
		 * For the user dialog window, changes the user ID
		 */
		changeUserProfile: function(name) {
			
			if(name.indexOf(' ') > -1) {
				name = name.substring(0, name.indexOf(' '));
			}
			
			var label = WPA.getProperty('stats_type_user').replace('<name>', name);
			jQuery('#wpa-stats-type-select-dialog').combobox('setLabelByValue', 'user', label);
		},
		
		/**
		 * Gathers the statistics parameters before the ajax call
		 */
		gatherParams: function(isDialog) {
			return {
				userId: isDialog ? WPA.currentUserProfileId : WPA.userId,
				period: isDialog ? WPA.profileFilterPeriod : WPA.filterPeriod,
				type: isDialog ? WPA.profileFilterType : WPA.filterType,
				ageCat: isDialog ? WPA.profileFilterAge : WPA.filterAge,
				eventCat: isDialog ? WPA.Stats.eventDialog : WPA.Stats.event,
				mode: isDialog ? WPA.Stats.typeDialog : WPA.Stats.type,
				stat: isDialog ? WPA.Stats.statDialog : WPA.Stats.stat
			};
		}

};
