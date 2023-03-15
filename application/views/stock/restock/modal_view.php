<?php echo form_open(get_uri("stock/restock_view_save"), array("id" => "remaining-form", "class" => "general-form", "role" => "form")); ?>
<div id="material-dropzone" class="post-dropzone">
  <div class="modal-body clearfix" id="temp-container">
    <input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
    <input type="hidden" name="group_id" value="<?php echo isset($model_info->group_id)? $model_info->group_id: ''; ?>" />
    <input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />

    
    <?php
      $readonly = false;
      if(empty($model_info->id)) {
        $readonly = isset($can_create) && !$can_create;
      } else {
        $readonly = isset($can_update) && !$can_update;
      }
    ?>

    <div class="form-group">
      <label for="material_id" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_material'); ?>
      </label>
      <div class="<?php echo $field_column; ?>">
        <select 
          name="material_id" class="form-control select-material" required 
          <?php if(!empty($model_info->id) && !empty($model_info->group_id))echo 'style="pointer-events:none;"'; ?> 
          <?php if($readonly)echo 'readonly'; ?> 
        >
          <option value="" data-unit=""></option>
          <?php
            foreach($material_dropdown as $d){
              $material_name = $d->name;
              if($bom_material_read_production_name == true){
                $material_name .= " - ".$d->production_name;
              }
              $selected = '';
              if($d->id == $model_info->material_id) $selected = 'selected';
              echo '<option value="'.$d->id.'" data-unit="'.$d->unit.'" '.$selected.'>'.$material_name.'</option>';
            }
          ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label for="stock" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_restock_quantity'); ?>
      </label>
      <div class="<?php echo $field_column; ?>">
        <div class="input-suffix">
          <input 
            type="number" name="stock" id="stock" required 
            class="form-control" min="0" step="0.0001" value="<?= $model_info->stock ?>" 
            <?php if($readonly)echo 'readonly'; ?> 
          />
          <div class="input-tag"></div>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for="remaining" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_restock_remaining'); ?>
      </label>
      <div class="<?php echo $field_column; ?>">
        <div class="input-suffix">
          <input 
            type="number" name="remaining" id="remaining" required 
            class="form-control" min="0" step="0.0001" value="<?= $model_info->remaining ?>" 
            <?php if($readonly)echo 'readonly'; ?> 
          />
          <div class="input-tag"></div>
        </div>
      </div>
    </div>
    <?php if($can_read_price){?>
      <div class="form-group">
        <label for="price" class="<?php echo $label_column; ?>">
          <?php echo lang('stock_restock_price'); ?>
        </label>
        <div class="<?php echo $field_column; ?>">
          <input 
            type="number" name="price" id="price" required 
            class="form-control" min="0" step="0.01" value="<?= $model_info->price ?>" 
            <?php if($readonly)echo 'readonly'; ?> 
          />
        </div>
      </div>
    <?php }?>
    <div class="form-group">
      <label for="expiration_date" class="<?php echo $label_column; ?>">
        <?php echo lang('expiration_date'); ?>
      </label>
      <div class="<?php echo $field_column; ?>" <?php if($readonly)echo 'style="pointer-events:none;"'; ?>>
        <?php
          echo form_input(array(
            "id" => "expiration_date",
            "name" => "expiration_date",
            "value" => is_date_exists($model_info->expiration_date) ? $model_info->expiration_date : "",
            "class" => "form-control",
            "placeholder" => lang('expiration_date'),
            "autocomplete" => "off",
            "readonly" => $readonly
          ));
        ?>
      </div>
    </div>
    <div class="form-group">
      <label for="note" class="<?php echo $label_column; ?>">
        <?php echo lang('note_real'); ?>
      </label>
      <div class="<?php echo $field_column; ?>">
        <?php
          echo form_textarea(array(
            "id" => "note",
            "name" => "note",
            "value" => $model_info->note ? $model_info->note : "",
            "class" => "form-control",
            "placeholder" => lang('note_real'),
            "data-rich-text-editor" => true,
            "readonly" => $readonly
          ));
        ?>
      </div>
    </div>
    
    <?php if((empty($model_info->id) && $can_create) || (!empty($model_info->id) && $can_update)){?>
      <div class="form-group">
        <div class="col-md-12 row pr0">
          <?php
            $this->load->view(
              "includes/file_list", 
              array(
                "files" => $model_info->files, 
                "image_only" => true
              )
            );
          ?>
        </div>
      </div>
    <?php }?>
    <?php $this->load->view("includes/dropzone_preview"); ?>
  </div>

  <div class="modal-footer">
    <?php if((empty($model_info->id) && $can_create) || (!empty($model_info->id) && $can_update)){?>
      <button class="btn btn-default upload-file-button pull-left btn-sm round" type="button" style="color:#7988a2">
        <i class="fa fa-camera"></i> <?php echo lang("upload_image"); ?>
      </button>
    <?php }?>
    <button type="button" class="btn btn-default" data-dismiss="modal">
      <span class="fa fa-close"></span> <?php echo lang('close'); ?>
    </button>
    <?php if((empty($model_info->id) && $can_create) || (!empty($model_info->id) && $can_update)){?>
      <button type="submit" class="btn btn-primary">
        <span class="fa fa-check-circle"></span> <?php echo lang('save'); ?>
      </button>
    <?php }?>
  </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
	$(document).ready(function () {
    
    <?php if((empty($model_info->id) && $can_create) || (!empty($model_info->id) && $can_update)){?>
      var uploadUrl = "<?php echo get_uri("stock/upload_file"); ?>";
      var validationUri = "<?php echo get_uri("stock/validate_modal_file"); ?>";
      var dropzone = attachDropzoneWithForm("#material-dropzone", uploadUrl, validationUri);
    <?php }?>

		$('[data-toggle="tooltip"]').tooltip();
    setDatePicker("#expiration_date");

    var remainingForm = $('#remaining-form');

		remainingForm.appForm({
			onSuccess: function (result) {
        appAlert.success(result.message, {duration: 10000});
        $("#remaining-table").appTable({newData: result.data, dataId: result.id});
        console.log(result.data);
			}
		});

    var tempContainer = $('#temp-container'),
        selectMaterial = tempContainer.find('.select-material');
    selectMaterial.select2();
    updateUnit();
    selectMaterial.change(function(){ updateUnit(); });
    function updateUnit(){
      let option = selectMaterial.find('[value="'+selectMaterial.val()+'"]');
      tempContainer.find('.input-tag').html(option.data('unit'));
    }

	});
</script>
