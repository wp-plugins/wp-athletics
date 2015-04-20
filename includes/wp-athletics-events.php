<?php

/**
 * Class for displaying upcoming and historic events
 */

if(!class_exists('WP_Athletics_Events')) {

	class WP_Athletics_Events extends WPA_Base {

		/**
		 * default constructor
		 */
		public function __construct( $db ) {
			parent::__construct( $db );
		}

		/**
		 * Creates an "Events" page
		 */
		public function create_page() {
			if( !get_option( 'wp-athletics_events_page_id' ) ) {

				$pages_created = get_option( 'wp-athletics_pages_created', array() );

				$the_page_id = $this->generate_page( $this->get_property('events_page_title') );

				if($the_page_id) {
					add_option('wp-athletics_events_page_id', $the_page_id, '', 'yes');

					array_push( $pages_created, $the_page_id );
					update_option( 'wp-athletics_pages_created', $pages_created);

					wpa_log('Events page created!');
			   }
		   }
		}

		/**
		 * Enqueues scripts and styles
		 */
		public function enqueue_scripts_and_styles() {
			// common scripts and styles
			$this->enqueue_common_scripts_and_styles();
			wp_enqueue_script( 'wpa-events' );
		}

		/**
		 * For content filtering, ensures the content is only displayed in the WP loop
		 */
		public function events_content_filter( $content ) {
			if( !in_the_loop() ) return $content;
			$this->events();
		}

		/**
		 * Generates a 'events' page when the shortcode [wpa-events] is used
		 */
		public function events() {

			global $current_user;
			global $wpa_settings;

			$this->enqueue_scripts_and_styles();

			$today = getdate();
			$this_year = $today['year'];

		?>
			<script type='text/javascript'>
				jQuery(document).ready(function() {

					// set up ajax and retrieve my results
					WPA.Ajax.setup('<?php echo admin_url( 'admin-ajax.php' ); ?>', '<?php echo wp_create_nonce( $this->nonce ); ?>', '<?php echo WPA_PLUGIN_URL; ?>', '<?php echo $current_user->ID; ?>',  function() {

						jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '<?php echo strtolower(get_option( 'wp-athletics_language', 'en') ); ?>' ] );

						var date = new Date();
						WPA.Events.filterYear = date.getFullYear();

						jQuery("#filterYear").combobox({
							select: function(event, ui) {
								WPA.Events.filterYear = ui.item.value;
								WPA.Events.displayResults();
							},
							selectClass: 'filter-highlight',
							defaultValue: WPA.Events.filterYear
						}).combobox('setValue', WPA.Events.filterYear);

						// common setup function
						WPA.setupCommon();

						// setup the edit event screen
						WPA.setupEditEventDialog(function() {
							WPA.Events.displayResults();
						});

						jQuery('#submit-event-button').button({
							icons: {
					        	primary: 'ui-icon-circle-plus'
					        }
						});
						
						jQuery('.submit-event').click(function(e) {
							e.preventDefault();
							WPA.showCreateEventDialog();
						});

						// load results
						WPA.Events.displayResults();

						// add/edit results dialog
						WPA.setupEditResultDialog(function() {
							WPA.Events.displayResults();
							WPA.reloadEventResults();
						});
					});
				});
			</script>
			
			<style>
			
			.event-month {
				display: none;
			}
			
			#submit-event-button {
				position: relative;
				top: -1px;
				height: 25px;
			}
			
			.wpa-menu {
				height: 35px !important;
				border-bottom: 1px solid #eee;
			}
			
			.event-month p {
				border-bottom: 1px solid #A5A5A5;
				font-size: 22px;
				margin: 0px;
			}
			
			.wpa-event-title.past {
				text-decoration: line-through;
			}
			
			.wpa-event-content {
				font-size: 14px;
			}
			
			.wpa-event {
				padding: 10px 0px;
				margin: 0px;
				border-bottom: 1px solid #eee;
			}
			
			.wpa-event-right {
				width: 250px;
				float: right;
				margin-right: 5px;
				text-align: right;
			}
			
			.wpa-event:hover {
				background-color: #FAF8DD;
				cursor: pointer;
			}
			
			.wpa-event-left {
				float: left;
				margin-left: 5px;
			}
			
			button {
				font-size: 11px !important;
			}

			.my-event {
				background-color: #FFFEAA;
			}
			
			#wpa-search {
				margin-top: 3px !important;
			}
			
			#event-add-pending-button {
				background: #FFDF98 !important;
			}
			
			</style>
			
			<?php $this->display_page_loading(); ?>

			<div class="wpa hide">

				<div class="wpa-menu">

					<!-- FILTERS -->
					<div class="wpa-filters ui-corner-all">

						<select id="filterYear">
							<option value="<?php echo $this_year; ?>"><?php echo $this->get_property('filter_period_option_this_year'); ?></option>
							<option value="<?php echo ((int)$this_year+1); ?>"><?php echo $this->get_property('filter_period_option_next_year'); ?></option>
							<?php

							for( $year = $this_year-1; $year >= $this_year-50; $year-- ) {
							?>
								<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
							<?php
							}
							?>
						</select>
						<?php 
						if( is_user_logged_in() && get_option( 'wp-athletics-allow-users-submit-events', 'yes' ) == 'yes' ) {
						?>
						<button class="submit-event" id="submit-event-button"><?= $this->get_property('submit_event_button') ?></button>
						<?php 
						}
						?>

					</div>

					<!-- EVENT / ATHLETE SEARCH -->
					<div class="wpa-ac-search wpa-records-search">
						<span class="wpa-search-image"></span>
						<input type="text" class="ui-corner-all ui-widget ui-state-default wpa-search wpa-search-disabled" default-text="<?php echo $this->get_property('wpa_search_text'); ?>" value="" id="wpa-search" class="text ui-widget-content ui-corner-all" />
					</div>

					<br style="clear:both"/>
				</div>

				<div class="feed-content-empty" id="events-empty">
					<p><?= $this->get_property('events_empty_text'); ?></p>
					<?php 
					if( is_user_logged_in() && get_option( 'wp-athletics-allow-users-submit-events', 'yes' ) == 'yes' ) {
					?>
						<a class="submit-event" href="#"><?= $this->get_property('events_empty_submit_event'); ?></a>
					<?php 
					}
					?>
				</div>
			
				<!-- EVENTS -->
				<div id="wpa-events">
				<?php 
					for($i = 1; $i <= 12; $i++) {
				?>
						<div class="event-month month-<?= $i ?>">
							<p><?= $this->get_property('month_' . $i)?></p>
						</div>
				<?php
					}
				?>
				</div>
				<div id="wpa-events-legend" style="display:none; margin-top: 10px">
					<span class="wpa-legend future-event"></span><span class="wpa-legend-key"><?= $this->get_property('legend_future_events')?></span>
				</div>

				<!-- ADD/EDIT RESULTS DIALOG -->
				<?php $this->create_edit_result_dialog(); ?>
				
				<!-- ADD/EDIT EVENT DIALOG -->
				<?php $this->create_edit_event_dialog(); ?>

				<!-- COMMON DIALOGS -->
				<?php $this->create_common_dialogs(); ?>
			</div>
		<?php
		}
	}
}
?>