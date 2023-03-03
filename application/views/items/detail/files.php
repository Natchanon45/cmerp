<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('files'); ?></h4>
    <div class="title-button-group">
      <?php
        // if ($can_edit_suppliers) {
          echo modal_anchor(
            get_uri("items/detail_file_modal/".@$item_id), 
            "<i class='fa fa-plus-circle'></i> " . lang('add_file'), 
            array(
              "class" => "btn btn-default", 
              "title" => lang('add_file'), 
              "data-post-item_id" => @$item_id
            )
          );
        // }
      ?>
    </div>
  </div>
  <div class="table-responsive">
    <table id="mixing-table-file" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#mixing-table-file").appTable({
      source: '<?php echo_uri("items/file_list/".@$item_id) ?>',
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("file_name"); ?>'},
        {title: '<?php echo lang("size"); ?>'},
        {title: '<?php echo lang("download"); ?>'},
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
      ],
      printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4]),
      xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4])
    });
  });
</script>
