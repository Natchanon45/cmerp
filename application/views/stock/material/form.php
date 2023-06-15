<style type="text/css">
.string-upper {
    text-transform: uppercase;
}
</style>

<input type="hidden" name="id" value="<?php echo isset($model_info->id) ? $model_info->id : ''; ?>" />
<input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />

<?php
$readonly = false;
if (empty($model_info->id)) {
	$readonly = isset($can_create) && !$can_create;
} else {
	$readonly = isset($can_update) && !$can_update;
}
?>

<div class="form-group">
	<label for="name" class="<?php echo $label_column; ?>">
		<?php echo lang('stock_material_name'); ?>
	</label>
	<div class="<?php echo $field_column; ?>">
		<?php
		echo form_input(
			array(
				"id" => "name",
				"name" => "name",
				"value" => $model_info->name,
				"class" => "form-control",
				"placeholder" => lang('stock_material_name'),
				"autofocus" => true,
				"data-rule-required" => true,
				"data-msg-required" => lang("field_required"),
				"readonly" => $readonly
			)
		);
		?>
	</div>
</div>

<?php if (isset($can_read_production_name) && $can_read_production_name) { ?>
	<div class="form-group">
		<label for="production_name" class="<?php echo $label_column; ?>">
			<?php echo lang('stock_material_production_name'); ?>
		</label>
		<div class="<?php echo $field_column; ?>">
			<?php
			echo form_input(
				array(
					"id" => "production_name",
					"name" => "production_name",
					"value" => $model_info->production_name,
					"class" => "form-control",
					"placeholder" => lang('stock_material_production_name'),
					"readonly" => $readonly
				)
			);
			?>
		</div>
	</div>
<?php } ?>

<div class="form-group">
	<label for="category_id" class="<?php echo $label_column; ?>">
		<?php echo lang('stock_material_category'); ?>
	</label>
	<div class="<?php echo $field_column; ?>">
		<?php
		echo form_input(
			array(
				"id" => "category_id",
				"name" => "category_id",
				"value" => $model_info->category_id ? $model_info->category_id : null,
				"class" => "form-control",
				"placeholder" => lang('stock_material_category'),
				"readonly" => $readonly
			)
		);
		?>
	</div>
</div>

<div class="form-group">
	<label for="account_id" class="<?php echo $label_column; ?>">
		<?php echo lang('account_category'); ?>
	</label>
	<div class="<?php echo $field_column; ?>">
		<?php
		echo form_input(
			array(
				"id" => "account_id",
				"name" => "account_id",
				"value" => $model_info->account_id ? $model_info->account_id : null,
				"class" => "form-control",
				"placeholder" => lang('account_category'),
				"readonly" => $readonly
			)
		);
		?>
	</div>
</div>

<div class="form-group">
	<label for="description" class="<?php echo $label_column; ?>">
		<?php echo lang('stock_material_description'); ?>
	</label>
	<div class="<?php echo $field_column; ?>">
		<?php
		echo form_textarea(
			array(
				"id" => "description",
				"name" => "description",
				"value" => $model_info->description ? $model_info->description : '',
				"class" => "form-control",
				"placeholder" => lang('stock_material_description'),
				"readonly" => $readonly
			)
		);
		?>
	</div>
</div>

<div class="form-group">
	<label for="unit" class="<?php echo $label_column; ?>">
		<?php echo lang('stock_material_unit'); ?>
	</label>
	<div class="<?php echo $field_column; ?>">
		<?php
		$set_unit = array(
			"id" => "unit",
			"name" => "unit",
			"value" => $model_info->unit,
			"class" => "form-control string-upper",
			"placeholder" => lang('stock_material_unit'),
			"data-rule-required" => true,
			"data-msg-required" => lang("field_required"),
			"readonly" => $readonly
		);

		if (isset($model_info->can_delete) && !$model_info->can_delete) {
			$set_unit["readonly"] = true;
		}

		echo form_input($set_unit);
		?>
	</div>
</div>

<div class="form-group">
	<label for="barcode" class="<?php echo $label_column; ?>">
		<?php echo lang('stock_material_barcode'); ?>
	</label>
	<div class="<?php echo $field_column; ?>">
		<?php
		echo form_input(
			array(
				"id" => "barcode",
				"name" => "barcode",
				"value" => @$model_info->barcode,
				"class" => "form-control",
				"placeholder" => lang('stock_material_barcode'),
				"readonly" => $readonly
			)
		);
		?>
	</div>
</div>

<div class="form-group">
	<label for="noti_threshold" class="<?php echo $label_column; ?>">
		<?php echo lang('stock_material_noti_threshold'); ?>
	</label>
	<div class="<?php echo $field_column; ?>">
		<input type="number" name="noti_threshold" class="form-control" min="0" step="0.0001" required
			name="noti_threshold" value="<?= $model_info->noti_threshold ?>"
			placeholder="<?php echo lang('stock_material_noti_threshold'); ?>" data-rule-required="true"
			data-msg-required="<?= lang("field_required") ?>" <?php if ($readonly)
				  echo 'readonly'; ?> />
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();
		$('#category_id').select2({ data: <?php echo json_encode($category_dropdown); ?> });
		$('#account_id').select2({ data: <?php echo json_encode($account_category); ?> });
	});
</script>