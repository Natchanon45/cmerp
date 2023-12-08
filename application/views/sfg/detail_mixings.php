<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('item_mixings'); ?></h4>
    <div class="title-button-group">
      <?php
        // if ($can_edit_suppliers) {
          echo modal_anchor(
            get_uri("sfg/detail_mixings_modal"), 
            "<i class='fa fa-plus-circle'></i> " . lang('item_mixing_add'), 
            array(
              "class" => "btn btn-default", 
              "title" => lang('item_mixing_add'), 
              "data-post-item_id" => $item_id
            )
          );
        // }
      ?>
    </div>
  </div>
  <div class="table-responsive">
    <table id="mixing-table" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    <?php if($this->Permission_m->access_semi_product_item_formula == true): ?>
      $("#mixing-table").appTable({
        source: '<?php echo current_url(); ?>',
        columns: [
          {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
          {title: '<?php echo lang("item_mixing_name"); ?>'},
          //{title: '<?php echo lang("category_name"); ?>'},
          {title: '<?php echo lang("item_mixing_ratio"); ?>', "class": "text-center w150"},
          {title: '<?php echo lang("item_mixing_is_public"); ?>', "class": "text-center w100"},
          {title: '<?php echo lang("item_mixing_for_client"); ?>', "class": "text-center w300"},
          {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
        ],
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4])
      });
    <?php else: ?>
      location.href = "<?php echo get_uri(); ?>";
    <?php endif; ?>
  });
</script>
