<div id="page-content" class="p20 clearfix">
    <div class="panel panel-default">
        <div class="page-title clearfix">
            <h1>
                <a class="title-back" href="<?php echo get_uri('stock'); ?>">
                    <i class="fa fa-chevron-left" aria-hidden="true"></i>
                </a>
                <?php echo lang('stock_restocks_item'); ?>
            </h1>
            <div class="title-button-group">
                <?php
                if ($can_create) {
                    echo modal_anchor(
                        get_uri("stock/restock_item_modal"),
                        "<i class='fa fa-plus-circle'></i> " . lang('stock_restock_item_add'),
                        array("class" => "btn btn-default", "title" => lang('stock_restock_item_add'))
                    );
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="restock-table" class="display" cellspacing="0" width="100%"></table>
        </div>
    </div>
</div>

<style type="text/css">
#restock-table {
    font-size: small;
}
</style>

<script type="text/javascript">
    $(document).ready(function () {
        $("#restock-table").appTable({
            source: '<?php echo_uri("stock/dev2_restockingItemList"); ?>',
            filterDropdown: [
                <?php if ($can_read): ?>
                    { name: "created_by", class: "w200", options: <?php echo $team_members_dropdown; ?> }
                <?php endif; ?>
            ],
            columns: [
                { title: "<?php echo lang('id'); ?>", class: "text-center w50" },
                { title: '<?php echo lang('stock_restock_item_name'); ?>', class: "w150" },
                { title: "<?php echo lang('serial_number'); ?>", class: "w150" },
                { title: "<?php echo lang('items'); ?>"},
                { title: "<?php echo lang('stock_restock_quantity'); ?>", class: "text-right" },
                { title: "<?php echo lang('stock_restock_remaining'); ?>", class: "text-right" },
                { title: "<?php echo lang('stock_material_unit'); ?>", class: "w50" },
                { title: '<?php echo lang('created_by'); ?>' },
                { title: '<?php echo lang('created_date'); ?>' },
                { title: '<i class="fa fa-bars"></i>', class: "text-center option w100" }
            ],
            order: [ 0, 'desc' ],
            summation: [
                { column: 4, dataType: 'number' },
                { column: 5, dataType: 'number' }
            ],
            <?php if (isset($is_admin) && $is_admin) { ?>
                printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8]),
                xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8])
            <?php } ?>
        });
    });
</script>

<!-- done -->