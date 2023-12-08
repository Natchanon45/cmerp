<style type="text/css">
.pr-19px {
    padding-right: 19px !important;
}
</style>
<div id="page-content" class="p20 clearfix">
    <div class="panel panel-default">
        <div class="page-title clearfix">
            <h1>
                <a class="title-back" href="<?php echo get_uri('stock'); ?>">
                    <i class="fa fa-chevron-left" aria-hidden="true"></i>
                </a>
                <?php echo lang('stock_items'); ?>
            </h1>
            
            <div class="title-button-group">
                <?php
                if ($can_create && $can_update) {
                    echo modal_anchor(
                        get_uri("stock/item_import_modal"),
                        "<i class='fa fa-upload'></i> " . lang('stock_item_import'),
                        array("class" => "btn btn-default", "title" => lang('stock_item_import'))
                    );
                }

                if($this->Permission_m->access_product_category == true){
                    echo modal_anchor(
                        get_uri("stock/item_category_modal"),
                        "<i class='fa fa-tags'></i> " . lang('add_category'),
                        array("class" => "btn btn-default", "title" => lang('add_category'), "data-post-type" => "FG")
                    );
                }

                if ($this->Permission_m->create_product_item == true) {
                    echo modal_anchor(get_uri("items/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("class" => "btn btn-default", "title" => lang('add_item')));
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="item-table" class="display" cellspacing="0" width="100%"></table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#item-table").appTable({
            source: '<?php echo_uri("items/list_data"); ?>',
            filterDropdown: [
                { name: "category_id", class: "w200", options: <?php echo json_encode($category_dropdown); ?> }
            ],
            columns: [
                { title: "<?php echo lang('id'); ?>", "class": "w50 text-center" },
                { title: "<?php echo lang('preview_image'); ?>", "class": "w50" },
                { title: "<?php echo lang("stock_item_code"); ?>", "class": "w100" },
                { title: "<?php echo lang('stock_products'); ?>", "class": "w20p" },
                { title: "<?php echo lang('description'); ?>" },
                { title: "<?php echo lang('category'); ?>" },
                { title: "<?php echo lang('unit_type'); ?>", "class": "w100" },
                { title: '<?php echo lang("stock_material_barcode"); ?>' },
                { title: "<?php echo lang('rate'); ?>", "class": "text-right w100" },
                { title: "<i class='fa fa-bars'></i>", "class": "text-center option w100" }
            ],
            <?php if ($can_read_production_name): ?>
                printColumns : combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]),
                xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9])
            <?php else: ?>
                printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8]),
                xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6, 7, 8])
            <?php endif; ?>
        });
    });
</script>