<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('stock_restock_list'); ?></h4>
    <div class="title-button-group">
      <?php 
        if($can_update){
          echo modal_anchor(
            get_uri("stock/restock_view_modal"), 
            "<i class='fa fa-plus-circle'></i> ".lang('stock_material_add'), 
            array(
              "class" => "btn btn-default", 
              "title" => lang('stock_material_add'), 
              "data-post-group_id" => $restock_id
            )
          );
        }
      ?>
    </div>
  </div>
  <div class="table-responsive">
    <table id="remaining-table" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#remaining-table").appTable({
      source: '<?php echo_uri("stock/restock_view_list/".$restock_id) ?>',
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("stock_material"); ?>'},
        {title: '<?php echo lang("files"); ?>', class: 'w125'},
        {title: '<?php echo lang("expiration_date"); ?>', class: 'w125'},
        {title: '<?php echo lang("stock_restock_quantity"); ?>', class: 'w125 text-right'},
        {title: '<?php echo lang("stock_material_remaining"); ?>', class: 'w125 text-right'},
        <?php if($can_read_price){?>
          {title: '<?php echo lang("stock_restock_price"); ?>', class: 'w125 text-right'},
          {title: '<?php echo lang("stock_restock_remining_value"); ?>', class: 'w125 text-right'},
        <?php }?>
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w125"}
      ],
      <?php if($can_read_price){?>
        <?php if(isset($is_admin) && $is_admin){?>
          printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7]),
        <?php }?>
        summation: [
          {column: 6, dataType: 'currency'}, 
          {column: 7, dataType: 'currency'}
        ]
      <?php }else{?>
        <?php if(isset($is_admin) && $is_admin){?>
          printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5])
        <?php }?>
      <?php }?>
    });
  });
</script>
