<div id="page-content" class="p20 clearfix">
  <div class="panel panel-default">
    <div class="page-title clearfix">
      <h1>
        <a class="title-back" href="<?php echo get_uri('stock'); ?>">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </a>
        <?php echo lang('stock_material_report'); ?>
      </h1>
      <?php if($add_pr_row) {?>
      <button type="button" class="btn btn-warning pull-right" id="btn-pr">
        <i class="fa fa-shopping-cart"></i> <?php echo lang('request_low_materials'); ?>
      </button>
      <?php } ?>
    </div>
    <div class="table-responsive">
      <table id="report-table" class="display" cellspacing="0" width="100%"></table>
    </div>
  </div>
</div>
<style>
.lacked_material{
  margin:0;
  padding:0;
  display:inline-block;
  /*background-color:orange;*/
  color:orange;
  font-weight:bold;
}
</style>
<script type="text/javascript">
  $(document).ready(function () {
    jQuery('#btn-pr').on('click', function() {
      var materials = [];
      var lacked_materials = jQuery('.lacked_material');
      for(var i=0;i<lacked_materials.length;i++) {
        var material_id = jQuery(lacked_materials[i]).attr('data-material-id');
        var price = jQuery(lacked_materials[i]).attr('data-price');
        var amount = parseFloat(jQuery(lacked_materials[i]).attr('data-lacked-amount'));
        var unit = jQuery(lacked_materials[i]).attr('data-unit');
        var supplier_id = jQuery(lacked_materials[i]).attr('data-supplier-id');
        var supplier_name = jQuery(lacked_materials[i]).attr('data-supplier-name');
        var currency = jQuery(lacked_materials[i]).attr('data-currency');
        var currency_symbol = jQuery(lacked_materials[i]).attr('data-currency-symbol');
        if(materials[material_id]!=undefined) {
          materials[material_id].amount = amount;
        }else if(material_id) {
          materials[material_id] = {'id':material_id,'amount':amount,'unit':unit,'price':price,'supplier_id':supplier_id,'supplier_name':supplier_name,'currency':currency,'currency_symbol':currency_symbol};
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
        form.append('<input type="hidden" name="materials['+key+'][currency]" value="'+material.currency+'" />');
        form.append('<input type="hidden" name="materials['+key+'][currency_symbol]" value="'+material.currency_symbol+'" />');
      });
      form.append('<?php
      $CI =& get_instance();
      echo sprintf(
				'<input type="hidden" name="%s" value="%s" />',
				$CI->security->get_csrf_token_name(),
				$CI->security->get_csrf_hash(),
			);
      ?>');
      var parentform = jQuery('#btn-pr').closest('form');
      if(parentform.length>0)
        parentform.after(form);
      else
        jQuery('#btn-pr').after(form);
      jQuery('#add-pr-form').submit();
    });

    $("#report-table").appTable({
      source: '<?php echo_uri("stock/material_report_list") ?>',
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w10"},
        {title: '<?php echo lang("stock_restock_name"); ?>'},
        {title: '<?php echo lang("stock_material"); ?>'},
        {title: '<?php echo lang("created_date"); ?>', class: 'w10'},
        {title: '<?php echo lang("expiration_date"); ?>', class: 'w10'},
        {title: '<?php echo lang("stock_restock_quantity"); ?>', class: 'w10 text-right'},
        {title: '<?php echo lang("stock_material_remaining"); ?>', class: 'w10 text-right'},
        {title: '<?php echo lang("stock_material_unit"); ?>', class: 'w10 text-right'},
        <?php if ($can_read_price) :?>
          {title: '<?php echo lang("stock_restock_price"); ?>', class: 'w20 text-right'},
          {title: '<?php echo lang("stock_restock_remining_value"); ?>', class: 'w20 text-right'},
          {title: '<?php echo lang("currency"); ?>', class: 'w20 text-right'},
        <?php endif; ?>
        // {title: '<i class="fa fa-bars"></i>', "class": "text-center option w125"}
      ],
      <?php if ($can_read_price) :?>
        <?php if (isset($is_admin) && $is_admin) :?>
          printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
          summation: [
            {column: 8, dataType: 'currency'},
            {column: 9, dataType: 'currency'}
          ],
        <?php endif; ?>
      <?php else: ?>
        <?php if (isset($is_admin) && $is_admin) :?>
          printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]),
        <?php endif; ?>
      <?php endif; ?>
    });
  });
</script>