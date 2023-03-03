
<input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
<input type="hidden" name="item_id" value="<?php echo isset($item->id)? $item->id: ''; ?>" />
<input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />

<div style="width:100%; max-width:600px;">
  <div class="form-group">
    <label for="name" class="<?php echo $label_column; ?>">
      <?php echo lang('item_mixing_name'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <?php
        echo form_input(array(
          "id" => "name",
          "name" => "name",
          "value" => $model_info->name,
          "class" => "form-control",
          "placeholder" => lang('item_mixing_name'),
          "autofocus" => true,
          "data-rule-required" => true,
          "data-msg-required" => lang("field_required"),
        ));
      ?>
    </div>
  </div>
  <div class="form-group">
    <label for="ratio" class="<?php echo $label_column; ?>">
      <?php echo lang('item_mixing_ratio'); ?>
    </label>
    <div class="<?php echo $field_column; ?>">
      <div class="input-suffix">
        <input 
          type="number" name="ratio" class="form-control" min="0" step="0.0001" required readonly 
          value="<?= isset($model_info->ratio) && $model_info->ratio > 0? to_decimal_format2($model_info->ratio): 1 ?>" 
        />
        <div class="input-tag"><?= $item->unit_type ?></div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="is_public" class="<?= $label_column ?>">
      <?php echo lang('item_mixing_is_public'); ?>
    </label>
    <div class="<?= $field_column ?>">
      <?php
        echo form_checkbox(
          "is_public", "1", 
          $model_info->is_public ? true : false, 
          "id='is_public'"
        );
      ?>                       
    </div>
  </div>
  <div class="form-group" id="client-form-group">
    <label for="for_client_id" class="<?= $label_column ?>">
      <?php echo lang('item_mixing_for_client'); ?>
    </label>
    <div class="<?= $field_column ?>">
      <?php
        echo form_dropdown(
          "for_client_id", 
          $clients_dropdown, 
          array($model_info->for_client_id), 
          "class='select2 validate-hidden'" 
        );
      ?>
    </div>
  </div>
</div>

<div id="type-container">
  <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">            
    <thead>
      <tr role="row">
        <th><?php echo lang('stock_material'); ?></th>
        <th class="w200"><?php echo lang('item_mixing_ratio'); ?></th>
        <th class="w70">
          <a href="javascript:" id="btn-add-material" class="btn btn-primary">
            <span class="fa fa-plus-circle"></span> <?php echo lang('add'); ?>
          </a>
        </th>
      </tr>
    </thead>
    <tbody id="table-body">
      <?php if(isset($material_mixings)){ foreach($material_mixings as $k){?>
        <tr>
          <td>
            <select name="material_id[]" class="form-control select-material" required>
              <option value="" data-unit=""></option>
              <?php
                foreach($material_dropdown as $d){
                  $selected = '';
                  if($d->id == $k->material_id) $selected = 'selected';
                  echo '<option value="'.$d->id.'" data-unit="'.$d->unit.'" '.$selected.'>'
                      .$d->name
                    .'</option>';
                }
              ?>
            </select>
          </td>
          <td>
            <div class="input-suffix">
              <input 
                type="number" name="mixing_ratio[]" required 
                class="form-control" min="0" step="0.0001" value="<?= $k->ratio ?>" 
              />
              <div class="input-tag"><?= $k->material_unit ?></div>
            </div>
          </td>
          <td class="w100">
            <a href="javascript:" class="btn btn-danger btn-delete-material">
              <span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
            </a>
          </td>
        </tr>
      <?php }}?>
    </tbody>
  </table>
</div>

<script type="text/javascript">
  $(document).ready(function () {

    $("#client-form-group .select2").select2();

    var clientFormGroup = $('#client-form-group'),
        publicSelect = $('#is_public');
    toggleClient();
    publicSelect.change(function(){ toggleClient(); });
    function toggleClient(){
      if(publicSelect.prop('checked')) clientFormGroup.css('display', 'none');
      else clientFormGroup.css('display', 'block');
    }
    
    var typeContainer = $('#type-container');
    var tableBody = typeContainer.find('#table-body'),
        btnAdd = typeContainer.find('#btn-add-material');
    btnAdd.click(function(e){
      e.preventDefault();
      tableBody.append(`
        <tr>
          <td>
            <select name="material_id[]" class="form-control select-material" required>
              <option value="" data-unit=""></option>
              <?php
                foreach($material_dropdown as $d){
                  echo '<option value="'.$d->id.'" data-unit="'.$d->unit.'">'
                      .$d->name
                    .'</option>';
                }
              ?>
            </select>
          </td>
          <td>
            <div class="input-suffix">
              <input 
                type="number" name="mixing_ratio[]" required 
                class="form-control" min="0" step="0.0001" value="0" 
              />
              <div class="input-tag"></div>
            </div>
          </td>
          <td>
            <a href="javascript:" class="btn btn-danger btn-delete-material">
              <span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
            </a>
          </td>
        </tr>
      `);
      processBinding();
    });
    processBinding();
    function processBinding(){
      typeContainer.find('.btn-delete-material').unbind();
      typeContainer.find('.btn-delete-material').click(function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
        processBinding();
      });
      
      typeContainer.find('.select-material').select2('destroy');
      typeContainer.find('.select-material').select2();
      
      typeContainer.find('.select-material').unbind();
      typeContainer.find('.select-material').change(function(){
        let self = $(this);
        let option = $(this).find('[value="'+this.value+'"]');
        self.closest('tr').find('.input-tag').html(option.data('unit'));
      });
    }

  });
</script>
