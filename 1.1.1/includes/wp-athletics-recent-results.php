<?php

/**
 * Class for displaying a list of recent results
 */

if(!class_exists('WP_Athletics_Recent_Results')) {

	class WP_Athletics_Recent_Results extends WPA_Base {

		/**
		 * default constructor
		 */
		public function __construct( $db ) {
			parent::__construct( $db );
		}

		/**
		 * Creates a "Recent Results" page
		 */
		public function create_page() {
			if( !get_option( 'wp-athletics_recent_results_page_id' ) ) {

				$pages_created = get_option( 'wp-athletics_pages_created', array() );

				$the_page_id = $this->generate_page( $this->get_property('recent_results_page_title') );

				if($the_page_id) {
					add_option('wp-athletics_recent_results_page_id', $the_page_id, '', 'yes');

					array_push( $pages_created, $the_page_id );
					update_option( 'wp-athletics_pages_created', $pages_created);

					wpa_log('Recent Results page created!');
			   }
		   }
		}

		/**
		 * Enqueues scripts and styles
		 */
		public function enqueue_scripts_and_styles() {
			// common scripts and styles
			$this->enqueue_common_scripts_and_styles();
			wp_enqueue_script( 'wpa-recent-results' );
		}

		/**
		 * For content filtering, ensures the content is only displayed in the WP loop
		 */
		public function recent_results_content_filter( $content ) {
			if( !in_the_loop() ) return $content;
			$this->recent_results();
		}

		/**
		 * Generates a 'recent results' settings page when the shortcode [wpa-recent-results] is used
		 */
		public function recent_results() {

			global $current_user;
			global $wpa_settings;

			$this->enqueue_scripts_and_styles();

			$today = getdate();
			$this_year = $today['year'];
			$this_month = $today['mon'];

		?>
			<script type='text/javascript'>
				jQuery(document).ready(function() {

					// set up ajax and retrieve my results
					WPA.Ajax.setup('<?php echo admin_url( 'admin-ajax.php' ); ?>', '<?php echo wp_create_nonce( $this->nonce ); ?>', '<?php echo WPA_PLUGIN_URL; ?>', '<?php echo $current_user->ID; ?>',  function() {

						jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '<?php echo strtolower(get_option( 'wp-athletics_language', 'en') ); ?>' ] );

						var date = new Date();
						WPA.RecentResults.filterMonth = 'all';
						WPA.RecentResults.filterYear = date.getFullYear();

						// setup combobox filters
						jQuery("#filterMonth").combobox({
							select: function(event, ui) {
								WPA.RecentResults.filterMonth = ui.item.value;
								WPA.RecentResults.displayResults();
							},
							selectClass: 'filter-highlight',
							defaultValue: WPA.RecentResults.filterMonth
						}).combobox('setValue', WPA.RecentResults.filterMonth);

						jQuery("#filterYear").combobox({
							select: function(event, ui) {
								WPA.RecentResults.filterYear = ui.item.value;
								WPA.RecentResults.displayResults();
							},
							selectClass: 'filter-highlight',
							defaultValue: WPA.RecentResults.filterYear
						}).combobox('setValue', WPA.RecentResults.filterYear);

						// common setup function
						WPA.setupCommon();

						// load results
						WPA.RecentResults.displayResults();

						// add/edit results dialog
						WPA.setupEditResultDialog(function() {
							WPA.loadEventResults();
							WPA.RecentResults.displayResults();
						});
					});
				});
			</script>

			<?php $this->display_page_loading(); ?>

			<div class="wpa hide">

				<div style="height:35px" class="wpa-menu wpa-border-bottom">

					<!-- FILTERS -->
					<div class="wpa-filters ui-corner-all">
						<select id="filterMonth">
							<option value="all"><?php echo $this->get_property('filter_month_all'); ?></option>
							<?php
							for( $month = 1; $month <= 12; $month++ ) {
							?>
								<option value="<?php echo $month; ?>"><?php echo $this->get_property('month_' . $month); ?></option>
							<?php
							}
							?>
						</select>

						<select id="filterYear">
							<option value="<?php echo $this_year; ?>"><?php echo $this->get_property('filter_period_option_this_year'); ?></option>
							<?php

							for( $year = $this_year-1; $year >= $this_year-10; $year-- ) {
							?>
								<option value="<?php echo $year; ?>"><?php echo $year; ?></option>
							<?php
							}
							?>
						</select>
					</div>

					<!-- EVENT / ATHLETE SEARCH -->
					<div class="wpa-ac-search wpa-records-search">
						<span class="wpa-search-image"></span>
						<input type="text" class="ui-corner-all ui-widget ui-state-default wpa-search wpa-search-disabled" default-text="<?php echo $this->get_property('wpa_search_text'); ?>" value="" id="wpa-search" class="text ui-widget-content ui-corner-all" />
					</div>

					<br style="clear:both"/>
				</div>
				
				<div class="feed-content-empty" id="recent-results-empty">
					<p><?= $this->get_property('recent_results_empty_text'); ?></p>
					<a href="<?= get_permalink(get_option('wp-athletics_my_results_page_id')); ?>">
					<?= $this->get_property('recent_results_empty_add_result'); ?>
					</a>
				</div>

				<!-- RECENT RESULTS -->
				<div id="recent-results">

				</div>

				<!-- ADD/EDIT RESULTS DIALOG -->
				<?php $this->create_edit_result_dialog(); ?>

				<!-- COMMON DIALOGS -->
				<?php $this->create_common_dialogs(); ?>
			</div>
		<?php
		}
	}
}
?>