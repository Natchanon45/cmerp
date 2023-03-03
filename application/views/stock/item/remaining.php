<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('stock_item_remaining'); ?></h4>
  </div>
  <div class="table-responsive">
    <table id="remaining-table" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#remaining-table").appTable({
      source: '<?php echo_uri("stock/item_remaining_list/".$item_id) ?>',
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("stock_restock_item_name"); ?>'},
        {title: '<?php echo lang("created_by"); ?>'},
        {title: '<?php echo lang("created_date"); ?>', class: 'w90'},
        {title: '<?php echo lang("expiration_date"); ?>', class: 'w90'},
        {title: '<?php echo lang("stock_restock_quantity"); ?>', class: 'w110 text-right'},
        {title: '<?php echo lang("stock_item_remaining"); ?>', class: 'w110 text-right'},
        <?php if($can_read_price){?>
          {title: '<?php echo lang("stock_restock_price"); ?>', class: 'w110 text-right'},
          {title: '<?php echo lang("stock_restock_remining_value"); ?>', class: 'w110 text-right'},
        <?php }?>
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w125"}
        
      ],
      order: [ 4, 'desc' ],
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
