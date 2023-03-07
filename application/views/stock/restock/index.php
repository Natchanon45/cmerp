<div id="page-content" class="p20 clearfix">
  <div class="panel panel-default">
    <div class="page-title clearfix">
      <h1>
        <a class="title-back" href="<?php echo get_uri('stock'); ?>">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </a>
        <?php echo lang('stock_restocks'); ?>
      </h1>
      <div class="title-button-group">
        <?php 
          if($can_create){
            echo modal_anchor(
              get_uri("stock/restock_modal"), 
              "<i class='fa fa-plus-circle'></i> ".lang('stock_restock_add'), 
              array("class" => "btn btn-default", "title" => lang('stock_restock_add'))
            );
          }
        ?>
      </div>
    </div>
    <div class="table-responsive">
      <table id="restock-table" class="display" cellspacing="0" width="100%"></table>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#restock-table").appTable({
      source: '<?php echo_uri("stock/restock_list") ?>',
      filterDropdown: [
        <?php if($can_read){?>
          {name: "created_by", class: "w200", options: <?php echo $team_members_dropdown; ?>}
        <?php }?>
      ],
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("stock_restock_name"); ?>'},
        {title: '<?php echo lang("po_ref2"); ?>', "class": "w150 text-center"},
        {title: '<?php echo lang("created_by"); ?>', "class": "w150"},
        {title: '<?php echo lang("created_date"); ?>', "class": "w100"},
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
      ],
      order: [ 3, 'desc' ],
      <?php if(isset($is_admin) && $is_admin){?>
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3])
      <?php }?>
    });
  });
</script>