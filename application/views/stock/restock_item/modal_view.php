<?php echo form_open(get_uri("stock/restock_item_view_save"), array("id" => "remaining-form", "class" => "general-form", "role" => "form")); ?>
<div id="material-dropzone" class="post-dropzone">
  <div class="modal-body clearfix" id="temp-container">

    <input type="hidden" name="id" value="<?php echo isset($model_info->id) ? $model_info->id : ''; ?>" />
    <input type="hidden" name="group_id" value="<?php echo isset($model_info->group_id) ? $model_info->group_id : ''; ?>" />
    <input type="hidden" name="view" value="<?php echo isset($view) ? $view : ''; ?>" />

    <?php
    $readonly = false;
    $disabled = false;
    if (empty($model_info->id)) {
      $readonly = isset($can_create) && !$can_create;
    } else {
      $readonly = isset($can_update) && !$can_update;
      if (isset($model_info->remaining) && ($model_info->remaining <= 0)) {
        $disabled = true;
      }
    }
    ?>

    <div class="form-group">
      <label for="item_id" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_item'); ?>
      </label>
      <div class="<?php echo $field_column; ?>">
        <select name="item_id" class="form-control select-material" <?php if (!empty($model_info->id) && !empty($model_info->group_id)) { echo 'style="pointer-events:none;"'; } if ($readonly) { echo 'readonly'; } ?> required>
          <?php
            foreach ($item_dropdown as $d) {
              $selected = '';
              if ($d->id == $model_info->item_id)
                $selected = 'selected';
                echo '<option value="' . $d->id . '" data-unit="' . $d->unit_type . '" ' . $selected . '>' . $d->title . '</option>';
            }
          ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="sern" class="<?php echo $label_column; ?>">
        <?php echo lang('serial_number'); ?>
      </label>
      <div class="<?php echo $field_column; ?>">
        <div class="input-suffix">
          <input type="text" name="sern" id="sern" class="form-control" placeholder="<?php echo lang('serial_number'); ?>"
          value="<?php echo $model_info->serial_number; ?>" <?php if ($disabled) { echo "disabled"; } ?> required />
        </div>
      </div>
    </div>

    <div class="form-group">
      <label for="stock" class="<?php echo $label_column; ?>">
        <?php echo lang('stock_restock_quantity'); ?>
      </label>
      <div class="<?php echo $field_column; ?>">
        <div class="input-suffix">
          <input type="number" name="stock" id="stock" required class="form-control" min="1" step="0.0001" <?php if ($disabled) { echo "disabled"; } ?>
            placeholder="<?php echo lang('stock_restock_quantity'); ?>" value="<?php echo $model_info->stock ? $model_info->stock : ''; ?>" 
            <?php if (isset($model_info->can_delete) && !$model_info->can_delete) echo 'readonly'; ?> />
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
          <input type="number" name="remaining" id="remaining" required class="form-control" min="1" step="0.0001" placeholder="<?php echo lang('stock_restock_remaining'); ?>"
            value="<?php echo $model_info->remaining ? $model_info->remaining : ''; ?>" <?php if ($disabled) { echo "disabled"; } ?> readonly />
          <div class="input-tag"></div>
        </div>
      </div>
    </div>

    <?php if ($can_read_price) { ?>
      <div class="form-group">
        <label for="price" class="<?php echo $label_column; ?>">
          <?php echo lang('stock_restock_price'); ?>
        </label>
        <div class="<?php echo $field_column; ?>">
          <div class="input-suffix">
            <input type="number" name="price" id="price" required class="form-control" min="1" step="0.01" placeholder="<?php echo lang('stock_restock_price'); ?>" <?php if ($disabled) { echo "disabled"; } ?>
              value="<?php echo $model_info->price ? $model_info->price : ''; ?>" <?php if (isset($model_info->can_delete) && !$model_info->can_delete) echo 'readonly'; ?> />
            <div class="input-tag-2"><?php echo lang('THB'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="price" class="<?php echo $label_column; ?>">
          <?php echo lang('rate'); ?>
        </label>
        <div class="<?php echo $field_column; ?>">
          <div class="input-suffix">
            <input type="number" name="priceunit" id="priceunit" required class="form-control" min="1" step="0.01" placeholder="<?php echo lang('rate'); ?>" <?php if ($disabled) { echo "disabled"; } ?>
              value="<?php echo @$model_info->priceunit ? $model_info->priceunit : ''; ?>" <?php if (isset($model_info->can_delete) && !$model_info->can_delete) echo 'readonly'; ?> />
            <div class="input-tag-2"><?php echo lang('THB'); ?></div>
          </div>
        </div>
      </div>
    <?php } ?>

    <div class="form-group">
      <label for="expiration_date" class="<?php echo $label_column; ?>">
        <?php echo lang('expiration_date'); ?>
      </label>
      <div class="<?php echo $field_column; ?>" <?php if ($readonly) echo 'style="pointer-events:none;"'; ?>>
        <?php
        $set_expire = array(
          "id" => "expiration_date",
          "name" => "expiration_date",
          "value" => is_date_exists($model_info->expiration_date) ? format_to_date($model_info->expiration_date) : '',
          "class" => "form-control",
          "placeholder" => lang('expiration_date'),
          "autocomplete" => "off",
          "readonly" => $readonly
        );
        if ($disabled) {
          $set_expire["disabled"] = true; 
        }
        echo form_input($set_expire);
        ?>
      </div>
    </div>

    <div class="form-group">
      <label for="note" class="<?php echo $label_column; ?>">
        <?php echo lang('note_real'); ?>
      </label>
      <div class="<?php echo $field_column; ?>">
        <?php
        $set_note = array(
          "id" => "note",
          "name" => "note",
          "value" => $model_info->note ? $model_info->note : '',
          "class" => "form-control",
          "placeholder" => lang('note_real'),
          "data-rich-text-editor" => true,
          "readonly" => $readonly
        );
        if ($disabled) {
          $set_note["disabled"] = true;
        }
        echo form_textarea($set_note);
        ?>
      </div>
    </div>

    <?php if ((empty($model_info->id) && $can_update) || (!empty($model_info->id) && $can_update)) { ?>
      <?php if (!$disabled): ?>
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
      <?php endif; ?>
    <?php } ?>

    <?php
    if (!$disabled) {
      $this->load->view("includes/dropzone_preview");
    } // load dropzone
    ?>
  </div>

  <div class="modal-footer">
    <?php if ((empty($model_info->id) && $can_update) || (!empty($model_info->id) && $can_update)) { ?>
      <?php if (!$disabled): ?>
        <button class="btn btn-default upload-file-button pull-left btn-sm round" type="button" style="color: #7988a2;">
          <i class="fa fa-camera"></i>
          <?php echo lang('upload_file'); ?>
        </button>
      <?php endif; ?>
    <?php } ?>
    <button type="button" class="btn btn-default" data-dismiss="modal">
      <span class="fa fa-close"></span>
      <?php echo lang('close'); ?>
    </button>
    <?php if ((empty($model_info->id) && $can_update) || (!empty($model_info->id) && $can_update)) { ?>
      <?php if (!$disabled): ?>
        <button type="submit" class="btn btn-primary">
          <span class="fa fa-check-circle"></span>
          <?php echo lang('save'); ?>
        </button>
      <?php endif; ?>
    <?php } ?>
  </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
  $(document).ready(function () {
    <?php if ((empty($model_info->id) && $can_update) || (!empty($model_info->id) && $can_update)) { ?>
      <?php if (!$disabled): ?>
        var uploadUrl = "<?php echo get_uri("stock/upload_file"); ?>";
        var validationUri = "<?php echo get_uri("stock/validate_modal_file"); ?>";
        var dropzone = attachDropzoneWithForm("#material-dropzone", uploadUrl, validationUri);
      <?php endif; ?>
    <?php } ?>

    $('[data-toggle="tooltip"]').tooltip();
    setDatePicker("#expiration_date");

    var remainingForm = $('#remaining-form');
    remainingForm.appForm({
      onSuccess: function (result) {
        appAlert.success(result.message, { duration: 10000 });
        $("#remaining-table").appTable({ newData: result.data, dataId: result.id });
      }
    });

    var tempContainer = $('#temp-container'), selectMaterial = tempContainer.find('.select-material');
    
    selectMaterial.select2();
    updateUnit();
    
    selectMaterial.change(function () {
      updateUnit();
    });
    
    function updateUnit() {
      let option = selectMaterial.find('[value="' + selectMaterial.val() + '"]');
      tempContainer.find('.input-tag').html(option.data('unit'));
    }

    $('#stock, #remaining, #price, #priceunit').click(function(e) {
      e.target.select();
    });

    $('#stock').change(function(e) {
      e.preventDefault();
      $('#remaining').val($('#stock').val());
    });

    $('#price').keypress(function(e) {
      if (e.key == "Enter") {
        e.preventDefault();
        $('#priceunit').select();
      }
    });

    $('#price').change(function(e) {
      let stock = parseFloat($('#stock').val());
      let price = parseFloat($('#price').val());

      $('#priceunit').val(priceUnitCalc(stock, price));
    });

    $('#priceunit').keypress(function(e) {
      if (e.key == "Enter") {
        e.preventDefault();
        $('#price').select();
      }
    });

    $('#priceunit').change(function(e) {
      let stock = parseFloat($('#stock').val());
      let price = parseFloat($('#priceunit').val());

      $('#price').val(priceTotalCalc(stock, price));
    });

    function priceUnitCalc(stock = 0, price = 0) {
      if (stock === 0 || price === 0) {
        return 0;
      } else {
        return (price / stock).toFixed(2);
      }
    }

    function priceTotalCalc(stock = 0, price = 0) {
      if (stock === 0 || price === 0) {
        return 0;
      } else {
        return (price * stock).toFixed(2);
      }
    }
  });
</script>