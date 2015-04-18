/*
 * Javascript functions to manage WPA CRUD operations.
 */

WPA.Ajax = {
		
	/**
	 * sets up the object with the AJAX url and security nonce, also retrieves language properties
	 */
	setup: function(url, nonce, pluginUrl, userId, callbackFn, skipLoadGlobals) {
		if(!WPA.ajaxSetup) {

			// create custom widgets
			initWpaCustom();
			
			WPA.globals.pluginUrl = pluginUrl;
			WPA.userId = userId;
			this.url = url;
			this.nonce = nonce;
			
			if(!skipLoadGlobals) {
				this.loadGlobalData(callbackFn);
			}
			else {
				callbackFn();
			}
		}
		else {
			console.error('Ajax setup function already called');
		}
	},
	
	/**
	 * Gets user profile info
	 */
	getUserProfile: function(userId, callbackFn) {
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_get_user_profile',
				user_id: userId
			},
			success: function(result){
				callbackFn(result);
			}
		});
	},
	
	/**
	 * Gets a user DOB
	 */
	getUserDOB: function(userId, callbackFn) {
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_get_user_dob',
				user_id: userId
			},
			success: function(result){
				callbackFn(result);
			}
		});
	},
	
	/**
	 * Returns the oldest recorded year for a user result
	 */
	getUserOldestResultYear: function(userId, callbackFn) {
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_get_user_oldest_result_year',
				user_id: userId
			},
			success: callbackFn
		});	
	},
	
	/**
	 * stores useful info such as age categories and event types
	 */
	loadGlobalData: function(callbackFn) {
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_load_global_data'
			},
			success: function(result) {
				WPA.ajaxSetup = true;
				WPA.globals.eventTypes = result.eventTypes;
				WPA.globals.ageCategories = result.ageCategories;
				WPA.globals.eventCategories = result.eventCategories;
				WPA.Props = result.languageProperties;
				WPA.Settings = result.settings;

				WPA.userDOB = result.userDOB;
				WPA.userHideDOB = result.userHideDOB;
				WPA.userGender = result.userGender;
				WPA.isLoggedIn = result.isLoggedIn;
				WPA.isAdmin = result.isAdmin;
				WPA.defaultUnit = result.defaultUnit;
				
				if(parseInt(result.pendingResults) > 0) {
					jQuery('.wpa-alert-count').html(result.pendingResults).show();
				}
				
				if(callbackFn) {
					callbackFn();
				}
			}
		});
	},
	
	/**
	 * Saves profile photo as a meta option. If user ID is not suppled, defaults to current user ID. 
	 */
	saveProfilePhoto: function(url, userId, callbackFn) {
		if(!userId) {
			userId = WPA.userId;
		}
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_save_profile_photo',
				url: url,
				userId: userId,
				security: WPA.Ajax.nonce
			},
			success: function(result) {
				if(result && result.filename && callbackFn) {
					callbackFn(result.filename);
				}
			}
		});
	},
	
	/**
	 * Validates that the user hasn't already entered a given event. If userId is not specified, checks for the current
	 * logged in user.
	 */
	validateEventEntry: function(eventId, trueFn, falseFn, userId) {
		WPA.toggleLoading(true);
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_validate_event_entry',
				eventId: eventId,
				userId: userId
			},
			success: function(result){
				WPA.toggleLoading(false);
				if(result.valid) {
					trueFn();
				}
				else {
					WPA.alertError(WPA.getProperty('error_event_already_entered'));
					if(falseFn) {
						falseFn();
					}
				}
			}
		});
	},
	
	/**
	 * deletes an existing user
	 */
	deleteAthlete: function(userId, callbackFn) {
		WPA.toggleLoading(true);
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_delete_user',
				userId: userId
			},
			success: function(result) {
				if(callbackFn) {
					WPA.toggleLoading(false);
					callbackFn(result);
				}
			}
		});
	},

	/**
	 * Edits an existing athlete record
	 */
	editAthlete: function(callbackFn) {
		if(WPA.validateAddEditForm('edit-user-dialog')) {
			WPA.toggleLoading(true);
			jQuery.ajax({
				type: "post",
				url: WPA.Ajax.url,
				data: {
					action: 'wpa_edit_user',
					name: jQuery('#editAthleteName').val(),
					userId: jQuery('#editAthleteId').val(),
					gender: jQuery('#editAthleteGender').val(),
					email: jQuery('#editAthleteEmail').val(),
					dob: jQuery('#editAthleteDob').val()
				},
				success: function(result) {
					WPA.toggleLoading(false);
					if(callbackFn) {
						if(result.error) {
							WPA.alertError(result.error);
						}
						else {
							callbackFn(result);
						}
					}
				}
			});
		}
	},
	
	/**
	 * Creates a new athlete from the new athlete dialog
	 */
	createAthlete: function(callbackFn) {
		var name = jQuery('#createAthleteName').val();
		if(name != '') {
			WPA.toggleLoading(true);
			jQuery.ajax({
				type: "post",
				url: WPA.Ajax.url,
				data: {
					action: 'wpa_create_user',
					name: name,
					username: jQuery('#createAthleteUsername').val(),
					gender: jQuery('#createAthleteGender').val(),
					email: jQuery('#createAthleteEmail').val(),
					sendEmail: jQuery('#createAthleteSendDetails').is(':checked'),
					dob: jQuery('#createAthleteDob').val()
				},
				success: function(result) {
					WPA.toggleLoading(false);
					if(callbackFn) {
						if(result.error) {
							WPA.alertError(result.error);
						}
						else {
							if(jQuery('#createAthleteSendDetails').is(':checked') == false || jQuery('#createAthleteEmail').val() == '') {
								// show dialog with athlete info
								jQuery('#create-user-success-dialog span').html(result.username + ' / ' + result.password);
								jQuery('#create-user-success-dialog').dialog({
									title: WPA.getProperty('add_result_create_user_success_dialog_title'),
									autoOpen: true,
									resizable: false,
									modal: true,
									height: 'auto',
									width: 300,
									buttons: [{
								    	text: WPA.getProperty('ok'),
								    	click: function() {
								    		jQuery('#create-user-success-dialog').dialog('close');
									    }
								    }]
								});
							}
							callbackFn(result);
						}
					}
				}
			});
		}
		else {
			jQuery('#createAthleteName').addClass('ui-state-error');
		}
	},
	
	/**
	 * Saves profile information to the user meta data table
	 */
	saveProfileData: function(data, element, callbackFn) {
		
		data['action'] = 'wpa_save_profile_data';
		data['security'] = WPA.Ajax.nonce;

		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: data,
			success: function(result){
				if(result.success) {
					if(element) {
						element.effect("highlight", {color: '#63ec39'}, 1000);
					}
					if(callbackFn) {
						callbackFn();
					}
				}
			}
		});
	},
	
	/**
	 * Performs delete action for a given event result
	 */
	deleteResult: function(id, callbackFn) {
		WPA.toggleLoading(true);
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_delete_result',
				security: WPA.Ajax.nonce,
				resultId: id
			},
			success: function(result){
				WPA.toggleLoading(false);
				if(result.success) {
					if(callbackFn) {
						callbackFn();
					}
				}
			}
		});
	},
	
	/**
	 * Deletes a given set of results IDs
	 */
	deleteResults: function(ids, callbackFn) {
		WPA.toggleLoading(true);
		ids = WPA.Ajax.toIdString(ids);
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_delete_results',
				security: WPA.Ajax.nonce,
				ids: ids
			},
			success: function(result) {
				WPA.toggleLoading(false);
				if(callbackFn) {
					callbackFn(result);
				}
			}
		});
	},
	
	/**
	 * Reassigns a set of results to another user ID
	 */
	reassignResults: function(ids, reassignId, callbackFn) {
		WPA.toggleLoading(true);
		ids = WPA.Ajax.toIdString(ids);
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_reassign_results',
				security: WPA.Ajax.nonce,
				reassignId: reassignId,
				ids: ids
			},
			success: function(result) {
				WPA.toggleLoading(false);
				if(callbackFn) {
					callbackFn(result);
				}
			}
		});
	},
	
	/**
	 * Retrieves result information for the update result screen
	 */
	loadResultInfo: function(id) {
		WPA.toggleLoading(true);
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_get_result_info',
				security: WPA.Ajax.nonce,
				resultId: id
			},
			success: function(result){
				WPA.toggleLoading(false);
				if(result) {
					WPA.setResultUpdateInfo(result);
				}
			}
		});
	},
	
	/**
	 * Creates or updates an event result to the database
	 */
	updateResult: function(data, callbackFn) {
		data['action'] = 'wpa_update_result';
		data['security'] = WPA.Ajax.nonce;
		
		jQuery.ajax({
			type: 'post',
			url: WPA.Ajax.url,
			data: data,
			success: callbackFn
		})
	},
	
	/**
	 * Edits or creates a single event details
	 */
	updateEvent: function(data, callbackFn, isCreate) {
		WPA.toggleLoading(true);
		
		data.action = isCreate ? 'wpa_create_event' : 'wpa_update_event';
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: data,
			success: function(result) {
				WPA.toggleLoading(false);
				callbackFn(result);
			}
		});
	},
	
	
	/**
	 * Retrieves single event info based on ID
	 */
	getEventInfo: function(id, callbackFn, noLoadingMask) {
		if(!noLoadingMask) {
			WPA.toggleLoading(true);
		}
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_get_event',
				eventId: id
			},
			success: function(result) {
				if(!noLoadingMask) {
					WPA.toggleLoading(false);
				}
				callbackFn(result);
			}
		});
	},
	
	/**
	 * Retrieves results for a particular event
	 */
	getEventResults: function(id, callbackFn) {
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_get_event_results',
				eventId: id
			},
			success: function(result) {
				if(result.id == id) {
					callbackFn(result.data, result.id)
				}
			}
		});
	},
	
	/**
	 * Merges 2 or more events. Reads in an array of events and a primary event ID for which to merge all the other results to.
	 */
	mergeEvents: function(ids, primaryId, callbackFn) {
		// convert array to a string for transmitting via ajax
		if(ids.length > 0) {
			var idStr = '';
			jQuery.each(ids, function(index, id) {
				// do not include the primary Id for deletion
				if(id != primaryId) {
					idStr += id + ',';
				}
			})
		
			// remove trailing comma
			idStr = idStr.substring(0, idStr.length-1);

			// the ajax call!
			jQuery.ajax({
				type: "post",
				url: WPA.Ajax.url,
				data: {
					action: 'wpa_merge_events',
					ids: idStr,
					reassignId: primaryId
				},
				success: callbackFn
			});
		}
	},
	
	/**
	 * converts an array of ids to a string for transmitting via ajax
	 */
	toIdString: function(ids) {
		idStr = '';
		
		if(ids.length > 0) {
			jQuery.each(ids, function(index, id) {
				idStr += id + ',';
			})
		
			// remove trailing comma
			idStr = idStr.substring(0, idStr.length-1);
		}
		
		return idStr;
	},
	
	/**
	 * Deletes event(s). Reads in an array of ids and an event id of which to reassign the results for the deleted events. 
	 */
	deleteEvents: function(ids, reassignId, callbackFn) {
		WPA.toggleLoading(true);
		ids = WPA.Ajax.toIdString(ids);

		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_delete_events',
				ids: idStr,
				reassignId: reassignId
			},
			success: function(result) {
				WPA.toggleLoading(false);
				callbackFn(result);
			}
		});
	},
	
	/** 
	 * Retrieves a list of personal bests. If age category or event category ID specified, will be filtered.
	 */
	getPersonalBests: function(callbackFn, params, disableLoading) {
		
		if(!disableLoading) {
			WPA.togglePbLoading(true);
		}

		var data = {action: 'wpa_get_personal_bests'};
		
		if(params) {
			jQuery.extend(data, params);
		}
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: data,
			success: function(result) {
				callbackFn(result);
				if(!disableLoading) {
					WPA.togglePbLoading(false);
				}
			}
		});
	},
	
	/**
	 * Retrieves statistics for a given criteria
	 */
	getStatistics: function(params, callbackFn) {
		WPA.toggleLoading(true);
		params.action = 'wpa_get_stats';
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: params,
			success: function(result) {
				WPA.toggleLoading(false);
				callbackFn(result);
			}
		});
	},
	
	/**
	 * Retrieves a list of results based on search criteria
	 */
	getGenericResults: function(params, callbackFn) {
		WPA.toggleLoading(true);
		params.action = 'wpa_get_generic_results';
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: params,
			success: function(result) {
				WPA.toggleLoading(false);
				callbackFn(result);
			}
		});
	},
	
	/**
	 * Retrieves a list of recent results
	 */
	getRecentResults: function(params, callbackFn) {
		WPA.toggleLoading(true);
		params.action = 'wpa_get_recent_results';
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: params,
			success: function(result) {
				WPA.toggleLoading(false);
				callbackFn(result);
			}
		});
	},
	
	/**
	 * Adds a pending result record to an event for a given user
	 */
	goingToEvent: function(eventId, callbackFn) {
		WPA.toggleLoading(true);
		var params = {
			'eventId': eventId,
			'userId': WPA.userId,
			'action': 'wpa_going_to_event'
		}
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: params,
			success: function(result) {
				WPA.toggleLoading(false);
				callbackFn(result);
			}
		});
	},
	 
	/**
	 * Retrieves a list of events for a given year
	 */
	getEvents: function(params, callbackFn) {
		WPA.toggleLoading(true);
		params.action = 'wpa_get_events_for_year';
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: params,
			success: function(result) {
				WPA.toggleLoading(false);
				callbackFn(result);
			}
		});
	}
}