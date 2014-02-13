<?php
if ( $this->has_permission_to_manage() ) {
	global $current_user;
	$nonce = wp_create_nonce( $this->nonce );

?>
	<script type="text/javascript">

	WPA.Admin.doAthleteNameFilter = function() {
		var defaultText = jQuery('#filterAthleteName').attr('default-text');
		var val = jQuery('#filterAthleteName').val();
		if(val != '' && defaultText != val) {
			WPA.filterAthleteName = val;
			WPA.Admin.athletesTable.fnFilter( val, 2 );
		}
		else {
			WPA.filterAthleteName = null;
			WPA.Admin.athletesTable.fnFilter( '', 2 );
		}
	}

	WPA.Admin.reloadAthletes = function() {
		WPA.Admin.athletesTable.fnDraw();
	}

	WPA.Admin.deleteAthlete = function(id) {
		WPA.Admin.deleteAthlete(id);
	}

	WPA.Admin.createAthletesTable = function() {
		WPA.Admin.athletesTable = jQuery('#athletes-table').dataTable(WPA.createTableConfig({
			"bServerSide": true,
			"sScrollX": "100%",
			"bScrollCollapse": true,
			"sDom": 'rt<"bottom fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix"lip>',
			"sAjaxSource": WPA.Ajax.url,
			"iDisplayLength": 25,
			"sServerMethod": "POST",
			"fnServerParams": function ( aoData ) {
			    aoData.push(
			    	{name : 'action', value : 'wpa_get_athletes' },
			    	{name : 'security', value : WPA.Ajax.nonce }
			    );
			},
			"aaSorting": [[ 2, "asc" ]],
			"aoColumns": [{
				"mData": "id",
				"mRender": WPA.renderAdminDeleteEditAthleteColumn,
				"bSortable": false
			},{
				"mData": "athlete_photo",
				"mRender": WPA.renderAthletePhoto,
				"sWidth": "60px",
				"sClass": "datatable-center"
			},{
				"mData": "athlete_name"
			},{
				"mData": "user_login"
			},{
				"mData": "user_email"
			},{
				"mData": "user_registered",
				"sWidth": "100px",
				"bSortable": false
			}]
		}));
	}

	jQuery(document).ready(function() {
		WPA.isAdminScreen = true;

		// set up ajax
		WPA.Ajax.setup('<?php echo admin_url( 'admin-ajax.php' ); ?>', '<?php echo $nonce; ?>', '<?php echo WPA_PLUGIN_URL; ?>', '<?php echo $current_user->ID; ?>',  function() {

			// create the table
			WPA.Admin.createAthletesTable();

			// common setup function
			WPA.setupCommon();

			// setup filters
			WPA.setupFilters(null, WPA.Admin.athleteTable, null, null, {}, WPA.Admin.doAthleteNameFilter);

			jQuery('#create-athlete-button button').button({
				icons: {
		        	primary: 'ui-icon-circle-plus'
		        }
			}).click(function(e) {
				e.preventDefault();
				WPA.showCreateAthleteDialog();
			});
		});
	});

	</script>

	<div>
		<div class="wpa-admin-title">
			<h2><?php echo $this->get_property('admin_manage_athletes_title'); ?></h2>
		</div>
		<br style="clear:both;"/>
	</div>

	<div class="wpa">

		<div class="wpa-menu">

			<!-- FILTERS -->
			<div class="wpa-filters ui-corner-all">

				<div class="filter-ignore-for-pb">
					<input id="filterAthleteName" highlight-class="filter-highlight" default-text="<?php echo $this->get_property('filter_athlete_name_input_text'); ?>" class="ui-corner-all ui-widget ui-widget-content ui-state-default wpa-search wpa-search-disabled"></input>
					<span id="filterAthleteNameCancel" style="display:none;" title="<?php echo $this->get_property('filter_athlete_name_cancel_text'); ?>" class="filter-name-remove"></span>
				</div>
			</div>

			<div id="create-athlete-button">
				<button><?php echo $this->get_property('admin_athlete_create_button'); ?></button>
			</div>

			<br style="clear:both"/>
		</div>

		<!-- DATA TABLE -->
		<div id="wpa-admin-manage-athletes">
			<table cellpadding="0" cellspacing="0" border="0" class="display ui-state-default" style="border-bottom:none" id="athletes-table" width="100%">
			  <thead>
				<tr>
					<th></th>
					<th></th>
					<th><?php echo $this->get_property('column_athlete_name') ?></th>
					<th><?php echo $this->get_property('column_athlete_username') ?></th>
					<th><?php echo $this->get_property('column_athlete_email') ?></th>
					<th><?php echo $this->get_property('column_athlete_registered') ?></th>
				</tr>
			  </thead>
			</table>
		</div>

		<!-- DELETE EVENTS CONFIRM DIALOG -->
		<div id="delete-athlete-confirm-dialog" style="display:none">
			<p>
				<?php echo $this->get_property('delete_athlete_text') ?>
			</p>
		</div>

		<!-- COMMON DIALOGS -->
		<?php $this->create_common_dialogs(); ?>

	</div>

<?php
}
?>