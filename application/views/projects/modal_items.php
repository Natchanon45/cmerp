<?php echo form_open(get_uri("projects/project_items_save"), array("id" => "item-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
  <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
  <input type="hidden" id="restock_process" name="restock_process" value="" />

  <div id="type-container">
    <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">            
      <thead>
        <tr role="row">
          <th style="width:20px; background:#f5f5f5!important;"></th>
          <th class="w250" style="background:#f5f5f5!important;">
            <?php echo lang('item'); ?>
          </th>
          <th class="w250" style="background:#f5f5f5!important;">
            <?php echo lang('item_mixing_name'); ?>
          </th>
          <th style="background:#f5f5f5!important;" class="w150">
            <?php echo lang('quantity'); ?>
          </th>
          <th class="w70" style="background:#f5f5f5!important;">
            <a href="javascript:" id="btn-add-material" class="btn btn-primary">
              <span class="fa fa-plus-circle"></span> <?php echo lang('add'); ?>
            </a>
          </th>
        </tr>
      </thead>
      <tbody id="table-body">
        <?php
          $project_materials = isset($project_materials)?$project_materials:[];
          $create_material_request = count($project_materials)>0;
          if(isset($project_materials) && count($project_materials)){
            foreach($project_materials as $n=>$k){
              $project_id = $k->project_id;
              $project_name = $k->title;
        ?>
          <tr>
            <td style="width:20px;">
              <?php if(isset($k->result) && sizeof($k->result)){?>
                <a class="btn-expand" href="javascript:" data-row="<?= $n ?>">
                  <em class="fa fa-plus-circle"></em>
                </a>
              <?php }?>
            </td>
            <td style="font-weight:600;">
              <?= $project_name ?>
            </td>
            <td style="font-weight:600;">
              <?= !empty($k->mixing_name)? $k->mixing_name: '-' ?>
            </td>
            <td style="font-weight:600;">
              <?= to_decimal_format2($k->quantity).' '.$k->unit_type ?>
            </td>
            <td style="font-weight:600;"></td>
          </tr>
          <?php if(isset($k->result) && sizeof($k->result)){?>
            <tr class="row-target" data-row="<?= $n ?>">
              <td colspan="5">
                <div class="toggle-container">
                  <table class="display dataTable" cellspacing="0" width="100%">            
                    <thead>
                      <tr role="row">
                        <th class="w250"><?php echo lang('stock_material'); ?></th>
                        <th class="w200"><?php echo lang('stock_restock_name'); ?></th>
                        <th class="w125 text-right"><?php echo lang('quantity'); ?></th>
                        <?php if($can_read_price){?>
                          <th class="w150 text-right"><?php echo lang('stock_calculator_value'); ?></th>
                        <?php }?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php //var_dump($k->result);exit;?>
                      <?php $total = 0; foreach($k->result as $s){?>
                        <tr>
                          <td><?= $s->material_name ?></td>
                          <td><?= !empty($s->stock_name)? $s->stock_name: '-' ?></td>
                          <td class="w125 text-right">
                            <?php
                            $create_material_request = $create_material_request && ($s->ratio>0);
                              $s->ratio = floatval($s->ratio);
                              $classer = 'color-red lacked_material';
                              if($s->ratio > 0) $classer = 'color-green';
                              ///$lack = $s->noti_threshold-abs($s->remaining);
                              echo '<div class="'.$classer.'" data-project-id="'.$project_id.'" data-project-name="'.$project_name.'" data-material-id="'.$s->id.'" data-lacked-amount="'.abs($s->ratio).'" data-ratio="'.$s->ratio.'" data-unit="'.$s->material_unit.'" data-price="'.abs($s->price2/$s->ratio).'" data-supplier-id="'.$s->supplier_id.'" data-supplier-name="'.$s->supplier_name.'" data-currency="'.($s->currency?$s->currency:'THB').'" data-currency-symbol="'.($s->currency_symbol?$s->currency_symbol:'฿').'">'
                                  .to_decimal_format2($s->ratio).' '.$s->material_unit
                                .'</div>' 
                            ?>
                          </td>
                          <?php if($can_read_price){?>
                            <td class="w150 text-right">
                              <?php 
                                if(!empty($s->value)){
                                  $total += $s->value;
                                  echo to_currency($s->value);
                                }else echo '-';
                              ?>
                            </td>
                          <?php }?>
                        </tr>
                      <?php }?>
                    </tbody>
                    <?php if($can_read_price){?>
                      <tfoot>
                        <tr>
                          <th colspan="2"></th>
                          <th class="text-right"><?= lang('total') ?></th>
                          <td class="text-right"><?= to_currency($total) ?></td>
                        </tr>
                      </tfoot>
                    <?php }?>
                  </table>
                </div>
              </td>
            </tr>
          <?php }?>
        <?php }
          }?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>

    <?php if($this->Permission_m->create_material_request == true): ?>
        <button type="submit" class="btn btn-primary" id="btn-submit"><span class="fa fa-check-circle"></span> <?php echo lang('stock_save_make_mr'); ?></button>
    <?php endif; ?>

    <?php echo anchor(get_uri("pdf_export/project_materials_pdf/" . $model_info->id), "<i class='fa fa-download'></i> " . lang('download_pdf'), array("title" => lang('download_pdf'), "class"=>"btn btn-default")); ?>
    
    <!-- <button type="button" class="btn btn-default" id="btn-restock"><span class="fa fa-recycle" aria-hidden="true"></span> <?php // echo "คำนวณใหม่"; ?></button> -->
    
    <?php if($this->Permission_m->create_purchase_request == true): ?>
        <button type="button" class="btn btn-danger pull-right" id="btn-pr"><i class="fa fa-shopping-cart"></i> <?php echo lang('request_purchasing_materials'); ?></button>
    <?php endif; ?>
</div>

<?php echo form_close(); ?>

<style>
  a.back-to-index-btn{
    display:none;
  }
  table.dataTable > thead > tr:hover > th, 
  table.dataTable > thead > tr > th,
  table.dataTable > tbody > tr:hover > td,
  table.dataTable > tbody > tr > td{background-color:#ffffff!important;}
  table.dataTable tr > th{border-top:1px solid #f2f2f2!important;}
  .color-red{font-weight:600; color:red;}
  .color-green{font-weight:600; color:green;}
  select.inactive{opacity:0!important; pointer-events:none!important;}
  .btn-expand{font-size:18px;}
  tr.row-target{pointer-events:none;}
  tr.row-target > td{padding:0!important}
  tr.row-target .toggle-container{
    display:block; padding:15px 15px 15px 45px; background:#f8f8f8;
  }
</style>

<script type="text/javascript">
  $(document).ready(function () {
    jQuery('#btn-pr').on('click', function(){
      var materials = [];
      var lacked_materials = jQuery('.lacked_material');
      for(var i=0;i<lacked_materials.length;i++) {
        var material_id = jQuery(lacked_materials[i]).attr('data-material-id');
        var ratio = parseFloat(jQuery(lacked_materials[i]).attr('data-ratio'));
        var price = jQuery(lacked_materials[i]).attr('data-price');
        var amount = parseFloat(jQuery(lacked_materials[i]).attr('data-lacked-amount'));
        var unit = jQuery(lacked_materials[i]).attr('data-unit');
        var supplier_id = jQuery(lacked_materials[i]).attr('data-supplier-id');
        var supplier_name = jQuery(lacked_materials[i]).attr('data-supplier-name');
        var project_id = parseInt(jQuery(lacked_materials[i]).attr('data-project-id'));
        var project_name = jQuery(lacked_materials[i]).attr('data-project-name');
        project_name = project_name?project_name:"";
        var currency = jQuery(lacked_materials[i]).attr('data-currency');
        var currency_symbol = jQuery(lacked_materials[i]).attr('data-currency-symbol');
        if(materials[material_id]!=undefined) {
          materials[material_id].ratio = materials[material_id].ratio+ratio;
          materials[material_id].amount = amount>Math.abs(materials[material_id].ratio)?amount:Math.abs(materials[material_id].ratio);
        }else if(material_id) {
          materials[material_id] = {'id':material_id,'ratio':ratio,'amount':(amount>Math.abs(ratio)?amount:Math.abs(ratio)),'unit':unit,'price':price,'supplier_id':supplier_id,'supplier_name':supplier_name,'project_id':project_id,'project_name':project_name,'currency':currency,'currency_symbol':currency_symbol};
        }
      }
      materials = materials.filter(function(ele){
        if(ele!=null) return ele;
      });
      //alert(JSON.stringify(materials));
      var form = jQuery('<form id="add-pr-form" method="post" action="<?php echo_uri("purchaserequests/add_pr_material_to_cart");?>"></form>');
      jQuery.each( materials, function( key, material ) {
        form.append('<input type="hidden" name="materials['+key+'][id]" value="'+material.id+'" />');
        form.append('<input type="hidden" name="materials['+key+'][price]" value="'+material.price+'" />');
        form.append('<input type="hidden" name="materials['+key+'][amount]" value="'+material.amount+'" />');
        form.append('<input type="hidden" name="materials['+key+'][unit]" value="'+material.unit+'" />');
        form.append('<input type="hidden" name="materials['+key+'][supplier_id]" value="'+material.supplier_id+'" />');
        form.append('<input type="hidden" name="materials['+key+'][supplier_name]" value="'+material.supplier_name+'" />');
        form.append('<input type="hidden" name="materials['+key+'][project_id]" value="'+material.project_id+'" />');
        form.append('<input type="hidden" name="materials['+key+'][project_name]" value="'+material.project_name+'" />');
        form.append('<input type="hidden" name="materials['+key+'][currency]" value="'+material.currency+'" />');
        form.append('<input type="hidden" name="materials['+key+'][currency_symbol]" value="'+material.currency_symbol+'" />');
      });

      form.append('<?php $CI =& get_instance(); echo sprintf('<input type="hidden" name="%s" value="%s" />',$CI->security->get_csrf_token_name(),$CI->security->get_csrf_hash(),)?>');

      var parentform = jQuery('#btn-pr').closest('form');
      if(parentform.length>0)
        parentform.after(form);
      else
        jQuery('#btn-pr').after(form);
      jQuery('#add-pr-form').submit();
    });
 

    var items = <?= json_encode($items) ?>;
   
    var itemMixings = <?php echo json_encode($item_mixings) ?>;
    
    var typeContainer = $('#type-container');
    var tableBody = typeContainer.find('#table-body'),
        btnAdd = typeContainer.find('#btn-add-material');
    
    let setItem = `<?php echo count($project_materials); ?>`;

    btnAdd.click(function(e){
      e.preventDefault();

      // one item in a project
      let dataItem = document.querySelectorAll("[data_item]");
      if (dataItem.length) {
        return false;
      }

      if (setItem > 0) {
        return false;
      }
      
      tableBody.prepend(`
        <tr>
          <td></td>
          <td>
            <select name="item_id[]" class="form-control select-material" data_item required>
              <option value="" data-unit=""></option>
              ${items.map(function(d){
                return `<option value="${d.id}" data-unit="${d.unit_type}">${d.title}</option>`;
              })}
            </select>
          </td>
          <td>
            <select name="item_mixing[]" class="form-control inactive">
              <option value=""></option>
            </select>
          </td>
          <td class="w150">
            <div class="input-suffix">
              <input 
                type="number" name="quantity[]" required 
                class="form-control" min="0" step="0.0001" value="1" 
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
        let parent = self.closest('tr');
        parent.find('.input-tag').html(option.data('unit'));

        let options = itemMixings.filter(d => d.item_id == this.value);
        let mixingSelect = parent.find('[name="item_mixing[]"]');
        mixingSelect.val('');
        mixingSelect.find('option').remove();
        mixingSelect.append('<option value=""></option>');
        if(options.length){
          mixingSelect.removeClass('inactive');
          options.map(function(d){
            
            mixingSelect.append(`<option value="${d.id}">${d.name}</option>`);
          });
        }else{
          mixingSelect.addClass('inactive');
        }
      });
    }

    typeContainer.find('.btn-expand').click(function(e){
      e.preventDefault();
      var rowId = $(this).data('row');
      var row = typeContainer.find('tr[data-row="'+rowId+'"]');
      if(row.length) row.find('.toggle-container').slideToggle();
    });

    var itemForm = $("#item-form");
    itemForm.appForm({
			onSuccess: function (result) {
        console.log(result);

        if(result.addnew){
          url = "<?php echo get_uri(); ?>materialrequests/view/" + result.mrid
          window.open(url, '_blank');
        }
        
        appAlert.success(result.message, {duration: 10000});
        let projectId = itemForm.find('[name="id"]').val();
        setTimeout(function(){
          $(`body .bom-item-modal[data-act="ajax-modal"][data-post-id="${projectId}"]`).click();
        }, 1100);
			}
		});

    // restock
    // $('#btn-restock').click(function(e){
    //   e.preventDefault();
    //   $('[name="restock_process"]').val(1);
    //   $('#btn-submit').click();
    // });

  });
</script>
