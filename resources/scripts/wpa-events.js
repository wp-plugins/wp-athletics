/*
 * Javascript functions for WPA events page
 */

WPA.Events = {
		
	/**
	 * Function to generate HTML of recent results
	 */
	displayResults: function() {

		var params = {
			year: WPA.Events.filterYear
		}
		
		WPA.Ajax.getEvents(params, function(result) {
			if(result && result.results) {
				WPA.Events.printEvents(result.results);
			}
		})
		
	},

	/**
	 * Generates and displays the HTML representing events
	 */
	printEvents: function(results) {
		// clear old results (if any)
		jQuery('.feed-content-empty').hide();
		jQuery('.event-month').hide();
		jQuery('.wpa-event').remove();
		
		if(results.length) {
			// now loops results and output each 
			jQuery(results).each(function(index, result) {
				WPA.Events.generateEventHTML(result);
			})
			
			// process content (time, links)
			WPA.Events.processContent();
			
			// add result button
			jQuery('button.event-add-result').button({
				icons: {
	              primary: 'ui-icon-circle-plus'
	            }
			}).click(function() {
				var eventId = jQuery(this).attr('event-id');
				WPA.launchAddResultDialog(eventId, true);
			});
			
			// never went button
			jQuery('button.event-never-ran').button().click(function() {
				var resultId = jQuery(this).attr('result-id');
				WPA.Ajax.deleteResult(resultId, function() {
					WPA.Events.displayResults();
				});
			});
			
			// going to event button
			jQuery('button.event-im-going').button().click(function() {
				var eventId = jQuery(this).attr('event-id');
				WPA.Ajax.goingToEvent(eventId, function(result) {
					if(result.success) {
						WPA.Events.displayResults();
					}
				});
			});
			
			// pending result, add the time
			jQuery('button.event-add-pending-result').button().click(function() {
				var eventId = jQuery(this).attr('result-id');
				WPA.editResult(eventId, WPA.userId);
			});
			
			jQuery('#wpa-events-legend').show();
		}
		else {
			jQuery('.feed-content-empty').show();
			jQuery('#wpa-events-legend').hide();
		}
	},
	
	/**
	 * Converts the log message to an interactive format
	 */
	processContent: function() {
		// convert events to links / bold
		var futureEvents = jQuery('.future event');
		jQuery(futureEvents).each(function(index, event) {
			jQuery(event).addClass('wpa-link-blue').click(function() {
				var eventId = jQuery(event).closest('span').attr('event-id');
				WPA.displayEventResultsDialog(eventId, true);
			});
		});
		
		var pastEvents = jQuery('.past event');
		jQuery(pastEvents).each(function(index, event) {
			jQuery(event).addClass('wpa-link-blue').click(function() {
				var eventId = jQuery(event).closest('span').attr('event-id');
				WPA.displayEventResultsDialog(eventId);
			});
		});
	},
	
	/**
	 * Generates the HTML for the going/gone link on each event result
	 */
	generateGoingGoneHTML: function(result) {
		if(WPA.isLoggedIn) {
			if(result.is_future == '1') {
				if(result.pending_result_id == null) {
					return '<br/><button event-id="' + result.event_id + '" class="event-im-going">' + WPA.getProperty('event_im_going_text') + '</button>'
				}
				else {
					return '<br/><span class="wpa-my-event">' + WPA.getProperty('event_youre_going_text') + '</span>';
				}
			}
			else {
				if(parseInt(result.pending_result_id) > 0) {
					return '<br/><button id="event-add-pending-button" result-id="' + result.pending_result_id + '" class="event-add-pending-result">' + WPA.getProperty('add_my_result_text') + '</button>' + 
					'&nbsp;<button result-id="' + result.pending_result_id + '" class="event-never-ran">' + WPA.getProperty('event_i_didnt_go_text') + '</button>';
				}
				else if(result.has_result == '0') {
					return '<br/><button event-id="' + result.event_id + '" class="event-add-result">' + WPA.getProperty('event_i_ran_this_text') + '</button>'
				}
				else if(result.has_result == '1') {
					return '<br/><span class="wpa-my-event">' + WPA.getProperty('event_you_ran_this_text') + '</span>';
				}
			}
		}
		else {
			return '';
		}
	},
	
	/**
	 * Generates ouput HTML for a single result
	 */
	generateEventHTML: function(result) {
		jQuery('.month-' + result.month).append(
			'<div class="wpa-event' + (result.is_future == '1' ? ' future-event' : '') + '">' + 
				'<div class="wpa-event-left">' +
					'<span event-id="' + result.event_id + '" class="wpa-event-content wpa-event-title ' + (result.is_future == '1' ? 'future' : 'past') + '"><event>' + result.name + (result.location ? (', ' + result.location) : '') + '</event></span>' + 
					'<br/><span class="wpa-result-date">' + result.display_date + '</span>' + 
				'</div>' + 
				'<div class="wpa-event-right">' +
					'<span event-id="' + result.event_id + '" class="wpa-event-count ' + (result.is_future == '1' ? 'future' : 'past') + '"><event>' + result.count + ' ' + 
					(result.is_future == '1' ? WPA.getProperty('event_runners_going') : (parseInt(result.count) == 1 ? WPA.getProperty('event_result_count') : WPA.getProperty('event_results_count')))  +
					'</event></span>' +
					WPA.Events.generateGoingGoneHTML(result) + 
				'</div>' + 
				'<br style="clear:both"/>' +
			'</div>'
		).show();
	}
		
}