/*
 * Javascript functions for WPA Admin.
 */


WPA.Admin = {
		
	/**
	 * Saves the admin settings
	 */
	saveSettings: function(callbackFn) {
		
		var data = {
			language: jQuery('#setting-language').val(),
			disableSqlView: jQuery('#setting-disable-sql-view').val(),
			theme: jQuery('#setting-theme').val(),
			recordsMode: jQuery('#setting-records-mode').val(),
			clubName: jQuery('#club-name').val(),
			defaultUnit: jQuery('#setting-default-unit').val(),
			submitEvents: jQuery('#setting-allow-submit-events').val(),
			enableNonWPA: jQuery('#setting-non-wpa-pages').val(),
			action: 'wpa_admin_save_settings'
		}
		
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: data,
			success: callbackFn
		});
	},
	
	/**
	 * Forces update of tables, view and indexes
	 */
	updateDatabase: function() {
		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: {
				action: 'wpa_admin_update_db'
			},
			success: function(result) {
				alert('Finished');
			}
		});
	},
	
	/**
	 * globals for storing checked records
	 */
	selectedRecords: [],
	selectedResultCount: 0,

	/**
	 * Wrapper event for selectRecordToggle() to allow it be called explitly
	 */
	selectRecordToggleWrapper: function() {
		WPA.Admin.selectRecordToggle(this);
	},
	
	/**
	 * Show the edit athlete changes
	 */
	displayEditAthlete: function(id, dob, name, gender, email) {
		jQuery('#editAthleteName').val(name);
		jQuery('#editAthleteEmail').val(email);
		jQuery('#editAthleteGender').val(gender);
		jQuery('#editAthleteId').val(id);
		jQuery('#editAthleteDob').val(dob != 'null' ? dob : '');
		jQuery('#edit-user-dialog').dialog('open');
	},
	
	/**
	 * Listener for when an checkbox input on an admin datatable is checked/unchecked
	 */
	selectRecordToggle: function(obj) {
		var id = jQuery(obj).attr('record-id');
		var resultCount = parseInt(jQuery(obj).attr('result-count'));
	
		if(jQuery(obj).prop('checked')) {
			WPA.Admin.selectedRecords.push(id);
			WPA.Admin.selectedResultCount += resultCount;
		}
		else {
			WPA.Admin.selectedRecords.splice( jQuery.inArray(id, WPA.Admin.selectedRecords), 1 );
			WPA.Admin.selectedResultCount -= resultCount;
		}
		WPA.Admin.toggleSelectOptions();
	},

	/**
	 * For toggling all checkboxes on an admin datatable
	 */
	toggleSelectOptions: function() {
		if(WPA.Admin.selectedRecords.length) {
			jQuery('.wpa-select-options').show();
		}
		else {
			jQuery('.wpa-select-options').hide();
		}
	},
	
	/**
	 * Resets the checkbox select globals
	 */
	resetSelectValues: function() {
		WPA.Admin.selectedRecords = [];
		WPA.Admin.selectedResultCount = 0;
		WPA.Admin.toggleSelectOptions();
	},
	
	/**
	 * Sets up a listener for the 'select all' checkbox in the datatable header
	 */
	configureSelectAllCheckboxes: function() {
		jQuery('#datatable-select-all').change(function() {
			WPA.Admin.selectedRecords = [];
			WPA.Admin.selectedResultCount = 0;

			var checked = jQuery(this).prop('checked');
			jQuery('table input[type=checkbox]').each(function() {

				var recordAttr = jQuery(this).attr('record-id');

				if (typeof recordAttr !== 'undefined' && recordAttr !== false) {
					jQuery(this).prop('checked', checked);
					WPA.Admin.selectRecordToggle(this);
				}
			});

			if(!checked) {
				WPA.Admin.selectedRecords = [];
				WPA.Admin.selectedResultCount = 0;
			}
		});
	}
}