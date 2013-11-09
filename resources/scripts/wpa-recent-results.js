/*
 * Javascript functions for WPA recent results page
 */

WPA.RecentResults = {
		
	/**
	 * Function to generate HTML of recent results
	 */
	displayResults: function() {
		
		var params = {
			year: WPA.RecentResults.filterYear,
			month: WPA.RecentResults.filterMonth
		}
		
		WPA.Ajax.getRecentResults(params, function(result) {
			if(result && result.results) {
				WPA.RecentResults.userPhotos = result.user_photos;
				WPA.RecentResults.printResults(result.results);
			}
		})
		
	},

	/**
	 * Generates and displays the HTML representing recent results
	 */
	printResults: function(results) {

		// clear old results (if any)
		jQuery('#recent-results').children().remove();
		
		// now loops results and output each 
		jQuery(results).each(function(index, result) {
			var html = WPA.RecentResults.generateResultHTML(result);
			jQuery('#recent-results').append(html);
		})
		
		// set profile photos
		jQuery.each(WPA.RecentResults.userPhotos, function(userId, photo) {
			jQuery('.wpa-profile-photo-' + userId).removeClass('wpa-profile-photo-default-small').css('background-image', 'url(' + photo + ')');
		})
		
		// process content (time, links)
		WPA.RecentResults.processResultContent();
	},
	
	/**
	 * Converts the log message to an interactive format
	 */
	processResultContent: function() {
		// convert the time values
		var times = jQuery('.wpa-result-content time');

		jQuery(times).each(function(index, time) {
			var millis = parseFloat(time.innerHTML);
			var eventCatId = jQuery(time).closest('.wpa-result-content').attr('event-cat-id');
			var format = WPA.getEventTimeFormat(eventCatId);
			jQuery(time).html(WPA.displayEventTime(millis, format)).css('fontWeight', 'bold');
		});
		
		// convert user names to links / bold
		var users = jQuery('.wpa-result-content user');
		jQuery(users).each(function(index, user) {
			jQuery(user).addClass('wpa-link-blue').click(function() {
				var userId = jQuery(user).closest('.wpa-result-content').attr('user-id');
				WPA.displayUserProfileDialog(userId);
			});
		});
		
		var photos = jQuery('.wpa-profile-photo-small');
		jQuery(photos).each(function(index, photo) {
			jQuery(photo).click(function() {
				var userId = jQuery(photo).attr('user-id');
				WPA.displayUserProfileDialog(userId);
			});
		});

		// convert events to links / bold
		var events = jQuery('.wpa-result-content event');
		jQuery(events).each(function(index, event) {
			jQuery(event).addClass('wpa-link-blue').click(function() {
				var eventId = jQuery(event).closest('.wpa-result-content').attr('event-id');
				console.log(eventId);
				WPA.displayEventResultsDialog(eventId);
			});
		});
	},
	
	/**
	 * Generates ouput HTML for a single result
	 */
	generateResultHTML: function(result) {
		return '<div class="recent-result">' + 
			'<div user-id="' + result.user_id + '" class="wpa-profile-photo-' + result.user_id + ' wpa-profile-photo-small wpa-profile-photo-default-small"></div>' +
			'<div>' +
				'<span user-id="' + result.user_id + '" event-id="' + result.event_id + '" event-cat-id="' + result.event_cat_id + '" class="wpa-result-content">' + result.content + '</span>' + 
				'<span class="wpa-result-date">' + result.display_date + '</span>' + 
			'</div>' + 
			'<br style="clear:both"/>' + 
		'</div>';
	}
		
}