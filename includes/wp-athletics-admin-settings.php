<?php

if(!class_exists('WP_Athletics_Admin')) {

	class WP_Athletics_Admin extends WPA_Base {

		public $nonce = 'wpaathleticsadmin';

		/**
		 * default constructor
		 */
		public function __construct($db) {
			parent::__construct($db);
			add_action( 'wp_ajax_wpa_admin_save_settings', array ( $this, 'save_settings') );
			add_action( 'wp_ajax_wpa_admin_save_event_categories', array ( $this, 'save_event_category') );
			add_action( 'wp_ajax_wpa_admin_delete_event_category', array ( $this, 'delete_event_category') );
			add_action( 'wp_ajax_wpa_admin_delete_age_category', array ( $this, 'delete_age_category') );
			add_action( 'wp_ajax_wpa_admin_save_age_category', array ( $this, 'save_age_category') );
			add_action( 'wp_ajax_wpa_get_all_events', array( $this, 'get_events' ) );
			add_action( 'wp_ajax_wpa_get_athletes', array( $this, 'get_athletes' ) );
			add_action( 'wp_ajax_wpa_delete_events', array( $this, 'delete_events' ) );
			add_action( 'wp_ajax_wpa_merge_events', array( $this, 'merge_events' ) );
			add_action( 'wp_ajax_wpa_create_user', array( $this, 'create_user' ) );
			add_action( 'wp_ajax_wpa_delete_user', array( $this, 'delete_user' ) );
			add_action( 'wp_ajax_wpa_edit_user', array( $this, 'edit_user' ) );
		}

		/**
		 * Constructs an admin menu
		 */
		function admin_menu() {
			if( $this->has_permission_to_manage( false ) ) {
				add_menu_page('WP Athletics Settings', 'WP Athletics', 'manage_wp_athletics', 'wp-athletics-settings', array( $this, 'wpa_settings' ), WPA_PLUGIN_URL . '/resources/images/wp-athletics-icon.png' );
				add_submenu_page( 'wp-athletics-settings', 'WP Athletics Add Results', 'Add Results', 'manage_wp_athletics', 'wp-athletics-add-results', array( $this, 'wpa_add_results') );
				add_submenu_page( 'wp-athletics-settings', 'WP Athletics Results Mangager', 'Manage Results', 'manage_wp_athletics', 'wp-athletics-manage-results', array( $this, 'wpa_manage_results') );
				add_submenu_page( 'wp-athletics-settings', 'WP Athletics Events Mangager', 'Manage Events', 'manage_wp_athletics', 'wp-athletics-manage-events', array( $this, 'wpa_manage_events') );
				add_submenu_page( 'wp-athletics-settings', 'WP Athletics Athlete Mangager', 'Manage Athletes', 'manage_wp_athletics', 'wp-athletics-manage-athletes', array( $this, 'wpa_manage_athletes') );
				add_submenu_page( 'wp-athletics-settings', 'WP Athletics Event Categories', 'Event Categories', 'manage_wp_athletics', 'wp-athletics-event-categories', array( $this, 'wpa_event_category_settings' ) );
				add_submenu_page( 'wp-athletics-settings', 'WP Athletics Age Categories', 'Age Categories', 'manage_wp_athletics', 'wp-athletics-age-categories', array( $this, 'wpa_age_category_settings') );
				add_submenu_page( 'wp-athletics-settings', 'WP Athletics Print Rankings', 'Print Rankings', 'manage_wp_athletics', 'wp-athletics-print-rankings', array( $this, 'wpa_print_rankings') );
				add_submenu_page( 'wp-athletics-settings', 'WP Athletics Log', 'Log', 'manage_wp_athletics', 'wp-athletics-log', array( $this, 'wpa_log') );
			}
		}

		/**
		 * [AJAX] Performs request for events
		 */
		function get_events() {
			// perform the query
			$result = $this->wpa_db->search_events( $_POST );

			// return as json
			wp_send_json( $result );
			die();
		}
		
		/**
		 * [AJAX] Performs request for athletes
		 */
		function get_athletes() {

			// perform the query
			$result = $this->wpa_db->search_athletes( $_POST );
		
			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * [AJAX] Create a new athlete user
		 */
		function create_user() {
			// perform the query
			$result = $this->wpa_db->create_user( $_POST );

			if( $result['error'] == false && $_POST['sendEmail'] == 'true' && $_POST['email'] != '' ) {
				$this->wp_new_user_notification( $result['username'], $result['password'] );
			}

			// return as json
			wp_send_json( $result );
			die();
		}
		
		/**
		 * [AJAX] Deletes a current user
		 */
		function delete_user() {
			// perform the query
			$result = $this->wpa_db->delete_user( $_POST );

			// return as json
			wp_send_json( $result );
			die();
		}
			
		/**
		 * [AJAX] Edits a current user
		 */
		function edit_user() {
			// perform the query
			$result = $this->wpa_db->update_user( $_POST );

			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * Send user notification of a new registration 
		 */
		function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
			wpa_log("sending email for $user_id and pass is $plaintext_pass");
		
			$user = new WP_User( $user_id );
		
			$user_login = stripslashes( $user->user_login );
			$user_email = stripslashes( $user->user_email );

			$message  = __('Hello Athlete,') . "\r\n\r\n";
			$message .= sprintf( __("You have been registered on %s! Here's how to log in:"), get_option('blogname')) . "\r\n\r\n";
			$message .= wp_login_url() . "\r\n";
			$message .= sprintf( __('Username: %s'), $user_login ) . "\r\n";
			$message .= sprintf( __('Password: %s'), $plaintext_pass ) . "\r\n\r\n";
			$message .= sprintf( __('URL: %s'), get_bloginfo('url') ) . "\r\n\r\n";
			$message .= sprintf( __('If you have any problems, please contact me at %s.'), get_option('admin_email') ) . "\r\n\r\n";
			$message .= __('Happy Running!');
		
			wp_mail(
				$user_email,
				sprintf( __('[%s] - Your username and password'), get_option('blogname') ),
				$message
			);
		}
		
		/**
		 * Sends lost password email for a user
		 * @param unknown_type $user_login
		 */
		function retrieve_password($user_login) {
			global $wpdb, $current_site;
		
			if ( empty( $user_login) ) {
				return false;
			} else if ( strpos( $user_login, '@' ) ) {
				$user_data = get_user_by( 'email', trim( $user_login ) );
				if ( empty( $user_data ) )
					return false;
			} else {
				$login = trim($user_login);
				$user_data = get_user_by('login', $login);
			}
		
			do_action('lostpassword_post');
		
		
			if ( !$user_data ) return false;
		
			// redefining user_login ensures we return the right case in the email
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
		
			do_action('retreive_password', $user_login);  // Misspelled and deprecated
			do_action('retrieve_password', $user_login);
		
			$allow = apply_filters('allow_password_reset', true, $user_data->ID);
		
			if ( ! $allow )
				return false;
			else if ( is_wp_error($allow) )
				return false;
		
			$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
			if ( empty($key) ) {
				// Generate something random for a key...
				$key = wp_generate_password(20, false);
				do_action('retrieve_password_key', $user_login, $key);
				// Now insert the new md5 key into the db
				$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
			}
			$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
			$message .= network_home_url( '/' ) . "\r\n\r\n";
			$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
			$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
			$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
			$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
		
			if ( is_multisite() )
				$blogname = $GLOBALS['current_site']->site_name;
			else
				// The blogname option is escaped with esc_html on the way into the database in sanitize_option
				// we want to reverse this for the plain text arena of emails.
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		
			$title = sprintf( __('[%s] Password Reset'), $blogname );
		
			$title = apply_filters('retrieve_password_title', $title);
			$message = apply_filters('retrieve_password_message', $message, $key);
		
			if ( $message && !wp_mail($user_email, $title, $message) )
				wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );
		
			return true;
			
			/**
			 * 		
		$user_login = sanitize_text_field( $_GET['user_login'] );
		
		if (retrieve_password($user_login)) {
			echo "SUCCESS";
		} else {
			echo "ERROR";
		}
			 * 
			 */
		}

		/**
		 * [AJAX] Deletes a list of events and reassigns their results to a specified event ID
		 */
		function delete_events() {
			// perform the query
			$result = $this->wpa_db->delete_events( $_POST );

			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * [AJAX] Merges a list of event results to a specific event ID and discards the events
		 */
		function merge_events() {
			// perform the query
			$results = $this->wpa_db->merge_events( $_POST );

			// return as json
			wp_send_json( $results );
			die();
		}

		/**
		 * checks if current admin user has privileges to manage WP athletics
		 */
		function has_permission_to_manage( $die = true ) {
			if ( current_user_can('manage_wp_athletics') ) {
				return true;
			}
			else {
				if( $die ) {
					wp_die('You do not have sufficient permissions to access this page.');
				}
				return false;
			}
		}

		/**
		 * [AJAX] Saves an age cateogry
		 */
		function save_age_category() {
			wp_send_json( array('success'=> $this->wpa_db->update_age_category( $_POST ) ) );
			die();
		}

		/**
		 * [AJAX] Deletes an age category
		 */
		function delete_age_category() {
			wp_send_json( array( 'success'=> $this->wpa_db->delete_age_category( $_POST['id'] ) ) );
			die();
		}

		/**
		 * [AJAX] Saves an event cateogry
		 */
		function save_event_category() {
			wp_send_json( array('success'=> $this->wpa_db->update_event_category( $_POST ) ) );
			die();
		}

		/**
		 * [AJAX] Deletes an event category by ID
		 */
		function delete_event_category() {
			wp_send_json( array( 'success'=> $this->wpa_db->delete_event_category( $_POST['id'] ) ) );
			die();
		}

		/**
		 * [AJAX] Saves Settings
		 */
		function save_settings() {

			if( isset( $_POST['language'] ) ) {
				update_option('wp-athletics_language', $_POST['language'] );
			}
			if( isset( $_POST['theme'] ) ) {
				update_option('wp-athletics_theme', $_POST['theme'] );
			}
			if( isset( $_POST['disableSqlView'] ) ) {
				update_option('wp-athletics-disable-sql-view', $_POST['disableSqlView'] );
			}
			if( isset( $_POST['recordsMode'] ) ) {
				$wpa_records = new WP_Athletics_Records( $this->wpa_db );
				$wpa_records->recreate_records_pages( $_POST['recordsMode'] );
			}
			if( isset( $_POST['clubName'] ) ) {
				update_option('wp-athletics_club_name', $_POST['clubName'] );
			}
			if( isset( $_POST['defaultUnit'] ) ) {
				update_option('wp-athletics_default-unit', $_POST['defaultUnit'] );
			}
			if( isset( $_POST['submitEvents'] ) ) {
				update_option('wp-athletics-allow-users-submit-events', $_POST['submitEvents'] );
			}

			$result = array('success'=>true);

			// return as json
			wp_send_json($result);
			die();
		}

		/**
		 * Adds a "settings" link to the actions links on the plugin page
		 */
		function action_links($links, $file) {
			if ($file == WPA_PLUGIN_BASENAME) {
				wpa_log('Adding a "settings" link to the plugin actions');
				$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wp-athletics-settings">Settings</a>';
				array_unshift( $links, $settings_link );
			}

			return $links;
		}

		/**
		 * Enqueues scripts and styles
		 */
		public function enqueue_scripts_and_styles() {
			$this->enqueue_common_scripts_and_styles();
		}

		/**
		 * Generates a page for the event category settings
		 */
		function wpa_event_category_settings() {
			require 'admin/event-category-settings.php';
		}

		/**
		 * Generates a page for the age category settings
		 */
		function wpa_age_category_settings() {
			require 'admin/age-category-settings.php';
		}

		/**
		 * Generates a page for result management
		 */
		function wpa_manage_results() {
			require 'admin/manage-results.php';
		}

		/**
		 * Generates a page for adding results
		 */
		function wpa_add_results() {
			require 'admin/add-results.php';
		}

		/**
		 * Generates a page for event management
		 */
		function wpa_manage_events() {
			require 'admin/manage-events.php';
		}
		
		/**
		 * Generates a page for athlete management
		 */
		function wpa_manage_athletes() {
			require 'admin/manage-athletes.php';
		}

		/**
		 * Generates a page for printing rankings
		 */
		function wpa_print_rankings() {
			require 'admin/print-rankings.php';
		}

		/**
		 * Generates a page for the WPA log
		 */
		function wpa_log() {
			require 'admin/log.php';
		}

		/**
		 * Generates a general admin settings page
		 */
		function wpa_settings() {
			require 'admin/general-settings.php';
		}
	}
}
?>