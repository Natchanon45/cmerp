<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('category'); ?></h4>
    <div class="title-button-group">
      <?php
        // if ($can_edit_suppliers) {
          echo modal_anchor(
            get_uri("items/detail_mixingcategory_modal"), 
            "<i class='fa fa-plus-circle'></i> " . lang('add_category'), 
            array(
              "class" => "btn btn-default", 
              "title" => lang('add_category'), 
              "data-post-item_id" => @$item_id
            )
          );
        // }
      ?>
    </div>
  </div>
  <div class="table-responsive">
    <table id="mixing-table-category" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#mixing-table-category").appTable({
      source: '<?php echo_uri("items/mixingcategories_list/".@$item_id) ?>',
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("category_name"); ?>'},
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
      ],
      printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4]),
      xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4])
    });
  });
</script>
