<?php

/**
 * Class for mananaging all database operations
 */

if(!class_exists('WP_Athletics_DB')) {

	class WP_Athletics_DB {

		public $EVENT_TABLE;
		public $EVENT_CAT_TABLE;
		public $RESULT_TABLE;
		public $RESULT_VIEW;
		public $LOG_TABLE;

		private $parent;

		/**
		 * Construct the 'my results' object
		 **/
		public function __construct() {
			global $wpdb;
			$this->RESULT_TABLE = $wpdb->prefix . "wpa_result";
			$this->EVENT_TABLE = $wpdb->prefix . "wpa_event";
			$this->EVENT_CAT_TABLE = $wpdb->prefix . "wpa_event_cat";
			$this->USER_TABLE = $wpdb->prefix . "users";
			$this->LOG_TABLE = $wpdb->prefix . "wpa_log";
			$this->RESULT_VIEW = "v_wpa_results";

			add_action('wp_login', array ( $this, 'log_login' ), 10, 2 );
		}

		/**
		 * Sets up the parent class for accessing common functions
		 */
		public function set_parent( $parent ) {
			$this->parent = $parent;
		}

		/**
		 * creates/updates the database tables
		 */
		public function create_db( $force = false ) {
			global $wpa_settings;
			$installed_ver = get_option( 'wp-athletics_db_version', 'not_installed');

			wpa_log('Installed DB version is ' . $installed_ver);
			wpa_log('Current DB version is ' . WPA_DB_VERSION);
			
			if($force) {
				wpa_log('Forcing DB update. Version is ' . WPA_DB_VERSION);
			}

			if( $force || $installed_ver != WPA_DB_VERSION ) {

				wpa_log('Creating ' . WPA_DB_VERSION . ' database tables');

				$sql = "CREATE TABLE $this->EVENT_TABLE (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				start_time varchar(10),
				name varchar(100) NOT NULL,
				event_cat_id mediumint(9) NOT NULL,
				sub_type_id varchar(2) NOT NULL,
				lat varchar(50),
				lng varchar(50),
				location varchar(100),
				address varchar(255),
				event_type varchar(10),
				contact_name varchar(100),
				contact_email varchar(100),
				contact_number varchar(100),
				cost varchar(10),
				url varchar(100),
				register_url varchar(100),
				details varchar(200),
				UNIQUE KEY id (id)
				);

				CREATE TABLE $this->EVENT_CAT_TABLE (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name tinytext NOT NULL,
				distance float(10) NOT NULL,
				distance_meters float(10) NOT NULL,
				unit varchar(6) NOT NULL,
				default_data boolean DEFAULT false,
				show_records boolean DEFAULT false,
				type varchar(20) NOT NULL,
				time_format varchar(6),
				UNIQUE KEY id (id)
				);

				CREATE TABLE $this->RESULT_TABLE (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				date_created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				user_id mediumint(9) NOT NULL,
				event_id mediumint(9) NOT NULL,
				time bigint(10) NOT NULL,
				garmin_id varchar(100),
				position integer(4),
				age_category varchar(4),
				gender varchar(1),
				pending smallint(1) DEFAULT '0',
				UNIQUE KEY id (id)
				);
				;

				CREATE TABLE $this->LOG_TABLE (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				type varchar(20) NOT NULL,
				date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				user_id mediumint(9) NOT NULL,
				event_id mediumint(9) NOT NULL,
				result_id mediumint(9),
				content longtext,
				UNIQUE KEY id (id)
				);
				";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );

				update_option( "wp-athletics_db_version", WPA_DB_VERSION );

				// create view
				$this->create_view();

				// create indexes
				$this->create_db_indexes();

				// is this a first time install? if so, create event categories, user meta data and a results view
				if($installed_ver == 'not_installed') {

					// insert default event cat data
					$this->create_default_event_cats();

					// create sample data (if setting is enabled)
					if( WP_DEBUG && (bool)$wpa_settings['create_demo_data_on_activate'] ) {
						require_once 'wp-athletics-demo-data.php';
						new WP_Athletics_Demo( $this );
					}
				}
			}
			return true;
		}

		/**
		 * Creates a result view
		 */
		public function create_view() {
			global $wpdb;
			$sql = 'CREATE OR REPLACE VIEW ' . $this->RESULT_VIEW . ' AS SELECT r.id, r.time, r.user_id, r.event_id, r.garmin_id, r.position, r.age_category, r.gender, r.pending, r.date_created,
			e.event_cat_id, e.name as event_name, e.location as event_location, ec.name as category, ec.time_format, ec.type, ec.distance_meters,
			e.date, e.sub_type_id as event_sub_type_id
			FROM ' . $this->RESULT_TABLE . ' r
			JOIN ' . $this->EVENT_TABLE . ' e on e.id = r.event_id
			JOIN ' . $this->EVENT_CAT_TABLE . ' ec on ec.id = e.event_cat_id;';
			$wpdb->query( $sql );
		}

		/**
		 * Creates database indexes
		 */
		public function create_db_indexes() {
			global $wpdb;
			$wpdb->query('CREATE INDEX wpa_result_idx ON ' . $this->RESULT_TABLE . '(gender, age_category, event_id, user_id)');
			$wpdb->query('CREATE INDEX wpa_event_idx ON ' . $this->EVENT_TABLE . '(name)');
		}

		/**
		 * Installs default categories into the event category table
		 */
		public function create_default_event_cats() {
			global $wpdb;
			$wpdb->query('delete from ' . $this->EVENT_CAT_TABLE . ' where default_data = true');
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '100m', 'distance' => 100, 'distance_meters' => 100, 'unit' => 'm', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 's:ms' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '200m', 'distance' => 200, 'distance_meters' => 200, 'unit' => 'm', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 's:ms' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '400m', 'distance' => 400, 'distance_meters' => 400, 'unit' => 'm', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'm:s:ms' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '800m', 'distance' => 800, 'distance_meters' => 800, 'unit' => 'm', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'm:s:ms' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '1000m', 'distance' => 1000, 'distance_meters' => 1000, 'unit' => 'm', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'm:s:ms' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '1500m', 'distance' => 1500, 'distance_meters' => 1500, 'unit' => 'm', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'm:s:ms' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '1 mile', 'distance' => 1, 'distance_meters' => 1609.34, 'unit' => 'mile', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'm:s:ms' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '3000m', 'distance' => 3000, 'distance_meters' => 3000, 'unit' => 'm', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'm:s:ms' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '5k', 'distance' => 5, 'distance_meters' => 5000, 'unit' => 'km', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'm:s' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '4 miles', 'distance' => 4, 'distance_meters' => 6437.38, 'unit' => 'mile', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'h:m:s' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '5 miles', 'distance' => 5, 'distance_meters' => 8046.72, 'unit' => 'mile', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'h:m:s' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '10k', 'distance' => 10, 'distance_meters' => 10000, 'unit' => 'km', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'h:m:s' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '10 miles', 'distance' => 10, 'distance_meters' => 16093.4, 'unit' => 'mile', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'h:m:s' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '1/2 Marathon', 'distance' => 21.0975, 'distance_meters' => 21097, 'unit' => 'km', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'h:m:s' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => 'Marathon', 'distance' => 42.195, 'distance_meters' => 42195, 'unit' => 'km', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'h:m:s' ) );
			$wpdb->insert( $this->EVENT_CAT_TABLE, array( 'name' => '50k', 'distance' => 50, 'distance_meters' => 50000, 'unit' => 'km', 'default_data' => true, 'type' => 'running', 'show_records' => true, 'time_format' => 'h:m:s' ) );
		}

		/**
		 * Returns a list of results for a given event ID
		 * @param $event_id the event ID
		 */
		public function get_event_results( $event_id, $include_club_rank = true) {
			global $wpdb;

			$wpdb->query('SET @rank=0;');

			$results = $wpdb->get_results(
					"
					SELECT @rank:=@rank+1 AS rank, r.id, r.user_id, r.time, r.pending, r.age_category, r.garmin_id, r.gender, r.position, ec.id as event_cat, ec.time_format, ec.distance_meters,
					(SELECT display_name FROM wp_users WHERE id = r.user_id) as athlete_name
					FROM $this->RESULT_TABLE r
					LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id
					LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id
					WHERE r.event_id = $event_id ORDER BY time asc
					"
			);

			return $results;
		}

		/**
		 * Returns a list of results based on the supplied criteria
		 */
		public function search_results( $request ) {
			global $wpdb;

			$user_id = $request['userId'];
			$age_category = $request['ageCat'];
			$gender = $request['gender'];
			$event_cat_id = $request['eventCat'];
			$event_sub_type_id = $request['type'];
			$date = $request['period'];
			$show_wins = $request['showWins'];
			$show_runner_up = $request['showRunnerUp'];
			$show_top_10 = $request['showTop10'];

			$where = 'WHERE r.pending = 0';

			// user id
			if( isset( $user_id ) && $user_id != '' ) {
				$where .=  ' AND user_id = ' . $user_id;
			}

			// date
			if( isset( $date ) && $date != 'all' && $date != '' ) {
				$date_where = $this->convert_date( $date );
				if( $date_where != '' ) {
					$where = $where . ($where == '' ? '' : ' AND ');
					$where = $where . $date_where;
				}
			}

			// age cat
			if( isset( $age_category ) && $age_category != '' && $age_category != 'all' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . "r.age_category = '" . $age_category . "'";
			}

			// gender
			if( isset( $gender ) && $gender != '' && $gender != 'B' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . "r.gender = '" . $gender . "'";
			}

			// event sub type
			if( isset( $event_sub_type_id ) && $event_sub_type_id != 'all' && $event_sub_type_id != '' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . "event_sub_type_id = '" . $event_sub_type_id . "'";
			}

			// event category
			if( isset( $event_cat_id ) && $event_cat_id != 'all' && $event_cat_id != '' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . 'event_cat_id = ' . $event_cat_id;
			}

			// position
			if( isset( $show_wins ) && $show_wins == 'true' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . 'r.position = 1';
			}
			else if( isset( $show_runner_up ) && $show_runner_up == 'true' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . 'r.position = 2';
			}
			else if( isset( $show_top_10 ) && $show_top_10 == 'true' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . 'r.position <= 10 AND r.position > 0';
			}
			
			if( !WPA_DB_DISABLE_SQL_VIEW ) {
				$results = $wpdb->get_results(
					"
					SELECT r.id, user_id, pending, u.display_name as athlete_name, time, age_category, gender, garmin_id, position, event_id,
					event_name, event_location, event_sub_type_id, date_format(date,'" . WPA_DATE_FORMAT . "') as event_date, category, distance_meters, time_format, event_cat_id AS event_cat
					FROM $this->RESULT_VIEW r LEFT JOIN $this->USER_TABLE u ON user_id = u.id
					$where ORDER BY date ASC
					"
				);
			}
			else {
				$results = $wpdb->get_results(
					"
					SELECT r.id, user_id, r.pending, u.display_name AS athlete_name, r.time, r.age_category, r.gender, r.garmin_id, r.position, r.event_id,
					e.name as event_name, e.location AS event_location, e.sub_type_id AS event_sub_type_id, date_format(e.date,'" . WPA_DATE_FORMAT . "') AS event_date, ec.name AS category, ec.distance_meters, ec.time_format, ec.id AS event_cat
					FROM $this->RESULT_TABLE r
					LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id
					LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id
					LEFT JOIN $this->USER_TABLE u ON r.user_id = u.id
					$where ORDER BY date ASC
					"
				);
			}

			return $results;
		}

		/**
		 * Gets the year of the oldest known result for a user. If no user ID is specified, will return oldest recorded record
		 */
		public function get_oldest_result_year( $user_id ) {
			global $wpdb;
			
			$where = 'WHERE r.pending = 0';

			$where .= ($user_id ? ' AND r.user_id = ' . $user_id : '');

			$year = $wpdb->get_var(
				"SELECT date_format(e.date,'%Y') FROM $this->RESULT_TABLE r
				LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id
				$where ORDER BY e.date ASC LIMIT 1"
			);

			return $year;
		}
		
		/**
		 * Returns a list of events for the provided criteria
		 */
		public function search_athletes( $request ) {
			global $wpdb;

			$where = '';
			$sEcho =  (int) $request['sEcho'];
			$limit =  (int) $request['iDisplayLength'];
			$offset = (int) $request['iDisplayStart'];
			$num_columns = (int) $request['iColumns'];
		
			$sortCol = $this->convert_event_column( $request['mDataProp_' . $request['iSortCol_0']] );
			$sortDir = $request['sSortDir_0'];
		
			$search_name;
		
			// loops columns and set and process any filters that have been set
			for($i = 0; $i < $num_columns; $i++) {
				$param = $request['sSearch_' . $i];
				if($param && $param != '') {
					$dataProp = $request['mDataProp_' . $i];
					if( $dataProp == 'athlete_name' ) {
						$search_name = $param;
					}
				}
			}
		
			// filters		
			if(isset( $search_name ) && $search_name != '') {
				$where = $where . ( $where == '' ? 'WHERE ' : ' AND ' );
				$where .= "lower(u.display_name) like '%" . strtolower( $search_name ) . "%'";
			}
		
			// get the result count for the datatable
			$result_display_count = $wpdb->get_var(
				"SELECT count(u.id) FROM $this->USER_TABLE u $where"
			);
		
			// ge the actual results
			$results = $wpdb->get_results(
					"
					SELECT u.id, u.display_name as athlete_name, u.user_login, u.user_email, date_format(u.user_registered,'" . WPA_DATE_FORMAT . "') as user_registered,
					(SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.id AND um.meta_key = 'wp-athletics_profile_photo') as athlete_photo,
					(SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.id AND um.meta_key = 'wp-athletics_dob') as athlete_dob,
					(SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.id AND um.meta_key = 'wp-athletics_gender') as athlete_gender
					FROM $this->USER_TABLE u $where ORDER BY $sortCol $sortDir LIMIT $offset,$limit
					"
			);
		
			// return the results as an object
			return array(
				'sEcho' => $sEcho,
				'iTotalRecords' => $this->get_event_count(),
				'iTotalDisplayRecords' => $result_display_count,
				'aaData' => $results
			);
		}

		/**
		 * Returns a list of events for the provided criteria
		 */
		public function search_events( $request ) {
			global $wpdb;

			$where = '';
			$sEcho =  (int) $request['sEcho'];
			$limit =  (int) $request['iDisplayLength'];
			$offset = (int) $request['iDisplayStart'];
			$num_columns = (int) $request['iColumns'];

			$sortCol = $this->convert_event_column( $request['mDataProp_' . $request['iSortCol_0']] );
			$sortDir = $request['sSortDir_0'];

			$search_category;
			$search_sub_type;
			$search_period;
			$search_event;

			// loops columns and set and process any filters that have been set
			for($i = 0; $i < $num_columns; $i++) {
				$param = $request['sSearch_' . $i];
				if($param && $param != '') {
					$dataProp = $request['mDataProp_' . $i];
					if( $dataProp == 'category' ) {
						$search_category = $param;
					}
					else if( $dataProp == 'event_sub_type_id' ) {
						$search_sub_type = $param;
					}
					else if( $dataProp == 'event_date' ) {
						$search_period = $param;
					}
					else if( $dataProp == 'event_name' ) {
						$search_event = $param;
					}
				}
			}

			// filters
			if(isset( $search_category ) && $search_category != 'all') {
				$where = $where . ( $where == '' ? 'WHERE ' : ' AND ' );
				$where .= 'e.event_cat_id = ' . $search_category;
			}

			if(isset( $search_sub_type ) && $search_sub_type != 'all') {
				$where = $where . ( $where == '' ? 'WHERE ' : ' AND ' );
				$where .= "e.sub_type_id = '" . $search_sub_type . "'";
			}

			if(isset( $search_period ) && $search_period != 'all') {
				$date_where = $this->convert_date( $search_period, 'e.date' );
				if( $date_where != '' ) {
					$where = $where . ( $where == '' ? 'WHERE ' : ' AND ' );
					$where .= $date_where;
				}
			}

			if(isset( $search_event ) && $search_event != '') {
				$where = $where . ( $where == '' ? 'WHERE ' : ' AND ' );
				$where .= "lower(e.name) like '%" . strtolower( $search_event ) . "%'";
			}

			// get the result count for the datatable
			$result_display_count = $wpdb->get_var(
				"
				SELECT count(e.id) FROM $this->EVENT_TABLE e
				LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id
				$where
				"
			);

			// get the actual results
			$results = $wpdb->get_results(
				"
				SELECT e.id as event_id, e.name AS event_name, e.location AS event_location, e.sub_type_id AS event_sub_type_id,
				date_format(e.date,'" . WPA_DATE_FORMAT . "') AS event_date, ec.name AS category, e.event_cat_id AS event_cat,
				(CASE WHEN (e.date > DATE(NOW())) THEN 1 ELSE 0 END) AS is_future,
				(SELECT count(r.id) from $this->RESULT_TABLE r WHERE r.pending = 0 AND r.event_id = e.id) AS result_count
				FROM $this->EVENT_TABLE e
				LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id
				$where ORDER BY $sortCol $sortDir LIMIT $offset,$limit
				"
			);

			// return the results as an object
			return array(
				'sEcho' => $sEcho,
				'iTotalRecords' => $this->get_event_count(),
				'iTotalDisplayRecords' => $result_display_count,
				'aaData' => $results
			);
		}

		/**
		 * Returns a list of results for the provided criteria
		 */
		public function get_results( $user_id, $request, $skip_rank = false ) {
			global $wpdb;

			$extra_where = '';
			$get_name = '"" as athlete_name';
			$sEcho =  (int) $request['sEcho'];
			$limit =  (int) $request['iDisplayLength'];
			$offset = (int) $request['iDisplayStart'];
			$pending = $request['pending'];
			$num_columns = (int) $request['iColumns'];

			$sortCol = $this->convert_result_column( $request['mDataProp_' . $request['iSortCol_0']] );
			$sortDir = $request['sSortDir_0'];

			$search_category;
			$search_sub_type;
			$search_age_cat;
			$search_period;
			$search_event;
			$search_athlete;
			
			$where = 'WHERE pending = ' . ($pending ? $pending : '0');

			if( $user_id > -1) {
				$where .= ' AND user_id = ' . $user_id;
			}
			else {
				$where .= ' AND user_id > 0';
			}

			// loops columns and set and process any filters that have been set
			for($i = 0; $i < $num_columns; $i++) {
				$param = $request['sSearch_' . $i];
				if($param && $param != '') {
					$dataProp = $request['mDataProp_' . $i];
					if($dataProp == 'category') {
						$search_category = $param;
					}
					else if($dataProp == 'event_sub_type_id') {
						$search_sub_type = $param;
					}
					else if($dataProp == 'age_category') {
						$search_age_cat = $param;
					}
					else if($dataProp == 'event_date') {
						$search_period = $param;
					}
					else if($dataProp == 'event_name') {
						$search_event = $param;
					}
					else if($dataProp == 'athlete_name') {
						$search_athlete = $param;
					}
				}
			}

			// filters
			if(isset( $search_category ) && $search_category != 'all') {
				$extra_where .= ' AND event_cat_id = ' . $search_category;
			}

			if(isset( $search_sub_type ) && $search_sub_type != 'all') {
				if( WPA_DB_DISABLE_SQL_VIEW ) {
					$extra_where .= " AND e.sub_type_id = '" . $search_sub_type . "'";
				}
				else {
					$extra_where .= " AND event_sub_type_id = '" . $search_sub_type . "'";
				}
			}

			if(isset( $search_age_cat ) && $search_age_cat != 'all') {
				$extra_where .= " AND age_category = '" . $search_age_cat . "'";
			}

			if(isset( $search_period ) && $search_period != 'all') {
				$date_where = $this->convert_date( $search_period, 'date' );
				if( $date_where != '' ) {
					$extra_where .= ' AND ' . $date_where;
				}
			}

			if(isset( $search_event ) && $search_event != '') {
				$name = WPA_DB_DISABLE_SQL_VIEW ? 'e.name' : 'event_name';
				$extra_where .= " AND lower(" . $name . ") like '%" . strtolower( $search_event ) . "%'";
			}

			if(isset( $search_athlete ) && $search_athlete != '') {
				$extra_where .= " AND lower(display_name) like '%" . strtolower( $search_athlete ) . "%'";
			}
			
			wpa_log("SELECT COUNT(id) FROM $this->RESULT_VIEW $where $extra_where");

			wpa_log("
				SELECT r.id, user_id, pending, u.display_name as athlete_name, time, age_category, gender, date_created as result_date, garmin_id, position, event_id,
				event_name, event_location, event_sub_type_id, date_format(date,'" . WPA_DATE_FORMAT . "') as event_date, category, distance_meters, time_format, event_cat_id AS event_cat,
				(CASE WHEN (date > DATE(NOW())) THEN 1 ELSE 0 END) AS is_future FROM $this->RESULT_VIEW r LEFT JOIN $this->USER_TABLE u ON user_id = u.id
				$where $extra_where ORDER BY $sortCol $sortDir LIMIT $offset, $limit
			");
			
			// using SQL results view
			if( !WPA_DB_DISABLE_SQL_VIEW ) {
				$result_display_count = $wpdb->get_var(
					"
					SELECT COUNT(id) FROM $this->RESULT_VIEW
					$where $extra_where
					"
				);
	
				$results = $wpdb->get_results(
					"
					SELECT r.id, user_id, r.pending, u.display_name as athlete_name, time, age_category, gender, date_created as result_date, garmin_id, position, event_id,
					event_name, event_location, event_sub_type_id, date_format(date,'" . WPA_DATE_FORMAT . "') as event_date, category, distance_meters, time_format, event_cat_id AS event_cat,
					(CASE WHEN (date > DATE(NOW())) THEN 1 ELSE 0 END) AS is_future FROM $this->RESULT_VIEW r LEFT JOIN $this->USER_TABLE u ON user_id = u.id
					$where $extra_where ORDER BY $sortCol $sortDir LIMIT $offset, $limit
					"
				);
				
			}
			else {
				// NOT using SQL results view
				$result_display_count = $wpdb->get_var(
					"
					SELECT count(r.id) FROM $this->RESULT_TABLE r
					LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id
					LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id
					LEFT JOIN $this->USER_TABLE u ON r.user_id = u.id
					$where $extra_where
					"
				);
				
				$results = $wpdb->get_results(
					"
					SELECT r.id, r.user_id, r.pending, u.display_name as athlete_name, r.time, r.age_category, r.gender, r.date_created as result_date, r.garmin_id, r.position, e.id as event_id, e.name as event_name, e.location as event_location, e.sub_type_id AS event_sub_type_id,
					date_format(e.date,'" . WPA_DATE_FORMAT . "') as event_date, ec.name as category, ec.distance_meters, ec.time_format, e.event_cat_id as event_cat,
					(CASE WHEN (e.date > DATE(NOW())) THEN 1 ELSE 0 END) AS is_future FROM $this->RESULT_TABLE r
					LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id
					LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id
					LEFT JOIN $this->USER_TABLE u ON r.user_id = u.id
					$where $extra_where ORDER BY $sortCol $sortDir LIMIT $offset,$limit
					"
				);
			}
			
			// LOGGING 
			/*
			$tmp = "SELECT r.id, user_id, u.display_name as athlete_name, time, age_category, gender, date_created as result_date, garmin_id, position, event_id,
			event_name, event_location, event_sub_type_id, date_format(date,'" . WPA_DATE_FORMAT . "') as event_date, category, distance_meters, time_format, event_cat_id AS event_cat 
			FROM " . $this->RESULT_VIEW . " r LEFT JOIN $this->USER_TABLE u ON user_id = u.id " . $where . " " . $extra_where . " ORDER BY " . $sortCol . " " .  $sortDir . " LIMIT " . $offset . ", " . $limit;

			$this->write_to_log( 'SQL', $tmp, 0, 0 );
			 */

			return array(
				'sEcho' => $sEcho,
				'iTotalRecords' => $this->get_result_count( $user_id, $request ),
				'iTotalDisplayRecords' => $result_display_count,
				'aaData' => $results
			);
		}

		public function get_result_club_ranking( $result_id, $age_cat, $gender, $event_cat_id ) {
			global $wpdb;
			$results = $wpdb->get_results(
			"SELECT result_id from (SELECT id as result_id, event_id, user_id, time FROM $this->RESULT_TABLE WHERE age_category='$age_cat' AND gender='$gender' ORDER BY time) r
			LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id
			LEFT JOIN $this->EVENT_CAT_TABLE ec1 ON e.event_cat_id = ec1.id WHERE e.event_cat_id = $event_cat_id GROUP BY user_id ORDER by time ASC");

			$rank = 0;
			foreach ( $results as $result ) {
				$rank++;
				if($result->result_id == $result_id) {
					return $rank;
				}
			}
			return '';
		}

		/**
		 * Returns the overall club rank for a specified result, filtered by event category (e.g 1500m) and age category (e.g M)
		 */
		public function get_result_club_rank( $result_id, $age_category, $gender, $event_cat_id ) {

			global $wpdb;
			return $wpdb->get_var( "
				SELECT rank_table.rank FROM
					(SELECT r1.id as id, @curRow := @curRow+1 as rank FROM $this->RESULT_TABLE r1
					LEFT JOIN $this->EVENT_TABLE e1 ON r1.event_id = e1.id
					LEFT JOIN $this->EVENT_CAT_TABLE ec1 ON e1.event_cat_id = ec1.id
					JOIN (SELECT @curRow := 0) crow
					WHERE r1.pending = 0 AND r1.age_category = '$age_category' AND r1.gender = '$gender' AND e1.event_cat_id = $event_cat_id
					ORDER BY r1.time) rank_table
				WHERE rank_table.id = $result_id
			" );
		}

		/**
		 * Returns a list of personal bests for each existing event category. If user is specified, will return results
		 * for that user, otherwise will return bests for the all athletes.
		 */
		public function get_personal_bests( $request ) {
			global $wpdb;

			$user_id = $request['userId'];
			$age_category = $request['ageCategory'];
			$gender = $request['gender'];
			$event_cat_id = $request['eventCategoryId'];
			$event_sub_type_id = $request['eventSubTypeId'];
			$date = $request['eventDate'];
			$rankings_display = $request['rankingDisplay'];
			$show_all_categories = $request['showAllCats'];

			$where = 'WHERE r.pending = 0 ';
			$rank = '';
			$show_all_records = 'AND ec.show_records = 1';
			$order_by = 'event_cat_id,time';
			$group_by_and_order = 'GROUP BY event_cat_id';
			$select_display_name = "(SELECT display_name FROM $this->USER_TABLE WHERE id = r.user_id) as athlete_name";

			if( isset( $show_all_categories ) && $show_all_categories == 'true' ) {
				$show_all_records = '';
			}

			if( isset( $user_id ) && $user_id != '' ) {
				$where = $where . 'AND r.user_id = ' . $user_id;
				$select_display_name = "'' as athlete_name";
			}

			if( isset( $date ) && $date != 'all' && $date != '' ) {
				$date_where = $this->convert_date( $date );
				if( $date_where != '' ) {
					$where = $where . ($where == '' ? '' : ' AND ');
					$where = $where . $date_where;
				}
			}

			if( isset( $age_category ) && $age_category != '' && $age_category != 'all' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . "age_category = '" . $age_category . "'";
			}

			if( isset( $gender ) && $gender != '' && $gender != 'B' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . "gender = '" . $gender . "'";
			}

			if( isset( $event_sub_type_id ) && $event_sub_type_id != 'all' && $event_sub_type_id != '' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . "sub_type_id = '" . $event_sub_type_id . "'";
			}

			if( isset( $event_cat_id ) && $event_cat_id != 'all' && $event_cat_id != '' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . 'event_cat_id = ' . $event_cat_id;
				$group_by_and_order = 'ORDER BY time ';
				$order_by = 'time';
				//$wpdb->query('SET @rank=0;');
				//$rank = '@rank:=@rank+1 AS rank,';
			}

			if( isset( $rankings_display ) && $rankings_display != '' ) {
				if( $rankings_display == 'best-athlete-result' ) {
					$group_by_and_order = 'GROUP BY user_id ' . $group_by_and_order;
				}
			}


			$sql = "SELECT d.id, d.athlete_name, d.gender, d.age_category, d.time, d.user_id, date_format(d.event_date,'" . WPA_DATE_FORMAT . "') as event_date, d.event_cat_id, d.event_name,
			d.event_location, d.event_sub_type_id, d.event_id, ec.name as category, ec.distance_meters, ec.time_format, d.garmin_id from " . $this->EVENT_CAT_TABLE . " ec
			JOIN (
			SELECT r.id, r.gender, r.age_category, r.time AS time, r.garmin_id, r.user_id, ec1.id AS event_cat_id, e.name AS event_name, e.location as event_location,
			e.sub_type_id AS event_sub_type_id, e.id as event_id, e.date AS event_date," . $select_display_name . " from " . $this->RESULT_TABLE . " r
			LEFT JOIN " . $this->EVENT_TABLE . " e ON r.event_id = e.id
			LEFT JOIN " . $this->EVENT_CAT_TABLE . " ec1 ON e.event_cat_id = ec1.id " . $where . " ORDER BY " . $order_by . ")
			d ON d.event_cat_id = ec.id WHERE d.time > 0 $show_all_records " . $group_by_and_order;

			wpa_log($sql);

			$results = $wpdb->get_results( $sql );

			$rank = 0;

			// loop each result and find the overall club rank for the age cat and event
			foreach ( $results as $result ) {
				$rank++;
				$result->rank = $rank;
				$result->club_rank = $this->get_result_club_ranking( $result->id, $result->age_category, $result->gender, $result->event_cat_id );
			}

			return $results;
		}

		/**
		 * Converts a date paramters into a where clause
		 */
		public function convert_date( $date, $date_field = 'date' ) {
			$returnVal = '';
			if( $date != '' ) {
				if( $date == 'future' ) {
					$returnVal = ' date >= DATE(NOW()) ';
				}
				else if( $date == 'this_month' ) {
					$returnVal = ' YEAR(' . $date_field . ') = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE()) ';
				}
				else if( $date == 'this_year' ) {
					$returnVal = ' YEAR(' . $date_field . ') = YEAR(CURDATE()) ';
				}
				else if( false !== strpos( ' ' . $date, 'year:' ) ) {
					$yearArr = explode( ":", $date );
					if(count($yearArr) == 2 ) {
						$returnVal = ' YEAR(' . $date_field . ') = ' . $yearArr[1] . ' ';
					}
				}
			}
			return $returnVal;
		}

		/**
		 * Returns a list of event categories
		 */
		public function get_event_categories() {
			global $wpdb;

			$results = $wpdb->get_results(
				"
				SELECT id, name, distance, distance_meters, unit, show_records, time_format, type
				FROM $this->EVENT_CAT_TABLE ORDER BY distance_meters ASC
				"
			,ARRAY_A);
			return $results;
		}

		/**
		 * Deletes an event category by ID
		 */
		public function delete_event_category( $id ) {
			global $wpdb;
			$success = false;

			if(isset( $id) ) {
				$success = $wpdb->query(
					$wpdb->prepare(
						"
						DELETE FROM $this->EVENT_CAT_TABLE
						WHERE id = %d
						",
						$id
					)
				);
			}
			return array('success' => $success);
		}

		/**
		 * Creates or updates an event category with the supplied data
		 */
		public function update_event_category( $data ) {
			global $wpdb;
			$is_update = (isset( $data['id'] ) && $data['id'] != '0');

			if( !$is_update ) {
				return $wpdb->query( $wpdb->prepare(
					"
					INSERT INTO $this->EVENT_CAT_TABLE
					( name, distance, distance_meters, unit, show_records, time_format, type )
					VALUES ( %s, %d, %d, %s, %d, %s, %s )
					",
					$data['name'],
					$data['distance'],
					$data['distanceMeters'],
					$data['unit'],
					$data['showRecords'],
					$data['timeFormat'],
					$data['type']
				) );
			}
			else {
				return $wpdb->update(
					$this->EVENT_CAT_TABLE,
					array(
						'name' => $data['name'],
						'distance' => $data['distance'],
						'distance_meters' => $data['distanceMeters'],
						'unit' => $data['unit'],
						'show_records' => $data['showRecords'],
						'time_format' => $data['timeFormat'],
						'type' => $data['type']
					),
					array( 'id' => $data['id'] ),
					array(
						'%s',
						'%d',
						'%d',
						'%s',
						'%d',
						'%s'
					),
					array( '%d' )
				);
			}
		}

		/**
		 * Returns list of results recorded for a user
		 */
		public function get_results_recorded( $user_id ) {
			global $wpdb;
			return $wpdb->get_var( "SELECT COUNT(id) FROM $this->RESULT_TABLE r WHERE r.pending = 0 AND user_id = $user_id" );
		}

		/**
		 * Returns list of events based on a search term
		 */
		public function get_events( $term ) {
			global $wpdb;

			return $wpdb->get_results( "SELECT e.id AS value, (CASE WHEN (e.date > DATE(NOW())) THEN 1 ELSE 0 END) AS is_future, CONCAT(e.name, ' (', ec.name, ', ', date_format(e.date,'%d/%m/%y'), ')') AS label
			FROM $this->EVENT_TABLE e
			LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id
			WHERE LOWER(e.name) LIKE '%$term%' ORDER BY e.date DESC LIMIT 15" );
		}

		/**
		 * Returns list of locations based on a search term
		 */
		public function get_locations( $term ) {
			global $wpdb;

			return $wpdb->get_results( "SELECT DISTINCT e.location AS value, e.location AS label FROM $this->EVENT_TABLE e
			WHERE LOWER(e.location) LIKE '%$term%' ORDER BY e.location ASC LIMIT 15" );
		}

		/**
		 * Returns a list of athletes based on a search term
		 */
		public function get_athletes( $term ) {
			global $wpdb;
			return $wpdb->get_results( "SELECT id AS value, display_name AS label FROM $this->USER_TABLE WHERE LOWER(display_name) LIKE '%$term%' ORDER BY display_name ASC LIMIT 10" );
		}

		/**
		 * Returns information on a single event based on supplied ID
		 */
		public function get_event( $id ) {
			global $wpdb;
			return $wpdb->get_row( "SELECT id, name, (CASE WHEN (date > DATE(NOW())) THEN 1 ELSE 0 END) AS is_future, event_cat_id, sub_type_id, location, date_format(date,'" . WPA_DATE_FORMAT . "') as date, cost, lat,lng, details, address, url, register_url, contact_name, contact_email, contact_number FROM $this->EVENT_TABLE WHERE id = $id"  );
		}

		/**
		 * Returns display name for a given user ID
		 */
		public function get_user_display_name( $user_id ) {
			global $wpdb;
			return $wpdb->get_var( "SELECT display_name FROM $this->USER_TABLE WHERE id = $user_id" );
		}

		/**
		 * updates an exsiting event details
		 */
		function update_event( $data ) {
			global $wpdb;

			return $wpdb->update(
				$this->EVENT_TABLE,
				array(
					'name' => $data['eventName'],
					'location' => $data['eventLocation'],
					'date' => $data['eventDate'],
					'event_cat_id' => $data['eventCategory'],
					'sub_type_id' => $data['eventSubType']
				),
				array( 'id' => $data['id'] ),
				array(
					'%s',
					'%s',
					'%s',
					'%d',
					'%s'
				),
				array( '%d' )
			);
		}

		/**
		 * updates or creates an event result
		 */
		function update_result( $data ) {
			global $wpdb;

			$is_update = isset($data['resultId']) && $data['resultId'] != '';
			$is_pending = isset($data['isPending']) && $data['isPending'] == '1';
			$is_admin_update = isset($data['isAdmin']) && $data['isAdmin'] != '';
			$is_demo_mode = isset($data['isDemo']) && $data['isDemo'] != '';
			$create_event = $data['eventId'] == null || $data['eventId'] == '';

			// event does not exist, we'll create a new one
			if( $create_event ) {
				$id = $this->create_event( $data, $is_admin_update );
				if( $id ) {
					wpa_log('created event with ID ' . $id);
					$data['eventId'] = $id;
				}
			}

			$success = false;

			// create the event result
			if( !$is_update ) {
				$success = $wpdb->query( $wpdb->prepare(
					"
					INSERT INTO $this->RESULT_TABLE
					( user_id, event_id, time, garmin_id, position, age_category, gender )
					VALUES ( %d, %d, %d, %s, %d, %s, %s )
					",
					$data['userId'],
					$data['eventId'],
					$data['time'],
					$data['garminId'],
					$data['position'],
					$data['ageCategory'],
					$data['gender']
				) );

				$data['resultId'] = $wpdb->get_var( "SELECT LAST_INSERT_ID()" );
			}
			// update the event result
			else {
				$success = $wpdb->update(
					$this->RESULT_TABLE,
					array(
						'event_id' => $data['eventId'],
						'time' => $data['time'],
						'garmin_id' => $data['garminId'],
						'position' => $data['position'],
						'age_category' => $data['ageCategory'],
						'pending' => '0'
					),
					array( 'id' => $data['resultId'] ),
					array(
						'%d',
						'%d',
						'%s',
						'%d',
						'%s',
						'%d'
					),
					array( '%d' )
				);
				
				if($success) {
					// update the log too
					$user = null;
					if($is_admin_update) {
						global $current_user;
						$user = $current_user;
					}
					else {
						$user = get_user_by( 'id', $data['userId'] );
					}
					$log_content = $this->parent->process_log_content_user_provided( $user, 'new_result', $data );
					wpa_log($log_content);
					$this->update_log_result( $user->ID, $data['resultId'], $log_content );
				}
			}

			// log event - do not log updated results when an admin manually adds them (too expensive)
			$prop = ($is_update && !$is_pending) ? 'update_result' : 'new_result';
			if( $success == true  && !$is_admin_update && !$is_demo_mode) {
				$log_content = $this->parent->process_log_content( $prop, $data );
				$this->write_to_log( $prop, $log_content, $data['eventId'], $data['resultId'] );
			}
			else if( $is_demo_mode || $is_admin_update ) {
				$user = get_user_by( 'id', $data['userId'] );
				$log_content = $this->parent->process_log_content_user_provided( $user, $prop, $data );
				$this->write_to_log_user_provided( $user, $prop, $log_content, $data['eventId'], $data['resultId'] );
			}

			return $success;
		}

		/**
		 * Updates a result in the logs table
		 *
		 */
		function update_log_result( $user_id, $result_id, $new_content ) {
			global $wpdb;
				
			if($user_id == null) {
				global $current_user;
				$user_id = $current_user->ID;
			}

			global $wpdb;
			$success = $wpdb->update(
				$this->LOG_TABLE,
				array(
					'content' => $new_content
				),
				array( 'type' => 'new_result', 'user_id' => $user_id, 'result_id' => $result_id )
			);
		}
		
		/**
		 * deletes a result from the database based on ID
		 */
		function delete_result( $id ) {
			global $wpdb;
			$success = $wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM $this->RESULT_TABLE
					WHERE id = %d
					",
					$id
				)
			);

			if($success) {
				// delete the log entry
				$wpdb->query("DELETE FROM $this->LOG_TABLE WHERE result_id = $id");
				return array('success' => true);
			}
		}

		/**
		 * Deletes a set of results from the database
		 */
		function delete_results( $ids ) {
			global $wpdb;
			$success = $wpdb->query("DELETE FROM $this->RESULT_TABLE WHERE id IN ($ids);");

			if($success) {
				$wpdb->query("DELETE FROM $this->LOG_TABLE WHERE result_id IN ($ids)");
				return array('success' => true);
			}
		}

		/**
		 * Reassigns a set of results to another user
		 */
		function reassign_results( $ids, $reassignId ) {
			global $wpdb;
			$success = $wpdb->query("update $this->RESULT_TABLE set user_id = $reassignId where id in ($ids);");

			if($success) {
				return array('success' => true);
			}
		}

		/**
		 * Retrieves single result info based on ID
		 */
		function get_result_info( $id ) {
			global $wpdb;
			$result = $wpdb->get_row(
				"
				SELECT r.id, r.time, r.garmin_id, r.position, r.event_id, r.age_category, r.pending
				FROM $this->RESULT_TABLE r WHERE r.id = $id
				"
			);

			return $result;
		}

		/**
		 * Updates a user display name
		 */
		function update_user_display_name( $user_id, $display_name ) {
			global $wpdb;
			$success = $wpdb->update(
				$this->USER_TABLE,
				array(
					'display_name' => $display_name
				),
				array( 'ID' => $user_id ),
				array( '%s' ),
				array( '%d' )
			);
		}

		/**
		 * inserts a new event into the database
		 */
		function create_event( $data, $is_admin_update = false ) {
			global $wpdb;
			$success = $wpdb->query( $wpdb->prepare(
				"
				INSERT INTO $this->EVENT_TABLE
				( date, sub_type_id, name, location, event_cat_id )
				VALUES ( %s, %s, %s, %s, %d )
				",
				$data['eventDate'],
				$data['eventSubType'],
				$data['eventName'],
				$data['eventLocation'],
				$data['eventCategory']
			) );

			if($success) {
				$id = $wpdb->get_var( "SELECT LAST_INSERT_ID()" );

				// write to log
				if( !$is_admin_update ) {
					$log_content = $this->parent->process_log_content( 'new_event', $data );
					$this->write_to_log( 'new_event', $log_content, $id );
				}

				// return id
				return $id;
			}

			return false;
		}

		/**
		 * Deletes a list of events and reassigns their results to a specified event ID (optional)
		 */
		public function delete_events( $request ) {
			global $wpdb;

			$ids = $request['ids'];
			$reassign_id = $request['reassignId'];

			if( isset( $ids ) ) {
				// do we flush the results or reassign them to another event ID?
				if( isset ( $reassign_id ) && $reassign_id != '' ) {
					// reassign results
					$wpdb->query("update $this->RESULT_TABLE set event_id = $reassign_id where event_id in ($ids);");
				}
				else {
					// delete results
					$wpdb->query("delete from $this->RESULT_TABLE where event_id in ($ids);");
				}
				// now get rid of those pesky events
				return $wpdb->query("delete from $this->EVENT_TABLE where id in ($ids);");
			}

			return 0;
		}

		/**
		 * Merges a list of event results to one specified event
		 */
		public function merge_events( $request ) {
			global $wpdb;

			$ids = $request['ids'];
			$reassign_id = $request['reassignId'];

			if( isset( $ids ) && isset ( $reassign_id ) ) {
				// reassign results to primary event ID
				$wpdb->query("update $this->RESULT_TABLE r set r.event_id = $reassign_id where r.pending = 0 AND r.event_id in ($ids);");

				// now remove the other remaining events
				return $wpdb->query("delete from $this->EVENT_TABLE where id in ($ids);");
			}

			return 0;
		}

		/**
		 * Checks if an alternative sort column should be used for results
		 */
		public function convert_result_column( $column )  {
			if( $column == 'event_date' ) {
				return 'date';
			}
			if( $column == 'result_date' ) {
				return 'date_created';
			}
			return $column;
		}

		/**
		 * Checks if an alternative sort column should be used for events
		 */
		public function convert_event_column( $column )  {
			if( $column == 'event_date' ) {
				return 'e.date';
			}
			if( $column == 'result_date' ) {
				return 'r.date_created';
			}
			return $column;
		}

		/**
		 * counts number of results available for a given user
		 */
		public function get_result_count($user_id, $request) {
			global $wpdb;
			$where = 'WHERE pending = 0 ';
			$where .= $user_id > -1 ? ('AND user_id = ' . $user_id) : 'AND user_id > 0';
			return $wpdb->get_var( "SELECT COUNT(id) FROM $this->RESULT_TABLE $where" );
		}

		/**
		 * counts number of events available
		 */
		public function get_event_count() {
			global $wpdb;
			return $wpdb->get_var( "SELECT COUNT(id) FROM $this->EVENT_TABLE" );
		}

		/**
		 * clear sample data before loading new data
		 */
		public function clear_sample_data() {
			global $wpdb;
			$wpdb->query("delete from wp_usermeta where user_id in (select id from $this->USER_TABLE where user_login like 'sample_user_%' )");
			$wpdb->query("delete from $this->USER_TABLE where user_login like 'sample_user_%'");
			$wpdb->query("delete from $this->EVENT_TABLE");
			$wpdb->query("delete from $this->RESULT_TABLE");
		}

		/**
		 * Removes all WPA tables and data
		 */
		public function uninstall_wpa() {
			$this->clear_sample_data();
			$this->remove_wpa_data();
			$this->uninstall_tables();
		}

		/**
		 * removes all the WP Athletics tables
		 */
		function uninstall_tables() {
			global $wpdb;
			$wpdb->query("drop table $this->EVENT_TABLE");
			$wpdb->query("drop table $this->RESULT_TABLE");
			$wpdb->query("drop table $this->EVENT_CAT_TABLE");
			$wpdb->query("drop table $this->LOG_TABLE");
			$wpdb->query("drop view $this->RESULT_VIEW");
		}

		/**
		 * removes any WP Athletics fields from the usermeta and options table
		 */
		function remove_wpa_data() {
			global $wpdb;
			$wpdb->query("delete from wp_usermeta where meta_key like '%wp-athletics%'");
			$wpdb->query("delete from wp_options where option_name like '%wp-athletics%'");
			$wpdb->query("delete from wp_options where option_name like 'wpa-%'");
		}

		/**
		 * Adds or removes capabilites to manage WP Athletics options
		 */
		public function toggle_capabilities( $add = true ) {
			$role = get_role( 'administrator' );

			if( $add == true ) {
				$role->add_cap( 'manage_wp_athletics' );
				wpa_log('added role capabilities');
			}
			else {
				$role->remove_cap( 'manage_wp_athletics' );
				wpa_log('removed role capabilities');
			}
		}

		/**
		 * gets an array of event sub types from the WP options or uses default value from settings if not set
		 */
		public function get_event_sub_types() {
			global $wpa_settings;
			return get_option( 'wp-athletics_event_sub_types', $wpa_settings['default_terrain_categories'] );
		}

		/**
		 * gets an array of event age categories from the WP options or uses default value from settings if not set
		 */
		public function get_age_categories() {
			global $wpa_settings;
			$cats = get_option( 'wp-athletics_age_cats', $wpa_settings['default_age_categories'] );

			// now sort by 'from' value
			foreach ($cats as $key => $row) {
				$from[$key]  = $row['from'];
			}
			array_multisort($from, SORT_ASC, $cats);

			return $cats;
		}

		/**
		 * removes an age category by ID
		 */
		public function delete_age_category( $id ) {
			$age_cats = $this->get_age_categories();
			unset($age_cats[$id]);
			return update_option( 'wp-athletics_age_cats', $age_cats );
		}

		/**
		 * Updates an age category with the supplied details
		 */
		public function update_age_category( $data ) {
			global $wpdb;

			$age_cats = $this->get_age_categories();
			$age_cat_keys = array_keys( $age_cats );

			$is_update = isset( $data['id'] ) && $data['id'] != '0';

			$success = false;

			$new_data = array(
				'name' => $data['name'],
				'from' => $data['from'],
				'to' => $data['to']
			);

			// create the age cat
			if( !$is_update ) {
				$id = 'C' . $data['from'] . $data['to'];
				$age_cats[$id]= $new_data;
			}
			// update the age cat
			else {
				foreach( $age_cat_keys as $age_cat) {
					if( $age_cat == $data['id'] ) {
						$age_cats[(string)$data['id']] = $new_data;
					}
				}
			}

			return update_option( 'wp-athletics_age_cats', $age_cats );
		}

		/**
		 * Validates if a user has already entered a particular event
		 */
		function validate_event_entry( $data ) {
			global $wpdb;
			global $current_user;
			$user_id;

			$event_id = $data['eventId'];

			if( isset( $data['userId'] ) &&  $data['userId'] != '' ) {
				$user_id = $data['userId'];
			}
			else {
				$user_id = $current_user->ID;
			}

			// get the result count for the datatable
			$results = $wpdb->get_results(
				"SELECT r.pending, r.id FROM $this->RESULT_TABLE r
				WHERE r.user_id = $user_id AND r.event_id = $event_id;"
			);
			
			// get the result count for the datatable
			$event_result = $wpdb->get_row(
				"SELECT (CASE WHEN (date > DATE(NOW())) THEN 1 ELSE 0 END) AS is_future
				FROM $this->EVENT_TABLE e WHERE id = $event_id;"
			);
			
			$result = null;
			
			if(!empty($results)) {
				$result = $results[0];
			}

			return array(
				'valid' => ($result == null && $event_result->is_future == '0'),
				'pending' => ($result && $result->pending == '1'),
				'future' => ($event_result && $event_result->is_future == '1'),
				'resultId' => ($result ? $result->id : '')
			);
		}
		
		/**
		 * Deletes an existing user
		 */
		function delete_user( $data ) {
			$id = $data['userId'];

			return array(
				'success' => wp_delete_user( $id )
			);
		}
		
		/**
		 * Edits an existing user
		 */
		function update_user( $data ) {
			$error = null;
			$success = false;
			
			$name = $data['name'];
			$gender = $data['gender'];
			$dob = $data['dob'];
			$email = $data['email'];
			$user_id = $data['userId'];
			
			$user = get_user_by( 'id', $user_id );
			if($user) {
				$user_id = wp_update_user( 
					array( 
						'ID' => $user_id,
						'display_name' => $name,
						'user_email' => $email
					)	
				);
				
				if ( is_wp_error( $user_id ) ) {
					$error = 'There was an error updating the athlete';
				} else {
					if( $dob != '' ) {
						update_user_meta( $user_id, 'wp-athletics_dob', $dob );
					}
					if($gender != '') {
						update_user_meta( $user_id, 'wp-athletics_gender', $gender );
					}
					$success = true;
				}
			}
			else {
				$error = "User with ID $user_id was not found";
			}
			
			return array(
				'success' => $success,
				'error' => $error
			);
		}

		/**
		 * Creates a new user profile
		 */
		function create_user( $data ) {
			global $wpdb;
			$error = false;

			$name = $data['name'];
			$gender = $data['gender'];
			$dob = $data['dob'];
			$email = $data['email'];
			$username = $data['username'];
			$send_email = $data['sendEmail'];
			$password = wp_generate_password();
			
			if( empty( $email ) ) {
				'no@email.com';
			}

			$user_id = wp_create_user( $username, $password, $email );

			if ( is_wp_error( $user_id ) ) {
				$error = $user_id->get_error_message();
			}
			else {
				// successs
				$this->update_user_display_name( $user_id, $name );
				if( $dob != '' ) {
					update_user_meta( $user_id, 'wp-athletics_dob', $dob );
				}
				update_user_meta( $user_id, 'wp-athletics_gender', $gender );
			}

			// return results
			return array(
				'error' => $error,
				'id' => $user_id,
				'username' => $username,
				'password' => $password,
				'sendEmail' => $send_email
			);
		}

		/**
		 * Generates statistics for a user
		 */
		function get_statistics( $request ) {
			$where = 'r.pending = 0 ';
			$user_id = $request['userId'];
			$age_category = $request['ageCat'];
			$event_cat_id = $request['eventCat'];
			$event_sub_type_id = $request['type'];
			$date = $request['period'];
			$stat = $request['stat'];
			$mode = $request['mode'];

			if( isset( $mode ) && $mode != '' ) {
				if( $mode == 'user' ) {
					$where = $where . 'AND user_id = ' . $user_id;
				}
				else {
					$where = $where . 'AND user_id > 0';
				}

				if( isset( $date ) && $date != 'all' && $date != '' ) {
					$date_where = $this->convert_date( $date, (WPA_DB_DISABLE_SQL_VIEW ? 'e.date' : 'r.date') );
					if( $date_where != '' ) {
						$where = $where . ($where == '' ? '' : ' AND ');
						$where = $where . $date_where;
					}
				}

				if( isset( $age_category ) && $age_category != '' && $age_category != 'all' ) {
					$where = $where . ($where == '' ? '' : ' AND ');
					$where = $where . "r.age_category = '" . $age_category . "'";
				}

				if( isset( $event_sub_type_id ) && $event_sub_type_id != 'all' && $event_sub_type_id != '' ) {
					$where = $where . ($where == '' ? '' : ' AND ');
					if( WPA_DB_DISABLE_SQL_VIEW ) {
						$where = $where . "e.sub_type_id = '" . $event_sub_type_id . "'";
					}
					else {
						$where = $where . "r.event_sub_type_id = '" . $event_sub_type_id . "'";
					}
				}

				if( ( $stat == 'events' || $stat == 'runner' ) && ( isset( $event_cat_id ) && $event_cat_id != 'all' && $event_cat_id != ''  ) ) {
					$where = $where . ($where == '' ? '' : ' AND ');
					if( WPA_DB_DISABLE_SQL_VIEW ) {
						$where = $where . 'e.event_cat_id = ' . $event_cat_id;
					}
					else {
						$where = $where . 'r.event_cat_id = ' . $event_cat_id;
					}
				}

				// perform query
				$results = $this->generate_statistics( $stat, $where );

				return $results;
			}
		}

		/**
		 * Generates statistics based on the type provided and the SQL where clause
		 */
		function generate_statistics( $type, $where ) {
			global $wpdb;
			$result;

			if( $type == 'summary' ) {

				if( WPA_DB_DISABLE_SQL_VIEW ) {
					// OVERALL STATS
					$sql = "SELECT SUM(ec.distance_meters) AS total_distance, SUM(r.time) AS total_time, COUNT(r.id) AS count,
					(select COUNT(id) FROM $this->USER_TABLE) as athletes
					FROM $this->RESULT_TABLE r 
					LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id 
					LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id WHERE $where";
	
					$overall = $wpdb->get_results( $sql );
	
					// EVENT CATEGORY DISTRIBUTION STATS
					$sql = "SELECT ec.id, ec.name, (SELECT COUNT(r.id) FROM $this->RESULT_TABLE r
					LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id 
					LEFT JOIN $this->EVENT_CAT_TABLE ec2 ON e.event_cat_id = ec2.id 
					WHERE $where AND e.event_cat_id = ec.id) AS count
					FROM $this->EVENT_CAT_TABLE ec";
	
					//wpa_log($sql);
	
					$events = $wpdb->get_results( $sql );
	
					// EVENT TYPE DISTRIBUTION STATS
					$sql = "SELECT e.sub_type_id AS type, COUNT(r.id) AS count FROM $this->RESULT_TABLE r
					LEFT JOIN $this->EVENT_TABLE e ON r.event_id = e.id
					LEFT JOIN $this->EVENT_CAT_TABLE ec ON e.event_cat_id = ec.id 
					WHERE $where GROUP BY e.sub_type_id";
	
					$types = $wpdb->get_results( $sql );
				}
				else {
					// OVERALL STATS
					$sql = "SELECT SUM(distance_meters) AS total_distance, SUM(time) AS total_time, COUNT(id) AS count,
					(select COUNT(id) FROM $this->RESULT_VIEW r WHERE r.position=1 AND $where) AS wins,
					(select COUNT(id) FROM $this->RESULT_VIEW r WHERE (r.position=2 OR r.position=3) AND $where) AS runner_up,
					(select COUNT(id) FROM $this->RESULT_VIEW r WHERE (r.position > 0 AND r.position < 11) AND $where) AS top_tens,
					(select COUNT(id) FROM $this->USER_TABLE) as athletes
					FROM $this->RESULT_VIEW r WHERE $where";
					
					$overall = $wpdb->get_results( $sql );
					
					// EVENT CATEGORY DISTRIBUTION STATS
					$sql = "SELECT ec.id, ec.name, 
							(SELECT COUNT(r.id) FROM $this->RESULT_VIEW r JOIN $this->EVENT_TABLE e ON e.id = r.event_id WHERE $where AND e.event_cat_id = ec.id) AS count
							FROM $this->EVENT_CAT_TABLE ec";
					
					$events = $wpdb->get_results( $sql );
					
					// EVENT TYPE DISTRIBUTION STATS
					$sql = "SELECT event_sub_type_id AS type, COUNT(id) AS count FROM $this->RESULT_VIEW r WHERE $where GROUP BY event_sub_type_id";
					
					$types = $wpdb->get_results( $sql );
				}

				return array(
					'summary' => $overall,
					'events' => $events,
					'types' => $types
				);
			}
			else if( $type == 'events' ) {
				if( WPA_DB_DISABLE_SQL_VIEW ) {
					$sql = "SELECT e.id as event_id, ec.distance_meters, r.position, e.sub_type_id as event_sub_type_id, e.name as event_name, e.event_cat_id, r.time, e.location as event_location, date_format(e.date,'" . WPA_DATE_FORMAT . "') AS event_date,
					date_format(e.date,'%M %d, %Y %k:%i:%s') AS date_js FROM " . $this->RESULT_TABLE . " r 
					LEFT JOIN " . $this->EVENT_TABLE . " e ON r.event_id = e.id 
					LEFT JOIN " . $this->EVENT_CAT_TABLE . " ec ON e.event_cat_id = ec.id 
					LEFT JOIN " . $this->USER_TABLE . " u ON r.user_id = u.id WHERE " . $where . " ORDER BY e.date ASC";
				}
				else {
					$sql = "SELECT event_id, distance_meters, position, event_sub_type_id, event_name, event_cat_id, time, event_location, date_format(date,'" . WPA_DATE_FORMAT . "') AS event_date,
					date_format(date,'%M %d, %Y %k:%i:%s') AS date_js FROM " . $this->RESULT_VIEW . " r WHERE " . $where . " ORDER BY date ASC";
				}
				wpa_log( $sql );

				$results = $wpdb->get_results( $sql );

				return $results;
			}
			else if( $type == 'runner' ) {
				if( WPA_DB_DISABLE_SQL_VIEW ) {
					$sql = "SELECT u.id, display_name AS name, COUNT(r.id) count FROM " . $this->RESULT_TABLE . " r 
					LEFT JOIN " . $this->EVENT_TABLE . " e ON r.event_id = e.id 
					LEFT JOIN " . $this->EVENT_CAT_TABLE . " ec ON e.event_cat_id = ec.id
					LEFT JOIN " . $this->USER_TABLE . " u ON r.user_id = u.id WHERE " . $where .
					" GROUP BY u.display_name ORDER BY count DESC LIMIT 10;";
				}
				else {
					$sql = "SELECT u.id, display_name AS name, COUNT(r.id) count FROM $this->USER_TABLE u LEFT JOIN $this->RESULT_VIEW r ON u.id = r.user_id WHERE $where 
					GROUP BY display_name ORDER BY count DESC LIMIT 10;";
				}

				wpa_log( $sql );

				$results = $wpdb->get_results( $sql );

				return $results;
			}
		}

		/**
		 * Generates event statistics
		 */
		function generate_event_statistics( $event_id, $where ) {
			global $wpdb;
			$result;

			$sql = 'SELECT ec.id, ec.name,
			(SELECT COUNT(r.id) FROM ' . $this->RESULT_TABLE . ' r JOIN ' . $this->EVENT_TABLE . ' e ON e.id = r.event_id WHERE ' . $where . ' AND e.event_cat_id = ec.id) AS count
			FROM ' . $this->EVENT_CAT_TABLE . ' ec';

			wpa_log( $sql );

			return $wpdb->get_results( $sql );
		}

		/**
		 * Writes a log entry
		 */
		function write_to_log( $type, $content, $event_id = 0, $result_id = 0 ) {
			global $current_user;
			return $this->write_to_log_user_provided( $current_user, $type, $content, $event_id, $result_id );
		}

		/**
		 * Writes a log entry
		 */
		function write_to_log_user_provided( $user, $type, $content, $event_id = 0, $result_id = 0 ) {
			global $wpdb;
			wpa_log('LOG: ' . $content);

			return $wpdb->query( $wpdb->prepare(
				"
				INSERT INTO $this->LOG_TABLE
				( type, user_id, event_id, result_id, content )
				VALUES ( %s, %d, %d, %d, %s)
				",
				$type,
				$user->ID,
				$event_id,
				$result_id,
				$content
			) );
		}

		/**
		 * retrieves list of log entries
		 */
		function get_logs( $data ) {
			global $wpdb;

			$where = '';
			$type = $data['type'];
			$period = $data['period'];

			if( isset( $period ) && $period != 'all' ) {
				$date_where = $this->convert_date( $period, 'date' );
				if( $date_where != '' ) {
					$where .= $date_where;
				}
			}

			if( isset( $type ) && $type != 'all' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . 'type = \'' . $type . '\'';
			}

			if( $where != '' ) {
				$where = 'WHERE ' . $where;
			}

			$sql = "SELECT date_format(l.date,'%d %b %Y %H:%i:%s') as date, l.type, l.content, l.user_id, l.event_id FROM " . $this->LOG_TABLE . " l " .
			$where . "ORDER BY l.date desc limit 1000";

			$results = $wpdb->get_results( $sql );

			return $results;
		}

		/**
		 * logs a login event
		 */
		function log_login( $user_login, $user ) {
			$content = $this->parent->process_log_content_user_provided( $user, 'user_login' );
			$this->write_to_log_user_provided( $user, 'user_login', $content );
		}
		
		/**
		 * Retrieves a list of events for a given year
		 */
		function get_events_for_year( $data ) {
			global $wpdb;
			global $current_user;
			
			$year = $data['year'];
			
			$sql = "SELECT date_format(e.date,'%W, %d %M %Y') as display_date, e.cost, e.url, MONTH(date) as month, e.location, e.name, e.id as event_id, " .
				"(SELECT COUNT(r.id) FROM " .  $this->RESULT_TABLE . " r WHERE event_id = e.id) AS count," .
				"(CASE WHEN (e.date > DATE(NOW())) THEN 1 ELSE 0 END) AS is_future, " .
				"(SELECT COUNT(r.id) FROM " .  $this->RESULT_TABLE . " r WHERE event_id = e.id AND r.pending = 0 AND user_id = " . $current_user->ID . ") AS has_result, " .
				"(SELECT r.id FROM " .  $this->RESULT_TABLE . " r WHERE event_id = e.id AND r.pending = 1 AND user_id = " . $current_user->ID . ") AS pending_result_id " .
				" from " . $this->EVENT_TABLE . " e WHERE YEAR(e.date) = $year ORDER BY e.date ASC";
			
			wpa_log($sql);

			return array(
				'results' => $wpdb->get_results( $sql )
			);
		}
		
		/**
		 * Registers a user for a future given event, obviously
		 */
		function register_user_for_event( $event_id ) {
			global $wpdb;
			global $current_user;
			
			$success = $wpdb->query( $wpdb->prepare(
				"
				INSERT INTO $this->RESULT_TABLE
				( user_id, event_id, pending )
				VALUES ( %d, %d, %d )
				",
				$current_user->ID,
				$event_id,
				1
			) );
			
			return array(
				'success' => $success
			);
		}

		/**
		 * retrieves a list of recent results
		 */
		function get_recent_results_by_date( $data ) {
			global $wpdb;

			$where = 'l.type = "new_result"';
			$month = $data['month'];
			$year = $data['year'];

			if( isset( $month ) && $month != 'all' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . 'MONTH(e.date) = ' . $month;
			}

			if( isset( $year ) && $year != 'all' ) {
				$where = $where . ($where == '' ? '' : ' AND ');
				$where = $where . 'YEAR(e.date) = ' . $year;
			}

			$sql = "SELECT date_format(e.date,'" . WPA_DATE_FORMAT . "') as display_date, e.event_cat_id, l.content, l.user_id, l.event_id from " .
			$this->LOG_TABLE . " l LEFT JOIN $this->EVENT_TABLE e ON l.event_id = e.id WHERE " .
			$where . " ORDER BY e.date desc";

			wpa_log($sql);

			$results = $wpdb->get_results( $sql );
			$user_photos = array();

			// now get profile photo values for unique users
			$in_user_array = '';
			if( count( $results ) > 0 ) {
				$users = array();
				foreach( $results as $result ) {

					$found = array_search( $result->user_id, $users );
					if( $found === false ) {
						array_push( $users, $result->user_id );
						$in_user_array .= ($result->user_id . ',');
					}
				}

				if( $in_user_array != '') {
					$in_user_array = substr($in_user_array, 0, -1);

					$sql = "SELECT user_id, meta_value AS photo FROM wp_usermeta WHERE meta_key = 'wp-athletics_profile_photo' and user_id in (" . $in_user_array . ")";
					$photos = $wpdb->get_results( $sql );

					if( count( $photos ) > 0) {
						foreach( $photos as $photo ) {
							$user_photos[ $photo->user_id ] = $photo->photo;
						}
					}
				}
			}

			return array(
				'results' => $results,
				'user_photos' => $user_photos
			);
		}

		/**
		 * retrieves a list of recent results
		 */
		function get_upcoming_events( $num = 5 ) {
			global $wpdb;

			$sql = "SELECT date_format(e.date,'%d %b %y') as display_date, e.location, e.name, e.id as event_id, " .
			"(SELECT COUNT(r.id) FROM " .  $this->RESULT_TABLE . " r WHERE pending = 1 AND event_id = e.id) AS count" .
			" from " . $this->EVENT_TABLE . " e WHERE e.date >= DATE(NOW()) ORDER BY e.date DESC LIMIT " . $num;

			return $wpdb->get_results( $sql );
		}

		/**
		 * Returns number of pending results to be added for a user -  past and pending events.
		 */
		function get_pending_result_count() {
			global $wpdb;
			global $current_user;

			return $wpdb->get_var(
				"SELECT count(r.id) FROM $this->RESULT_TABLE r 
				JOIN $this->EVENT_TABLE e ON r.event_id = e.id 
				WHERE r.pending = 1 AND e.date <= DATE(NOW());"
			);
		}
		
		/**
		 * retrieves a list of recent results
		 */
		function get_recent_results( $num = 5 ) {
			global $wpdb;
		
			$sql = "SELECT date_format(e.date,'%d %b %y') as display_date, l.content, l.user_id, l.event_id, e.event_cat_id from " .
					$this->LOG_TABLE . " l LEFT JOIN $this->EVENT_TABLE e ON l.event_id = e.id WHERE type = 'new_result' ORDER BY e.date desc LIMIT " . $num;
		
			wpa_log($sql);
		
			return $wpdb->get_results( $sql );
		}
 	}
}
?>