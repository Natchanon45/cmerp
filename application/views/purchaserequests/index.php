<div id="page-content" class="p20 clearfix">
    <div class="panel clearfix hide">
        <ul id="pr-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab">
                <h4 class="pl15 pt10 pr15">
                    <?php echo lang('material_shortage'); ?>
                </h4>
            </li>
            <li class="title-tab">
                <a id="by-summarize-button" class="active" role="presentation" href="<?php echo_uri('purchaserequests/dev2_summarize_data/'); ?>" data-target="#summarize-data">
                    <?php echo lang('by_summarize'); ?>
                </a>
            </li>
            <li class="title-tab">
                <a id="by-records-button" role="presentation" href="<?php echo_uri('purchaserequests/dev2_records_data/'); ?>" data-target="#records-data">
                    <?php echo lang('by_records'); ?>
                </a>
            </li>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <?php echo $buttonTops; ?>
                </div>
            </div>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="records-data"></div>
            <div role="tabpanel" class="tab-pane fade" id="summarize-data"></div>
            <div role="tabpanel" class="tab-pane fade" id="monthly-purchaserequests">
                <div class="table-responsive">
                    <table id="monthly-pr-table" class="display" cellspacing="0" width="100%"></table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-purchaserequests"></div>
        </div>
    </div>
</div>

<style>
    #low-project {
        padding-left: 10px;
        padding-right: 10px;
        padding-bottom: 0;
        margin-bottom: 0;
    }

    #low-project-table .col-name-danger {
        color: red;
        font-weight: bold;
    }

    #low-project-table .col-name-warning {
        color: orange;
        font-weight: bold;
    }

    #low-stock {
        padding: 10px;
        padding-bottom: 5px;
        margin-bottom: 5px;
    }

    #low-stock-table .col-name-danger {
        color: red;
        font-weight: bold;
    }

    #low-stock-table .col-name-warning {
        color: orange;
        font-weight: bold;
    }

    #low-project-table_wrapper .datatable-tools,
    #low-stock-table_wrapper .datatable-tools,
    #low-stock-table-item_wrapper .datatable-tools {
        display: none;
    }

    #monthly-pr-table_length,
    #yearly-pr-table_length {
        display: none;
    }

    #low-project-table td {
        vertical-align: top;
    }

    #low-project-table span.lacked_from_project {
        display: block;
        border-bottom: 1px solid #f2f2f2;
        padding-top: 12px;
        padding-bottom: 12px;
    }

    #low-project-table span.lacked_from_project:last-child {
        border-bottom: 0;
    }
</style>

<script type="text/javascript">
    loadPrTable = function (selector, dateRange) {
        $(selector).appTable({
            source: '<?php echo_uri("purchaserequests/list_data") ?>',
            order: [[0, "desc"]],
            dateRangeType: dateRange,
            filterDropdown: [
                { name: "status_id", class: "w150", options: <?php echo json_encode($pr_status_indexs); ?> },
                { name: "supplier_id", class: "w150", options: <?php $this->load->view("purchaserequests/pr_suppliers_dropdown"); ?> },
                { name: "limit", class: "w100", options: [
                    { "id": 10, "text": "รายการ" }, { "id": 10, "text": "10" }, 
                    { "id": 25, "text": "25" }, { "id": 50, "text": "50" }, 
                    { "id": 100, "text": "100" }, { "id": 500, "text": "500" }
                ] }
            ],
            columns: [
                { title: "<?php echo lang("purchaserequests_") ?>" },
                { title: "<?php echo lang("category_name") ?>", "class": "text-left w15p" },
                { title: "<?php echo lang("project_name") ?>", "class": "text-left w10p" },
                { title: "<?php echo lang("buyer_org") ?>", "class": "text-left w10p" },
                { visible: false, searchable: false },
                { title: "<?php echo lang("pr_date") ?>", "iDataSort": 2, "class": "w10p" },
                { title: "<?php echo lang("amount") ?>", "class": "text-right w10p" },
                { title: "<?php echo lang("status") ?>", "class": "text-center w10p" },
                { title: "<i class='fa fa-bars'></i>", "class": "text-center option w10p" }
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
            summation: [{ column: 4, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol }]
        });
    };

    <?php if ($this->Permission_m->access_purchase_request == true): ?>
        loadLowProjectTable = function(selector) {
            $(selector).appTable({
                source: '<?php echo_uri("purchaserequests/list_lacked_project_materials") ?>',
                order: [[0, "desc"]],
                columns: [
                    { title: "<?php echo lang("ID") ?> ", "class": "w5p" },
                    { title: "ชื่อวัตถุดิบ/สินค้าสำเร็จ" },
                    { title: "โปรเจค" },
                    { title: "จำนวน", "class": "w15p" },
                    { title: "<?php echo lang("action") ?>", "class": "w20p" }
                ],
                sDefaultContent: "Not found any data",
                searchPanes: {
                    controls: false
                },
                dom: 'Plfrtip'
            });
        }

        loadLowStockTable = function (selector) {
            $(selector).appTable({
                source: '<?php echo_uri("purchaserequests/list_lacked_stock_materials") ?>',
                order: [[0, "desc"]],
                columns: [
                    { title: "<?php echo lang("ID") ?> ", "class": "w5p" },
                    { title: "<?php echo lang("import_name") ?> " },
                    { title: "<?php echo lang("material_amount") ?>", "class": "w15p" },
                    { title: "<?php echo lang("action") ?>", "class": "w20p" }
                ],
                sDefaultContent: "Not found any data",
                searchPanes: {
                    controls: false
                },
                dom: 'Plfrtip'
            });
        }

        loadLowStockTableItem = function (selector) {
            $(selector).appTable({
                source: '<?php echo_uri("purchaserequests/list_lacked_stock_item") ?>',
                order: [[0, "desc"]],
                columns: [
                    { title: "<?php echo lang("ID") ?> ", "class": "w5p" },
                    { title: "<?php echo lang("stock_restock_item_name") ?> " },
                    { title: "<?php echo lang("item_amount") ?>", "class": "w15p" },
                    { title: "<?php echo lang("action") ?>", "class": "w20p" }
                ],
                sDefaultContent: "Not found any data",
                searchPanes: {
                    controls: false
                },
                dom: 'Plfrtip'
            });
        }

        function purchaseRequest(btn_selector, prefix) {
            var clicked_btn = jQuery(btn_selector);
            var materials = [];
            var lacked_materials = jQuery('.' + prefix + 'lacked_material');
            for (var i = 0; i < lacked_materials.length; i++) {
                var material_id = jQuery(lacked_materials[i]).attr('data-material-id');
                var amount = parseFloat(jQuery(lacked_materials[i]).attr('data-lacked-amount'));
                var unit = jQuery(lacked_materials[i]).attr('data-unit');
                var supplier_name = jQuery(lacked_materials[i]).attr('data-supplier-name');
                var supplier_id = jQuery(lacked_materials[i]).attr('data-supplier-id');
                var project_id = parseInt(jQuery(lacked_materials[i]).attr('data-project-id'));
                var project_name = jQuery(lacked_materials[i]).attr('data-project-name');
                var price = jQuery(lacked_materials[i]).attr('data-price');
                var currency = jQuery(lacked_materials[i]).attr('data-currency');
                var currency_symbol = jQuery(lacked_materials[i]).attr('data-currency_symbol');

                project_name = project_name ? project_name : "";
                if (materials[material_id] != undefined) {
                    materials[material_id].amount = amount;
                } else if (material_id) {
                    materials[material_id] = {
                        'id': material_id, 'amount': amount, 'unit': unit, 'supplier_id': supplier_id, 
                        'supplier_name': supplier_name, 'project_id': project_id, 'project_name': project_name, 
                        'price': price, 'currency': currency, 'currency_symbol': currency_symbol
                    };
                }
            }

            materials = materials.filter(function (ele) {
                if (ele != null) return ele;
            });

            var form = jQuery('<form id="add-pr-form" method="post" action="<?php echo_uri('purchaserequests/add_pr_material_to_cart'); ?>"></form>');
            jQuery.each(materials, function (key, material) {
                form.append('<input type="hidden" name="materials[' + key + '][id]" value="' + material.id + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][amount]" value="' + material.amount + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][unit]" value="' + material.unit + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][supplier_id]" value="' + material.supplier_id + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][supplier_name]" value="' + material.supplier_name + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][project_id]" value="' + material.project_id + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][project_name]" value="' + material.project_name + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][price]" value="' + material.price + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][currency]" value="' + material.currency + '" />');
                form.append('<input type="hidden" name="materials[' + key + '][currency_symbol]" value="' + material.currency_symbol + '" />');
            });

            form.append('<?php
                $CI =& get_instance();
                echo sprintf(
                    '<input type="hidden" name="%s" value="%s" />',
                    $CI->security->get_csrf_token_name(),
                    $CI->security->get_csrf_hash(),
                );
            ?>');

            var parentform = jQuery(clicked_btn).closest('form');
            if (parentform.length > 0) parentform.after(form);
            else jQuery(clicked_btn).after(form);
            jQuery('#add-pr-form').submit();
        };

        function purchaseRequestItem(btn_selector, prefix) {
            var clicked_btn = jQuery(btn_selector);
            var item = [];
            var lacked_materials = jQuery('.' + prefix + 'lacked_material');
            for (var i = 0; i < lacked_materials.length; i++) {
                var item_id = jQuery(lacked_materials[i]).attr('data-item-id');
                var amount = parseFloat(jQuery(lacked_materials[i]).attr('data-lacked-amount'));
                var unit = jQuery(lacked_materials[i]).attr('data-unit');
                var supplier_name = jQuery(lacked_materials[i]).attr('data-supplier-name');
                var supplier_id = jQuery(lacked_materials[i]).attr('data-supplier-id');
                var project_id = parseInt(jQuery(lacked_materials[i]).attr('data-project-id'));
                var project_name = jQuery(lacked_materials[i]).attr('data-project-name');
                var price = jQuery(lacked_materials[i]).attr('data-price');
                var currency = jQuery(lacked_materials[i]).attr('data-currency');
                var currency_symbol = jQuery(lacked_materials[i]).attr('data-currency_symbol');

                project_name = project_name ? project_name : "";
                if (item[item_id] != undefined) {
                    item[item_id].amount = amount;
                } else if (item_id) {
                    item[item_id] = {
                        'id': item_id, 'amount': amount, 'unit': unit, 'supplier_id': supplier_id, 
                        'supplier_name': supplier_name, 'project_id': project_id, 'project_name': project_name, 
                        'price': price, 'currency': currency, 'currency_symbol': currency_symbol
                    };
                }
            }

            item = item.filter(function (ele) {
                if (ele != null) return ele;
            });
            
            var form = jQuery('<form id="add-pr-form1" method="post" action="<?php echo_uri('purchaserequests/add_pr_item_to_cart'); ?>"></form>');
            jQuery.each(item, function (key, item) {
                form.append('<input type="hidden" name="item[' + key + '][id]" value="' + item.id + '" />');
                form.append('<input type="hidden" name="item[' + key + '][amount]" value="' + item.amount + '" />');
                form.append('<input type="hidden" name="item[' + key + '][unit]" value="' + item.unit + '" />');
                form.append('<input type="hidden" name="item[' + key + '][supplier_id]" value="' + item.supplier_id + '" />');
                form.append('<input type="hidden" name="item[' + key + '][supplier_name]" value="' + item.supplier_name + '" />');
                form.append('<input type="hidden" name="item[' + key + '][project_id]" value="' + item.project_id + '" />');
                form.append('<input type="hidden" name="item[' + key + '][project_name]" value="' + item.project_name + '" />');
                form.append('<input type="hidden" name="item[' + key + '][price]" value="' + item.price + '" />');
                form.append('<input type="hidden" name="item[' + key + '][currency]" value="' + item.currency + '" />');
                form.append('<input type="hidden" name="item[' + key + '][currency_symbol]" value="' + item.currency_symbol + '" />');
            });

            form.append('<?php
            $CI =& get_instance();
                echo sprintf(
                    '<input type="hidden" name="%s" value="%s" />',
                    $CI->security->get_csrf_token_name(),
                    $CI->security->get_csrf_hash(),
                );
            ?>');

            var parentform = jQuery(clicked_btn).closest('form');
            if (parentform.length > 0) parentform.after(form);
            else jQuery(clicked_btn).after(form);
            jQuery('#add-pr-form1').submit();
        };
    <?php endif; ?>
    
    $(document).ready(function () {
        $('#back-to-stock').on('click', function () {
            window.location = '<?php echo_uri('stock'); ?>';
        });
    });
</script>
