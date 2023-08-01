<?php echo form_open(get_uri("materialrequests/mr_create_save"), array("id" => "pr-form", "class" => "general-form", "role" => "form")); ?>

<?php
// Setting disabled
$disabled = '';
if (isset($model_info->status_id) && !empty($model_info->status_id)) {
	if ($model_info->status_id != '1') {
		$disabled = 'disabled';
	}
}

// Setting readonly
$readonly = 'false';
if (isset($model_info->id) && !empty($model_info->id)) {
	$readonly = 'true';
}
?>

<style type="text/css">
.no-border {
	border: none;
}

.no-outline {
	outline: none;
}

.pointer-none {
    pointer-events: none;
}
</style>

<div class="modal-body clearfix">
	<input type="hidden" id="id" name="id" value="<?php echo @$model_info->id; ?>" />
	<input type="hidden" id="status_id" name="status_id" value="<?php echo @$model_info->status_id; ?>" />

	<div class="form-group" id="form-doc-no">
		<label for="doc_no" class="col-md-3"><?php echo lang("document_number"); ?></label>
		<div class="col-md-9">
			<?php
			echo form_input(array(
				"id" => "doc_no",
				"name" => "doc_no",
				"value" => @$model_info->doc_no,
				"class" => "form-control bg-white no-outline",
				"readonly" => true
			));
			?>
		</div>
	</div>

	<div class="form-group">
		<label for="mr_type" class="col-md-3"><?php echo lang('material_request_type'); ?></label>
		<div class="col-md-9">
			<select name="mr_type" id="mr_type" class="form-control select2 <?php if ($readonly == 'true') echo "pointer-none"; ?>" <?php echo $disabled; ?> required>
				<?php if (isset($model_info->mr_type) && !empty($model_info->mr_type)): ?>
					<option value="1" <?php if ($model_info->mr_type == 1) echo "selected"; ?>><?php echo lang('stock_materials'); ?></option>
					<!-- <option value="2" <?php // if ($model_info->mr_type == 2) echo "selected"; ?>><?php // echo lang('stock_items'); ?></option> -->
				<?php else: ?>
					<option value="1"><?php echo lang('stock_materials'); ?></option>
					<!-- <option value="2"><?php // echo lang('stock_items'); ?></option> -->
				<?php endif; ?>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label for="project_id" class="col-md-3"><?php echo lang("project_refer"); ?></label>
		<div class="col-md-9">
			<?php if ($disabled == 'disabled'): ?>
				<input type="text" class="form-control" value="<?php echo @$model_info->project_name ? @$model_info->project_name : '-'; ?>" <?php echo $disabled; ?>>
			<?php else: ?>
				<?php $dropdown_projects = $this->Projects_model->getOpenProjectList(); ?>
				<select name="project_id" id="project_id" class="form-control select2 <?php if ($readonly == 'true') echo "pointer-none"; ?>" data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" required>
					<option value="">-</option>
					<?php foreach ($dropdown_projects as $item): ?>
						<option value="<?php echo $item->id; ?>" <?php if (@$model_info->project_id == $item->id) echo "selected"; ?>><?php echo $item->title; ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="catid" class="col-md-3"><?php echo lang("category_name"); ?></label>
		<div class="col-md-9">
			<?php
			$category = [];
			foreach ($categories as $cate) {
				$category[$cate->id] = $cate->title;
			}

			echo form_dropdown(
				'catid',
				$category,
				@$model_info->catid,
				'class="select2" ' . $disabled
			);
			?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="mr_date" class=" col-md-3"><?php echo lang("material_request_date"); ?></label>
		<div class="col-md-9">
			<input type="text" id="mr_date" name="mr_date" class="form-control" value="<?php echo @$model_info->mr_date ? $model_info->mr_date : date('Y-m-d'); ?>" placeholder="<?php echo lang('material_request_date'); ?>" autocomplete="off" data-rule-required="true" data-msg-required="<?php echo lang('field_required'); ?>" <?php echo $disabled; ?>>
		</div>
	</div>
	
	<div class="form-group">
		<label for="requester_id" class=" col-md-3"><?php echo lang("material_request_person"); ?></label>
		<div class="col-md-9">
			<?php echo form_dropdown(
				"requester_id",
				@$buyers_dropdown,
				@array($model_info->requester_id ? $model_info->requester_id : $this->login_user->id),
				'class="select2 validate-hidden" id="requester_id" data-rule-required="true" data-msg-required="' . lang('field_required') . '" ' . $disabled,
			); ?>
		</div>
	</div>
	
	<div class="form-group">
		<label for="note" class=" col-md-3"><?php echo lang("pr_note"); ?></label>
		<div class=" col-md-9">
			<?php
			echo form_textarea(array(
				'name' => 'note',
				'id' => 'note',
				'value' => @$model_info->note,
				'class' => 'form-control',
				'placeholder' => lang('remark'),
				'data-rich-text-editor' => true
			));
			?>
		</div>
	</div>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">
		<span class="fa fa-close"></span> 
		<?php echo lang('close'); ?>
	</button>
	<?php if (empty($model_info->status_id) || $model_info->status_id == '1'): ?>
		<button type="submit" class="btn btn-primary">
			<span class="fa fa-check-circle"></span> 
			<?php echo lang('save'); ?>
		</button>
	<?php endif; ?>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">

$(document).ready(function() {
	$("#pr-form").appForm({
		onSuccess: function(result) {
			// console.log(result);

			if (result.record == 'created') {
				let url = '<?php echo_uri('materialrequests/view/'); ?>' + result.data_id;
				window.open(url, '_self');
			} else {
				window.location.reload();
			}
		}
	});
	
	$("#pr-form .select2").select2();
	setDatePicker("#mr_date");

	<?php if (empty(@$model_info->id)): ?>
		$('#form-doc-no').addClass('hide');
	<?php endif; ?>
});
</script>
