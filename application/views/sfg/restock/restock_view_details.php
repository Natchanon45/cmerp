<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('stock_restock_item_list'); ?></h4>
    <div class="title-button-group">
      <?php
      if ($can_create) {
        echo modal_anchor(
          get_uri("sfg/restock_item_details_modal_addedit"),
          "<i class='fa fa-plus-circle'></i> " . lang('stock_restock_item_add'),
          array("class" => "btn btn-default", "title" => "เพิ่มการนำเข้าสินค้ากึ่งสำเร็จ", "data-post-group_id" => $restock_id)
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
      source: '<?php echo_uri("sfg/restock_item_details/" . $restock_id) ?>',
      columns: [
        { title: '<?php echo lang('id') ?>', class: 'text-center w50' },
        { title: 'รายการสินค้ากึ่งสำเร็จ' },
        { title: '<?php echo lang('serial_number'); ?>' },
        { title: '<?php echo lang('files'); ?>', class: 'w125' },
        { title: '<?php echo lang('expiration_date'); ?>', class: 'w125' },
        { title: '<?php echo lang('stock_restock_quantity'); ?>', class: 'w125 text-right' },
        { title: '<?php echo lang('stock_material_remaining'); ?>', class: 'w125 text-right' },
        { title: '<?php echo lang('stock_material_unit'); ?>', class: 'w60 text-right' },
        <?php if ($can_read_price): ?>
          { title: '<?php echo lang('stock_restock_price'); ?>', class: 'w125 text-right' },
          { title: '<?php echo lang('stock_restock_remining_value'); ?>', class: 'w125 text-right' },
          { title: '<?php echo lang('currency'); ?>', class: 'w60 text-right' },
        <?php endif; ?>
        { title: '<i class="fa fa-bars"></i>', class: 'text-center option w125' }
      ],
      <?php if ($can_read_price): ?>
          printColumns: combineCustomFieldsColumns([0, 1, 2, 4, 5, 6, 7, 8, 9, 10]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 2, 4, 5, 6, 7, 8, 9, 10]),
          summation: [
            { column: 5, dataType: 'number' },
            { column: 6, dataType: 'number' },
            { column: 8, dataType: 'currency' },
            { column: 9, dataType: 'currency' }
          ]
      <?php else: ?>
          printColumns: combineCustomFieldsColumns([0, 1, 2, 4, 5, 6, 7]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 2, 4, 5, 6, 7]),
          summation: [
            { column: 5, dataType: 'number' },
            { column: 6, dataType: 'number' }
          ]
      <?php endif; ?>
    });
  });
</script>

<!-- done -->