<?php

/**
 * WPA recent results widget
 */
class WPA_Recent_Results extends WP_Widget {

	public $nonce = '2345676543';

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'wpa_recent_results_widget', // Base ID
			__('Recent Athletic Results', 'recent_results'), // Name
			array( 'description' => __( 'Displays recent athletics results', 'recent_results' ), ) // Args
		);
	}

	/**
	 * Writes out the last X recent results
	 */
	public function display_recent_results( $num = 5 ) {
	?>
		<table class="wpa-widget" style="display:none" id="wpa-widget-recent-results-table">
			<tbody>
	<?php
			global $wpa;
			$results = $wpa->wpa_common->wpa_db->get_recent_results( $num );

			if(!empty($results)) {
				foreach ( $results as $result ) {
					echo '<tr user-id="' . $result->user_id . '" event-id="' . $result->event_id . '">
							<td class="wpa-widget-date">' . $result->display_date . '</td>
							<td class="wpa-widget-content">' . $result->content . '</td>
						</tr>';
				}
			}
			else {
				echo '<tr><td style="text-align:center; color:#aaa" colspan="2" class="widget-no-results">' . $wpa->wpa_common->get_property('widget_recent_results_no_results') . '</tr>';
			}
	?>
			</tbody>
		</table>
	<?php
	}


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $wpa;
		global $current_user;
		$wpa_common = $wpa->wpa_common;

		// required scripts / styles
		wp_enqueue_script( 'wpa-functions' );
		wp_enqueue_style( 'wpa_style' );

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( isset( $instance[ 'wpa_number_results' ] ) ) {
			$num = $instance[ 'wpa_number_results' ];
		}
		else {
			$num = 5;
		}

		// write the results
		$this->display_recent_results( $num );

		?>

		<div id="wpa-widget-recent-results-links">
			<span onclick="window.location='<?php echo get_permalink(get_option('wp-athletics_recent_results_page_id')); ?>'">
				<?php echo $wpa->wpa_common->get_property('results_widget_recent_results_link')?>
			</span>
		</div>

		<script type='text/javascript'>
			var wpaEnabled = <?= WPA_ENABLE_ON_NON_WPA_PAGES ? 'true' : 'false' ?>;

			jQuery(document).ready(function() {
				WPA.processLogContent('wpa-widget-recent-results-table', !wpaEnabled, false);
				jQuery('#wpa-widget-recent-results-table').show();
			});
		 
		</script>
		<?php

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Recent Athletic Results', 'text_domain' );
		}

		if ( isset( $instance[ 'wpa_number_results' ] ) ) {
			$num = $instance[ 'wpa_number_results' ];
		}
		else {
			$num = __( '5', 'text_domain' );
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'wpa_number_results' ); ?>"><?php _e( 'Number of results:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'wpa_number_results' ); ?>" name="<?php echo $this->get_field_name( 'wpa_number_results' ); ?>" type="text" value="<?php echo esc_attr( $num ); ?>" />
		</p>

		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['wpa_number_results'] = ( ! empty( $new_instance['wpa_number_results'] ) ) ? strip_tags( $new_instance['wpa_number_results'] ) : '';

		return $instance;
	}

}
?>