<div id="page-content" class="p20 clearfix">
    <div class="panel panel-default">
        <div class="page-title clearfix">
            <h1>
                <?php echo lang('items'); ?>
            </h1>
            <div class="title-button-group">
                <?php
                    if($this->Permission_m->access_product_category == true){
                        echo modal_anchor(
                            get_uri("stock/item_category_modal"),
                            "<i class='fa fa-tags'></i> " . lang('add_category'),
                            array("class" => "btn btn-default", "title" => lang('add_category'), "data-post-type" => "FG")
                        );
                    }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="item-table" class="display" cellspacing="0" width="100%">
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    //alert('<?php echo json_encode($categories_dropdown); ?>');
    $(document).ready(function () {
        $("#item-table").appTable({
            source: '<?php echo_uri("items/list_data") ?>',
            order: [[0, 'desc']],
            filterDropdown: [
                { name: "category_id", class: "w200", options: <?php echo $categories_dropdown; ?> }
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
            printColumns: [0, 1, 2, 3, 4, 5, 6, 7],
            xlsColumns: [0, 1, 2, 3, 4, 5, 6, 7]
        });
    });
</script>