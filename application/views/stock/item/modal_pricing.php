<?php echo form_open(get_uri("stock/item_pricing_save"), array("id" => "item-pricing-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
	<input type="hidden" name="id" value="<?php echo (isset($model_info->id) && !empty($model_info->id)) ? $model_info->id : ''; ?>" />
	<input type="hidden" name="item_id" value="<?php echo (isset($item_id) && !empty($item_id)) ? $item_data->id : ''; ?>" />
	<input type="hidden" name="view" value="<?php echo (isset($view) && !empty($view)) ? $view : ''; ?>" />

	<?php // $readonly = isset($can_update) && !$can_update; ?>

	<div class="form-group">
		<label for="supplier_id" class="col-md-3">
			<?php echo lang('stock_supplier'); ?>
		</label>
		<div class="col-md-9" <?php if (!empty($model_info->id) && !empty($model_info->supplier_id)) echo 'style="pointer-events: none;"'; ?>>
			<?php
			echo form_input(
				array(
					"id" => "supplier_id",
					"name" => "supplier_id",
					"value" => (isset($model_info->supplier_id) && !empty($model_info->supplier_id)) ? $model_info->supplier_id : null,
					"class" => "form-control",
					"placeholder" => lang('stock_supplier'),
					"data-rule-required" => true,
					"data-msg-required" => lang("field_required"),
					"readonly" => false
				)
			);
			?>
		</div>
	</div>

	<div class="form-group">
		<label for="ratio" class="col-md-3">
			<?php echo lang('stock_material_quantity'); ?>
		</label>
		<div class="col-md-9">
			<div class="input-suffix">
				<input type="number" name="ratio" class="form-control" min="0" step="0.0001" required name="ratio" 
					value="<?php echo (isset($model_info->ratio) && !empty($model_info->ratio)) ? $model_info->ratio : ''; ?>" placeholder="<?php echo lang('stock_material_quantity'); ?>"
					data-rule-required="true" data-msg-required="<?= lang("field_required") ?>" <?php if (@$readonly) echo 'readonly'; ?> />
				<div class="input-tag">
					<?= (isset($item_data->unit_type) && !empty($item_data->unit_type)) ? $item_data->unit_type : ''; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label for="price" class="col-md-3">
			<?php echo lang('price'); ?>
		</label>
		<div class="col-md-9">
			<?php
			echo form_input(
				array(
					"id" => "price",
					"name" => "price",
					"value" => (isset($model_info->price) && !empty($model_info->price)) ? $model_info->price : '', 
					"class" => "form-control",
					"placeholder" => lang('price'),
					"type" => "number",
					"min" => 0,
					"step" => 0.01,
					"autofocus" => true,
					"data-rule-required" => true,
					"data-msg-required" => lang("field_required"),
					"readonly" => false
				)
			);
			?>
		</div>
	</div>

</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
		<?php echo lang('close'); ?>
	</button>
	<?php // if (isset($can_update) && !empty($can_update)): ?>
		<button type="submit" class="btn btn-primary">
			<span class="fa fa-check-circle"></span>
			<?php echo lang('save'); ?>
		</button>
	<?php // endif; ?>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();
		$("#item-pricing-form").appForm({
			onSuccess: function (response) {
				console.log(response);
				if (response.success) $("#item-pricing-table").appTable({ newData: response.data_result, dataId: response.data_result_id });
			}
		});

		let supplier_dropdown = JSON.parse('<?php echo json_encode($supplier_dropdown); ?>');
		$('#supplier_id').select2({
			data: supplier_dropdown,
			placeholder: '<?php echo lang('stock_supplier'); ?>'
		});
	});
</script>