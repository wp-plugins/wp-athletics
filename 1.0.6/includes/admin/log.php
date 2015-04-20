<?php
if ( $this->has_permission_to_manage() ) {
	global $current_user;
	$nonce = wp_create_nonce( $this->nonce );
?>

	<script type="text/javascript">

	WPA.Admin.getLogs = function() {

		WPA.toggleLoading(true);

		var params = {
			'action': 'wpa_get_logs',
			'period': WPA.filterPeriod,
			'type': WPA.filterLogType
		}

		jQuery.ajax({
			type: "post",
			url: WPA.Ajax.url,
			data: params,
			success: function(result) {
				WPA.toggleLoading(false);
				WPA.Admin.populateLog(result);
			}
		});
	}

	WPA.Admin.populateLog = function(results) {

		// clear the table first
		jQuery('#wpa-log table tbody tr').remove();

		if(results) {
			jQuery(results).each(function(index, result) {
				var html = '<tr user-id="' + result.user_id  +'" event-id="' + result.event_id + '" class="wpa-log-row ' + result.type + '"><td>' + result.date + '</td><td>' + WPA.Admin.translateLogType(result.type) + '</td><td>' + result.content + '</td></tr>';
				jQuery('#wpa-log table tbody').append(html);
			});
		}

		WPA.Admin.processLogContent();
	}

	WPA.Admin.translateLogType = function(type) {
		return WPA.getProperty('filter_log_type_option_' + type);
	}

	WPA.Admin.processLogContent = function(content) {
		// convert the time values
		var times = jQuery('#wpa-log table tbody tr time');

		jQuery(times).each(function(index, time) {
			var millis = parseFloat(time.innerHTML);
			jQuery(time).html(WPA.displayEventTime(millis, 'h:m:s'));
		});

		// convert user names to links
		var users = jQuery('#wpa-log table tbody tr user');
		jQuery(users).each(function(index, user) {
			jQuery(user).addClass('wpa-link').click(function() {
				var userId = jQuery(user).closest('tr').attr('user-id');
				WPA.displayUserProfileDialog(userId);
			});
		});

		// convert events to links
		var events = jQuery('#wpa-log table tbody tr event');
		jQuery(events).each(function(index, event) {
			jQuery(event).addClass('wpa-link').click(function() {
				var eventId = jQuery(event).closest('tr').attr('event-id');
				WPA.displayEventResultsDialog(eventId);
			});
		});
	}

	jQuery(document).ready(function() {
		WPA.isAdminScreen = true;

		// set up ajax
		WPA.Ajax.setup('<?php echo admin_url( 'admin-ajax.php' ); ?>', '<?php echo $nonce; ?>', '<?php echo WPA_PLUGIN_URL; ?>', '<?php echo $current_user->ID; ?>',  function() {

			// sorry WP footer but we don't want you getting in the way
			jQuery('#wpfooter').hide();

			// common setup function
			WPA.setupCommon();

			// setup combos
			jQuery("#filterLogType").combobox({
				select: function(event, ui) {
					WPA.filterLogType = ui.item.value;
					WPA.Admin.getLogs();
				},
				selectClass: 'filter-highlight'
			}).combobox('setValue', 'all');

			WPA.setupPeriodFilter('filterLogPeriod', 1980, undefined, -1, function() {
				WPA.Admin.getLogs();
			})

			jQuery("#filterLogPeriod").combobox('setValue', 'this_month');

			// load logs
			WPA.Admin.getLogs();

		});
	});

	</script>

	<div class="wpa">

		<div class="wpa-menu">

			<!-- FILTERS -->
			<div class="wpa-filters ui-corner-all">

				<select id="filterLogPeriod">
					<option value="all" selected="selected"><?php echo $this->get_property('filter_period_option_all'); ?></option>
					<option value="this_month"><?php echo $this->get_property('filter_period_option_this_month'); ?></option>
					<option value="this_year"><?php echo $this->get_property('filter_period_option_this_year'); ?></option>
				</select>

				<select id="filterLogType">
					<option value="all" selected="selected"><?php echo $this->get_property('filter_log_type_option_all'); ?></option>
					<option value="user_login" selected="selected"><?php echo $this->get_property('filter_log_type_option_user_login'); ?></option>
					<option value="new_result" selected="selected"><?php echo $this->get_property('filter_log_type_option_new_result'); ?></option>
					<option value="update_result" selected="selected"><?php echo $this->get_property('filter_log_type_option_update_result'); ?></option>
					<option value="new_event" selected="selected"><?php echo $this->get_property('filter_log_type_option_new_event'); ?></option>
					<option value="profile_update" selected="selected"><?php echo $this->get_property('filter_log_type_option_profile_update'); ?></option>
				</select>

				<span id="log-max-note">
					<?php echo $this->get_property('log_max_note'); ?>
				</span>
			</div>

			<div id="wpa-log">
				<table>
					<thead>
						<tr>
							<th style="width:150px"><?php echo $this->get_property('log_admin_column_date'); ?></th>
							<th style="width:150px"><?php echo $this->get_property('log_admin_column_type'); ?></th>
							<th><?php echo $this->get_property('log_admin_column_log'); ?></th>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>

			<br style="clear:both"/>
		</div>

		<!-- COMMON DIALOGS -->
		<?php $this->create_common_dialogs(); ?>

	</div>

<?php
}
?>