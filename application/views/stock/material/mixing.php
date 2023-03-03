<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('stock_material_mixing'); ?></h4>
  </div>
  <?php echo form_open(get_uri("stock/material_mixing_save/"), array("id" => "company-form", "class" => "general-form dashed-row white", "role" => "form")); ?>
  <div class="panel">
    <div class="panel-body">
    
      <input type="hidden" name="id" value="<?php echo isset($material_info->id)? $material_info->id: ''; ?>" />
      <div class="form-group" style="margin-bottom:0;">
        <label class="col-md-2 col-sm-3 col-xs-4">
          <?php echo lang('stock_material_type'); ?>
        </label>
        <div class="col-lg-2 col-md-3 col-sm-4 col-xs-8">
          <select name="type" class="form-control" required>
            <option value="0" <?php if($material_info->type==0) echo 'selected'; ?>>เป็นวัตถุดิบสำเร็จ</option>
            <option value="1" <?php if($material_info->type==1) echo 'selected'; ?>>เป็นวัตถุดิบผสม</option>
          </select>
        </div>
      </div>

      <div id="type-container" <?php if($material_info->type==0) echo 'style="display:none;"'; ?>>
        <div class="form-group" style="padding-top:15px;">
          <label class="col-md-2 col-sm-3 col-xs-4">
            <?php echo lang('stock_material_ratio'); ?>
          </label>
          <div class="col-lg-2 col-md-3 col-sm-4 col-xs-8">
            <div class="input-suffix">
              <input 
                type="number" name="ratio" class="form-control" min="0" step="0.0001" required 
                value="<?= sizeof($material_mixing)? $material_mixing[0]->ratio: 0 ?>" 
              />
              <div class="input-tag"><?= $material_info->unit ?></div>
            </div>
          </div>
        </div>
        <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">            
          <thead>
            <tr role="row">
              <th class="w300"><?php echo lang('stock_materials'); ?></th>
              <th><?php echo lang('stock_material_ratio'); ?></th>
              <th class="w70">
                <a href="javascript:" id="btn-add-material" class="btn btn-primary">
                  <span class="fa fa-plus-circle"></span> <?php echo lang('add'); ?>
                </a>
              </th>
            </tr>
          </thead>
          <tbody id="table-body">
            <?php foreach($material_mixing as $k){?>
              <tr>
                <td>
                  <select name="using_material_id[]" class="form-control select-material" required>
                    <option value="" data-unit=""></option>
                    <?php
                      foreach($material_dropdown as $d){
                        $selected = '';
                        if($d->id == $k->using_material_id) $selected = 'selected';
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
                      type="number" name="using_ratio[]" required class="form-control" 
                      min="0" step="0.0001" value="<?= $k->using_ratio ?>" 
                    />
                    <div class="input-tag"><?= $k->using_material_unit ?></div>
                  </div>
                </td>
                <td class="w100">
                  <a href="javascript:" class="btn btn-danger btn-delete-material">
                    <span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
                  </a>
                </td>
              </tr>
            <?php }?>
          </tbody>
        </table>
      </div>

    </div>
    <?php // if ($can_edit_clients) { ?>
      <div class="panel-footer">
        <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
      </div>
    <?php // } ?>
  </div>
  <?php echo form_close(); ?>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#company-form").appForm({
      isModal: false,
      onSuccess: function (result) {
        appAlert.success(result.message, {duration: 10000});
      }
    });

    var typeSelect = $('[name="type"]'),
        typeContainer = $('#type-container');
    typeContainerToggle(typeSelect.val());
    typeSelect.change(function(){ typeContainerToggle(this.value); });
    function typeContainerToggle(val){
      if(val==1) typeContainer.slideDown();
      else typeContainer.slideUp();
    }

    var tableBody = typeContainer.find('#table-body'),
        btnAdd = typeContainer.find('#btn-add-material');
    btnAdd.click(function(e){
      e.preventDefault();
      tableBody.append(`
        <tr>
          <td>
            <select name="using_material_id[]" class="form-control select-material" required>
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
                type="number" name="using_ratio[]" required class="form-control" 
                min="0" step="0.0001" value="0" 
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
      
      typeContainer.find('.select-material').unbind();
      typeContainer.find('.select-material').change(function(){
        let self = $(this);
        let option = $(this).find('[value="'+this.value+'"]');
        self.closest('tr').find('.input-tag').html(option.data('unit'));
      });
    }
  });
</script>