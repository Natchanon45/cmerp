<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('stock_item_pricings'); ?></h4>
    <div class="title-button-group">
      <?php
        if($can_update && $can_update_supplier){
          echo modal_anchor(
            get_uri("stock/item_pricing_modal"), 
            "<i class='fa fa-plus-circle'></i> " . lang('stock_item_pricing_add'), 
            array(
              "class" => "btn btn-default", 
              "title" => lang('stock_item_pricing_add'), 
              "data-post-item_id" => $item_id
            )
          );
        }
      ?>
    </div>
  </div>
  <div class="table-responsive">
    <table id="pricing-table" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#pricing-table").appTable({
      source: '<?php echo_uri("stock/item_pricing_list/".$item_id) ?>',
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("stock_supplier_name"); ?>'},
        {title: '<?php echo lang("stock_supplier_contact_name"); ?>'},
        {title: '<?php echo lang("stock_supplier_contact_phone"); ?>'},
        {title: '<?php echo lang("stock_supplier_contact_email"); ?>'},
        {title: '<?php echo lang("stock_material_quantity"); ?>'},
        {title: '<?php echo lang("price"); ?>'},
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
      ],
      <?php if(isset($is_admin) && $is_admin){?>
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7])
      <?php }?>
    });
  });
</script>
