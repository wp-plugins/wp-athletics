<?php

$common_lang = array(
	// results/records tables
	'activity_link_text' => 'View activity',
	'rankings_link_text' => 'View club rankings for this event',
	'column_event_date' => 'Event Date',
	'column_result_date' => 'Date Entered',
	'column_event_type' => 'Terrain',
	'column_event_name' => 'Event',
	'column_athlete_name' => 'Athlete',
	'column_athlete_username' => 'Username',
	'column_athlete_email' => 'Email',
	'column_athlete_registered' => 'Registered',
	'column_event_location' => 'Location',
	'column_category' => 'Distance',
	'column_time' => 'Time',
	'column_pace' => 'Pace',
	'column_position' => 'Pos.',
	'column_garmin' => 'Activity',
	'column_athlete_name' => 'Name',
	'column_members_attending' => 'Members Attending',
	'column_athlete_dob' => 'DOB',
	'column_age_category' => 'Class',
	'column_rankings' => 'Rankings',
	'column_club_rank' => 'Rank',
	'column_result_count' => 'Result Count',
	'column_event_shortcode' => 'Embed Code',
	'column_age_grade' => 'Age Grade',

	// my results - profile
	'my_profile_display_name_label' => 'Athlete Name',
	'my_profile_age_class' => 'Age Class',
	'my_profile_select_fave_event' => 'Select Event',
	'my_profile_fave_event' => 'Favourite Event',
	'my_profile_results_recorded' => 'Results Recorded',
	'my_profile_dob' => 'D.O.B',
	'my_profile_hide_dob' => 'Hide D.O.B',
	'my_profile_gender' => 'Gender',
	'my_profile_select_sub_type' => 'Select Terrain',
	'my_profile_image_upload_text' => 'Click to upload new profile photo',
	'my_profile_select_profile_image' => 'Select as profile photo',
	'my_profile_select_profile_image_title' => 'Select Profile Photo',
	'my_profile_upcoming_events' => 'Upcoming Events',

	// filters
	'filter_events_option_all' => 'All Events',
	'filter_period_option_all' => 'All Time',
	'filter_period_future_events' => 'Future Events',
	'filter_month_all' => 'All Months',
	'filter_period_option_this_month' => 'This Month',
	'filter_period_option_this_year' => 'This Year',
	'filter_period_option_next_year' => 'Next Year',
	'filter_type_option_all' => 'All Terrains',
	'filter_age_option_all' => 'All Age Classes',
	'filter_event_name_input_text' => 'Filter event name',
	'filter_event_name_cancel_text' => 'Remove event name filter',
	'filter_athlete_name_input_text' => 'Filter athlete name',
	'filter_athlete_name_cancel_text' => 'Remove athlete name filter',

	// user profile dialog
	'user_profile_dialog_title' => 'User Profile',

	// event results dialog
	'event_results_dialog_title' => 'Event Results',
	'future_event_dialog_title' => "Upcoming Event Details",
	'event_dialog_future_event_info' => 'This event is in the future.',
		
	// create user dialog
	'create_user_dialog_title' => 'Add New Athlete',

	// generic results dialog
	'generic_results_dialog_title' => 'Results Viewer',

	// rankings dialog - ** DO NOT TRANSLATE THE PROPERTIES IN [brackets] AS THESE ARE TOKENS THAT WILL BE REPLACED WHEN RENDERED **
	'rankings_dialog_title' => '[age] [gender] [category] Rankings ([period], [type])',
	'rankings_display_best_athlete_result' => 'Show only best result for each athlete',
	'rankings_display_all_athlete_results' => 'Show all results for each athlete',
	'rankings_column_hover_text' => 'Click for full rankings',

	// my results - tabs
	'my_results_main_tab' => 'My Results',
	'my_results_personal_bests_tab' => 'My Personal Bests',
	'my_results_upcoming_events_tab' => 'My Events',

	// results - tabs
	'results_main_tab' => 'Results',
	'results_personal_bests_tab' => 'Personal Bests',

	// wpa search
	'wpa_search_text' => 'Search athlete or event',
	'wpa_search_category_event' => 'Events',
	'wpa_search_category_athlete' => 'Athletes',

	// my_results - add result
	'add_result_event_name' => 'Event Name',
	'add_result_name' => 'Name',
	'add_result_event_category' => 'Distance',
	'add_result_age_class' => 'Age Class',
	'add_result_location' => 'Location',
	'add_result_event_date' => 'Date',
	'add_result_event_position' => 'Position',
	'add_result_event_time_hours' => 'Hours',
	'add_result_event_time_minutes' => 'Minutes',
	'add_result_event_time_seconds' => 'Seconds',
	'add_result_event_time_milliseconds' => 'Milliseconds',
	'add_result_activity_link' => 'Activity URL',
	'add_result_select_event' => 'Select Event Category',
	'add_result_optional' => 'optional',
	'add_result_event_sub_type' => 'Terrain',
	'add_result_title' => 'Add Event Result',
	'add_my_result_text' => 'Add My Result',
	'add_result_title_event_dialog' => 'Add My Result',
	'edit_result_title' => 'Edit Event Result',
	'add_result_no_dob' => 'You have not set your date of birth. This means your age grade % will be based on the averge age of the selected age class. <br/><br/>
		To ensure an accurate age grade %, set your date of birth.',
		
	// add/edit event
	'add_event_contact_email' => 'Contact Email',
	'add_event_contact_name' => 'Contact Name',
	'add_event_contact_number' => 'Contact Phone',
	'add_event_url' => 'Event Website',
	'add_event_register_url' => 'Register Link',
	'add_event_cost' => 'Cost',
	'add_event_details' => 'Event Details',
	'add_event_more_detail' => 'Show more detail',

	// results - other
	'my_results_add_result_button' => 'Add Event Result',
	'delete_result_text' => 'Delete this result (cannot be undone)',
	'edit_result_text' => 'Edit this result',
	'confirm_result_delete_title' => 'Confirm Delete Result',
	'confirm_result_delete' => 'Are you sure you wish to delete this result? This cannot be undone',
	'my_results_dob_changed' => 'Your date of birth has changed. All of your results have been updated to ensure the correct age grade % is applied.',

	// help texts
	'help_add_result_activity_url' => 'If you have the activity recorded on an external website (e.g Garmin, Strava, RunKeeper etc), copy and paste the full link here',
	'help_add_result_event_name' => 'The event may already exist in our database, start typing the event name and select if it appears in the drop down menu. Otherwise a new event will be created, in this case please try to record the event details as accurately possible.',
	'help_add_result_cancel_event' => 'Remove chosen event',
	'help_column_rank' => 'Represents an all-time club ranking for this result based on gender, event category and age class',
	'help_column_age_grade' => 'Age grading is a way of putting all race participants on a level playing field, regardless of age or gender. Age graded scores let you compare your race times to those of different runners, as well as to the standard for your age and gender.
		<br/><br/>Each value is calculate using 2015 age-grading factors from the World Masters Association (WMA)<br/><br/>
		<b>The percentiles can be interpreted as follows</b><br/><br/>
		<br/><b>- Over 90%</b> World class</li>
		<br/><b>- Over 80%</b> National class</li>
		<br/><b>- Over 70%</b> Regional class</li>
		<br/><b>- Over 60%</b> Local class</li><br/><br/>',

	// datatables
	'table_no_results' => 'There are no records to display',
	'table_loading_message' => 'Loading Results...',
	'table_loading_records_message' => 'Loading Records...',

	// errors
	'ajax_no_permission' => 'You do not have permission to perform this request',
	'my_results_not_logged_in' => 'You must be logged in to manage your athletic results. Please login or register',
	'error_problem_creating_event' => 'There was a problem creating the new event, please try again later',
	'error_add_result_no_gender_dob' => 'You have not entered your gender and date of birth. Please fill in these details on the "Manage Results" page before you add a result, this way we can accurately classify your result against others',
	'error_dialog_title' => 'Error',
	'alert_dialog_title' => 'Alert',
	'error_no_age_category' => 'Could not determine an age category. Please check and try again or manually select an age category below',
	'error_event_already_entered' => 'You have already recorded your result for this event.',
	'error_event_already_entered_pending' => 'You have already registered your future participation in this event. You can enter your result once the event has completed',
	'error_not_logged_in' => 'You must be logged in to perform this action. If you do not have an account, please register',

	// records page
	'all_age_classes' => 'All age classes',
	'all_age_classes_label' => 'All',

	// labels for buttons etc
	'delete' => 'Delete',
	'submit' => 'Submit',
	'ok' => 'OK',
	'cancel' => 'Cancel',
	'save' => 'Save',
	'edit' => 'Edit',
	'go' => 'Go',

	// misc
 	'gender_M' => 'Male',
	'gender_F' => 'Female',
	'gender_B' => 'All Genders',
	'loading_dialog_text' => 'One moment...',
	'edit_event_dialog_title' => 'Edit Event',
	'create_event_dialog_title' => 'Create Event',
	'embedded_event_results_club_records_link' => 'View all Club Records',
	'embedded_event_results_add_result_link' => 'Add My Result',
	'embedded_event_results_male_records_link' => 'View Male Records',
	'embedded_event_results_female_records_link' => 'View Female Records',
	'embedded_event_results_recent_results_link' => 'Recent Results',
	'embedded_event_results_events_link' => 'View Events',
	'embedded_event_results_manage_link' => 'Manage My Results',
	'embeded_event_results_link_select' => 'Jump to...',
	'embedded_event_results_error_no_id' => '[WPA Error: ID for the event was not supplied]',
	'time_invalid_text' => 'Invalid Time',
	'time_no_value_text' => 'Unavavilable',
	'time_pending_value_text' => 'Pending',
	'time_days_label' => 'days',
	'page_loading_text' => 'Content loading...',
	'recent_results_empty_text' => 'No results to display right now',
	'events_empty_text' => 'No events available',
	'recent_results_empty_add_result' => 'Why not add your own race results?',
	'events_empty_submit_event' => 'Why not submit an upcoming event?',
	'event_runners_going' => 'going',
	'event_results_count' => 'members ran this',
	'event_result_count' => 'member ran this',
	'event_youre_going_text' => "You're going",
	'event_im_going_text' => "I'm going",	
	'event_im_not_going_text' => "I'm not going",
	'event_you_ran_this_text' => 'You ran this',
	'event_i_ran_this_text' => 'I ran this',
	'event_i_didnt_go_text' => "I didn't go",
	'event_you_ran_this_pending_text' => 'You ran this, add your result',
	'legend_future_events' => 'Future Events',
	'submit_event_button' => 'Submit an Event',
	'yes' => 'Yes',
	'no' => 'No',
	'na' => 'N/A',
	
	// widgets
	'results_widget_recent_results_link' => 'View all Recent Results',
	'events_widget_upcoming_events_link' => 'View all Events',
	'widget_recent_results_no_results' => 'No results right now. Register and add your results!',
	'widget_no_upcoming_events' => 'No upcoming events right now',

	// stats
	'stats_tab' => 'Statistics',
	'stats_type_label' => 'Viewing',
	'stats_type_user' => "<name>'s Statistics",
	'stats_type_mine' => 'My Statistics',
	'stats_type_club' => 'Club Statistics',
	'stats_event_label' => 'Event',
	'stats_event_combo_default' => 'Select Event',
	'stats_events_default_message' => 'Please choose an event category above',
	'stats_filter_note' => '<b>Note:</b> Use the drop-down filters above to further customise statistics',
	'stats_heading_summary' => 'Summary',
	'stats_events_not_enough_results' => 'There are no results available',
	'stats_heading_events' => 'Event Statistics',
	'stats_heading_runner' => 'Athlete Statistics',
	'stats_label_total_races' => 'Races',
	'stats_label_total_distance' => 'Distance Covered',
	'stats_label_total_time' => 'Total Racing Time',
	'stats_label_total_wins' => 'Wins',
	'stats_label_total_runner_up' => 'Runner Up',
	'stats_label_total_top_10' => 'Top 10 Finishes',
	'stats_label_total_athletes' => 'Club Athletes',
	'stats_label_event_name' => 'Event',
	'stats_label_event_best' => 'Best Result',
	'stats_label_event_worst' => 'Worst Result',
	'stats_label_event_avg' => 'Average',
	'stats_event_chart_title' => 'Recent Performance',
	'stats_runners_chart_title' => 'Top 10 Busiest Racers',

	// pace
	'pace_minute' => 'min',
	'pace_m' => 'mile',
	'pace_km' => 'km',

	// measurements
	'km' => 'km',
	'mile' => 'miles',

	// pages
	'my_results_page_title' => 'Manage Results',
	'recent_results_page_title' => 'Recent Results',
	'records_male_page_title' => 'Male Club Records',
	'records_female_page_title' => 'Female Club Records',
	'records_page_title' => 'Club Records',
	'events_page_title' => 'Events',

	// logs
	'new_result' => '<user>{name}</user> ran a time of <time>{result}</time> at the <event>{event-name}</event>',
	'new_result_position_addon' => ' and came {position} overall',
	'update_result' => '<user>{name}</user> updated the result for <event>{event-name}</event>',
	'new_event' => '<user>{name}</user> has created a new event <event>{event-name}</event>',
	'user_login' => '<user>{name}</user> has logged in',
	'profile_update' => '<user>{name}</user> has updated their profile information',
	'log_max_note' => 'Note: A maximum of 1000 log entries will be displayed',

	'filter_log_type_option_all' => 'All Log Types',
	'filter_log_type_option_user_login' => 'User Logins',
	'filter_log_type_option_new_result' => 'Result Added',
	'filter_log_type_option_update_result' => 'Result Edited',
	'filter_log_type_option_new_event' => 'Events Created',
	'filter_log_type_option_profile_update' => 'Profile Update',
	'filter_log_type_option_SQL' => 'SQL',
		
	'filter_events_all' => 'All Events',
	'filter_events_my_events' => 'My Events',
	'filter_events_future_events' => 'Future Events',

	// date & time
	'month_1' => 'January',
	'month_2' => 'February',
	'month_3' => 'March',
	'month_4' => 'April',
	'month_5' => 'May',
	'month_6' => 'June',
	'month_7' => 'July',
	'month_8' => 'August',
	'month_9' => 'September',
	'month_10' => 'October',
	'month_11' => 'November',
	'month_12' => 'December',
);

$admin_lang = array(
	// event category table
	'admin_column_event_cat_name' => 'Name',
	'admin_column_event_cat_distance' => 'Distance',
	'admin_column_event_cat_unit' => 'Unit',
	'admin_column_event_cat_time_format' => 'Time Format',
	'admin_column_event_cat_show_records' => 'Show Records',
	'admin_column_age_cat_id' => 'ID',
	'admin_column_age_cat_name' => 'Name',
	'admin_column_age_cat_from' => 'From (years)',
	'admin_column_age_cat_to' => 'To (years)',
		
	// settings
	'admin_settings_label_button_save' => 'Save Settings',
	'admin_settings_label_button_saving' => 'Saving...',
	'admin_settings_label_button_saved' => 'Saved!',
	'admin_settings_tab_label_general' => 'General',
	'admin_settings_tab_label_advanced' => 'Advanced',
	'admin_settings_label_theme' => 'Theme',
	'admin_settings_label_club_name' => 'Club Name',
	'admin_settings_label_language' => 'Language',
	'admin_settings_label_unit' => 'Default Unit',
	'admin_settings_label_update_db' => 'Force DB Update',
	'admin_settings_label_disable_sql_view' => 'Disable SQL View',
	'admin_settings_label_records_mode' => 'Records Mode',
	'admin_settings_submit_events_label' => 'Users can submit events',
	'admin_settings_record_label_separate' => 'Separate',
	'admin_settings_record_label_combined' => 'Combined',
	'admin_settings_label_enable_non_wpa' => 'Enable on non-WPA pages',
	'admin_settings_help_disable_sql_view' => 'Choose this option if your hosting provider does not allow the creation of an SQL VIEW.',
	'admin_settings_help_records_mode' => 'Choose \'Separate\' if you wish to use two separate pages for club records (male and female) or choose \'Combined\' if you would rather a single page with a dropdown menu allowing users to filter by gender',
	'admin_settings_help_submit_events' => 'Allow your athletes to submit both past and future events on the events page. By default this is allowed',
	'admin_settings_help_non_wpa' => 'If set to yes, loads the WPA plugin on all pages throughout your site. This allows WPA interactivity (loading user/event dialogs) with widgets if you have them enabled. This has been known to cause issues on some sites',
		
	// column help
	'admin_edit_event_cat_column_unit' => 'Valid units are \'m\' (meters), \'km\' (kilometers) and \'mile\' (miles)',
	'admin_edit_event_cat_column_time_format' => 'Enter the time display format for this event category. The values should be separated by a colon and valid parameters are \'h\' (hours), \'m\' (minutes) \'s\' (seconds) and \'ms\' (milliseconds). This field also determines what inputs are displayed when entering a time for this event category on the dialog for adding results. Example: \'h:m:s\'',
	'admin_edit_event_cat_column_show_records' => 'Tick this box if you want this event category to be included in club records',

	// errors
	'admin_edit_event_cat_invalid_name' => 'You have not entered a valid name',
	'admin_edit_event_cat_invalid_unit' => 'You have not entered a valid unit',
	'admin_edit_event_cat_invalid_distance' => 'You have not entered a valid distance',
	'admin_edit_event_cat_invalid_time_format' => 'You have not entered a valid time format',
	'admin_edit_age_cat_invalid_name' => 'You have not entered a valid name',
	'admin_edit_age_cat_invalid_from_year' => 'You have not entered a valid from year value',
	'admin_edit_age_cat_invalid_to_year' => 'You have not entered a valid to year value',
	'admin_edit_age_cat_invalid_range' => 'The age range conflicts with the existing age category: ',
	'admin_edit_age_cat_from_greater_than_to' => 'The \'from\' value must be less than the \'to\' value',

	// buttons
	'admin_edit_event_cat_create_button' => 'Create Event Category',
	'admin_edit_age_cat_create_button' => 'Create Age Category',

	// page titles
	'admin_edit_event_cat_title' => 'Event Category Settings',
	'admin_edit_age_cat_title' => 'Age Category Settings',
	'admin_manage_results_title' => 'Manage Results',
	'admin_manage_athletes_title' => 'Manage Athletes',
	'admin_manage_events_title' => 'Manage Events',
	'admin_print_rankings_title' => 'Print Rankings',
		
	// athlete manager
	'admin_athlete_create_button' => 'Add New Athlete',
	'delete_athlete_text' => 'Are you sure you wish to remove this athlete and all associated results?',
	'delete_athlete_title' => 'Delete Athlete?',
	'delete_athlete_tooltip' => 'Delete this athlete (cannot be undone)',
	'edit_athlete_tooltip' => 'Edit this athlete',
	'email_details_text' => 'Send this athlete their login details',

	// event manager
	'edit_event_text' => 'Edit Event',
	'delete_event_text' => 'Delete Event',
	'delete_selected_events_text' => 'Delete Selected Events',
	'delete_events_reassign_results_text' => 'Choose event to reassign results',
	'delete_events_confirm_title' => 'Delete Event(s)',
	'delete_events_warning_title' => 'WARNING',
	'delete_events_text' => 'You are about to delete <span id="event-count"></span> event(s). Are you sure you wish to proceed?',
	'delete_events_reassign_text' => 'There are <span id="result-count"></span> existing result(s) associated with these events.<br/>You can reassign the results to an existing event below. <br/><b>If you do not reassign the results, they will be deleted.</b>',
	'delete_events_invalid_reassign_event' => 'You cannot reassign results to an event that is already selected for deletion',
	'merge_events_text' => 'Please select the primary event below. This is the event to which all the other selected event results will be merged to',
	'merge_events_invalid_selection' => 'You must select at least 2 events to perform a merge operation',
	'merge_events_title' => 'Merge Events',
	'merge_selected_events_text' => 'Merge Selected Events',
	'merge' => 'Merge',
	'events_create_button' => 'Create New Event',
	'embed_event_results_column_help' => 'Copy and paste this shortcode into a new post to display the event results',

	// result manager
	'delete_selected_results_text' => 'Delete Selected Results',
	'delete_results_confirm_title' => 'Delete Results',
	'reassign_selected_results_text' => 'Assign Results to Another User',
	'delete_results_confirm_text' => 'You are about to delete <span id="result-count"></span> result(s). Are you sure you wish to proceed?',
	'reassign_results_text' => 'Please choose the user that you wish to reassign the selected results to. Start typing the user name and select when it appears in the suggest menu.',
	'reassign_results_input_text' => 'Reassign results to... ',
	'reassign_results_title' => 'Reassign Results',
	'reassign_results_no_user_selected' => 'Please select a user for which the results will be reassigned to',
	'reassign_results_error' => 'There was a problem reassigning the results to this user',

	// add results
	'admin_add_results_title' => 'Add Results',
	'add_results_event_input_text' => 'Start typing event name',
	'add_results_choose_event_title' => 'Choose Event',
	'add_results_choose_event_text' => 'Select the event you wish to add results for. Check if the event already exists by typing the name in the input below. If it does not exist, simply create a new event.',
	'add_results_title' => 'Add Results',
	'add_results_text' => 'Now add as many results as you like below. Before creating a new athlete, please ensure the athlete does not exist in the database already. When you create a new athlete using this tool, this person cannot manage his/her own results unless you inform the user of their login credentials.',
	'add_results_athlete_input_text' => 'Start typing athlete name',
	'add_result_create_athlete_button' => 'Create New Athlete',
	'add_results_view_current_event_results' => 'View current event results',
	'add_result_button_text' => 'Add Result',
	'add_result_gender_text' => 'The gender for this athlete is currently unknown. Please specify the gender below before entering results',
	'add_result_gender_dialog_title' => 'Athlete Gender Required',
	'add_result_age_class_help' => 'This should be the age class for the athlete on the date of this event. You are requested to provide an age class because the date of birth for this athlete is unknown. <br/><br/>If you know this athletes date of birth, click on the <b>Set date of birth</b> link to avoid setting this value in the future',
	'add_result_set_dob_text' => 'Set date of birth',
	'add_result_dob_dialog_title' => 'Set Athlete Date of Birth',
	'add_result_dob_text' => 'Please choose the athletes date of birth using the date picker below',
	'add_result_set_dob_error' => 'You have not yet selected a date of birth',
	'add_result_success_message' => 'The result has been entered successfully',
	'add_result_create_user_dob_help' => 'A date of birth is not required but will make life easier when adding results for this user as the age class for results is automatically determined using the athletes date of birth and the date of the event',
	'add_result_create_user_success_dialog_title' => 'Athlete Create',
	'add_result_create_user_success_text' => 'The athlete has been created successfully. The login details can be provided to this athlete to allow them manage their own results. Please make a note of username / password details below if you wish to allow this user log into the system.',
	'add_result_embed_text' => 'Embed these event results in a post using the following shortcode:',

	// misc
	'select_unselect_all_tooltip' => 'Select/Deselect All',
	'create_user_dialog_title_result_' => 'Create New Athlete',
	'edit_user_dialog_title' => 'Edit Athlete',

	// print rankings
	'print_rankings_text' => 'rankings',
	'print_rankings_powered_by' => 'Powered by <b>WP-Athletics</b> a plugin for Wordpress',
	'print_rankings_enter_results_text' => 'Not on the list? Register and enter your results at:',
	'admin_print_rankings_description' => 'Select the rankings criteria below and click "Print", a new tab will open with the printable rankings table.',

	// log
	'log_admin_column_date' => 'Date',
	'log_admin_column_type' => 'Log Type',
	'log_admin_column_log' => 'Log'
);

return array(
	'common' => $common_lang,
	'admin' => $admin_lang
)

?>