<div class="panel">
  <div class="tab-title clearfix">
    <h4><?php echo lang('stock_restock_used_list'); ?></h4>
  </div>
  <div class="table-responsive">
    <table id="used-table" class="display" width="100%"></table>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $("#used-table").appTable({
      source: '<?php echo_uri("stock/restock_used_list/".$restock_id) ?>',
      columns: [
        {title: "<?php echo lang("id") ?>", "class": "text-center w50"},
        {title: '<?php echo lang("stock_material"); ?>'},
        {title: '<?php echo lang("project"); ?>'},
        {title: '<?php echo lang("date"); ?>', class: 'w150'},
        {title: '<?php echo lang("note_real"); ?>'},
        {title: '<?php echo lang("stock_restock_used_quantity"); ?>', class: 'w125 text-right'},
        {title: '<?php echo lang("stock_material_unit"); ?>', class: 'w125 text-right'},
        <?php if($can_read_price){?>
          {title: '<?php echo lang("stock_restock_used_value"); ?>', class: 'w125 text-right'},
          {title: '<?php echo lang("currency"); ?>', class: 'w125 text-right'}
        <?php }?>
      ],
      order: [[ 1, 'asc' ]],
      <?php if($can_read_price){?>
        <?php if(isset($is_admin) && $is_admin){?>
          printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7, 8]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6, 7, 8]),
        <?php }?>
        summation: [
          {column: 7, dataType: 'currency'}
        ]
      <?php }else{?>
        <?php if(isset($is_admin) && $is_admin){?>
          printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6]),
          xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5, 6])
        <?php }?>
      <?php }?>
    });
  });
</script>
