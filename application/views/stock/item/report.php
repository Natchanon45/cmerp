<div id="page-content" class="p20 clearfix">
  <div class="panel panel-default">
    <div class="page-title clearfix">
      <h1>
        <a class="title-back" href="<?php echo get_uri('stock'); ?>">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </a>
        <?php echo lang('stock_item_report'); ?>
      </h1>
      <?php if($add_pr_row) {?>
      <button type="button" class="btn btn-warning pull-right" id="btn-pr">
        <i class="fa fa-shopping-cart"></i> <?php echo lang('request_low_item'); ?>
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
      var item = [];
      var lacked_materials = jQuery('.lacked_material');
      for(var i=0;i<lacked_materials.length;i++) {
        var item_id = jQuery(lacked_materials[i]).attr('data-item-id');
        var price = jQuery(lacked_materials[i]).attr('data-price');
        var amount = parseFloat(jQuery(lacked_materials[i]).attr('data-lacked-amount'));
        var unit = jQuery(lacked_materials[i]).attr('data-unit');
        var supplier_id = jQuery(lacked_materials[i]).attr('data-supplier-id');
        var supplier_name = jQuery(lacked_materials[i]).attr('data-supplier-name');
        var currency = jQuery(lacked_materials[i]).attr('data-currency');
        var currency_symbol = jQuery(lacked_materials[i]).attr('data-currency-symbol');
        if(item[item_id]!=undefined) {
          item[item_id].amount = amount;
        }else if(item_id) {
          item[item_id] = {'id':item_id,'amount':amount,'unit':unit,'price':price,'supplier_id':supplier_id,'supplier_name':supplier_name,'currency':currency,'currency_symbol':currency_symbol};
        }
      }
      item = item.filter(function(ele){
        if(ele!=null) return ele;
      });
      //alert(JSON.stringify(materials));
      var form = jQuery('<form id="add-pr-form" method="post" action="<?php echo_uri("purchaserequests/add_pr_item_to_cart");?>"></form>');
      jQuery.each( item, function( key, item ) {
        form.append('<input type="hidden" name="item['+key+'][id]" value="'+item.id+'" />');
        form.append('<input type="hidden" name="item['+key+'][price]" value="'+item.price+'" />');
        form.append('<input type="hidden" name="item['+key+'][amount]" value="'+item.amount+'" />');
        form.append('<input type="hidden" name="item['+key+'][unit]" value="'+item.unit+'" />');
        form.append('<input type="hidden" name="item['+key+'][supplier_id]" value="'+item.supplier_id+'" />');
        form.append('<input type="hidden" name="item['+key+'][supplier_name]" value="'+item.supplier_name+'" />');
        form.append('<input type="hidden" name="item['+key+'][currency]" value="'+item.currency+'" />');
        form.append('<input type="hidden" name="item['+key+'][currency_symbol]" value="'+item.currency_symbol+'" />');
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
      source: '<?php echo_uri("stock/item_report_list/") ?>',
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("stock_item_name"); ?>'},
        {title: '<?php echo lang("stock_item"); ?>'},
        {title: '<?php echo lang("created_date"); ?>', class: 'w90'},
        {title: '<?php echo lang("expiration_date"); ?>', class: 'w90'},
        {title: '<?php echo lang("stock_restock_quantity"); ?>', class: 'w110 text-right'},
        {title: '<?php echo lang("stock_item_remaining"); ?>', class: 'w110 text-right'},
        <?php if($can_read_price){?>
          {title: '<?php echo lang("stock_restock_price"); ?>', class: 'w110 text-right'},
          {title: '<?php echo lang("stock_restock_remining_value"); ?>', class: 'w110 text-right'},
        <?php }?>
        // {title: '<i class="fa fa-bars"></i>', "class": "text-center option w125"}
      ],
      <?php if($can_read_price){?>
        <?php if(isset($is_admin) && $is_admin){?>
          printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8]),
        <?php }?>
        summation: [
          {column: 7, dataType: 'currency'},
          {column: 8, dataType: 'currency'}
        ],
      <?php }else{?>
        <?php if(isset($is_admin) && $is_admin){?>
          printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]),
        <?php }?>
      <?php }?>
    });
  });
</script>