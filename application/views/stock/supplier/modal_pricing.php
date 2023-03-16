<?php echo form_open(get_uri("stock/supplier_pricing_save"), array("id" => "pricing-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix" id="pricing-modal">
	<input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
	<input type="hidden" name="supplier_id" value="<?php echo isset($model_info->supplier_id)? $model_info->supplier_id: ''; ?>" />
	<input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />
	
	<?php
    $readonly = isset($can_update) && !$can_update;
	?>

	<div class="form-group">
		<label for="material_id" class="<?php echo $label_column; ?>">
			<?php echo lang('stock_material'); ?>
		</label>
		<div class="<?php echo $field_column; ?>" <?php if(!empty($model_info->id) && !empty($model_info->supplier_id))echo 'style="pointer-events:none;"'; ?>>
			<?php
				echo form_input(array(
					"id" => "material_id",
					"name" => "material_id",
					"value" => $model_info->material_id ? $model_info->material_id : null,
					"class" => "form-control",
					"placeholder" => lang('stock_material'),
					"data-rule-required" => true,
					"data-msg-required" => lang("field_required"),
					"readonly" => $readonly
				));
			?>
		</div>
	</div>
	<div class="form-group">
		<label for="ratio" class="<?php echo $label_column; ?>">
			<?php echo lang('stock_material_quantity'); ?>
		</label>
		<div class="<?php echo $field_column; ?>">
			<div class="input-suffix">
				<input 
					type="number" name="ratio" class="form-control" min="0" step="0.0001" required 
					name="ratio" value="<?= $model_info->ratio ?>" 
					placeholder="<?php echo lang('stock_material_quantity'); ?>" 
					data-rule-required="true" data-msg-required="<?= lang("field_required") ?>" 
					<?php if($readonly)echo 'readonly'; ?> 
				/>
				<div class="input-tag"><?= isset($material->unit)? $material->unit: '' ?></div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label for="price" class="<?php echo $label_column; ?>">
			<?php echo lang('price'); ?>
		</label>
		<div class="<?php echo $field_column; ?>">
			<?php
				echo form_input(array(
					"id" => "price",
					"name" => "price",
					"value" => $model_info->price,
					"class" => "form-control",
					"placeholder" => lang('price'),
					"type" => "number",
					"min" => 0,
					"step" => 0.01,
					"autofocus" => true,
					"data-rule-required" => true,
					"data-msg-required" => lang("field_required"),
					"readonly" => $readonly
				));
			?>
		</div>
	</div>
	
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
	<?php if($can_update){?>
		<button type="submit" class="btn btn-primary">
			<span class="fa fa-check-circle"></span> <?php echo lang('save'); ?>
		</button>
	<?php }?>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
	$(document).ready(function () {
		;
		$('[data-toggle="tooltip"]').tooltip();
		$("#pricing-form").appForm({
			onSuccess: function (result) {
				if (result.view === "details") {
					appAlert.success(result.message, {duration: 10000});
					setTimeout(function () {
						location.reload();
					}, 500);
				} else {
					$("#pricing-table").appTable({newData: result.data, dataId: result.id});
				}
			}
		});

		var materials = JSON.parse('<?php echo json_encode($material_dropdown); ?>');

		$('#material_id').select2({
			data: materials,
			placeholder: '<?php lang('stock_material') ?>'
		});
		$('#material_id').change(function(){
			var temp = materials.filter(d => d.id == this.value);
			if(temp.length) {
				$('#pricing-modal .input-tag').html(temp[0].unit);
			} else {
				$('#pricing-modal .input-tag').html('');
			}
		});

	});
</script>