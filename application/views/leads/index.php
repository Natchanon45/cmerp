<div id="page-content" class="p20 clearfix">
	<ul class="nav nav-tabs bg-white title" role="tablist">
		<li class="title-tab">
			<h4 class="pl15 pt10 pr15">
				<?php echo lang("leads"); ?>
			</h4>
		</li>

		<?php $this->load->view("leads/tabs", array("active_tab" => "leads_list")); ?>

		<div class="tab-title clearfix no-border">
			<div class="title-button-group">
				<?php echo modal_anchor(get_uri("leads/import_leads_modal_form"), "<i class='fa fa-upload'></i> " . lang('import_leads'), array("class" => "btn btn-default", "title" => lang('import_leads'))); ?>
				<?php echo modal_anchor(get_uri("leads/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_lead'), array("class" => "btn btn-default", "title" => lang('add_lead'))); ?>
			</div>
		</div>
	</ul>

	<div class="panel panel-default">
		<div class="table-responsive">
			<table id="lead-table" class="display" cellspacing="0" width="100%">
			</table>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function () {
    $("#lead-table").appTable({
        source: '<?php echo current_url(); ?>',
        columns: [
            {title: "<?php echo lang("company_client_name") ?>"},
            {title: "<?php echo lang("address");?>"},
            {title: "<?php echo lang("phone_number");?>"},
            {title: "<?php echo lang("primary_contact") ?>"},
            {title: "<?php echo lang("owner") ?>"},
            {title: "<?php echo lang("status") ?>"}
            <?php if($custom_field_headers != null) echo $custom_field_headers; ?>,
            {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
        ],
        filterDropdown: [
            {name: "status", class: "w200", options: <?php $this->load->view("leads/lead_statuses"); ?>},
            {name: "source", class: "w200", options: <?php $this->load->view("leads/lead_sources"); ?>},
            <?php if(get_array_value($this->login_user->permissions, "lead") !== "own"){ ?>
                {name: "owner_id", class: "w200", options: <?php echo json_encode($owners_dropdown); ?>}
            <?php } ?>
        ],
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7], '<?php echo $custom_field_headers; ?>'),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7], '<?php echo $custom_field_headers; ?>')
    });
});
</script>

<?php $this->load->view("leads/update_lead_status_script"); ?>