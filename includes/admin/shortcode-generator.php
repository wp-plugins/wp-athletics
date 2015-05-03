<?php
if ( $this->has_permission_to_manage() ) {
	global $current_user;
	$nonce = wp_create_nonce( $this->nonce );
	
	$today = getdate();
	$this_year = $today['year'];
	$this_month = $today['mon'];
?>

	<script type="text/javascript">

	WPA.Admin.shortcodeAttributes = {};

	WPA.Admin.generateShortcode = function() {
		var text = '[' + WPA.Admin.shortcode;
		if(WPA.Admin.shortcodeAttributes) {
			jQuery.each(WPA.Admin.shortcodeAttributes, function(att, value) {
				if(value && value != '') {
					text += (' ' + att + '="' + value + '"');
				}
			})
		}
		text += ']';
		jQuery('#shortcodeOutput textarea').val(text);
	}

	jQuery(document).ready(function() {
		WPA.isAdminScreen = true;

		// set up ajax
		WPA.Ajax.setup('<?php echo admin_url( 'admin-ajax.php' ); ?>', '<?php echo $nonce; ?>', '<?php echo WPA_PLUGIN_URL; ?>', '<?php echo $current_user->ID; ?>',  function() {

			// common setup function
			WPA.setupCommon();

			// setup common filters
			//WPA.setupFilters();

			// setup gender filter
			//jQuery("#filterGender").combobox({
			//	selectClass: 'filter-highlight',
			//	defaultValue: 'B'
			//});
			
			// add items to combos
			jQuery.each(WPA.globals.eventCategories, function(index, item) {
				jQuery("#filterEvent").append('<option value="' + item.id + '">' + item.name + '</option>');
			});
			jQuery('#filterEvent :nth-child(0)').prop('selected', true);
			
			jQuery.each(WPA.globals.eventTypes, function(type, name) {
				jQuery("#filterType").append('<option value="' + type + '">' + name + '</option>');
			});
			
			jQuery.each(WPA.globals.ageCategories, function(cat, item) {
				jQuery("#filterAge").append('<option value="' + cat + '">' + item.name + '</option>');
			});

			jQuery('#add-athlete-cancel').click(function() {
				WPA.Admin.addAthleteCancel();
			});
		    
			jQuery('#add-event-cancel').click(function() {
				jQuery(this).hide();
				jQuery('#eventName').removeAttr('readonly').val('').focus();
			});

			jQuery('#add-athlete-cancel').click(function() {
				jQuery(this).hide();
				jQuery('#athleteName').removeAttr('readonly').val('').focus();
			});

			// autocomplete for choosing event
			jQuery("#eventName").autocomplete({
				source: WPA.Ajax.url + '?action=wpa_event_autocomplete',
				minLength: 2,
				select: function( event, ui ) {
					WPA.Admin.shortcodeAttributes[WPA.Admin.autocompleteId] = ui.item.value;
					WPA.Admin.generateShortcode();
					setTimeout("jQuery('#eventName').val('')", 50);
				}
		    }).focus(function(){
		        this.select();
		    })

			// autocomplete for choosing athlete
			jQuery("#athleteName").autocomplete({
				source: WPA.Ajax.url + '?action=wpa_user_autocomplete',
				minLength: 2,
				select: function( event, ui ) {
					WPA.Admin.shortcodeAttributes['user'] = ui.item.value;
					WPA.Admin.generateShortcode();
					setTimeout("jQuery('#athleteName').val('')", 50);
				}
		    }).focus(function(){
		        this.select();
		    })

			jQuery('#generateShortcodeButton button').button().click(function() {
				var period = jQuery('#filterPeriod').val();
				var type = jQuery('#filterType').val();
				var age = jQuery('#filterAge').val();
				var event = jQuery('#filterEvent').val();
				var gender = jQuery('#filterGender').val();
			});

			jQuery('select[shortcode-attr]').change(function() {
				WPA.Admin.shortcodeAttributes[jQuery(this).attr('shortcode-attr')] = jQuery(this).val();
				WPA.Admin.generateShortcode();
			});

			jQuery('input[shortcode-attr]').keyup(function() {
				WPA.Admin.shortcodeAttributes[jQuery(this).attr('shortcode-attr')] = jQuery(this).val();
				WPA.Admin.generateShortcode();
			});

			jQuery('.shortcode-select').change(function() {
				// reset everything
				jQuery('#shortcodeOutput textarea').val('');
				jQuery('.shortcode-filter').hide();
				jQuery('#shortcodeStep2,#shortcodeStep3,#generateShortcodeButton').hide();
				jQuery('select[shortcode-attr]').each(function() {
					jQuery(this).children().first().prop('selected', true);
				});
				jQuery('input[shortcode-attr]').val('');
				WPA.Admin.shortcodeAttributes = {};

				// get selected options
				var selected = jQuery(this).children(":selected");
				var options = jQuery(selected).attr('options');
				WPA.Admin.shortcode = jQuery(selected).attr('shortcode');
				if(!WPA.Admin.shortcode) return;

				// display required filters
				if(options) {
					options = options.split(',');
					jQuery(options).each(function(i, optionId) {
						jQuery('#' + optionId).show();
					});
				}
				else {
					jQuery('#no-options-text').show();
				}

				// set autocomplete id
				if(jQuery(selected).attr('autocomplete-id')) {
					WPA.Admin.autocompleteId = jQuery(selected).attr('autocomplete-id');
				}
				
				// show step 2 and 3
				jQuery('#shortcodeStep2,#shortcodeStep3,#generateShortcodeButton').show();

				// auto generate the shortcode if specified
				if(jQuery(selected).attr('auto-generate')) {
					WPA.Admin.generateShortcode();
				}
			});

			jQuery('#shortcodeOutput textarea').click(function() {
				jQuery(this).select();
			});

			jQuery('.shortcode-filter').hide();
		});
	});

	</script>
	
	<style>

		#shortcodeOutput textarea {
			width: 500px;
			font-size: 20px;
			height: 100px;
			color: #1174C7;
		}
		
		.shortcode-step {
			margin-top: 50px;
			display: none;
		}

	</style>

	<div class="wpa">
	
		<p><?= $this->get_property('shortcode_help_text') ?></p>
		
		<div>
			<h2>1. <?= $this->get_property('shortcode_select_type_text') ?></h2>
			<select class="shortcode-select">
				<option><?= $this->get_property('shortcode_select_option_simple_default' )?></option>
				<option simple="1" shortcode="wpa-simple-rankings" options="filterEvent,filterPeriod,filterType,filterGender,filterAge,filterLimit,filterTitle">
					<?= $this->get_property('shortcode_rankings_option_text')?>
				</option>
				<option simple="1" shortcode="wpa-simple-results" options="filterUser,filterSplitBy">
					<?= $this->get_property('shortcode_user_results_option_text')?>
				</option>
				<option autocomplete-id="event" simple="1" shortcode="wpa-simple-results" options="filterEventName">
					<?= $this->get_property('shortcode_event_results_option_text')?>
				</option>
			</select>
	
			<select class="shortcode-select">
				<option><?= $this->get_property('shortcode_select_option_interactive_default' )?></option>
				<option autocomplete-id="id" simple="0" shortcode="wpa-event" options="filterEventName">
					<?= $this->get_property('shortcode_event_results_option_text')?>
				</option>
				<option auto-generate="1" simple="0" shortcode="wpa-records" options="filterGender">
					<?= $this->get_property('shortcode_records_option_text')?>
				</option>
				<option auto-generate="1" simple="0" shortcode="wpa-events">
					<?= $this->get_property('shortcode_events_option_text')?>
				</option>
				<option auto-generate="1" simple="0" shortcode="wpa-recent-results">
					<?= $this->get_property('shortcode_recent_results_option_text')?>
				</option>
			</select>
		</div>
		
		<div id="shortcodeStep2" class="shortcode-step">
			<h2>2. <?= $this->get_property('shortcode_select_options_text') ?></h2>

			<div class="shortcode-input shortcode-filter" id="filterLimit">
				<label><?= $this->get_property('shortcode_max_results') ?>:</label><input shortcode-attr="limit" type="text" size="5"/>
			</div>
			
			<!--
			<div class="shortcode-input shortcode-filter" id="filterTitle">
				<label>Title Text:</label><input shortcode-attr="title" type="text" size="50"/>
			</div>
			-->
				
			<div class="wpa-menu">
				<!-- FILTERS -->
				<div class="wpa-filters ui-corner-all">
				
					<div class="shortcode-filter" id="filterEventName">
						<input style="background:#fff" size="50" id="eventName" default-text="<?php echo $this->get_property('add_results_event_input_text'); ?>" class="ui-corner-all ui-widget ui-widget-content ui-state-default wpa-search wpa-search-disabled"></input>
						<span style="display:none;" id="add-event-cancel" class="input-cancel"></span>
					</div>
					
					<div class="shortcode-filter" id="filterUser">
						<input style="background:#fff" size="30" id="athleteName" default-text="<?php echo $this->get_property('add_results_athlete_input_text'); ?>" class="ui-corner-all ui-widget ui-widget-content ui-state-default wpa-search wpa-search-disabled"></input>
						<span style="display:none;" id="add-athlete-cancel" class="input-cancel"></span>
					</div>
				
					<select shortcode-attr="split" class="shortcode-filter" id="filterSplitBy">
						<option value="" selected="selected"><?php echo $this->get_property('shortcode_select_split'); ?></option>
						<option value="year"><?php echo $this->get_property('filter_split_by_year'); ?></option>
						<option value="distance"><?php echo $this->get_property('filter_split_by_distance'); ?></option>
					</select>
				
					<select shortcode-attr="event" class="shortcode-filter" id="filterEvent">
						<option value="" selected="selected"><?php echo $this->get_property('shortcode_select_event'); ?></option>
					</select>
				
					<select shortcode-attr="date" class="shortcode-filter" id="filterPeriod">
						<option value="" selected="selected"><?php echo $this->get_property('shortcode_select_period'); ?></option>
						<option value="this_year"><?php echo $this->get_property('filter_period_option_this_year'); ?></option>
						<?php for( $year = $this_year-1; $year >= $this_year-10; $year-- ) { ?>
							<option value="year:<?php echo $year; ?>"><?php echo $year; ?></option>
						<?php } ?>
					</select>
		
					<select shortcode-attr="terrain" class="shortcode-filter" id="filterType">
						<option value="" selected="selected"><?php echo $this->get_property('shortcode_select_terrain'); ?></option>
					</select>
		
					<select shortcode-attr="gender" class="shortcode-filter" id="filterGender">
						<option value=""><?php echo $this->get_property('shortcode_select_gender'); ?></option>
						<option value="M"><?php echo $this->get_property('gender_M'); ?></option>
						<option value="F"><?php echo $this->get_property('gender_F'); ?></option>
					</select>
		
					<select shortcode-attr="category" class="shortcode-filter" id="filterAge">
						<option value="" selected="selected"><?php echo $this->get_property('shortcode_select_category'); ?></option>
					</select>
					
					<span class="shortcode-filter" id="no-options-text">
						<?= $this->get_property('shortcode_no_options_text') ?>
					</span>
					
				</div>
			</div>

		</div>
		
		<!-- THE SHORTCODE OUTPUT -->
		<div class="shortcode-step" id="shortcodeStep3">
			<h2>3. <?= $this->get_property('shortcode_copy_text') ?></h2>

			<div id="shortcodeOutput">
				<textarea></textarea>
			</div>
		</div>
		
		<!-- COMMON DIALOGS -->
		<?php $this->create_common_dialogs(); ?>

	</div>

<?php
}
?>