<?php
/*
Plugin Name: WP Athletics
Plugin URI: http://www.conormccauley.me/wordpress-athletics/
Description: Record, compare and analyse your athletic results. Plan events, track personal bests, compare age graded leaderboards and more.
Author: Conor McCauley
Version: 1.1.1
Author URI: http://www.conormccauley.me
*/

include_once 'includes/wp-athletics-functions.php';
include_once 'includes/wp-athletics-db.php';
include_once 'includes/wp-athletics-manage-results.php';
include_once 'includes/wp-athletics-recent-results.php';
include_once 'includes/wp-athletics-events.php';
include_once 'includes/wp-athletics-event-results.php';
include_once 'includes/wp-athletics-records.php';
include_once 'includes/wp-athletics-admin-settings.php';
include_once 'includes/widgets/wp-athletics-recent-results-widget.php';
include_once 'includes/widgets/wp-athletics-upcoming-events-widget.php';

global $wpa_lang;
global $wpa_settings;

// define a plugin class
if(!class_exists('WP_Athletics')) {

	class WP_Athletics {

		public $wpa_admin;
		public $wpa_records;
		public $wpa_manage_results;
		public $wpa_recent_results;
		public $wpa_events;
		public $wpa_db;
		public $wpa_common;

		protected static $instance;

		public static function init() {
			is_null( self::$instance ) AND self::$instance = new self;
			return self::$instance;
		}

		/**
		 * Construct the plugin object
		 **/
		public function __construct() {
			$this->setup();
			$this->wpa_db = new WP_Athletics_DB();
			
			// admin
			if( is_admin() ) {
				$this->wpa_admin = new WP_Athletics_Admin( $this->wpa_db );
				add_action( 'admin_menu', array( $this, 'do_admin' ) );
			}
			else {
				add_action('wp', array( $this, 'check_is_wpa_page' ) );
			}
			
			// update db on plugin update
			add_action( 'plugins_loaded', $this->wpa_db, 'create_db' );

			global $wpa_lang;
			global $wpa_settings;

			// retrieve language properties
			$this->setup_language();

			// load settings file
			$wpa_settings = require 'includes/wp-athletics-settings.php';

			$this->wpa_records = new WP_Athletics_Records( $this->wpa_db );
			$this->wpa_events = new WP_Athletics_Events( $this->wpa_db );
			$this->wpa_recent_results = new WP_Athletics_Recent_Results( $this->wpa_db );
			$this->wpa_manage_results = new WP_Athletics_Manage_Results( $this->wpa_db );
			$this->wpa_event_results = new WP_Athletics_Event_Results( $this->wpa_db );
			$this->wpa_common = new WPA_Base( $this->wpa_db );

			// init plugins
			$this->init_plugins();

			// installation and uninstallation hooks
			register_activation_hook( __FILE__, array ( $this, 'activate') );
			register_deactivation_hook( __FILE__, array ( $this, 'deactivate') );
			register_uninstall_hook( __FILE__, array( 'WP_Athletics', 'uninstall' ) );

			// short codes
			add_shortcode( 'wpa-records', array( $this, 'do_records' ) );
			add_shortcode( 'wpa-recent-results', array( $this, 'do_recent_results' ) );
			add_shortcode( 'wpa-event', array( $this, 'do_event_results' ) );
			add_shortcode( 'wpa-events', array( $this, 'do_events' ) );
			add_shortcode( 'wpa-my-results', array( $this, 'do_my_results' ) );

			add_action( 'init', array( $this , 'register_assets') );
			add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		}
		
		/**
		 * Determines the language and loads the properties files
		 */
		function setup_language() {
			global $wpa_lang;
			
			// get language from options
			$lang = strtolower( get_option( 'wp-athletics_language', 'en') );
			
			// load standard lang
			$standard_lang = require 'includes/lang/wp-athletics-en.php';
			$standard_common_lang = $standard_lang['common'];
			$standard_admin_lang = $standard_lang['admin'];
			
			// if using a custom language, merge with the standard lang (ensures a fallback if some properties are not translated)
			if( $lang != 'en' ) {
				$custom_lang = require 'includes/lang/wp-athletics-' . $lang . '.php';
				
				// now replace into standard lang any translated properties we find
				$custom_common_lang = $custom_lang['common'];
				$custom_admin_lang = $custom_lang['admin'];

				// loops the common keys and if the property is found in the custom file, replace it
				$standard_common_lang = $this->process_language_properties( $standard_common_lang, $custom_common_lang);
				
				// if admin mode, get the keys for admin 
				if( is_admin() ) {
					$standard_admin_lang = $this->process_language_properties( $standard_admin_lang, $custom_admin_lang);
				}
			}
			
			// if in admin mode, return the additional admin language properties
			if( !is_admin() ) {
				$wpa_lang = $standard_common_lang;
			}
			else {
				$wpa_lang = array_merge( $standard_common_lang, $standard_admin_lang );
			}
		}
		
		/**
		 * Replaces any properties that exist within one language file, into the other language file
		 */
		function process_language_properties( $standard_lang, $custom_lang ) {
			$keys = array_keys( $standard_lang );
				
			// loops the keys and if the property is found in the custom file, replace it
			foreach ( $keys as $key ) {
				if( array_key_exists( $key, $custom_lang ) ) {
					$standard_lang[ $key ] = $custom_lang[ $key ];
				}
			}
			
			return $standard_lang;
		}

		/**
		 * Checks if the current page is a WPA page and filters the content using the relevant shortcode function call
		 */
		function check_is_wpa_page() {
			global $post;
			global $records_gender;

			$this->enqueue_scripts();

			if( isset( $post->ID ) ) {
				// my results
				if( $post->ID == get_option('wp-athletics_my_results_page_id') ) {
					$filter = array( $this->wpa_manage_results, 'my_results_content_filter' );
				}

				// recent results
				else if( $post->ID == get_option('wp-athletics_recent_results_page_id') ) {
					$filter = array( $this->wpa_recent_results, 'recent_results_content_filter' );
				}
				
				// events
				else if( $post->ID == get_option('wp-athletics_events_page_id') ) {
					$filter = array( $this->wpa_events, 'events_content_filter' );
				}

				// records (both)
				else if( $post->ID == get_option('wp-athletics_records_page_id') ) {
					$filter = array( $this->wpa_records, 'records_content_filter' );
				}

				// records (male)
				else if( $post->ID == get_option('wp-athletics_records_male_page_id') ) {
					$records_gender = 'M';
					$filter = array( $this->wpa_records, 'records_content_filter' );
				}

				// records (female)
				else if( $post->ID == get_option('wp-athletics_records_female_page_id') ) {
					$records_gender = 'F';
					$filter = array( $this->wpa_records, 'records_content_filter' );
				}

				if( isset ( $filter ) ) {
					add_filter('the_content', $filter, 1);
					return;
				}
			}
			
			if( !is_admin() && !isset($filter) ) {
				// It's a non WPA page, use core functionality only
				if( is_front_page() ) {
					add_filter('wp_footer', array( $this->wpa_common, 'init_common' ), 1);
				}
				else {
					add_filter('the_content', array( $this->wpa_common, 'init_common_filter' ), 1);
				}
			}
 		}

 		/**
 		 * Scans plugin folder for plugins and inits any plugin classes
 		 */
 		public function init_plugins() {

 			$path = WPA_PLUGIN_DIR . '/plugins/';

 			$dir_contents = scandir( $path );

 			foreach ( $dir_contents as $file ) {
 				if ($file === '.' or $file === '..') continue;

 				if (is_file( $path . '/' . $file ) && ends_with( $file, '.php' ) ) {
 					include_once 'plugins/' . $file;
 				}
 			}

 			// init classes
 			if( defined( 'WPA_STATS_ENABLED' ) ) {
 				$this->wpa_stats = new WP_Athletics_Stats( $this->wpa_db );
 			}
 		}

		/**
		 * Ensure the correct (bundled) jquery build is included to avoid conflicts
		 */
		public function enqueue_scripts() {
			
			// ensure the bundled wordpress jQuery/UI is always used
			//if( !is_home() ) {

				if( wp_script_is( 'jquery' ) ) {
					wp_deregister_script('jquery');
				}

				wp_register_script('jquery', '/wp-includes/js/jquery/jquery.js');
				wp_enqueue_script( 'jquery' );

				if( wp_script_is( 'jquery-ui' ) ) {
					wp_dequeue_script('jquery-ui');
				}
			//}
		}

		/**
		 * registers scripts and stylesheets
		 */
		public function register_assets() {
			$theme = strtolower( get_option( 'wp-athletics_theme', 'default') );

			// scripts
			wp_register_script( 'wpa-functions', WPA_PLUGIN_URL . '/resources/scripts/wpa-functions.js' );
			wp_register_script( 'wpa-custom', WPA_PLUGIN_URL . '/resources/scripts/wpa-custom.js' );
			wp_register_script( 'wpa-ajax', WPA_PLUGIN_URL . '/resources/scripts/wpa-ajax.js' );
			wp_register_script( 'wpa-my-results', WPA_PLUGIN_URL . '/resources/scripts/wpa-my-results.js' );
			wp_register_script( 'wpa-stats', WPA_PLUGIN_URL . '/resources/scripts/wpa-stats.js' );
			wp_register_script( 'wpa-records', WPA_PLUGIN_URL . '/resources/scripts/wpa-records.js' );
			wp_register_script( 'wpa-recent-results', WPA_PLUGIN_URL . '/resources/scripts/wpa-recent-results.js' );
			wp_register_script( 'wpa-events', WPA_PLUGIN_URL . '/resources/scripts/wpa-events.js' );
			wp_register_script( 'datatables', WPA_PLUGIN_URL . '/resources/scripts/jquery.dataTables.min.js', array('jquery'), '1.0', true );

			// styles
			wp_register_style( 'datatables', WPA_PLUGIN_URL . '/resources/css/jquery.dataTables.css' );
			wp_register_style( 'wpa_style', WPA_PLUGIN_URL . '/resources/css/wpa-style.css' );
		}

		/**
		 * Registers any WPA widgets
		 */
		public function register_widgets() {
			register_widget( 'WPA_Recent_Results' );
			register_widget( 'WPA_Upcoming_Events' );
		}

		/**
		 * Creates plugin globals and manages version number
		 */
		public function setup() {
			// define global variables
			if (!defined('WPA_THEME_DIR') )
				define('WPA_THEME_DIR', ABSPATH . 'wp-content/themes/' . get_template() );

			if (!defined('WPA_PLUGIN_NAME') )
				define('WPA_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__) ), '/') );

			if (!defined('WPA_PLUGIN_DIR') )
				define('WPA_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPA_PLUGIN_NAME);

			if (!defined('WPA_PLUGIN_URL') )
				define('WPA_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPA_PLUGIN_NAME);

			if (!defined('WPA_PLUGIN_BASENAME') )
				define('WPA_PLUGIN_BASENAME', plugin_basename(__FILE__) );

			if(!defined('WPA_DATE_FORMAT') )
				define('WPA_DATE_FORMAT', '%d %b %Y');

			if (!defined('WPA_VERSION_NUM') )
				define('WPA_VERSION_NUM', '1.1.1');

			if (!defined('WPA_DB_VERSION') )
				define('WPA_DB_VERSION', '1.3');
			
			if (!defined('WPA_DB_DISABLE_SQL_VIEW') )
				define('WPA_DB_DISABLE_SQL_VIEW', get_option( 'wp-athletics-disable-sql-view', 'no' ) == 'yes');

			// store plugin version number
			add_option('wp-athletics_version', WPA_VERSION_NUM );
		}

		/**
		 * Generates admin menu options
		 */
		public function do_admin() {
			add_filter('plugin_action_links', array( $this->wpa_admin, 'action_links' ), 10, 2 );
			$this->wpa_admin->admin_menu();
		}

		/**
		 * Shortcode action for the records page
		 */
		public function do_records( $atts ) {
			$this->wpa_records->records( $atts );
		}

		/**
		 * Shortcode action for the my results page
		 */
		public function do_my_results( $atts ) {
			$this->wpa_manage_results->my_results( $atts );
		}

		/**
		 * Shortcode action for the recent results page
		 */
		public function do_recent_results( $atts ) {
			$this->wpa_recent_results->recent_results( $atts );
		}
		
		/**
		 * Shortcode action for the events page
		 */
		public function do_events( $atts ) {
			$this->wpa_events->events( $atts );
		}

		/**
		 * Shortcode action for an event result with a supplied id
		 */
		public function do_event_results( $atts) {
			ob_start();
			$this->wpa_event_results->event_results( $atts );
			$content = ob_get_clean();
			return $content;
		}

		/**
		 * Activate the plugin
		 **/
		public function activate() {

			if( $this->is_fresh_install() ) {
				// add admin capabilities
				$this->wpa_db->toggle_capabilities();
			}

			// create the WPA pages - will not create if they do not yet exist
			
			// create a "my results" page
			$this->wpa_manage_results->create_page();
			
			// create a "recent results" page
			$this->wpa_recent_results->create_page();
			
			// create an "events" page
			$this->wpa_events->create_page();
			
			// create a records pages
			$this->wpa_records->create_pages();

			// install database and create/update tables
			$this->wpa_db->create_db();

		}

		/**
		 * Determines if the plugin is freshly installed or just updated/activated
		 */
		public function is_fresh_install() {
			$installed_ver = get_option( 'wp-athletics_db_version', 'not_installed' );
			return $installed_ver == 'not_installed';
		}

		/**
		 * Deactivate the plugin
		 **/
		public function deactivate() {
			// Do nothing
		}

		/**
		 * Uninstalls the plugin
		 */
		public static function uninstall() {
			if ( ! current_user_can( 'activate_plugins' ) )
				return;

			wpa_log('Uninstalling WPA Athletics...');

			// remove pages we created
			$pages_created = get_option( 'wp-athletics_pages_created' );
			if( $pages_created && is_array ( $pages_created ) ) {
				foreach( $pages_created as $page_id ) {
					wpa_log('deleting page ' . $page_id);
					wp_delete_post( $page_id, true );
				}
			}

			// remove tables and meta data
			$wpa_db = new WP_Athletics_DB();
			$wpa_db->uninstall_wpa();

			// remove admin capabilities
			$wpa_db->toggle_capabilities(false);
		}
	}

	// WP Athletics begins right here!
	if(class_exists('WP_Athletics')) {
		global $wpa;
		$wpa = new WP_Athletics();
	}
}
?>