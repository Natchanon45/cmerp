<style type="text/css">
.pr-25px {
  padding-right: 25px !important;
}
</style>

<div class="panel">
  <div class="tab-title clearfix">
    <h4>
      <?php echo lang('stock_restock_item_used_list'); ?>
    </h4>
  </div>
  <div class="table-responsive">
    <table id="used-table" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#used-table").appTable({
      source: '<?php echo_uri("sfg/detail_item_used/" . $item_id); ?>',
      columns: [
        { title: '<?php echo lang("id"); ?>', "class": "text-center w50" },
        { title: 'ชื่อการนำเข้าสินค้ากึ่งสำเร็จ' },
        { title: '<?php echo lang("project_name"); ?>' },
        { title: 'เลขที่ใบเบิก' },
        { title: '<?php echo lang("used_date"); ?>', class: 'w125' },
        { title: '<?php echo lang("used_by"); ?>' },
        { title: '<?php echo lang("note_real"); ?>' },
        { title: '<?php echo lang("stock_restock_used_quantity"); ?>', class: 'w125 text-right' },
        { title: '<?php echo lang("stock_item_unit"); ?>', class: 'w80 text-right' },
        <?php if ($can_read_price) { ?>
            { title: '<?php echo lang("stock_restock_used_value"); ?>', class: 'w125 text-right' },
            { title: '<?php echo lang("currency"); ?>', class: 'w80 text-right pr-25px' }
        <?php } ?>
      ],
      order: [[0, 'desc']],
      <?php if ($can_read_price) { ?>
        printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7, 8, 9]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7, 8, 9]),
        summation: [
          { column: 6, dataType: 'number' },
          { column: 8, dataType: 'currency' }
        ]
      <?php } else { ?>
        printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7]),
        summation: [
          { column: 6, dataType: 'number' }
        ]
      <?php } ?>
    });
  });
</script>
