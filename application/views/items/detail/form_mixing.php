<input type="hidden" name="id" id="id" value="<?php echo isset($model_info->id)? $model_info->id: ''; ?>" />
<input type="hidden" name="clone_to_new_item" id="clone_to_new_item" value="0" />
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
    <label for="item_id" class="<?= $label_column ?>">
      <?php echo lang('items'); ?>
    </label>
    <div class="<?= $field_column ?>">
      <?php
        echo form_dropdown(
          "item_id", 
          $items_dropdown, 
          array($model_info->item_id), 
          "class='select2 validate-hidden'" 
        );
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
<?php /*<td>
  <select name="cat_id[]" class="form-control select-category" required>
    <option value="" data-unit=""></option>
    <?php
      foreach($categories_dropdown as $cat_id=>$text){
        $selected = '';
        if($cat_id == @$k->cat_id) $selected = 'selected';
        echo '<option value="'.$cat_id.'" '.$selected.'>'
            .$text
          .'</option>';
      }
    ?>
  </select>
</td>*/?>
<div id="type-container">
  <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">            
    <thead>
      <tr role="row">
        <th><?php echo lang('stock_material'); ?></th>
        <?php /*<th class="w200"><?php echo lang('category'); ?></th>*/ ?>
        <th class="w200"><?php echo lang('item_mixing_ratio'); ?></th>
        <th class="w70">
          <a href="javascript:" id="btn-add-category" class="btn btn-primary">
            <span class="fa fa-plus-circle"></span> <?php echo lang('add_category'); ?>
          </a>
        </th>
      </tr>
    </thead>
<?php
/*
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
        </tr>*/
?>
    <tbody id="table-body">
      <?php if(isset($material_cat_mixings)){
        foreach($material_cat_mixings as $cat_id=>$v) {
          $temp_cat_id = 'cat_'.uniqid();
        ?>
        <tr><td colspan="3">&nbsp;</td></tr>
        <tr>
          <td colspan="2">
            <select name="cat_id[<?php echo $temp_cat_id;?>]" temp-cat-id="<?php echo $temp_cat_id;?>" class="form-control select-category" required>
              <option value="" data-unit=""><?php echo lang('select_category');?></option>
              <?php
                foreach($categories_dropdown as $k=>$title){
                  $selected = '';
                  if($k == $cat_id) $selected = 'selected';
                  echo '<option value="'.$k.'" '.$selected.'>'.$title.'</option>';
                }
              ?>
            </select>
          </td>
          <td>
            <a href="javascript:" id="" class="btn btn-primary btn-add-material" temp-cat-id="<?php echo $temp_cat_id;?>">
              <span class="fa fa-plus-circle"></span> <?php echo lang('add'); ?>
            </a>
          </td>
        </tr>
        <tr><td colspan="3">
          <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
            <thead class="hide_head">
              <tr class="hide_head">
                <td class="hide_head">&nbsp;</td>
                <td class="w200 hide_head">&nbsp;</td>
                <td class="w70 hide_head">&nbsp;</td>
              </tr>
            </thead>
            <tbody class="table-body2">
              <?php foreach($material_cat_mixings[$cat_id] as $material) {?>
                <tr>
                  <td>
                    <select name="material_id[<?php echo $temp_cat_id;?>][]" class="form-control select-material" required>
                        <option value="" data-unit=""><?php echo lang('select_material');?></option>
                        <?php
                            foreach($material_dropdown as $d){
                                $material_name = $d->name;
                                if($bom_material_read_production_name == true) $material_name .= " - ".$d->production_name;

                                $selected = '';
                                if($d->id == $material->material_id) $selected = 'selected';
                                echo '<option value="'.$d->id.'" data-unit="'.$d->unit.'" '.$selected.'>'.$material_name.'</option>';
                            }
                        ?>
                    </select>
                  </td>
                  <td>
                    <div class="input-suffix">
                      <input 
                        type="number" name="mixing_ratio[<?php echo $temp_cat_id;?>][]" required 
                        class="form-control" min="0" step="0.0001" value="<?= $material->ratio ?>" 
                      />
                      <div class="input-tag"><?= $material->material_unit ?></div>
                    </div>
                  </td>
                  <td>
                    <a href="javascript:" class="btn btn-danger btn-delete-material">
                      <span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
                    </a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table></td>
        </tr>
      <?php }
      }?>
    </tbody>
  </table>
</div>
<style>
.hide_head{
  height:0 !important;
  line-height:0 !important;
  padding:0 !important;
  margin:0 !important;
}
</style>
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
    var btnAdd = typeContainer.find('.btn-add-material'),
        tableBody = typeContainer.find('#table-body')
        btnAddCat = typeContainer.find('#btn-add-category');
<?php /*
<td>
  <select name="cat_id[]" class="form-control select-category" required>
    <option value="" data-unit=""></option>
    <?php
      foreach($categories_dropdown as $cat_id=>$text){
        $selected = '';
        if($cat_id == @$k->cat_id) $selected = 'selected';
        echo '<option value="'.$cat_id.'" '.$selected.'>'
            .$text
          .'</option>';
      }
    ?>
  </select>
</td>
*/?>
  btnAddCat.click(function(e) {
      //tableBody = typeContainer.find('#table-body');
      e.preventDefault();
      let temp_cat_id = 'cat_'+$.now();
      tableBody.append(`
        <tr><td colspan="3">&nbsp;</td></tr>
        <tr>
          <td colspan="2">
            <select name="cat_id[${temp_cat_id}]" temp-cat-id="${temp_cat_id}" class="form-control select-category" required>
              <option value="" data-unit=""><?php echo lang('select_category');?></option>
              <?php
                foreach($categories_dropdown as $k=>$title){
                  echo '<option value="'.$k.'">'
                      .$title
                    .'</option>';
                }
              ?>
            </select>
          </td>
          <td>
            <a href="javascript:" id="" temp-cat-id="${temp_cat_id}" class="btn btn-primary btn-add-material">
              <span class="fa fa-plus-circle"></span> <?php echo lang('add'); ?>
            </a>
            <?php /*<a href="javascript:" class="btn btn-danger btn-delete-material">
              <span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
            </a>*/?>
          </td>
        </tr>
        <tr><td colspan="3">
          <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
            <thead class="hide_head">
              <tr class="hide_head">
                <td class="hide_head">&nbsp;</td>
                <td class="w200 hide_head">&nbsp;</td>
                <td class="w70 hide_head">&nbsp;</td>
              </tr>
            </thead>
            <tbody class="table-body2"></tbody>
          </table></td>
        </tr>
      `);
      processBindingCat();
    });
    processBindingCat();
    function processBindingCat(){
      typeContainer.find('.btn-add-material').unbind();
      typeContainer.find('.btn-add-material').click(function(e){
        e.preventDefault();
        addMaterialRow($(this).attr('temp-cat-id'), $(this).closest('tr').next().find('.table-body2'));
      });
    }
    function addMaterialRow(temp_cat_id, tableBody_) {
      // console.log(temp_cat_id);
      // return;
      tableBody_.append(`
        <tr>
          <td>
            <select name="material_id[${temp_cat_id}][]" class="form-control select-material" required>
              <option value="" data-unit=""><?php echo lang('select_material');?></option>
              <?php
                foreach($material_dropdown as $d){
                    $material_name = $d->name;
                    if($bom_material_read_production_name == true) $material_name .= " - ".$d->production_name;
                  
                    echo '<option value="'.$d->id.'" data-unit="'.$d->unit.'">'.$material_name.'</option>';
                }
              ?>
            </select>
          </td>
          <td>
            <div class="input-suffix">
              <input 
                type="number" name="mixing_ratio[${temp_cat_id}][]" required 
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
    }


    btnAdd.click(function(e){
      e.preventDefault();
      addMaterialRow($(this).attr('temp-cat-id'), $(this).closest('tr').next().find('.table-body2'));
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
