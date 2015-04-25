<?php

/**
 * Logs a message to the output file when debug mode is enabled
 * @param $message
 */
function wpa_log( $message ) {
	if (WP_DEBUG === true) {
		if (is_array( $message ) || is_object( $message ) ) {
			error_log( "[WPA]: " . date('Y-m-d h:i:s') . " : " . print_r( $message, true ) . "\n",  3, "/var/tmp/wp-log.log");
		} else {
			error_log( "[WPA]: " . date('Y-m-d h:i:s') . " : " . $message . "\n" , 3, "/var/tmp/wp-log.log");
		}
	}
}

/**
 * ends with utility function
 */
function ends_with( $haystack, $needle ) {
	return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Base class for classes requiring database access and utility functions
 */
if( !class_exists( 'WPA_Base' ) ) {

	class WPA_Base {

		public $wpa_db;
		public $nonce = 'ab6l4lfjhsh46hjdhhs';

		/**
		 * Constructor for base class, reads the db object and sets as a global
		 **/
		public function __construct( $db ) {

			$this->wpa_db = $db;
			$this->wpa_db->set_parent( $this );

			// add actions
			add_action( 'wp_ajax_wpa_get_personal_bests', array ( $this, 'get_personal_bests') );
			add_action( 'wp_ajax_wpa_load_global_data', array ( $this, 'load_global_data') );
			add_action( 'wp_ajax_wpa_get_event_categories', array ( $this, 'get_event_categories') );
			add_action( 'wp_ajax_wpa_get_age_categories', array ( $this, 'get_age_categories') );
			add_action( 'wp_ajax_wpa_get_results', array ( $this, 'get_results') );
			add_action( 'wp_ajax_wpa_get_all_results', array ( $this, 'get_all_results') );
			add_action( 'wp_ajax_wpa_get_all_results_for_user', array ( $this, 'get_all_results_for_user') );
			add_action( 'wp_ajax_wpa_update_age_grades', array ( $this, 'update_age_grades') );
			add_action( 'wp_ajax_wpa_get_event_results', array ( $this, 'get_event_results') );
			add_action( 'wp_ajax_wpa_get_user_profile', array ( $this, 'get_user_profile') );
			add_action( 'wp_ajax_wpa_get_user_dob', array ( $this, 'get_user_dob') );
			add_action( 'wp_ajax_wpa_search_autocomplete', array ( $this, 'search_autocomplete') );
			add_action( 'wp_ajax_wpa_get_user_oldest_result_year', array ( $this, 'get_user_oldest_result_year') );
			add_action( 'wp_ajax_wpa_save_profile_data', array ( $this, 'save_profile_data') );
			add_action( 'wp_ajax_wpa_event_autocomplete', array ( $this, 'event_autocomplete') );
			add_action( 'wp_ajax_wpa_location_autocomplete', array ( $this, 'location_autocomplete') );
			add_action( 'wp_ajax_wpa_user_autocomplete', array ( $this, 'user_autocomplete') );
			add_action( 'wp_ajax_wpa_get_event', array ( $this, 'get_event') );
			add_action( 'wp_ajax_wpa_update_result', array ( $this, 'update_result') );
			add_action( 'wp_ajax_wpa_validate_event_entry', array ( $this, 'validate_event_entry') );
			add_action( 'wp_ajax_wpa_delete_result', array ( $this, 'delete_result') );
			add_action( 'wp_ajax_wpa_delete_results', array ( $this, 'delete_results') );
			add_action( 'wp_ajax_wpa_reassign_results', array ( $this, 'reassign_results') );
			add_action( 'wp_ajax_wpa_get_result_info', array ( $this, 'get_result_info') );
			add_action( 'wp_ajax_wpa_update_event', array( $this, 'update_event' ) );
			add_action( 'wp_ajax_wpa_create_event', array( $this, 'create_event' ) );
			add_action( 'wp_ajax_wpa_get_logs', array( $this, 'get_logs' ) );
			add_action( 'wp_ajax_wpa_get_recent_results', array( $this, 'get_recent_results' ) );
			add_action( 'wp_ajax_wpa_get_events_for_year', array( $this, 'get_events_for_year' ) );
			add_action( 'wp_ajax_wpa_get_generic_results', array( $this, 'get_generic_results' ) );
			add_action( 'wp_ajax_wpa_save_profile_photo', array ( $this, 'save_profile_photo') );
			add_action( 'wp_ajax_wpa_going_to_event', array ( $this, 'going_to_event') );

			// no priv actions (for users not logged in)
			add_action( 'wp_ajax_nopriv_wpa_get_personal_bests', array ( $this, 'get_personal_bests') );
			add_action( 'wp_ajax_nopriv_wpa_load_global_data', array ( $this, 'load_global_data') );
			add_action( 'wp_ajax_nopriv_wpa_get_results', array ( $this, 'get_results') );
			add_action( 'wp_ajax_nopriv_wpa_get_event', array ( $this, 'get_event') );
			add_action( 'wp_ajax_nopriv_wpa_get_events_for_year', array( $this, 'get_events_for_year' ) );
			add_action( 'wp_ajax_nopriv_wpa_get_event_results', array ( $this, 'get_event_results') );
			add_action( 'wp_ajax_nopriv_wpa_get_user_profile', array ( $this, 'get_user_profile') );
			add_action( 'wp_ajax_nopriv_wpa_search_autocomplete', array ( $this, 'search_autocomplete') );
			add_action( 'wp_ajax_nopriv_wpa_get_user_oldest_result_year', array ( $this, 'get_user_oldest_result_year') );
			add_action( 'wp_ajax_nopriv_wpa_get_recent_results', array( $this, 'get_recent_results' ) );
			add_action( 'wp_ajax_nopriv_wpa_get_generic_results', array( $this, 'get_generic_results' ) );

			if( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array ($this, 'enqueue_common_scripts_and_styles' ) );
			}
		}
		
		/**
		 * [AJAX] Saves new user profile photo in user metadata table
		 */
		public function save_profile_photo() {
			global $current_user;
	
			$filename = $this->process_photo_to_150px( $_POST['url'] );
			
			$user_id = $_POST['userId'];
	
			update_user_meta( $user_id, 'wp-athletics_profile_photo', $filename );
	
			$result = array('filename'=>$filename);
	
			// return as json
			wp_send_json($result);
			die();
		}
		
		/**
		 * [AJAX] Operation to update all of a users age grades
		 */
		public function update_age_grades() {
			global $current_user;
			
			// perform the query
			$result = $this->wpa_db->update_age_grades( $_POST['data'] );
			
			// update option so this is only done once
			update_user_meta( $current_user->ID, 'wp-athletics_age_grades_done', 'yes' );
			
			// return as json
			wp_send_json($result);
			die();
		}

		/**
		 * [AJAX] Retrieves a list of log entries
		 */
		public function get_logs() {

			// perform the query
			$result = $this->wpa_db->get_logs( $_POST );

			// return as json
			wp_send_json($result);
			die();
		}

		/**
		 * [AJAX] Retrieves a list of results based on provided search criteria
		 */
		public function get_generic_results() {
			// perform the query
			$result = $this->wpa_db->search_results( $_POST );

			// return as json
			wp_send_json($result);
			die();
		}

		/**
		 * [AJAX] Retrieves a list of recent results
		 */
		public function get_recent_results() {

			// perform the query
			$result = $this->wpa_db->get_recent_results_by_date( $_POST );

			// return as json
			wp_send_json($result);
			die();
		}
		
		/**
		 * [AJAX] Retrieves a list of events for a given year
		 */
		public function get_events_for_year() {
		
			// perform the query
			$result = $this->wpa_db->get_events_for_year( $_POST );
		
			// return as json
			wp_send_json($result);
			die();
		}

		/**
		 * [AJAX] Retrieves useful info such as event types and age categories
		 */
		public function load_global_data() {
			global $global_data_loaded;
			global $wpa_lang;
			global $wpa_settings;
			global $current_user;

			if( !isset( $global_data_loaded ) && $global_data_loaded == false  ) {

				$age_cats = $this->wpa_db->get_age_categories();
				$sub_types = $this->wpa_db->get_event_sub_types();
				$event_cats = $this->wpa_db->get_event_categories();

				wp_send_json(array(
					'ageCategories' => $age_cats,
					'eventTypes' => $sub_types,
					'eventCategories' => $event_cats,
					'languageProperties' => $wpa_lang,
					'settings' => $wpa_settings,
					'userDOB' => get_user_meta( $current_user->ID, 'wp-athletics_dob', true ),
					'userHideDOB' => (get_user_meta( $current_user->ID, 'wp-athletics_hide_dob', true ) == 'yes'),
					'userGender' => get_user_meta( $current_user->ID, 'wp-athletics_gender', true ),
					'ageGradesDone' => get_user_meta( $current_user->ID, 'wp-athletics_age_grades_done', 'no') == 'yes',
					'defaultUnit' => get_option( 'wp-athletics_default-unit', 'm' ),
					'isLoggedIn' => is_user_logged_in(),
					'pendingResults' => $current_user ? $this->wpa_db->get_pending_result_count() : 0,
					'isAdmin' => is_admin()
				));
				$global_data_loaded = true;
			}
			else {
				wpa_log('global data already loaded');
			}
			die();
		}

		/**
		 * [AJAX] Retrieves single event based on ID
		 */
		public function get_event() {
			global $current_user;

			// perform the query
			$result = $this->wpa_db->get_event( intval( $_POST['eventId'] ) );

			// return as json
			wp_send_json($result);
			die();
		}

		/**
		 * [AJAX] Updates a single event details
		 */
		function update_event() {
			// perform the query
			$result = $this->wpa_db->update_event( $_POST );

			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * [AJAX] Creates a single event
		 */
		function create_event() {
			// perform the query
			$result = $this->wpa_db->create_event( $_POST );

			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * [AJAX] Validates an event entry
		 */
		function validate_event_entry() {
			// perform the query
			$result = $this->wpa_db->validate_event_entry( $_POST );

			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * [AJAX] Saves or updates an event result
		 */
		public function update_result() {
			//if( $this->is_valid_ajax() ) {
				global $current_user;

				if( !isset($_POST['userId']) ) {
					$_POST['userId'] = $current_user->ID;
				}
				// perform the insert or update
				$result = $this->wpa_db->update_result( $_POST );

				// return as json
				wp_send_json($result);
			//}
			die();
		}

		/**
		 * [AJAX] Saves provided profile data to the user meta data table
		 */
		public function save_profile_data() {
			//if( $this->is_valid_ajax( $this->nonce ) ) {
				global $current_user;

				$gender = $_POST['gender'];
				$dob = $_POST['dob'];
				$hide_dob = $_POST['hideDob'];
				$fave_event = $_POST['faveEvent'];
				$display_name = $_POST['displayName'];

				$user_id = $current_user->ID;

				if( isset( $_POST['userId'] ) && $_POST['userId'] != '' ) {
					$user_id = $_POST['userId'];
				}

				if(isset( $gender ) ) {
					wpa_log('updating gender');
					update_user_meta( $user_id, 'wp-athletics_gender', $gender );
				}
				if(isset( $dob ) ) {
					wpa_log('updating dob');
					update_user_meta( $user_id, 'wp-athletics_dob', $dob );
				}
				if(isset( $hide_dob ) ) {
					wpa_log('updating hide dob value');
					update_user_meta( $user_id, 'wp-athletics_hide_dob', ( $hide_dob == 'true' ? 'yes' : 'no' ) );
				}
				if(isset( $fave_event ) ) {
					wpa_log('updating fave event');
					update_user_meta( $user_id, 'wp-athletics_fave_event_category', $fave_event );
				}
				if(isset( $display_name ) ) {
					wpa_log('updating display name');
					$this->wpa_db->update_user_display_name( $user_id, $display_name );
				}

				$result = array('success'=>true);

				$this->wpa_db->write_to_log( 'profile_update', $this->process_log_content( 'profile_update' ) );

				// return as json
				wp_send_json($result);
			//}
			die();
		}

		/**
		 * [AJAX] Deletes an event result
		 */
		public function delete_result() {
			//if( $this->is_valid_ajax( $this->nonce ) ) {
				global $current_user;

				// perform the delete
				$result = $this->wpa_db->delete_result( $_POST['resultId'] );

				// return as json
				wp_send_json( $result );
			//}
			die();
		}

		/**
		 * [AJAX] Deletes a set of event results
		 */
		public function delete_results() {
			global $current_user;

			// perform the delete
			$result = $this->wpa_db->delete_results( $_POST['ids'] );

			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * [AJAX] Reassigns a set of event results to another user ID
		 */
		public function reassign_results() {
			global $current_user;

			// perform the delete
			$result = $this->wpa_db->reassign_results( $_POST['ids'],  $_POST['reassignId'] );

			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * [AJAX] Retrieves event info
		 */
		public function get_result_info() {
			//if( $this->is_valid_ajax( $this->nonce ) ) {
				global $current_user;

				// perform the delete
				$result = $this->wpa_db->get_result_info( $_POST['resultId'] );

				// return as json
				wp_send_json( $result );
			//}
			die();
		}

		/**
		 * [AJAX] Gets a list of event categories
		 */
		public function get_event_categories() {
			wp_send_json( $this->wpa_db->get_event_categories() );
			die();
		}

		/**
		 * [AJAX] Gets a list of age categories
		 */
		public function get_age_categories() {
			wp_send_json( $this->wpa_db->get_age_categories() );
			die();
		}

		/**
		 * [AJAX] Gets a list of sub type categories
		 */
		public function get_sub_type_categories() {
			wp_send_json( $this->wpa_db->get_event_sub_types() );
			die();
		}

		/**
		 * [AJAX] Retrieves the oldest year for which the user has a recorded result
		 */
		public function get_user_oldest_result_year() {
			if( isset( $_POST['user_id'] ) ) {
				return wp_send_json( $this->wpa_db->get_oldest_result_year( (integer)$_POST['user_id'] ) );
			}
			return false;
			die();
		}

		/**
		 * [AJAX] Retrieves list of events for autocomplete search
		 */
		public function event_autocomplete() {
			// perform the query
			$results = $this->wpa_db->get_events( strtolower($_GET['term']) );

			// return as json
			wp_send_json( $results );
			die();
		}

		/**
		 * [AJAX] Retrieves list of locations for autocomplete search
		 */
		public function location_autocomplete() {
			// perform the query
			$results = $this->wpa_db->get_locations( strtolower($_GET['term']) );

			// return as json
			wp_send_json( $results );
			die();
		}

		/**
		 * [AJAX] Retrieves list of users for autocomplete search
		 */
		public function user_autocomplete() {
			// perform the query
			$results = $this->wpa_db->get_athletes( strtolower($_GET['term']) );

			// return as json
			wp_send_json( $results );
			die();
		}

		/**
		 * [AJAX] Returns info to display for a user profile
		 */
		public function get_user_profile() {
			$user_id = (integer) $_POST['user_id'];
			$result = array(
				'gender' => get_user_meta( $user_id, 'wp-athletics_gender', true ),
				'dob' => get_user_meta( $user_id, 'wp-athletics_dob', true ),
				'hideDob' => get_user_meta( $user_id, 'wp-athletics_hide_dob', true ),
				'faveEvent' => get_user_meta( $user_id, 'wp-athletics_fave_event_category', true ),
				'name' => $this->wpa_db->get_user_display_name( $user_id ),
				'photo' => get_user_meta( $user_id, 'wp-athletics_profile_photo', true ),
				'upcomingEvents' => $this->wpa_db->get_upcoming_user_events( $user_id )
			);
			wp_send_json($result);
			die();
		}

		/**
		 * [AJAX] Returns a user DOB value
		 */
		public function get_user_dob() {
			$user_id = (integer) $_POST['user_id'];
			$result = get_user_meta( $user_id, 'wp-athletics_dob', true );
			wp_send_json($result);
			die();
		}

		/**
		 * [AJAX] Performs ajax request for autocomple search on events and users
		 */
		public function search_autocomplete() {
			$term = strtolower($_GET['term']);

			// perform the event query
			$events = $this->wpa_db->get_events( $term );
			foreach ( $events as $event ) {
				$event->category = 'event';
			}

			// perform athlete query
			$athletes = $this->wpa_db->get_athletes( $term );
			foreach ( $athletes as $athlete ) {
				$athlete->category = 'athlete';
			}

			// merge both results
			$results = array_merge($events, $athletes);

			// return as json
			wp_send_json($results);
			die();
		}

		/**
		 * [AJAX] Retrieves list of all results for a given users
		 */
		public function get_all_results_for_user() {
		
			// perform the query
			$results = $this->wpa_db->get_all_results_for_user();
		
			// return as json
			wp_send_json($results);
			die();
		}
		
		/**
		 * [AJAX] Retrieves list of results for all users
		 */
		public function get_all_results() {

			// perform the query
			$results = $this->wpa_db->get_results( -1, $_POST, true );

			// return as json
			wp_send_json($results);
			die();
		}

		/**
		 * [AJAX] Retrieves list of results for a user
		 */
		public function get_results() {
			global $current_user;
			$user_id;

			if( isset( $_POST['user_id'] )) {
				$user_id = (integer) $_POST['user_id'];
			}
			else {
				$user_id = $current_user->ID;
			}

			// perform the query
			$results = $this->wpa_db->get_results( $user_id, $_POST );

			// return as json
			wp_send_json( $results );
			die();
		}

		/**
		 * [AJAX] Retrieves list of results for a given event
		 */
		public function get_event_results() {
			global $current_user;

			$event_id = (integer) $_POST['eventId'];

			// perform the query
			$results = $this->wpa_db->get_event_results( $event_id );

			$data = array(
				'id' => $event_id,
				'data' => $results
			);

			// return as json
			wp_send_json( $data );
			die();
		}

		/**
		 * [AJAX] returns personal bests for current user
		 */
		public function get_personal_bests() {

			// perform the query
			$results = $this->wpa_db->get_personal_bests( $_POST );
			wpa_log('retrieved ' . count($results) . ' personal best result');

			// return as json
			wp_send_json($results);
			die();
		}
		
		/**
		 * [AJAX[ Registers event participation for a user
		 */
		public function going_to_event() {
			$event_id = (integer) $_POST['eventId'];
			
			// perform the query
			$result = $this->wpa_db->register_user_for_event( $event_id );
			
			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * processes a log message before writing to the db
		 */
		public function process_log_content( $property, $data = array() ) {
			global $current_user;
			return $this->process_log_content_user_provided( $current_user, $property, $data );
		}

		/**
		 * processes a log message before writing to the db
		 */
		public function process_log_content_user_provided( $user, $property, $data = array() ) {
			$content = $this->get_property( $property );

			// special case for new result and position, if position is specified, add on extra text
			if( $property == 'new_result' && isset( $data['position'] ) && intval( $data['position'] > 0 ) ) {
				$content .= $this->get_property( 'new_result_position_addon' );
			}

			// replace the event result token
			if( isset( $data['time'] ) ) {
				$content = str_replace( '{result}', $data['time'], $content );
			}

			// replace the user name
			$content = str_replace( '{name}', $user->display_name, $content );

			// replace the position
			if( isset( $data['position'] ) ) {
				$position = $this->format_position( intval($data['position']) );
				$content = str_replace( '{position}', $position, $content );
			}

			// replace the event name
			if( isset( $data['eventName'] ) ) {
				$content = str_replace( '{event-name}', $data['eventName'], $content );
			}

			return $content;
		}
		
		/**
		 * For content filtering, ensures the content is only displayed in the WP loop
		 */
		public function init_common_filter( $content ) {
			if( !in_the_loop() || has_shortcode( $content, 'wpa-event') ) return $content;
			
			$this->init_common();
		}

		/**
		 * Outputs common dialogs for WPA functionality
		 */
		public function init_common() {
			global $current_user;
			global $wpa_settings;
		
			// enqueue scripts
			wp_enqueue_script( 'jquery' );
			$this->enqueue_common_scripts_and_styles();
			$nonce = wp_create_nonce( $this->nonce );
		
			?>
			<script type='text/javascript'>
				jQuery(document).ready(function() {

					// set up ajax and retrieve my results
					WPA.Ajax.setup('<?php echo admin_url( 'admin-ajax.php' ); ?>', '<?php echo $nonce; ?>', '<?php echo WPA_PLUGIN_URL; ?>', '<?php echo $current_user->ID; ?>',  function() {
						// common setup function
						WPA.setupCommon();

						// setup results dialog
						WPA.setupEditResultDialog(WPA.reloadEventResults);
					});
				});
			</script>

			<div style="margin:0px !important" class="wpa">
			
				<!-- ADD/EDIT RESULTS DIALOG -->
				<?php $this->create_edit_result_dialog(); ?>
			
				<!-- COMMON DIALOGS -->
				<?php $this->create_common_dialogs(); ?>
			</div>
			<?php
		}
		
		/**
		 * Formats a position into a readable value, e.g 2 becomes 2nd, 103 becomes 103rd
		 */
		function format_position( $num ) {
			if ( !in_array( ( $num % 100 ),array( 11, 12, 13 ) ) ) {
				switch ( $num % 10 ) {
					// Handle 1st, 2nd, 3rd
					case 1:  return $num.'st';
					case 2:  return $num.'nd';
					case 3:  return $num.'rd';
				}
			}
			return $num.'th';
		}

		/**
		 * takes filename of a photo and returns the cropped 150 x 150px version
		 */
		public function process_photo_to_150px( $filename ) {

			if( isset($filename) && $filename != '' ) {
				
				$path_info = pathinfo( $filename );
				
				$new_file = $path_info['dirname'] . '/' . $path_info['filename'] . '-150x150.' . $path_info['extension'];
				
				if( $this->check_photo_exists( $new_file ) ) {
					return $new_file;
				}
				else {
					return $path_info['dirname'] . '/' . $path_info['filename'] . '.' . $path_info['extension'];
				}
			}
			return '';
		}
		
		/**
		 * Checks if a given image URL exists
		 */
		public function check_photo_exists( $url ) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if(curl_exec($ch)!==FALSE) {
				return true;
			}
			else {
				return false;
			}
		}

		/**
		 * retrives a language property based on a supplied key, if it does not exist, returns the $default value or the key
		 */
		public function get_property($key, $default = null) {
			global $wpa_lang;

			if( array_key_exists( $key, $wpa_lang ) ) {
				return $wpa_lang[$key];
			}

			return $default ? $default : $key;
		}

		/**
		 * checks if the nonce value is valid for a wordpress AJAX request
		 */
		public function is_valid_ajax() {
			if( check_ajax_referer( $this->nonce, 'security', false ) ) {
				return true;
			}
			else {
				die( $this->get_property( 'ajax_no_permission') );
			}
		}

		/**
		 * Creates a display page
		 */
		public function generate_page( $title, $status = 'publish') {
			$page = array(
				'post_title' => $title,
				'post_content' => '',
				'post_status' => $status,
				'post_type' => 'page',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_category' => array(1)
			);
			return wp_insert_post( $page );
		}

		/**
		 * enqueues the common required JS scripts for front end or admin pages
		 */
		public function enqueue_common_scripts_and_styles( $load_wp_media = false ) {
			$theme = strtolower(get_option( 'wp-athletics_theme', 'default') );

			// common scripts
			wp_enqueue_script( 'wpa-custom' );
			wp_enqueue_script( 'wpa-functions' );
			wp_enqueue_script( 'wpa-age-grade' );
			wp_enqueue_script( 'wpa-ajax' );
			wp_enqueue_script( 'datatables' );

			// common styles
			wp_enqueue_style( 'datatables' );

			if( !is_admin() ) {

				wp_register_style( 'wpa_theme_jqueryui', WPA_PLUGIN_URL . '/resources/css/themes/' . $theme . '/jquery-ui.css' );

				// enqueue scripts

				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-autocomplete' );
				wp_enqueue_script( 'jquery-ui-tooltip' );
				wp_enqueue_script( 'jquery-effects-highlight' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				if($load_wp_media) {
					wp_enqueue_media();
				}

				// stats scripts
				if( defined( 'WPA_STATS_ENABLED' ) ) {
					wp_enqueue_script( 'wpa-stats' );
					wp_enqueue_script( 'jquery-ui-accordion' );
				}

				// enqueue styles
				wp_enqueue_style( 'wpa_theme_jqueryui' );
				wp_enqueue_style( 'wpa_style' );
			}
			else {

				// register scripts and styles
				wp_register_style( 'wpa_admin_style', WPA_PLUGIN_URL . '/resources/css/wpa-admin-style.css' );
				wp_register_style( 'wpa_theme_jqueryui', WPA_PLUGIN_URL . '/resources/css/themes/default/jquery-ui.css' );
				wp_register_script( 'wpa-admin', WPA_PLUGIN_URL . '/resources/scripts/wpa-admin.js' );

				// enqueue scripts
				wp_enqueue_script( 'jquery' );

				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-autocomplete' );
				wp_enqueue_script( 'jquery-ui-tooltip' );
				wp_enqueue_script( 'jquery-effects-highlight' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_media();

				// stats scripts
				if( defined( 'WPA_STATS_ENABLED' ) ) {
					wp_enqueue_script( 'wpa-stats' );
					wp_enqueue_script( 'jquery-ui-accordion' );
				}

				wp_enqueue_script( 'wpa-admin' );

				// enqueue styles
				wp_enqueue_style( 'wpa_style' );
				wp_enqueue_style( 'wpa_admin_style' );
				wp_enqueue_style( 'wpa_theme_jqueryui' );
			}
		}

		/**
		 * Creates a dialog to edit/add events
		 */
		public function create_edit_event_dialog() {
		?>
			<div id="edit-event-dialog" style="display:none">
			<input type="hidden" id="editEventDate" value=""/>
				<div class="wpa-add-result-field">
					<label class="required"><?php echo $this->get_property('add_result_event_name'); ?>:</label>
					<input style="background: #fff" class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="40" maxlength=100 type="text" id="editEventName" />
				</div>
				<div class="wpa-add-result-field add-result-no-bg">
					<label class="required"><?php echo $this->get_property('add_result_event_category'); ?>:</label>
					<select class="add-result-required" id="editEventCategory">
						<option value="" selected="selected"></option>
					</select>
				</div>
				<div class="wpa-add-result-field add-result-no-bg">
					<label class="required"><?php echo $this->get_property('add_result_location'); ?>:</label>
					<input class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="25" maxlength=100 type="text" id="editEventLocation" />
				</div>
				<div class="wpa-add-result-field add-result-no-bg">
					<label class="required"><?php echo $this->get_property('add_result_event_sub_type'); ?>:</label>
					<select class="add-result-required" id="editEventSubType">
						<option value="" selected="selected"></option>
					</select>
				</div>
				<div class="wpa-add-result-field add-result-no-bg">
					<label class="required"><?php echo $this->get_property('add_result_event_date'); ?>:</label>
					<input readonly="readonly" style="position:relative; top:-2px" class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="30" type="text" id="editResultDate"/>
				</div>
				<div id="wpa-add-event-show-detail" class="wpa-add-result-field add-result-no-bg">
					<label></label>
					<button id="wpa-add-event-show-detail-button"><?= $this->get_property('add_event_more_detail') ?></button>
				</div>
				<div style="display:none" id="wpa-add-event-more-detail">
					<div class="wpa-add-result-field wpa-textarea add-result-no-bg">
						<label><?php echo $this->get_property('add_event_details'); ?>:</label>
						<textarea class="ui-widget ui-widget-content ui-state-default ui-corner-all" maxlength=250 id="editEventDetail"></textarea>
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label><?php echo $this->get_property('add_event_contact_email'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="25" maxlength=100 type="text" id="editEventContactEmail" />
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label><?php echo $this->get_property('add_event_contact_name'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="25" maxlength=100 type="text" id="editEventContactName" />
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label><?php echo $this->get_property('add_event_contact_number'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="25" maxlength=100 type="text" id="editEventContactNumber" />
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label><?php echo $this->get_property('add_event_cost'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="25" maxlength=100 type="text" id="editEventCost" />
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label><?php echo $this->get_property('add_event_url'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="25" maxlength=100 type="text" id="editEventUrl" />
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label><?php echo $this->get_property('add_event_register_url'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="25" maxlength=100 type="text" id="editEventRegisterUrl" />
					</div>
				</div>
			</div>
		<?php
		}
	
		/**
		 * Creates a dialog to add/edit an athlete
		 */
		public function create_athlete_dialogs() {
		?>

		<div id="edit-user-dialog" style="display:none">
			<input type="hidden" id="editAthleteId"/>
			<div class="wpa-add-result-field add-result-no-bg">
				<label class="required"><?php echo $this->get_property('add_result_name'); ?>:</label>
				<input class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="30" maxlength=100 type="text" id="editAthleteName" />
			</div>
			<div class="wpa-add-result-field add-result-no-bg">
				<label><?php echo $this->get_property('column_athlete_email'); ?>:</label>
				<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="30" type="text" id="editAthleteEmail"/>
			</div>
			<div class="wpa-add-result-field add-result-no-bg">
				<label class="required"><?php echo $this->get_property('my_profile_gender'); ?>:</label>
				<select id="editAthleteGender" class="add-result-required">
					<option value="M"><?php echo $this->get_property('gender_M'); ?></option>
					<option value="F"><?php echo $this->get_property('gender_F'); ?></option>
				</select>
			</div>
			<div class="wpa-add-result-field add-result-no-bg">
				<label><?php echo $this->get_property('my_profile_dob'); ?>:</label>
				<input readonly="readonly" class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="20" type="text" id="editAthleteDob"/>
				<span class="wpa-help" title="<?php echo $this->get_property('add_result_create_user_dob_help'); ?>"></span>
			</div>
		</div>
		
		<div id="create-user-dialog" style="display:none">
			<div class="wpa-add-result-field add-result-no-bg">
				<label class="required"><?php echo $this->get_property('add_result_name'); ?>:</label>
				<input class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="30" maxlength=100 type="text" id="createAthleteName" />
			</div>
			<div class="wpa-add-result-field add-result-no-bg">
				<label class="required"><?php echo $this->get_property('column_athlete_username'); ?>:</label>
				<input class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="30" maxlength=100 type="text" id="createAthleteUsername" />
			</div>
			<div class="wpa-add-result-field add-result-no-bg">
				<label><?php echo $this->get_property('column_athlete_email'); ?>:</label>
				<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="30" type="text" id="createAthleteEmail"/>
			</div>
			<div class="wpa-add-result-field add-result-no-bg" style="position: relative; top: 7px;">
				<label></label>
				<input checked="checked" type="checkbox" id="createAthleteSendDetails"/>
				<span><?php echo $this->get_property('email_details_text'); ?></span>
			</div>
			<div class="wpa-add-result-field add-result-no-bg">
				<label class="required"><?php echo $this->get_property('my_profile_gender'); ?>:</label>
				<select id="createAthleteGender" class="add-result-required">
					<option value="M"><?php echo $this->get_property('gender_M'); ?></option>
					<option value="F"><?php echo $this->get_property('gender_F'); ?></option>
				</select>
			</div>
			<div class="wpa-add-result-field add-result-no-bg">
				<label><?php echo $this->get_property('my_profile_dob'); ?>:</label>
				<input readonly="readonly" class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="20" type="text" id="createAthleteDob"/>
				<span class="wpa-help" title="<?php echo $this->get_property('add_result_create_user_dob_help'); ?>"></span>
			</div>
		</div>
		<?php
		}

		/**
		 * Creates a dialog to edit/add event results
		 */
		public function create_edit_result_dialog() {
			global $edit_result_dialog_created;

			if( !$edit_result_dialog_created ) {
		?>
			<div style="display:none" id="add-result-dialog">
				<form>
					<input type="hidden" id="addResultId" value=""/>
					<input type="hidden" id="isPendingResult" value=""/>
					<input type="hidden" id="addResultEventId" value=""/>
					<input type="hidden" id="addResultEventDate" value=""/>

					<div class="wpa-add-result-field">
						<label class="required"><?php echo $this->get_property('add_result_event_name'); ?>:</label>
						<input style="background: #fff" class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="40" maxlength=100 type="text" id="addResultEventName" />
						<span class="wpa-help" title="<?php echo $this->get_property('help_add_result_event_name'); ?>"></span>
						<span style="display:none;" title="<?php echo $this->get_property('help_add_result_cancel_event'); ?>" class="add-result-cancel-event"></span>
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_event_category'); ?>:</label>
						<select class="add-result-required" id="addResultEventCategory">
							<option value="" selected="selected"></option>
						</select>
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_location'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="25" maxlength=100 type="text" id="addResultEventLocation" />
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_event_sub_type'); ?>:</label>
						<select class="add-result-required" id="addResultEventSubType">
							<option value="" selected="selected"></option>
						</select>
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_event_date'); ?>:</label>
						<input readonly="readonly" style="position:relative; top:-2px" class="ui-widget ui-widget-content ui-state-default ui-corner-all add-result-required" size="20" type="text" id="addResultDate"/>
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_age_class'); ?>:</label>
						<select class="add-result-required" id="addResultAgeCat">
							<option value="" selected="selected"></option>
						</select>
					</div>
					<div style="display:none;" time-format="h" class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_event_time_hours'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" time-format="h" maxlength="2" size="3" type="text" id="addResultTimeHours" value="0">
					</div>
					<div style="display:none;" time-format="m" class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_event_time_minutes'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" time-format="m" maxlength="2" size="3" type="text" id="addResultTimeMinutes" value="0">
					</div>
					<div style="display:none;" time-format="s" class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_event_time_seconds'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" time-format="s" maxlength="2" size="3" type="text" id="addResultTimeSeconds" value="0">
					</div>
					<div style="display:none;" time-format="ms" class="wpa-add-result-field add-result-no-bg">
						<label class="required"><?php echo $this->get_property('add_result_event_time_milliseconds'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" time-format="ms" maxlength="2" size="3" type="text" id="addResultTimeMilliSeconds" value="0">
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label><?php echo $this->get_property('add_result_event_position'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="5" type="text" id="addResultPosition" value="">
					</div>
					<div class="wpa-add-result-field add-result-no-bg">
						<label><?php echo $this->get_property('add_result_activity_link'); ?>:</label>
						<input class="ui-widget ui-widget-content ui-state-default ui-corner-all" size="30" type="text" id="addResultGarminId" value="">
						<span class="wpa-help" title="<?php echo $this->get_property('help_add_result_activity_url'); ?>"></span>
					</div>
				</form>
			</div>
			<?php
				$edit_result_dialog_created = true;
			}
		}

		/**
		 * Writes the HTML to generate the event results table
		 */
		public function create_event_results_table( $tableId = 'event-results-table' ) {
		?>
			<div id="event-results">
				<table cellpadding="0" cellspacing="0" border="0" class="display ui-state-default" style="border-bottom:none" id="<?php echo $tableId; ?>" width="100%">
					<thead>
						<tr>
						<th></th>
						<th></th>
						<th></th>
						<th><name><?php echo $this->get_property('column_athlete_name') ?></name></th>
						<th><?php echo $this->get_property('column_time') ?></th>
						<th><?php echo $this->get_property('column_age_grade') ?><span class="column-help" title="<?php echo $this->get_property('help_column_age_grade'); ?>"></span></th>
						<th><?php echo $this->get_property('column_pace') ?></th>
						<th><?php echo $this->get_property('column_age_category') ?></th>
						<th><?php echo $this->get_property('column_position') ?></th>
						<th></th>
						</tr>
					</thead>
				</table>
			  </div>
		<?php
		}

		/**
		 * Writes the HTML to generate the generic results table
		 */
		public function create_generic_results_table() {
		?>
			<div id="generic-results">
				<table cellpadding="0" cellspacing="0" border="0" class="display ui-state-default" style="border-bottom:none" id="generic-results-table" width="100%">
					<thead>
						<tr>
						<th></th>
						<th><?php echo $this->get_property('column_athlete_name') ?></th>
						<th><?php echo $this->get_property('column_event_date') ?></th>
						<th><?php echo $this->get_property('column_event_name') ?></th>
						<th><?php echo $this->get_property('column_event_location') ?></th>
						<th><?php echo $this->get_property('column_category') ?></th>
						<th><?php echo $this->get_property('column_age_category') ?></th>
						<th><?php echo $this->get_property('column_time') ?></th>
						<th><?php echo $this->get_property('column_age_grade') ?><span class="column-help" title="<?php echo $this->get_property('help_column_age_grade'); ?>"></span></th>
						<th><?php echo $this->get_property('column_pace') ?></th>
						<th><?php echo $this->get_property('column_position') ?></th>
						<th></th>
						</tr>
					</thead>
				</table>
			  </div>
		<?php
		}
		
		/**
		 * Writes HTML for the page loading div
		 */
		public function display_page_loading() {
		?>
			<div class="wpa-page-loading"><?= $this->get_property('page_loading_text') ?></div>
		<?php
		}

		/**
		 * Writes HTML to generate a dialogs for user profile and event results
		 */
		public function create_common_dialogs() {
			global $common_dialogs_created;
			global $current_user;

			if( !$common_dialogs_created ) {
		?>
				<!-- USER PROFILE DIALOG -->
				<div style="display:none" id="user-profile-dialog">
					<div style="margin-top:0px" class="wpa">
						<div class="wpa-profile">
							<!-- ATHLETE INFO -->
							<div class="wpa-profile-info">

								<!-- ATHLETE PHOTO -->
								<div class="wpa-profile-photo wpa-profile-photo-default" id="wpaUserProfilePhoto"></div>

								<div class="wpa-profile-info-fieldset">
									<!-- DISPLAY NAME -->
									<div class="wpa-profile-field">
										
										<span id="wpa-profile-name"></span>
									</div>

									<!-- DOB -->
									<div id="wpa-profile-dob-container" class="wpa-profile-field">
										<label><?php echo $this->get_property('my_profile_dob'); ?>:</label>
										<span id="wpa-profile-dob"></span>
									</div>

									<!-- AGE CLASS -->
									<div class="wpa-profile-field">
										<label><?php echo $this->get_property('my_profile_age_class'); ?>:</label>
										<span id="wpa-profile-age-class"></span>
									</div>

									<!-- FAVOURITE EVENT -->
									<div class="wpa-profile-field">
										<label><?php echo $this->get_property('my_profile_fave_event'); ?>:</label>
										<span id="wpa-profile-fave-event"></span>
									</div>
								</div>

								<br style="clear:both"/>
							</div>
							
							<!-- UPCOMING RACES -->
							<div class="wpa-profile-upcoming-events">
								<p><?= $this->get_property('my_profile_upcoming_events') ?></p>
								<div id="wpa-profile-upcoming-events-container">
									<table class="wpa-widget" style="" id="wpa-profie-upcoming-events-table">
										<tbody>
										</tbody>
									</table>
								</div>
							</div>
							
							<br style="clear:both"/>
						</div>

						<div class="wpa-menu">

							<!-- FILTERS -->
							<div class="wpa-filters ui-corner-all" style="width:100%">
								<div class="filter-ignore-for-pb-dialog">
									<select id="profileFilterEvent">
										<option value="all" selected="selected"><?php echo $this->get_property('filter_events_option_all'); ?></option>
									</select>
								</div>

								<select id="profileFilterPeriod">
									<option value="all" selected="selected"><?php echo $this->get_property('filter_period_option_all'); ?></option>
									<option value="this_month"><?php echo $this->get_property('filter_period_option_this_month'); ?></option>
									<option value="this_year"><?php echo $this->get_property('filter_period_option_this_year'); ?></option>
								</select>

								<select id="profileFilterType">
									<option value="all" selected="selected"><?php echo $this->get_property('filter_type_option_all'); ?></option>
								</select>

								<select id="profileFilterAge">
									<option value="all" selected="selected"><?php echo $this->get_property('filter_age_option_all'); ?></option>
								</select>

								<div class="filter-ignore-for-pb-dialog">
									<input id="profileFilterEventName" highlight-class="filter-highlight" default-text="<?php echo $this->get_property('filter_event_name_input_text'); ?>" class="ui-corner-all ui-widget ui-widget-content ui-state-default wpa-search wpa-search-disabled"></input>
									<span id="profileFilterEventNameCancel" style="display:none;" title="<?php echo $this->get_property('filter_event_name_cancel_text'); ?>" class="filter-name-remove"></span>
								</div>
							</div>

							<br style="clear:both"/>
						</div>

						<!-- RESULTS TABS -->
						<div class="wpa-tabs wpa-results-tabs" id="results-tabs">
						  <ul>
						    <li><a href="#tabs-results"><?php echo $this->get_property('results_main_tab') ?></a></li>
						    <li><a href="#tabs-personal-bests"><?php echo $this->get_property('results_personal_bests_tab') ?></a></li>
							<?php
							  if( defined( 'WPA_STATS_ENABLED' ) ) {
							  	global $wpa;
							  	$wpa->wpa_stats->display_stats_tab(true);
							  }
							?>
						  </ul>
						  <div id="tabs-results" wpa-tab-type="results">
							<table cellpadding="0" cellspacing="0" border="0" class="display ui-state-default" style="border-bottom:none" id="results-table" width="100%">
								<thead>
									<tr>
										<th></th>
										<th><?php echo $this->get_property('column_event_date') ?></th>
										<th><?php echo $this->get_property('column_event_name') ?></th>
										<th><?php echo $this->get_property('column_event_location') ?></th>
										<th><?php echo $this->get_property('column_event_type') ?></th>
										<th><?php echo $this->get_property('column_category') ?></th>
										<th><?php echo $this->get_property('column_age_category') ?></th>
										<th><?php echo $this->get_property('column_time') ?></th>
										<th><?php echo $this->get_property('column_age_grade') ?><span class="column-help" title="<?php echo $this->get_property('help_column_age_grade'); ?>"></span></th>
										<th><?php echo $this->get_property('column_pace') ?></th>
										<th><?php echo $this->get_property('column_position') ?></th>
										<th></th>
									</tr>
								</thead>
							</table>
						  </div>
						  <div id="tabs-personal-bests" wpa-tab-type="pb">
							<table cellpadding="0" cellspacing="0" border="0" class="display ui-state-default" id="personal-bests-table" width="100%">
								<thead>
									<tr>
										<th></th>
										<th></th>
										<th></th>
										<th><?php echo $this->get_property('column_category') ?></th>
										<th><?php echo $this->get_property('column_time') ?></th>
										<th><?php echo $this->get_property('column_pace') ?></th>
										<th><?php echo $this->get_property('column_event_name') ?></th>
										<th><?php echo $this->get_property('column_event_location') ?></th>
										<th><?php echo $this->get_property('column_event_type') ?></th>
										<th><?php echo $this->get_property('column_age_category') ?></th>
										<th><?php echo $this->get_property('column_event_date') ?></th>
										<th><?php echo $this->get_property('column_age_grade') ?><span class="column-help" title="<?php echo $this->get_property('help_column_age_grade'); ?>"></span></th>
										<th><?php echo $this->get_property('column_club_rank') ?><span class="column-help" title="<?php echo $this->get_property('help_column_rank'); ?>"></span></th>
										<th></th>
									</tr>
								</thead>
							</table>
						  </div>

						  <?php
							  if( defined( 'WPA_STATS_ENABLED' ) ) {
							  	global $wpa;
							  	$wpa->wpa_stats->display_stats_tab_content(true);
							  }
						  ?>

						</div>
					</div>
				</div>

				<!-- EVENT RESULTS DIALOG -->
				<div style="display:none" id="event-results-dialog">
				  <div style="margin-top: 5px;" class="wpa">

				  	  <div>
					  	  <!-- event info -->
						  <div class="wpa-event-info">
							<div class="wpa-event-info-title">
								<span id="eventInfoName"></span>
							</div>
							<div>
								<span id="eventInfoDate"></span>
								<span id="eventInfoDetail"></span>
							</div>
						  </div>
						  <!-- add result button -->
						 <div class="wpa-event-info-actions">
						 	<button style="display:none" id="wpa-event-info-add-result"><?php echo $this->get_property('add_result_title_event_dialog') ?></button>
						 	<button style="display:none" id="wpa-event-im-going-button"><?php echo $this->get_property('event_im_going_text') ?></button>
						 	<button style="display:none" id="wpa-event-im-not-going-button"><?php echo $this->get_property('event_im_not_going_text') ?></button>
						 </div>
						 <br style="clear:both;"/>
					 </div>
					 
					 <div id="eventDetails">
					 	
					 </div>
					 
					 <?php $this->create_event_results_table(); ?>
				  </div>
				</div>

				<!-- GENERIC RESULTS DIALOG -->
				<div style="display:none" id="generic-results-dialog">
				  <div class="wpa">
					 <?php $this->create_generic_results_table(); ?>
				  </div>
				</div>

				<!-- RANKINGS DIALOG -->
				<div style="display:none" id="rankingsDialog">
					<div id="rankingsDisplayOptions">
						<form id="rankings-display-form">
							<input type="radio" id="best-athlete-result-radio" checked="checked" name="rankings-display-mode" value="best-athlete-result">
								<label for="best-athlete-result-radio"><?php echo $this->get_property('rankings_display_best_athlete_result') ?></label>
							</input>
							<input type="radio" id="all-athlete-results-radio" name="rankings-display-mode" value="all-athlete-results">
								<label for="all-athlete-results-radio"><?php echo $this->get_property('rankings_display_all_athlete_results') ?></label>
							</input>
						</form>
					</div>
					<table width="100%" class="display ui-state-default" id="table-rankings">
						<thead>
							<tr>
								<th></th>
								<th></th>
								<th>#</th>
								<th><?php echo $this->get_property('column_time') ?></th>
								<th><?php echo $this->get_property('column_age_grade') ?><span class="column-help" title="<?php echo $this->get_property('help_column_age_grade'); ?>"></span></th>
								<th><?php echo $this->get_property('column_pace') ?></th>
								<th><?php echo $this->get_property('column_athlete_name') ?></th>
								<th><?php echo $this->get_property('column_event_name') ?></th>
								<th><?php echo $this->get_property('column_event_location') ?></th>
								<th><?php echo $this->get_property('column_event_type') ?></th>
								<th><?php echo $this->get_property('column_event_date') ?></th>
								<th><?php echo $this->get_property('column_age_category') ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>

				<!-- CONFIRM DELETE RESULT DIALOG -->
				<div style="display:none" id="result-delete-confirm" title="<?php echo $this->get_property('confirm_result_delete_title'); ?>">
	  				<p class="wpa-alert">
	  					<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
	  					<?php echo $this->get_property('confirm_result_delete'); ?>
	  				</p>
				</div>

				<!--  ERROR DIALOG -->
				<div style="display:none" id="wpa-error-dialog" title="<?php echo $this->get_property('error_dialog_title'); ?>">
	  				<div id="wpa-error-dialog-text"></div>
				</div>
				
				<!--  ALERT DIALOG -->
				<div style="display:none" id="wpa-alert-dialog" title="<?php echo $this->get_property('alert_dialog_title'); ?>">
	  				<div id="wpa-alert-dialog-text"></div>
				</div>

				<!-- LOADING DIALOG -->
				<div style="display:none" id="wpa-loading-dialog">
					<div class="wpa-loading">
						<div id="wpa-loading-animation"></div>
						<div id="wpa-loading-text"><?php echo $this->get_property('loading_dialog_text'); ?></div>
						<br style="clear:both;"/>
					</div>
				</div>

				<!-- CREATE USER SUCCESS DIALOG -->
				<div id="create-user-success-dialog" style="display:none">
					<p><?php echo $this->get_property('add_result_create_user_success_text'); ?></p>
					<div>
						<b><span></span></b>
					</div>
				</div>

				<!-- PERSONAL BESTS TABLE LOADING -->
				<div id="wpa-pb-table-processing" class="dataTables_pb_processing" style="display:none;"><?php echo $this->get_property('table_loading_records_message'); ?>.</div>

			<?php
				$common_dialogs_created = true;
			}
		}

	}
}

?>