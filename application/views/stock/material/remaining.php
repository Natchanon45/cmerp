<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('stock_restock_list'); ?></h4>
  </div>
  <div class="table-responsive">
    <table id="remaining-table" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#remaining-table").appTable({
      source: '<?php echo_uri("stock/material_remaining_list/" . $material_id) ?>',
      filterDropdown: [
				{ name: "is_zero", options: [ { "id": 0, "text": "<?php echo lang("remain_only"); ?>" }, { "id": 1, "text": "<?php echo lang("all_add_stock"); ?>" } ], class: "w150" }
			],
      columns: [
        { title: '<?php echo lang("id"); ?>', class: "text-center w50" },
        { title: '<?php echo lang("stock_restock_name"); ?>' },
        { title: '<?php echo lang('serial_number'); ?>' },
        { title: '<?php echo lang("created_by"); ?>' },
        { title: '<?php echo lang("created_date"); ?>', class: 'w90' },
        { title: '<?php echo lang("expiration_date"); ?>', class: 'w90' },
        { title: '<?php echo lang("stock_restock_quantity"); ?>', class: 'w125 text-right' },
        { title: '<?php echo lang("stock_material_remaining"); ?>', class: 'w125 text-right' },
        { title: '<?php echo lang("stock_material_unit"); ?>', class: 'w80 text-right' },
        <?php if ($can_read_price) { ?>
          { title: '<?php echo lang("stock_restock_price"); ?>', class: 'w125 text-right' },
          { title: '<?php echo lang("stock_restock_remining_value"); ?>', class: 'w125 text-right' },
          { title: '<?php echo lang("currency"); ?>', class: 'w80 text-right' },
        <?php } ?>
        { title: '<i class="fa fa-bars"></i>', "class": "text-center option" }
      ],
      order: [0, 'asc'],
      <?php if ($can_read_price) { ?>
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
        summation: [
          { column: 6, dataType: 'number' },
          { column: 7, dataType: 'number' },
          { column: 9, dataType: 'currency' },
          { column: 10, dataType: 'currency' }
        ]
      <?php } else { ?>
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7]),
        summation: [
          { column: 6, dataType: 'number' },
          { column: 7, dataType: 'number' }
        ]
      <?php } ?>
    });
  });
</script>
