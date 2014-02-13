<?php
if ( !defined( 'WPA_STATS_ENABLED' ) ) {
	define( 'WPA_STATS_ENABLED', true );
}

/**
 * Class for generating and displaying user stats tab
 */

if( !class_exists('WP_Athletics_Stats') ) {

	class WP_Athletics_Stats extends WPA_Base {

		/**
		 * default constructor
		 */
		public function __construct( $db ) {
			parent::__construct( $db );

			$this->wpa_db = $db;

			// add actions
			add_action( 'wp_ajax_wpa_get_stats', array ( $this, 'get_stats') );
			add_action( 'wp_ajax_nopriv_wpa_get_stats', array ( $this, 'get_stats') );
		}

		/**
		 * [AJAX] Retrieves stats
		 */
		public function get_stats() {

			// perform the query
			$result = $this->wpa_db->get_statistics( $_POST );

			// return as json
			wp_send_json( $result );
			die();
		}

		/**
		 * generates the label for the user statistics dropdown
		 */
		public function print_user_stats_filter_label( $is_dialog ) {
			if( !$is_dialog ) {
				echo $this->get_property('stats_type_mine');
			}
			else {
				echo $this->get_property('stats_type_user');
			}
		}

		/**
		 * generates the stats tab on a user profile
		 */
		public function display_stats_tab( $is_dialog = false ) {
			$suffix = $is_dialog ? '-dialog' : '';
		?>
			<li><a href="#tabs-stats<?php echo $suffix; ?>"><?php echo $this->get_property('stats_tab') ?></a></li>
		<?php
		}

		/**
		 * generates the stats tab on a users profile
		 */
		public function display_stats_tab_content( $is_dialog = false ) {

			global $current_user;
			$user_id = $current_user->ID;
			$suffix = $is_dialog ? '-dialog' : '';
			$is_dialog_str = $is_dialog ? 'true' : 'false';

			?>

			<div id="tabs-stats<?php echo $suffix ?>" wpa-tab-type="stats">

				<script type="text/javascript" src='https://www.google.com/jsapi?autoload={"modules":[{"name":"visualization","version":"1","packages":["corechart"]}]}'></script>

				<script type="text/javascript">

					jQuery(document).ready(function() {

						// set defaults
						WPA.Stats.type = 'user';
						WPA.Stats.typeDialog = 'user';
						WPA.Stats.eventDialog = 'all';
						WPA.Stats.event = 'all';
						WPA.Stats.stat = 'summary';
						WPA.Stats.statDialog = 'summary';

						WPA.Stats.userId = <?php echo $user_id; ?>;

						// stats type select (club/user)
						jQuery('#wpa-stats-type-select' + '<?php echo $suffix; ?>').combobox({
							select: function(event, ui) {

								if(<?php echo $is_dialog_str; ?>) {
									WPA.Stats.typeDialog = ui.item.value
								}
								else {
									WPA.Stats.type = ui.item.value;
								}
								WPA.Stats.loadStats(<?php echo $is_dialog_str; ?>);
							},
							defaultValue: 'user'
						});

						jQuery('#wpa-stats-event-select' + '<?php echo $suffix; ?>').combobox({
							select: function(event, ui) {
								jQuery('#wpa-stats-events-no-results' + '<?php echo $suffix; ?>').hide();
								jQuery('#wpa-stats-events-content' + '<?php echo $suffix; ?>').hide();
								jQuery('#wpa-stats-events-default' + '<?php echo $suffix; ?>').hide();

								if(<?php echo $is_dialog_str; ?>) {
									WPA.Stats.eventDialog = ui.item.value
								}
								else {
									WPA.Stats.event = ui.item.value;
								}

								if(ui.item.value == 'default') {
									jQuery('#wpa-stats-events-default' + '<?php echo $suffix; ?>').show();
								}
								else {
									WPA.Stats.loadStats(<?php echo $is_dialog_str; ?>);
								}
							},
							defaultValue: 'default'
						});

						// create stats accordian widget
						var icons = {
					      header: "ui-icon-circle-arrow-e",
					      activeHeader: "ui-icon-circle-arrow-s"
					    };

						jQuery('.wpa-stats-accordian' + '<?php echo $suffix; ?>').accordion({
					      icons: icons,
					      activate: function( event, ui ) {

					    	  var stat = ui.newHeader[0].attributes['wpa-stat-type'].value;
					    	  var showEvents = ui.newHeader[0].attributes['wpa-stat-require-event-selection'] && ui.newHeader[0].attributes['wpa-stat-require-event-selection'].value == 'true';

							  // show or hide the event selector
							  if(showEvents) {
								  jQuery('#wpa-stats-event' + '<?php echo $suffix; ?>').show();
							  }
							  else {
								  jQuery('#wpa-stats-event' + '<?php echo $suffix; ?>').hide();
							  }

					    	  if(<?php echo $is_dialog_str; ?>) {
					    		  WPA.Stats.statDialog = stat;
					    	  }
					    	  else {
					    		  WPA.Stats.stat = stat;
					    	  }
					    	  WPA.Stats.loadStats(<?php echo $is_dialog_str; ?>);
						  }
					    });

						jQuery('.wpa-stat-club-only<?php echo $suffix; ?>').hide();
					});

				</script>

				<div class="wpa-stats">

					<div>

						<div id="wpa-stats-type-container<?php echo $suffix?>" class="wpa-stats-type">
							<label for="wpa-stats-type-select<?php echo $suffix?>"><?php echo $this->get_property('stats_type_label')?></label>:
							<select id="wpa-stats-type-select<?php echo $suffix?>">
								<option selected="selected" value="user"><?php $this->print_user_stats_filter_label( $is_dialog ) ?></option>
								<option value="club"><?php echo $this->get_property('stats_type_club')?></option>
							</select>
						</div>

						<div style="display:none" class="wpa-stats-event" id="wpa-stats-event<?php echo $suffix?>">
							<label for="wpa-stats-event-select<?php echo $suffix?>"><?php echo $this->get_property('stats_event_label')?></label>:
							<select class="event-combo" id="wpa-stats-event-select<?php echo $suffix?>">
								<option selected="selected" value="all"><?php echo $this->get_property('stats_event_combo_default') ?></option>
							</select>
						</div>

						<span id="wpa-stats-type-note">
							<i><?php echo $this->get_property('stats_filter_note')?></i>
						</span>

						<br style="clear:both"/>

					</div>

					<div class="wpa-stats-content wpa-stats-accordian<?php echo $suffix?>">

					  <!-- SUMMARY -->
					  <h3 wpa-stat-type="summary"><?php echo $this->get_property('stats_heading_summary'); ?></h3>
					  <div id="wpa-stats-summary<?php echo $suffix?>">

					  	<!-- SUMMARY STATS -->
					  	<div class="wpa-summary-left">

						  	<div class="wpa-stats-item">
						  		<label><?php echo $this->get_property('stats_label_total_races'); ?>:</label>
						  		<span id="wpa-stats-total-races<?php echo $suffix?>"></span>
						  	</div>

						  	<div class="wpa-stats-item">
						  		<label><?php echo $this->get_property('stats_label_total_distance'); ?>:</label>
						  		<span id="wpa-stats-total-distance<?php echo $suffix?>"></span>
						  	</div>

						  	<div class="wpa-stats-item">
						  		<label><?php echo $this->get_property('stats_label_total_time'); ?>:</label>
						  		<span id="wpa-stats-total-time<?php echo $suffix?>"></span>
						  	</div>

						  	<?php 
						  	if( defined('WPA_DB_DISABLE_SQL_VIEW') && WPA_DB_DISABLE_SQL_VIEW == false )  {?>
							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_total_wins'); ?>:</label>
							  		<span id="wpa-stats-total-wins<?php echo $suffix?>"></span>
							  	</div>
							
							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_total_runner_up'); ?>:</label>
							  		<span id="wpa-stats-total-runner-up<?php echo $suffix?>"></span>
							  	</div>
	
							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_total_top_10'); ?>:</label>
							  		<span id="wpa-stats-total-top-10<?php echo $suffix?>"></span>
							  	</div>
						  	<?php 
							}
							?>

						  	<div class="wpa-stats-item wpa-stats-club-only">
						  		<label><?php echo $this->get_property('stats_label_total_athletes'); ?>:</label>
						  		<span id="wpa-stats-total-athletes<?php echo $suffix?>"></span>
						  	</div>

					  	</div>

						<!-- SUMMARY CHART -->
					  	<div class="wpa-summary-right">
							<div id="wpa-stats-summary-terrain-chart<?php echo $suffix?>"></div>
							<div style="margin-left: 30px" id="wpa-stats-summary-event-chart<?php echo $suffix?>"></div>
							<br style="clear:both;"/>
					  	</div>

					  	<br style="clear:both;"/>
					  </div>

					  <!-- EVENT STATISTICS -->
					  <h3 wpa-stat-require-event-selection="true" wpa-stat-type="events"><?php echo $this->get_property('stats_heading_events'); ?></h3>
					  <div id="wpa-stats-events<?php echo $suffix?>">

					    <div id="wpa-stats-events-default<?php echo $suffix?>">
					    	<?php echo $this->get_property('stats_events_default_message'); ?>
					    </div>

					    <div style="display:none" id="wpa-stats-events-no-results<?php echo $suffix?>">
					    	<?php echo $this->get_property('stats_events_not_enough_results'); ?>
					    </div>

					  	<div style="display:none" id="wpa-stats-events-content<?php echo $suffix?>">
					  		<!-- EVENT STATS -->
						  	<div class="wpa-summary-left">

							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_event_name'); ?>:</label>
							  		<span id="wpa-stats-event-name<?php echo $suffix?>"></span>
							  	</div>

							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_total_races'); ?>:</label>
							  		<span id="wpa-stats-event-count<?php echo $suffix?>"></span>
							  	</div>

							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_total_distance'); ?>:</label>
							  		<span id="wpa-stats-event-total-distance<?php echo $suffix?>"></span>
							  	</div>

							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_total_time'); ?>:</label>
							  		<span id="wpa-stats-event-total-time<?php echo $suffix?>"></span>
							  	</div>

							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_event_best'); ?>:</label>
							  		<span id="wpa-stats-event-best<?php echo $suffix?>"></span>
							  	</div>

							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_event_worst'); ?>:</label>
							  		<span id="wpa-stats-event-worst<?php echo $suffix?>"></span>
							  	</div>

							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_event_avg'); ?>:</label>
							  		<span id="wpa-stats-event-avg<?php echo $suffix?>"></span>
							  	</div>

							  	<div class="wpa-stats-item">
							  		<label><?php echo $this->get_property('stats_label_total_top_10'); ?>:</label>
							  		<span id="wpa-stats-event-top10s<?php echo $suffix?>"></span>
							  	</div>

						  	</div>

						  	<!-- EVENT CHARTS -->
						  	<div class="wpa-summary-right">
								<div id="wpa-stats-event-results-chart<?php echo $suffix?>"></div>
					  		</div>

					  		<br style="clear:both;"/>
					  	</div>
					  </div>

					  <!-- RUNNER STATS (still in dev) -->
					  <!-- 
					  <h3 wpa-stat-require-event-selection="true" wpa-stat-type="runner"><?php echo $this->get_property('stats_heading_runner'); ?></h3>
					  <div id="wpa-stats-runner<?php echo $suffix?>">

					  </div>
					  -->

					</div>
				</div>

			</div>

		<?php
		}
	}
}

?>