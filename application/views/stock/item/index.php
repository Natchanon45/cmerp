<div id="page-content" class="p20 clearfix">
  <div class="panel panel-default">
    <div class="page-title clearfix">
      <h1>
        <a class="title-back" href="<?php echo get_uri('stock'); ?>">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </a>
        <?php echo lang('stock_items'); ?>
      </h1>
      <div class="title-button-group">
        <?php 
          if($can_create && $can_update){
            echo modal_anchor(
              get_uri("stock/item_import_modal"), 
              "<i class='fa fa-upload'></i> " . lang('stock_item_import'), 
              array("class" => "btn btn-default", "title" => lang('stock_item_import'))
            );
            echo modal_anchor(
              get_uri("stock/item_category_modal"), 
              "<i class='fa fa-tags'></i> " . lang('add_category'), 
              array("class" => "btn btn-default", "title" => lang('add_category'), "data-post-type" => "item")
            );
          }
          if($can_create){
            echo modal_anchor(
              get_uri("stock/item_modal"), 
              "<i class='fa fa-plus-circle'></i> ".lang('stock_item_add'), 
              array("class" => "btn btn-default", "title" => lang('stock_item_add'))
            );
          }
        ?>
      </div>
    </div>
    <div class="table-responsive">
      <table id="material-table" class="display" cellspacing="0" width="100%"></table>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#material-table").appTable({
      source: '<?php echo_uri("stock/item_list") ?>',
      filterDropdown: [
        {name: "category_id", class: "w200", options: <?php echo json_encode($category_dropdown); ?>}
      ],
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: "<?php echo lang('preview_image') ?> ", "class": "w100" },
        {title: '<?php echo lang("stock_item_code"); ?>', "class": ""},
        {title: '<?php echo lang("stock_item_name"); ?>', "class": ""},
        {title: '<?php echo lang("stock_item_barcode"); ?>',"class": "w200"},
        <?php if($can_read_production_name){?>
          {title: '<?php echo lang("stock_item_rate"); ?>', "class": "w100 text-center"},
        <?php }?>
        {title: '<?php echo lang("stock_item_category"); ?>', "class": ""},
        // {title: '<?php // echo lang("account_category"); ?>', "class": "w200"},
        {title: '<?php echo lang("description"); ?>'},
        
        {title: '<?php echo lang("stock_item_remaining"); ?>', "class": "text-right"},
        {title: '<?php echo lang("stock_item_unit"); ?>', "class": "w50 text-center"},
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
      ],
      <?php if(isset($is_admin) && $is_admin){?>
        <?php if($can_read_production_name){?>
          printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]),
        <?php }else{?>
          printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        <?php }?>
      <?php }?>
    });
  });
</script>