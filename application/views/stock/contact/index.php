<?php if (!$view_type == "list_view") { ?>
  <div class="panel">
    <div class="tab-title clearfix">
      <h4><?php echo lang('contacts'); ?></h4>
      <div class="title-button-group">
          <?php
            if($can_update){
              echo modal_anchor(
                get_uri("stock/supplier_contact_modal"), 
                "<i class='fa fa-plus-circle'></i> " . lang('add_contact'), 
                array(
                  "class" => "btn btn-default", 
                  "title" => lang('add_contact'), 
                  "data-post-supplier_id" => $supplier_id
                )
              );
            }
          ?>
      </div>
    </div>
    <div class="table-responsive">
      <table id="contact-table" class="display" width="100%"></table>
    </div>
  </div>
<?php } else { ?>
  <div class="table-responsive">
    <table id="contact-table" class="display" width="100%"></table>
  </div>
<?php } ?>



<script type="text/javascript">
  $(document).ready(function () {

    var showCompanyName = true;
    if ("<?php echo $supplier_id ?>") {
      showCompanyName = false;
    }

    var showOptions = true;
    // if (!"<?php // echo $can_edit_suppliers; ?>") {
    //   showOptions = false;
    // }

    $("#contact-table").appTable({
      source: '<?php echo_uri("stock/supplier_contact_list/" . $supplier_id) ?>',
      order: [[1, "asc"]],
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: "<?php echo lang("name") ?>", "class": "w150"},
        {visible: showCompanyName, title: "<?php echo lang("company_name") ?>", "class": "w150"},
        {title: "<?php echo lang("email") ?>", "class": "w20p"},
        {title: "<?php echo lang("phone") ?>", "class": "w100"},
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w50", visible: showOptions}
      ],
      <?php if(isset($is_admin) && $is_admin){?>
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4])
      <?php }?>
    });
  });
</script>