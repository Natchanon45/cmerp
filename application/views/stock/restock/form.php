<input type="hidden" name="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
<input type="hidden" name="created_by" value="<?php echo isset($model_info->created_by)? $model_info->created_by: ''; ?>" />
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
  <label for="name" class="<?php echo $label_column; ?>">
    <?php echo lang('stock_restock_name'); ?>*
  </label>
  <div class="<?php echo $field_column; ?>">
    <?php
      echo form_input(array(
        "id" => "name",
        "name" => "name",
        "value" => $model_info->name,
        "class" => "form-control",
        "placeholder" => lang('stock_restock_name'),
        "autofocus" => true,
        "data-rule-required" => true,
        "data-msg-required" => lang("field_required"),
        "readonly" => $readonly
      ));
    ?>
  </div>
</div>
<div class="form-group">
  <label for="po_no" class="<?php echo $label_column; ?>">
    <?php echo lang('po_no'); ?>
  </label>
  <div class="<?php echo $field_column; ?>">
    <?php
      echo form_input(array(
        "id" => "po_no",
        "name" => "po_no",
        "value" => $model_info->po_no,
        "class" => "form-control",
        "placeholder" => lang('po_ref'),
        "autofocus" => true,
        //"data-rule-required" => true,
        //"data-msg-required" => lang("field_required"),
        "readonly" => $readonly
      ));
    ?>
  </div>
</div>
<?php if($this->login_user->is_admin){?>
  <div class="form-group">
    <label for="created_by" class="<?php echo $label_column; ?>">
      <?php echo lang('stock_restock_creator'); ?>*
    </label>
    <div class="<?php echo $field_column; ?>">
      <?php
        echo form_input(array(
          "id" => "created_by",
          "name" => "created_by",
          "value" => $model_info->created_by ? $model_info->created_by : $this->login_user->id,
          "class" => "form-control",
          "placeholder" => lang('stock_restock_creator'),
          "data-rule-required" => true,
          "data-msg-required" => lang("field_required"),
          "readonly" => $readonly
        ));
      ?>
    </div>
  </div>
<?php }?>
<div class="form-group">
  <label for="created_date" class="<?php echo $label_column; ?>">
    <?php echo lang('stock_restock_date'); ?>*
  </label>
  <div class="<?php echo $field_column; ?>" <?php if($readonly)echo 'style="pointer-events:none;"'; ?>>
    <?php
      echo form_input(array(
          "id" => "created_date",
          "name" => "created_date",
          "value" => is_date_exists($model_info->created_date) ? $model_info->created_date : "",
          "class" => "form-control",
          "placeholder" => lang('stock_restock_date'),
          "autocomplete" => "off",
          "data-rule-required" => true,
          "data-msg-required" => lang("field_required"),
          "readonly" => $readonly
      ));
    ?>
  </div>
</div>

<?php if(isset($material_restocks)){?>
  <div id="type-container">
    <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">            
      <thead>
        <tr role="row">
          <th class="w250"><?php echo lang('stock_materials'); ?></th>
          <th><?php echo lang('stock_restock_quantity'); ?></th>
          <?php if($can_read_price){?>
            <th><?php echo lang('stock_restock_price'); ?></th>
          <?php }?>
          <?php if(!$readonly){?>
            <th class="w70">
              <a href="javascript:" id="btn-add-material" class="btn btn-primary">
                <span class="fa fa-plus-circle"></span> <?php echo lang('add'); ?>
              </a>
            </th>
          <?php }?>
        </tr>
      </thead>
      <tbody id="table-body">
        <?php foreach($material_restocks as $k){?>
          <tr>
            <td>
              <input type="hidden" name="restock_id[]" value="<?= $k->id ?>" />
              <select 
                name="material_id[]" class="form-control select-material" required 
                style="pointer-events:none;" <?php if($readonly)echo 'readonly'; ?> 
              >
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
                  type="number" name="stock[]" required <?php if($readonly)echo 'readonly'; ?> 
                  class="form-control" min="0" step="0.0001" value="<?= $k->stock ?>" 
                />
                <div class="input-tag"><?= $k->material_unit ?></div>
              </div>
            </td>
            <?php if($can_read_price){?>
              <td>
                <input 
                  type="number" name="price[]" required <?php if($readonly)echo 'readonly'; ?> 
                  class="form-control" min="0" step="0.01" value="<?= $k->price ?>" 
                />
              </td>
            <?php }?>
            <?php if(!$readonly){?>
              <td class="w100">
                <a href="javascript:" class="btn btn-danger btn-delete-material">
                  <span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
                </a>
              </td>
            <?php }?>
          </tr>
        <?php }?>
      </tbody>
    </table>
  </div>
<?php }?>

<script type="text/javascript">
  $(document).ready(function () {

    $('[data-toggle="tooltip"]').tooltip();
    <?php if($this->login_user->is_admin){?>
      $('#created_by').select2({data: <?php echo $team_members_dropdown; ?>});
    <?php }?>
    setDatePicker("#created_date");
    
    <?php if(isset($material_restocks)){?>
      var typeContainer = $('#type-container');
      var tableBody = typeContainer.find('#table-body'),
          btnAdd = typeContainer.find('#btn-add-material');
      btnAdd.click(function(e){
        e.preventDefault();
        tableBody.append(`
          <tr>
            <td>
              <input type="hidden" name="restock_id[]" />
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
                  type="number" name="stock[]" required 
                  class="form-control" min="0" step="0.0001" value="0" 
                />
                <div class="input-tag"></div>
              </div>
            </td>
            <?php if($can_read_price){?>
              <td>
                <input 
                  type="number" name="price[]" required 
                  class="form-control" min="0" step="0.01" value="0" 
                />
              </td>
            <?php }?>
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
    <?php }?>

  });
</script>
