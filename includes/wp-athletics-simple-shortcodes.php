<?php

/**
 * Class for displaying a simple shortcode output
 */

if(!class_exists('WP_Athletics_Simple_Shortcodes')) {

	class WP_Athletics_Simple_Shortcodes extends WPA_Base {

		/**
		 * default constructor
		 */
		public function __construct( $db ) {
			parent::__construct( $db );
		}
		
		public function validate_attributes( $atts, $required = 'id' ) {
			if(!isset( $atts ) || !isset( $atts[$required] ) ) {
				echo "<div>" . ($this->get_property('shortcode_error_required') . ': ' . $required) . "</div>";
				return false;
			}
			return true;
		}
		
		public function enqueue_scripts_and_styles() {
			wp_enqueue_script( 'wpa-functions' );
			wp_enqueue_style( 'wpa_style' );
			wp_enqueue_style( 'wpa_simple_style' );
		}
		
		/**
		 * Outputs a user results table
		 */
		public function display_rankings( $atts ) {

			$age_cats = $this->wpa_db->get_age_categories();
			$terrains = $this->wpa_db->get_event_sub_types();

			if($this->validate_attributes( $atts, 'event' ) ) {
				$this->enqueue_scripts_and_styles();
				
				$limit = isset($atts['limit']) ? intval($atts['limit']) : null;
				
				$params = array(
					'eventCategoryId' => $atts['event'],
					'rankingDisplay' => 'best-athlete-result',
					'limit' => $limit,
					'eventDate' => isset($atts['date']) ? $atts['date'] : null,
					'ageCategory' => isset($atts['category']) ? $atts['category'] : null,
					'eventSubTypeId' => isset($atts['terrain']) ? $atts['terrain'] : null,
					'gender' => isset($atts['gender']) ? $atts['gender'] : null,
					'skipClubRank' => 'y'
				);

				$results = $this->wpa_db->get_personal_bests($params);
				$category = null;
				
				$title = "";
				if(isset($atts['title'])) {
					$title = $atts['title'];
				}
				else {
					if(!empty($results)) {
						$category = $results[0]->category;
						$title = $category . " " . $this->get_property('column_rankings');
					}
				}
				?>
				
				<script>
					jQuery(document).ready(function() {
						WPA.processSimpleShortcodeTable();
					});
				</script>
				
				<?php
				if(!empty($results) && !isset($atts['notitle'])) {
					echo "<p class='wpa-simple-table-title'>$title</p>";
				}
				?>
				<table id="wpa-user-results" class="wpa-simple-table">
					<thead>
						<tr>
							<th></th>
							<th><?= $this->get_property('column_athlete_name') ?></th>
							<th><?= $this->get_property('add_result_event_date') ?></th>
							<th><?= $this->get_property('column_event_name') ?></th>
							<th><?= $this->get_property('column_event_type') ?></th>
							<th><?= $this->get_property('column_age_category') ?></th>
							<th><?= $this->get_property('column_age_grade') ?></th>
							<th><?= $this->get_property('column_pace_miles') ?></th>
							<th><?= $this->get_property('column_time') ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php 
						if(!empty($results)) {
							foreach($results as $result ) {	
					?>
								<tr>
									<td class="center rank"><?= $result->rank ?></td>
									<td><?= $result->athlete_name ?></td>
									<td><?= $result->event_date ?></td>
									<td><?= $result->event_name ?></td>
									<td class="center"><?= isset($terrains[$result->event_sub_type_id]) ? $terrains[$result->event_sub_type_id] : $result->event_sub_type_id ?></td>
									<td class="center">
										<?= $this->get_property('gender_' . $result->gender) ?>&nbsp;
										<?= isset($age_cats[$result->age_category]) ? $age_cats[$result->age_category]['name'] : $result->age_category  ?>
									</td>
									<td class="center"><?= $result->age_grade > 0 ? ($result->age_grade . '%') : '-' ?></td>
									<td class="center wpa-pace" millis="<?= $result->time ?>" meters="<?= $result->distance_meters ?>"></td>
									<td class="center wpa-time" millis="<?= $result->time ?>" time-format="<?= $result->time_format ?>"></td>
									<td class="center wpa-activity-link" url="<?= $result->garmin_id ?>"></td>
								</tr>
					<?php 
							}
						}
						else {
							echo "<tr><td class='empty' colspan='10'>" . $this->get_property('shortcode_table_no_results') . "</td></tr>";
						}
					?>
					</tbody>
				</table>
		<?php
			}
		}

		/**
		 * Outputs an event results table
		 */
		public function display_event_results( $atts ) {
			
			$age_cats = $this->wpa_db->get_age_categories();
			$terrains = $this->wpa_db->get_event_sub_types();
			
			if($this->validate_attributes( $atts, 'event' ) ) {
				$this->enqueue_scripts_and_styles();
				$results = $this->wpa_db->get_event_results($atts['event'], false);
				
				wpa_log($atts);

?>
				<script>
					jQuery(document).ready(function() {
						WPA.processSimpleShortcodeTable();
					});
				</script>

				<?php 
					if(!empty($results) && !isset($atts['notitle'])) {
						$first = $results[0];
						if($first) {
							echo '<p><strong>' . $first->event_name . ' ' . $this->get_property('results_main_tab') .  '</strong> // ';
							echo '<strong>' . $first->event_date . '</strong> // ';
							echo '<strong>' . $first->distance . ' ' . $terrains[$first->sub_type_id] . '</strong></p>';
						}
					}
				?>
				
				<table id="wpa-event-results" class="wpa-simple-table">
					<thead>
						<tr>
							<th></th>
							<th><?= $this->get_property('column_athlete_name') ?></th>
							<th><?= $this->get_property('column_category') ?></th>
							<th><?= $this->get_property('column_time') ?></th>
							<th><?= $this->get_property('column_age_grade') ?></th>
							<th><?= $this->get_property('column_pace_miles') ?></th>
							<th><?= $this->get_property('column_position') ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
<?php 
					if(!empty($results)) {
						foreach($results as $result) {
?>
							<tr>
								<td class="center rank"><?= $result->rank ?></td>
								<td><?= $result->athlete_name ?></td>
								<td class="center">
									<?= $this->get_property('gender_' . $result->gender) ?>&nbsp;
									<?= isset($age_cats[$result->age_category]) ? $age_cats[$result->age_category]['name'] : $result->age_category  ?>
								</td>
								<td class="center wpa-time" millis="<?= $result->time ?>" time-format="<?= $result->time_format ?>"></td>
								<td class="center"><?= $result->age_grade > 0 ? ($result->age_grade . '%') : '-' ?></td>
								<td class="center wpa-pace" millis="<?= $result->time ?>" meters="<?= $result->distance_meters ?>"></td>
								<td class="center"><?= $result->position ? $result->position : '-' ?></td>
								<td class="center wpa-activity-link" url="<?= $result->garmin_id ?>"></td>
							</tr>
<?php 
						}
					}
					else {
						echo "<tr><td class='empty' colspan='8'>" . $this->get_property('shortcode_table_no_results') . "</td></tr>";
					}
?>
					</tbody>
				</table>
<?php
				
			}
		}

		/**
		 * Outputs a user results table
		 */
		public function display_user_results( $atts ) {
			if($this->validate_attributes( $atts, 'user' ) ) {
				$this->enqueue_scripts_and_styles();
				$results = $this->wpa_db->get_all_results_for_user($atts['user']);
				
				$split_by = isset($atts['split']) ? strtolower($atts['split']) : false;

				$result_set = array();
				if($split_by) {
					foreach($results as $result) {
						
						$key = $result->$split_by;
						
						if(!array_key_exists($key, $result_set)) {
							$result_set[$key] = array();
						}
						$result_set[$key][] = $result;
					}
				}
				else {
					$result_set['default'] = $results;
				}
				
?>

			<script>
				jQuery(document).ready(function() {
					WPA.processSimpleShortcodeTable();
				});
			</script>
			
			<?php 
			
				if(empty($result_set)) {
					echo $this->get_property('shortcode_table_no_results');
				}
			
				foreach($result_set as $split_key => $results) {
					if($split_by) {
						echo "<p class='wpa-split-by'>$split_key</p>";
					}
			?>
			<table id="wpa-user-results" class="wpa-simple-table">
				<thead>
					<tr>
						<th><?= $this->get_property('add_result_event_date') ?></th>
						<th><?= $this->get_property('column_event_name') ?></th>
						<th><?= $this->get_property('column_category') ?></th>
						<th><?= $this->get_property('column_age_grade') ?></th>
						<th><?= $this->get_property('column_position') ?></th>
						<th><?= $this->get_property('column_pace_miles') ?></th>
						<th><?= $this->get_property('column_time') ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php 
					if(!empty($results)) {
						foreach($results as $result ) {
				?>
						<tr>
							<td><?= $result->event_date ?></td>
							<td><?= $result->event_name ?></td>
							<td><?= $result->distance ?></td>
							<td class="center"><?= $result->age_grade > 0 ? ($result->age_grade . '%') : '-' ?></td>
							<td class="center"><?= $result->position ? $result->position : '-' ?></td>
							<td class="center wpa-pace" millis="<?= $result->time ?>" meters="<?= $result->distance_meters ?>"></td>
							<td class="center wpa-time" millis="<?= $result->time ?>" time-format="<?= $result->time_format ?>"></td>
							<td class="center wpa-activity-link" url="<?= $result->url ?>"></td>
						</tr>
				<?php 
						}
					}
					else {
						echo "<tr><td class='empty' colspan='8'>" . $this->get_property('shortcode_table_no_results') . "</td></tr>";
					}
				?>
				</tbody>
			</table>
<?php
				}
			}
		}
	}
}
?>