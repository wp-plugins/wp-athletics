<?php
if ( $this->has_permission_to_manage() ) {
	global $current_user;
	$nonce = wp_create_nonce( $this->nonce );
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {

			// set up ajax
			WPA.Ajax.setup('<?php echo admin_url( 'admin-ajax.php' ); ?>', '<?php echo $nonce; ?>', '<?php echo WPA_PLUGIN_URL; ?>', '<?php echo $current_user->ID; ?>',  function() {

				// set setting fields
				jQuery('#setting-language').val('<?php echo strtolower(get_option( 'wp-athletics_language', 'en') ); ?>');
				//jQuery('#setting-default-unit').val('<?php echo strtolower(get_option( 'wp-athletics_default-unit', 'm') ); ?>');
				jQuery('#setting-theme').val('<?php echo strtolower(get_option( 'wp-athletics_theme', 'default') ); ?>');
				//jQuery('#setting-records-mode').val('<?php echo strtolower(get_option( 'wp-athletics_records_mode', 'combined') ); ?>');
				//jQuery('#setting-disable-sql-view').attr('checked', '<?php echo strtolower(get_option( 'wp-athletics-disable-sql-view', 'no') ); ?>' == 'yes');

				// save settings button
				jQuery('#wpa-save-settings button').button({
					icons: {
				        primary: "ui-icon-check"
					}
				}).click(function() {
					jQuery('#wpa-save-settings button').button('option', 'label', '<?php echo $this->get_property('admin_settings_label_button_saving') ?>').button('option', 'disabled', true);
					WPA.Admin.saveSettings(function(result) {
						if(result.success) {
							jQuery('#wpa-save-settings button').button('option', 'label', '<?php echo $this->get_property('admin_settings_label_button_saved') ?>');
							setTimeout("jQuery('#wpa-save-settings button').button('option', 'label', '<?php echo $this->get_property('admin_settings_label_button_save') ?>').button('option', 'disabled', false);", 1000);
						}
					});
				});

				// tooltips
				jQuery(document).tooltip({
					track: true
				});

				jQuery('#wpa-admin-settings-tab').tabs();

			}, true);


		});
	</script>
	<div class="wpa-admin"></div>
	<div class="wpa-admin-intro">
		<h2>WP Football Golf Settings</h2>
		<div id="wpa-admin-settings-container">
			<div id="wpa-admin-settings-tab">
				<ul>
					<li><a href="#wpa-admin-settings-general"><?php echo $this->get_property('admin_settings_tab_label_general') ?></a></li>
				</ul>
				
				<!-- GENERAL SETTINGS TAB -->
				<div id="wpa-admin-settings-general">
					<div class="wpa-admin-setting">
						<label><?php echo $this->get_property('admin_settings_label_language') ?>:</label>
						<select id="setting-language">
							<option value="en">English</option>
							<option value="it">Italian</option>
							<option value="pt">Portuguese</option>
							<!--
							<option value="fr">French</option>
							<option value="es">Spanish</option>
							<option value="de">German</option>
							<option value="sw">Swedish</option>
							-->
						</select>
					</div>
					<div class="wpa-admin-setting">
						<label><?php echo $this->get_property('admin_settings_label_theme') ?>:</label>
						<select id="setting-theme">
							<option value="default">Gray</option>
							<option value="red">Red</option>
							<option value="blue">Blue</option>
							<option value="yellow">Yellow</option>
						</select>
					</div>
					<br style="clear:both"/>
				</div>

			</div>
			<div id="wpa-save-settings">
				<button><?php echo $this->get_property('admin_settings_label_button_save') ?></button>
			</div>
			<br style="clear:both;"/>
		</div>
	</div>
<?php
}
?>