<?php echo form_open(get_uri("materialrequests/save_header"), array("id" => "pr-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">

	<input type="hidden" id="id" name="id" value="<?php echo $model_info->id; ?>" />
	<input type="hidden" id="status_id" name="status_id" value="<?php echo $model_info->status_id; ?>" />

	 <?php if ( $is_clone ) :?>
		<!-- <input type="hidden" name="is_clone" value="1" /> -->
	<?php endif; ?>
	<?php // echo $this->dao->getBombInputs(); ?>

	<div class="form-group">
		<label for="doc_no" class="col-md-3"><?php echo lang("document_number"); ?></label>
		<div class="col-md-9">
			<?php echo form_input(array(
				"id" => "doc_no",
				"name" => "doc_no",
				"value" => $model_info->doc_no,
				"class" => "form-control",
				"readonly" => true
			)); ?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="catid" class="col-md-3"><?php echo lang("category_name"); ?></label>
		<div class="col-md-9">
			<?php
				$category = [];
				foreach ( $categories as $cate ) {
					$category[$cate->id] = $cate->title;
				}
				echo form_dropdown("catid", $category, array( $model_info->catid ), "class='select2'");
			?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="project_name" class="col-md-3"><?php echo lang("project_name"); ?></label>
		<div class="col-md-9">
			<?php
				echo form_input(array(
					"id" => "project_name",
					"name" => "project_name",
					"value" => $model_info->project_name,
					"class" => "form-control",
					"readonly" => true
				));
			?>
			<!-- <a id="pr_project_name_dropdwon_icon" tabindex="-1" href="javascript:void(0);" style="color: #b3b3b3; float: right; padding: 5px 7px; margin-top: -35px; font-size: 18px;"><span>×</span></a> -->
		</div>
	</div>
	
	<div class="form-group">
		<label for="payment_type" class="col-md-3"><?php echo lang("payment_type"); ?></label>
		<div class="col-md-9">
			<?php
				echo form_input(array(
					"id" => "payment_type",
					"name" => "payment",
					"value" => $model_info->payment,
					"class" => "form-control",
					"placeholder" => lang("payment_type_placeholdere")
				));
			?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="credit" class="col-md-3"><?php echo lang('credit'); ?></label>
		<div class="col-md-9">
			<?php
				echo form_input(array(
					"id" => "credit",
					"name" => "credit",
					"value" => $model_info->credit ? $model_info->credit : "0",
					"class" => "form-control",
					"type"=>"number"
				));
			?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="mr_date" class=" col-md-3"><?php echo lang("material_request_date"); ?></label>
		<div class="col-md-9">
			<?php
				echo form_input(array(
					"id" => "mr_date",
					"name" => "mr_date",
					"value" => $model_info->mr_date,
					"class" => "form-control",
					"placeholder" => lang("material_request_date"),
					"autocomplete" => "off",
					"data-rule-required" => true,
					"data-msg-required" => lang("field_required"),
				));
			?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="mr_buyer_id" class=" col-md-3"><?php echo lang("material_request_person"); ?></label>
		<div class="col-md-9">
			<?php echo form_dropdown(
				"mr_buyer_id",
				$buyers_dropdown,
				array($model_info->requester_id),
				"class='select2 validate-hidden' id='mr_buyer_id' data-rule-required='true', data-msg-required='" . lang('field_required') . "'"
			); ?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="tax_id" class=" col-md-3"><?php echo lang("tax"); ?></label>
		<div class="col-md-9">
			<?php
				echo form_dropdown("tax_id", $taxes_dropdown, array( $model_info->tax_id ), "class='select2 tax-select2'");
			?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="tax_id" class=" col-md-3"><?php echo lang('second_tax'); ?></label>
		<div class="col-md-9">
			<?php
				echo form_dropdown("tax_id2", $taxes_dropdown, array( $model_info->tax_id2 ), "class='select2 tax-select2'");
			?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="mr_note" class=" col-md-3"><?php echo lang("pr_note"); ?></label>
		<div class=" col-md-9">
			<?php
				echo form_textarea(array(
					"id" => "mr_note",
					"name" => "mr_note",
					"value" => $model_info->note ? $model_info->note : "",
					"class" => "form-control",
					"placeholder" => lang("pr_note"),
					"data-rich-text-editor" => true
				));
			?>
		</div>
	</div>
	
	<?php $this->load->view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?> 
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
	<button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">

$(document).ready(function() {
	$("#pr-form").appForm({
		onSuccess: function(result) {
			// console.log(result);

			setTimeout(function() {
				location.reload();
			}, 300);
		}
	});
	
	$("#pr-form .select2").select2();
	
	var isUpdate = "<?php echo $model_info->id; ?>";
        if (!isUpdate) {
            applySelect2OnProjectName();
        }

        $("#pr_project_name_dropdwon_icon").click(function() {
            applySelect2OnProjectName();
        });
        
        // setDatePicker("#expired");
        setDatePicker("#mr_date");
});

function applySelect2OnProjectName() {
	$("#project_name").select2({
		showSearchBox: true,
		ajax: {
			url: "<?php echo get_uri("projects/list_data_options"); ?>",
			dataType: 'json',
			quietMillis: 250,
			data: function (term, page) {
				return {
					q: term
					// Search term
				};
			},
			results: function(data, page) {
				return {results: data};
			}
		}
	}).change(function(e) { // alert('change mtr title'); // on select an option
		if (e.val === "#") {
			$("#project_name").select2("destroy").val("").focus();
		}
		
		// console.log(jQuery('#project_name').val());
		// jQuery('#project_name').val(e.added.text);
		// Get existing item info
		
		<?php 
			/* $.ajax({
				url: "<?php echo get_uri("materialrequests/get_material_info_suggestion"); ?>",
				data: {matrial_id: e.val},
				cache: false,
				type: 'POST',
				dataType: "json",
				success: function (response) {
				// Auto fill the description, unit type and rate fields.
					if (response && response.success) {
						$("#item_id").val(0);
						$("#code").val(response.item_info.name);
						$("#material_id").val(response.item_info.id);
						$("#pr_item_mtr_title").val(response.item_info.name+' : '+response.item_info.production_name);
						$("#pr_item_description").val(response.item_info.description);
						$("#pr_unit_type").val(response.item_info.unit);
						$("#pr_item_rate").val(0);
					}
				}
			}); */
		?>
	});
}

</script>