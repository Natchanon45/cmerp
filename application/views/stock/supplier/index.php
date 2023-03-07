<div id="page-content" class="p20 clearfix">
  <div class="panel panel-default">
    <div class="page-title clearfix">
      <h1>
        <a class="title-back" href="<?php echo get_uri('stock'); ?>">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </a>
        <?php echo lang('stock_suppliers'); ?>
      </h1>
      <?php if($can_create){?>
        <div class="title-button-group">
          <?php 
            echo modal_anchor(
              get_uri("stock/supplier_modal"), 
              "<i class='fa fa-plus-circle'></i> ".lang('stock_supplier_add'), 
              array("class" => "btn btn-default", "title" => lang('stock_supplier_add'))
            );
          ?>
        </div>
      <?php }?>
    </div>
    <div class="table-responsive">
      <table id="supplier-table" class="display" cellspacing="0" width="100%">            
      </table>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#supplier-table").appTable({
      source: '<?php echo_uri("stock/supplier_list") ?>',
      filterDropdown: [
        <?php if(isset($team_members_dropdown)){ ?>
          {name: "owner_id", class: "w200", options: <?php echo $team_members_dropdown; ?>}
        <?php }?>
      ],
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("stock_code_supplier"); ?>', "class": "w250"},
        {title: '<?php echo lang("stock_supplier_name"); ?>', "class": "w250"},
        {title: '<?php echo lang("stock_supplier_address"); ?>'},
        {title: '<?php echo lang("stock_supplier_contact_name"); ?>', "class": "w200"},
        {title: '<?php echo lang("stock_supplier_contact_phone"); ?>', "class": "w100"},
        {title: '<?php echo lang("stock_supplier_contact_email"); ?>', "class": "w100"},
        {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
      ],
      <?php if(isset($is_admin) && $is_admin){?>
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5])
      <?php }?>
    });
  });
</script>