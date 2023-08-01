<?php echo form_open(get_uri("projects/project_items_save"), array("id" => "item-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
  <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
  <div id="type-container">
    <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">      
      <?php
        $project_materials = isset($project_materials) && $project_materials ? $project_materials : array();
        $create_material_request = count($project_materials) > 0;
      ?>      
      <thead>
        <tr role="row">
          <th style="width: 20px; height: 32px; background: #f5f5f5 !important;"></th>
          <th class="w250" style="background: #f5f5f5 !important;"><?php echo lang('item'); ?></th>
          <th class="w250" style="background: #f5f5f5 !important;"><?php echo lang('item_mixing_name'); ?></th>
          <th class="w150 text-right" style="background: #f5f5f5 !important;"><?php echo lang('quantity'); ?></th>
          <th class="w70 text-center" style="background: #f5f5f5 !important;">
          <?php if (!$create_material_request): ?>
            <a href="javascript:void();" id="btn-add-material" class="btn btn-primary w80p">
              <span class="fa fa-plus-circle"></span>
              <?php echo lang('add'); ?>
            </a>
          <?php endif; ?>
          </th>
        </tr>
      </thead>
      <tbody id="table-body">
        <?php if(isset($project_materials) && $create_material_request): ?>
          <?php foreach($project_materials as $n => $k): ?>
            <tr>
              <td style="width: 47px; padding: 0px; margin: 0px;" class="text-center">
                <?php if(isset($k->result) && sizeof($k->result)): ?>
                  <a href="javascript:void();" class="btn-expand" data-row="<?php echo $n; ?>"><em class="fa fa-plus-circle"></em></a>
                <?php endif; ?>
              </td>
              <td style="font-weight: 600;"><?php echo $k->title; ?></td>
              <td style="font-weight: 600;"><?php echo isset($k->mixing_name) && $k->mixing_name ? $k->mixing_name : '-'; ?></td>
              <td style="font-weight: 600;" class="text-right"><?php echo isset($k->quantity) && $k->quantity ? to_decimal_format2($k->quantity) . ' ' . strtoupper($k->unit_type) : ''; ?></td>
              <td></td>
            </tr>
            <?php if(isset($k->result) && sizeof($k->result)): ?>
              <tr class="row-target" role="row" data-row="<?php echo $n; ?>">
                <td colspan="5">
                  <div class="toggle-container">
                    <table class="display dataTable" cellspacing="0" width="100%" style="font-size: small;">
                      <thead>
                        <tr role="row">
                          <th class="w30p"><?php echo lang('stock_material'); ?></th>
                          <th class="w25p"><?php echo lang('stock_restock_name'); ?></th>
                          <th class="w15p text-right"><?php echo lang('quantity'); ?></th>
                          <?php if($can_read_price): ?>
                            <th class="w15p text-right"><?php echo lang('stock_calculator_value'); ?></th>
                          <?php endif; ?>
                          <th class="w15p text-center"><?php echo lang('status_material_request'); ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $total = 0; foreach($k->result as $s): ?>
                          <tr>
                            <td class="nowrap-name"><?php echo $can_read_material_name && $s->material_name ? $s->material_name . ' - ' . $s->material_desc : $s->material_name; ?></td>
                            <td class="nowrap-restock"><?php echo isset($s->stock_name) && $s->stock_name ? $s->stock_name : '-'; ?></td>
                            <td class="text-right">
                              <?php
                                $s_ratio = floatval($s->ratio);
                                $s_class = "color-red lacked_material";
                                if($s_ratio > 0) {
                                  $s_class = "color-green";
                                }
                              ?>
                              <div 
                                class="<?php echo $s_class; ?>" data-project-id="<?php echo $k->project_id; ?>" data-project-name="<?php echo $k->title; ?>" 
                                data-material-id="<?php echo $s->id; ?>" data-lacked-amount="<?php echo abs($s_ratio); ?>" data-ratio="<?php echo $s_ratio; ?>" 
                                data-unit="<?php echo $s->material_unit; ?>" data-price="<?php echo abs($s->price2/$s_ratio); ?>" 
                                data-supplier-id="<?php echo $s->supplier_id; ?>" data-supplier-name="<?php echo $s->supplier_name; ?>" 
                                data-currency="<?php echo isset($s->currency) && $s->currency ? $s->currency : 'THB'; ?>" 
                                data-currency-symbol="<?php echo isset($s->currency_symbol) && $s->currency_symbol ? $s->currency_symbol : '฿'; ?>"
                              >
                                <?php echo to_decimal_format2($s_ratio) . ' ' . strtoupper($s->material_unit); ?>
                              </div>
                            </td>
                            <?php if($can_read_price): ?>
                              <td class="text-right">
                                <?php
                                  if(isset($s->value) && $s->value) {
                                    $total += $s->value;
                                    echo to_currency($s->value, lang('THB'));
                                  } else {
                                    echo '-';
                                  }
                                ?>
                              </td>
                            <?php endif; ?>
                            <td class="text-center mr_view">
                              <?php echo isset($s->mr_id) && !empty($s->mr_id) ? anchor(get_uri("materialrequests/view/" . $s->mr_id), $s->mr_doc, array("title" => $s->mr_doc, "target" => "_blank")) : '-'; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                      <?php if($can_read_price): ?>
                        <tfoot>
                          <tr>
                            <td colspan="2"></td>
                            <td class="text-right"><strong><?php echo lang('total'); ?></strong></td>
                            <td class="text-right"><strong><?php echo to_currency($total, lang('THB')); ?></strong></td>
                            <td></td>
                          </tr>
                        </tfoot>
                      <?php endif; ?>
                    </table>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <!-- If the logged in user have permission to create a material request document -->
    <?php if ($this->Permission_m->create_material_request): ?>
      
      <!-- If the document has not been saved before. -->
      <?php if (!$create_material_request): ?>
        <button type="submit" class="btn btn-info" id="btn-submit" name="save_type_id" value="0">
          <span class="fa fa-check-circle"></span>
          <?php echo ' ' . lang('save'); ?>
        </button>
      <?php endif; ?>
      
      <!-- If the material request document have been created -->
      <?php if ($create_material_request): ?>
        <?php if ($can_create_mr): ?>
          <button type="submit" class="btn btn-primary" id="btn-submit" name="save_type_id" value="1">
            <span class="fa fa-book"></span>
            <?php echo " " . lang('create_matreq'); ?>
          </button>
          <!-- btn-req-create -->
        <?php endif; ?>

        <?php if ($can_recalc): ?>
          <button type="submit" class="btn btn-warning" id="btn-submit" name="save_type_id" value="2">
            <span class="fa fa-refresh"></span>
            <?php echo " " . lang('re_calc_stock'); ?>
          </button>
          <!-- btn-re-calc -->
        <?php endif; ?>
      <?php endif; ?>

    <?php endif; ?>
    
    <!-- If project item material has been saved -->
    <?php
    if ($create_material_request) {
      echo anchor(
        get_uri("pdf_export/project_materials_pdf/" . $model_info->id),
        "<i class='fa fa-download'></i> " . lang('download_pdf'),
        array(
          "title" => lang('download_pdf'),
          "class" => "btn btn-default",
          "target" => "_blank"
        )
      ); // btn-download
    }
    ?>

    <!-- If have permission to create purchase request and lower material requirement -->
    <!-- <?php // if ($this->Permission_m->create_purchase_request && $can_recalc): ?>
      <button type="button" class="btn btn-danger pull-right" id="btn-create-pr">
        <i class="fa fa-shopping-cart"></i>
        <?php // echo ' ' . lang('request_purchasing_materials'); ?>
      </button>
    <?php // endif; ?> -->
    <!-- btn-create-pr -->
</div>
<?php echo form_close(); ?>

<style type="text/css">
a.back-to-index-btn {
  display: none;
}
  
table.dataTable > thead > tr:hover > th, 
table.dataTable > thead > tr > th, 
table.dataTable > tbody > tr:hover > td, 
table.dataTable > tbody > tr > td {
  background-color: #ffffff !important;
}

table.dataTable tr > th {
  border-top: 1px solid #f2f2f2 !important;
}

.color-red {
  font-weight: 600; 
  color: red;
}
  
.color-green {
  font-weight: 600; 
  color: green;
}
  
select.inactive {
  opacity: 0 !important; 
  pointer-events: none !important;
}
  
.btn-expand {
  font-size: 18px;
}

tr.row-target {
  pointer-events: none|auto;
}
  
tr.row-target > td {
  padding: 0 !important;
}
  
tr.row-target .toggle-container {
  display: block; 
  background: #f8f8f8;
}

.toggle-container {
  padding: 15px 15px 15px 57.5px !important;
}

.nowrap-name {
  max-width: 303px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nowrap-restock {
  max-width: 242px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.string-upper {
  text-transform: uppercase;
}

@media (min-width: 999px) {
  .modal-dialog {
    width: 1080px;
  }
}
</style>

<script type="text/javascript">
  $(document).ready(function () {
    jQuery('#btn-pr').on('click', function () {
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
        project_name = project_name ? project_name : "";
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
      // alert(JSON.stringify(materials));

      var form = jQuery('<form id="add-pr-form" method="post" action="<?php echo_uri("purchaserequests/add_pr_material_to_cart"); ?>"></form>');
      jQuery.each(materials, function (key, material) {
        form.append('<input type="hidden" name="materials[' + key + '][id]" value="' + material.id + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][price]" value="' + material.price + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][amount]" value="' + material.amount + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][unit]" value="' + material.unit + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][supplier_id]" value="' + material.supplier_id + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][supplier_name]" value="' + material.supplier_name + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][project_id]" value="' + material.project_id + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][project_name]" value="' + material.project_name + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][currency]" value="' + material.currency + '" />');
        form.append('<input type="hidden" name="materials[' + key + '][currency_symbol]" value="' + material.currency_symbol + '" />');
      });

      form.append('<?php $CI =& get_instance();
      echo sprintf('<input type="hidden" name="%s" value="%s" />', $CI->security->get_csrf_token_name(), $CI->security->get_csrf_hash(), ) ?>');

      var parentform = jQuery('#btn-pr').closest('form');
      if (parentform.length > 0)
        parentform.after(form);
      else
        jQuery('#btn-pr').after(form);
      jQuery('#add-pr-form').submit();
    });
    
    var items = <?= json_encode($items) ?>;
    var itemMixings = <?php echo json_encode($item_mixings) ?>;
    
    var typeContainer = $('#type-container');
    var tableBody = typeContainer.find('#table-body'), btnAdd = typeContainer.find('#btn-add-material');
    
    let setItem = `<?php echo count($project_materials); ?>`;
    if (setItem > 0) {
      btnAdd.addClass('hide');
    }

    btnAdd.click(function (e) {
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
              <div class="input-tag string-upper"></div>
            </div>
          </td>
          <td class="text-center">
            <a href="javascript:void();" class="btn btn-danger btn-delete-material w80p">
              <span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
            </a>
          </td>
        </tr>
      `);
      processBinding();
    });
    processBinding();

    function processBinding() {
      let dataItem = document.querySelectorAll("[data_item]");
      if (dataItem.length) {
        typeContainer.find('#btn-add-material').addClass('hide');
      } else {
        typeContainer.find('#btn-add-material').removeClass('hide');
      } 

      typeContainer.find('.btn-delete-material').unbind();
      typeContainer.find('.btn-delete-material').click(function (e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        processBinding();
      });
      
      typeContainer.find('.select-material').select2('destroy');
      typeContainer.find('.select-material').select2();
      
      typeContainer.find('.select-material').unbind();
      typeContainer.find('.select-material').change(function () {
        let self = $(this);
        let option = $(this).find('[value="' + this.value + '"]');
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

    typeContainer.find('.btn-expand').click(function (e) {
      e.preventDefault();
      var rowId = $(this).data('row');
      var row = typeContainer.find('tr[data-row="'+rowId+'"]');
      if (row.length) row.find('.toggle-container').slideToggle();
    });

    var itemForm = $("#item-form");
    itemForm.appForm({
			onSuccess: function (result) {
        // console.log(result);
        appAlert.success(result.message, { duration: 3101 });

        let btnProjectBag = $('body').find(`.bom-item-modal[data-act="ajax-modal"][data-post-id="${result.data.id}"]`);
        setTimeout(function () {
          btnProjectBag.click();
        }, 1101);

        if(result.data.new_mr){
          url = "<?php echo get_uri(); ?>materialrequests/view/" + result.data.new_mr;
          window.open(url, '_blank');
        }
			}
		});

    // $('#btn-create-pr').on('click', function(e) {
    //   let url = '<?php // echo get_uri('purchaserequests/pr_project/' . $model_info->id); ?>';
    //   window.open(url, '_blank');
    // });
  });
</script>
