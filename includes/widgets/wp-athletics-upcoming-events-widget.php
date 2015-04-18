<?php

/**
 * WPA upcoming events widget
 */
class WPA_Upcoming_Events extends WP_Widget {

	public $nonce = '574738362';

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'wpa_upcoming_events_widget', // Base ID
			__('Upcoming Athletic Events ', 'upcoming_events'), // Name
			array( 'description' => __( 'Displays upcoming athletic events', 'upcoming_events' ), ) // Args
		);
	}

	/**
	 * Writes out the next X upcoming events
	 */
	public function display_upcoming_events( $num = 5 ) {
	?>
		<table class="wpa-widget" style="display:none" id="wpa-widget-upcoming-events-table">
			<tbody>
	<?php
			global $wpa;
			$events = $wpa->wpa_common->wpa_db->get_upcoming_events( $num );

			if(!empty($events)) {
				foreach ( $events as $event ) {
					echo '<tr event-id="' . $event->event_id . '">
							<td class="wpa-widget-date">' . $event->display_date . '</td>
							<td class="wpa-widget-content"><event future="1">' . $event->name . ($event->location ? (', ' . $event->location ) : '' ) . '</event></td>
							<td class="wpa-widget-event-count"><event future="1">' . $event->count . ' ' . $wpa->wpa_common->get_property('event_runners_going') . '</event></td>
						</tr>';
				}
			}
			else {
				echo '<tr><td style="text-align:center; color:#aaa" colspan="2" class="widget-no-results">' . $wpa->wpa_common->get_property('widget_no_upcoming_events') . '</tr>';
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

		if ( isset( $instance[ 'wpa_number_events' ] ) ) {
			$num = $instance[ 'wpa_number_events' ];
		}
		else {
			$num = 5;
		}

		// write the results
		$this->display_upcoming_events( $num );

		?>

		<div id="wpa-widget-recent-results-links">
			<span onclick="window.location='<?php echo get_permalink(get_option('wp-athletics_events_page_id')); ?>'">
				<?php echo $wpa->wpa_common->get_property('events_widget_upcoming_events_link') ?>
			</span>
		</div>

		<script type='text/javascript'>
			jQuery(document).ready(function() {
				WPA.processLogContent('wpa-widget-upcoming-events-table', false, false);
				jQuery('#wpa-widget-upcoming-events-table').show();
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
			$title = __( 'Upcoming Athletic Events', 'text_domain' );
		}

		if ( isset( $instance[ 'wpa_number_events' ] ) ) {
			$num = $instance[ 'wpa_number_events' ];
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
			<label for="<?php echo $this->get_field_id( 'wpa_number_events' ); ?>"><?php _e( 'Number of events:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'wpa_number_events' ); ?>" name="<?php echo $this->get_field_name( 'wpa_number_events' ); ?>" type="text" value="<?php echo esc_attr( $num ); ?>" />
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
		$instance['wpa_number_events'] = ( ! empty( $new_instance['wpa_number_events'] ) ) ? strip_tags( $new_instance['wpa_number_events'] ) : '';

		return $instance;
	}

}
?>