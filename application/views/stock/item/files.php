<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('files'); ?></h4>
    <div class="title-button-group">
      <?php
        if($can_update){
          echo modal_anchor(
            get_uri("stock/item_file_modal"), 
            "<i class='fa fa-plus-circle'></i> " . lang('add_files'), 
            array(
              "class" => "btn btn-default", 
              "title" => lang('add_files'), 
              "data-post-item_id" => $item_id
            )
          );
        }
      ?>
    </div>
  </div>

  <div class="table-responsive">
    <table id="item-file-table" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#item-file-table").appTable({
      source: '<?php echo_uri("stock/item_file_list/" . $item_id) ?>',
      order: [[0, "desc"]],
      columns: [
        {title: '<?php echo lang("id") ?>'},
        {title: '<?php echo lang("file") ?>'},
        {title: '<?php echo lang("size") ?>'},
        {title: '<?php echo lang("uploaded_by") ?>'},
        {title: '<?php echo lang("created_date") ?>'},
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
      ]
    });
  });
</script>
