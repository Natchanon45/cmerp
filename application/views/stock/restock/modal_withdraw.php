<style type="text/css">
.string-upper {
    text-transform: uppercase;
}
</style>

<?php echo form_open(get_uri("stock/restock_withdraw_save"), array("id" => "withdraw-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix" id="temp-container">
  <input type="hidden" name="id" value="<?php echo isset($model_info->id) ? $model_info->id : ''; ?>" />
  <input type="hidden" name="group_id" value="<?php echo isset($model_info->group_id) ? $model_info->group_id : ''; ?>" />
  <input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />
  
  <div class="form-group">
    <label for="material_id" class="<?php echo $label_column; ?>">
      <?php echo lang('stock_material'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <select name="material_id" class="form-control select-material" <?php if (!empty($model_info->id) && !empty($model_info->group_id)) echo 'style="pointer-events: none;"'; ?> required>
        <?php
        foreach ($material_dropdown as $d) {
          $selected = '';
          if ($d->id == $model_info->material_id)
            $selected = 'selected';
          echo '<option value="' . $d->id . '" data-unit="' . $d->unit . '" ' . $selected . '>' . $d->name . '</option>';
        }
        ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label for="remaining" class="<?php echo $label_column; ?>">
      <?php echo lang('stock_restock_remaining'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <div class="input-suffix">
        <input type="number" name="remaining" id="remaining" disabled class="form-control" min="1" step="0.0001" value="<?= $model_info->remaining ?>" />
        <div class="input-tag string-upper"></div>
      </div>
    </div>
  </div>

  <div class="form-group">
    <label for="ratio" class="<?php echo $label_column; ?>">
      <?php echo lang('stock_restock_withdraw'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <div class="input-suffix">
        <input type="number" name="ratio" id="ratio" required class="form-control" min="0.0001" max="<?= $model_info->remaining ?>" step="0.0001" value="0" />
        <div class="input-tag string-upper"></div>
      </div>
    </div>
  </div>

  <div class="form-group">
    <label for="note" class="<?php echo $label_column; ?>">
      <?php echo lang('note_real'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <?php
      echo form_textarea(
        array(
          "id" => "note",
          "name" => "note",
          "value" => $model_info->note ? $model_info->note : "",
          "class" => "form-control",
          "placeholder" => lang('note_real'),
          "data-rich-text-editor" => true
        )
      );
      ?>
    </div>
  </div>
</div>

<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal">
    <span class="fa fa-close"></span> 
    <?php echo lang('close'); ?>
  </button>
  <button type="submit" class="btn btn-primary">
    <span class="fa fa-check-circle"></span> 
    <?php echo lang('save'); ?>
  </button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();
		$("#withdraw-form").appForm({
			onSuccess: function (result) {
        appAlert.success(result.message, { duration: 10000 });
        $("#remaining-table").appTable({ newData: result.data, dataId: result.id });
			}
		});

    $('#ratio, #note').click(function(e) {
      e.target.select();
    });

    var tempContainer = $('#temp-container'), selectMaterial = tempContainer.find('.select-material');
    updateUnit();

    selectMaterial.change(function() {
      updateUnit();
    });

    function updateUnit() {
      let option = selectMaterial.find('[value="' + selectMaterial.val() + '"]');
      tempContainer.find('.input-tag').html(option.data('unit'));
    }
	});
</script>
