<style>
    #accounting_navs:after,
    .tabs:after,
    .buttons:after {
        display: block;
        clear: both;
        content: '';
    }

    #accounting_navs .tabs {
        width: 70%;
        float: left;
        list-style: none;
        margin-top: 18px;
        margin-left: 18px;
        padding: 0;
    }

    #accounting_navs .tabs li {
        float: left;
        width: fit-content;
        border: 1px solid #ccc;
        border-right: 0;
    }

    #accounting_navs .tabs li:first-child {
        border-radius: 22px 0 0 22px;
    }

    #accounting_navs .tabs li:last-child {
        border-right: 1px solid #ccc;
        border-radius: 0 22px 22px 0;
    }

    #accounting_navs .tabs li a {
        display: block;
        padding: 5px 16px;
        padding-top: 6px;
        color: #333;
    }

    #accounting_navs .tabs li a:hover {
        cursor: pointer;
    }

    #accounting_navs .tabs li a.custom-color {
        cursor: default;
    }

    #accounting_navs .buttons {
        width: 20%;
        float: right;
        text-align: right;
        list-style: none;
        margin-top: 18px;
        margin-right: 18px;
    }

    #accounting_navs .buttons .add a i {
        margin-right: 5px;
    }

    #datagrid {
        font-size: normal;
    }

    .p20 {
        padding-top: 0 !important;
    }

    .select-status {
        width: calc(100% - 15px);
        padding: .3rem .8rem;
        outline: none;
        background: none;
        border-color: #e2e7f1;
    }

    .pointer-none {
        pointer-events: none;
        appearance: none;
    }
</style>

<a id="popup" data-act="ajax-modal" class="btn ajax-modal"></a>
<div id="page-content" class="p20 clearfix">
    <ul class="nav nav-tabs bg-white title" role="tablist">
        <!-- <li><a href="<?php // echo get_uri("accounting/coa"); ?>"><?php // echo lang('coa'); ?></a></li> -->
        <li><a href="<?php echo get_uri("accounting/sell"); ?>"><?php echo lang('sell_account'); ?></a></li>
        <li class="active"><a><?php echo lang('buy_account'); ?></a></li>
    </ul>

    <div class="panel panel-default">
        <div class="table-responsive">
            <div id="accounting_navs">
                <ul class="tabs">
                    <?php $number_of_enable_module = 0; ?>

                    <?php if ($permissions['access_purchase_request']):
                        $number_of_enable_module++; ?>
                        <li data-module="purchase_request" class="<?php if ($module == "purchase_request") echo 'active custom-bg01'; ?>">
                            <a class="<?php if ($module == "purchase_request") echo 'custom-color'; ?>"><?php echo lang('purchase_request'); ?></a>
                        </li>
                    <?php endif; ?>

                    <?php if ($permissions['access_purchase_request']): $number_of_enable_module++; ?>
                        <li data-module="purchase_order" class="<?php if ($module == "purchase_order") echo 'active custom-bg01'; ?>">
                            <a class="<?php if ($module == "purchase_order") echo 'custom-color'; ?>"><?php echo lang('purchase_order'); ?></a>
                        </li>
                    <?php endif; ?>

                    <?php if ($permissions['access_purchase_request']): $number_of_enable_module++; ?>
                        <li data-module="payment_voucher" class="<?php if($module == "payment_voucher") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "payment_voucher") echo 'custom-color'; ?>"><?php echo lang('payment_voucher'); ?></a>
                        </li>
                    <?php endif; ?>

                    <?php if ($permissions['access_purchase_request']): $number_of_enable_module++; ?>
                        <li data-module="goods_receipt" class="<?php if ($module == "goods_receipt") echo 'active custom-bg01'; ?>">
                            <a class="<?php if ($module == "goods_receipt") echo 'custom-color'; ?>"><?php echo lang('goods_receipt'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="buttons">
                    <li class="add">
                        <a data-act="ajax-modal" class="btn btn-default" data-title="<?php echo $modal_header; ?>"><i class="fa fa-plus-circle"></i><span></span></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    <?php if ($number_of_enable_module <= 1): ?>
        #accounting_navs .tabs li {
            border-radius: 22px !important;
        }
    <?php endif; ?>
</style>

<script type="text/javascript">
    let active_module = '<?php echo $module; ?>';

    $(document).ready(function () {
        loadDataGrid();

        $(".tabs li").click(function () {
            $(".tabs li").removeClass("active, custom-bg01");
            $(".tabs li a").removeClass("custom-color");

            $(this).addClass("active, custom-bg01");
            $(this).children("a").addClass("custom-color");

            if ($(this).data("module") == active_module) return;

            active_module = $(this).data("module");
            loadDataGrid();
        });
    });

    function loadDataGrid() {
        $("#datagrid_wrapper").empty();
        $("#accounting_navs .buttons li.add span").empty();
        $("<table id='datagrid' class='display' cellspacing='0' width='100%''></table>").insertAfter("#accounting_navs");

        let status_dropdown = null;
        let supplier_dropdown = null;
        let type_dropdown = null;
        let grid_filters = null;
        let grid_columns = null;
        let grid_print = null;
        let grid_excel = null;
        let grid_summation = null;

        if (active_module == 'purchase_request') {
            $(".buttons").removeClass('hide');
            $(".buttons li.add a").attr('data-action-url', '<?php echo get_uri('purchase_request/addedit'); ?>');
            $(".buttons li.add span").append('<?php echo lang('purchase_request_add'); ?>');

            status_dropdown = '<?php echo $pr_status_dropdown; ?>';
            supplier_dropdown = '<?php echo $supplier_dropdown; ?>';
            type_dropdown = '<?php echo $type_dropdown; ?>';

            grid_filters = [
                { name: 'status', class: 'w150', options: JSON.parse(status_dropdown) },
                { name: 'pr_type', class: 'w200', options: JSON.parse(type_dropdown) },
                { name: 'supplier_id', class: 'w250', options: JSON.parse(supplier_dropdown) }
            ];

            grid_columns = [
                { title: '<?php echo lang('request_date'); ?>', class: 'w10p' },
                { title: '<?php echo lang('pr_number'); ?>', class: 'w10p' },
                { title: '<?php echo lang('pr_type'); ?>', class: 'w10p' },
                { title: '<?php echo lang('supplier_name'); ?>', class: 'w25p' },
                { title: '<?php echo lang('request_by'); ?>', class: 'w15p' },
                { title: '<?php echo lang('total_amount'); ?>', class: 'text-right w15p' },
                { title: '<?php echo lang('status'); ?>', class: 'w10p' },
                { title: '<i class="fa fa-bars"></i>', class: 'text-center option w10p' }
            ];

            grid_print = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]);
            grid_excel = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]);

            grid_summation = [
                { column: 5, dataType: 'currency' }
            ];
        } else if (active_module == 'purchase_order') {
            $(".buttons").removeClass('hide');
            $(".buttons li.add a").attr('data-action-url', '<?php echo get_uri('purchase_order/addedit'); ?>');
            $(".buttons li.add span").append('<?php echo lang('purchase_order_add'); ?>');

            status_dropdown = '<?php echo $po_status_dropdown; ?>';
            supplier_dropdown = '<?php echo $supplier_dropdown; ?>';
            type_dropdown = '<?php echo $type_dropdown; ?>';

            grid_filters = [
                { name: 'status', class: 'w150', options: JSON.parse(status_dropdown) },
                { name: 'po_type', class: 'w200', options: JSON.parse(type_dropdown) },
                { name: 'supplier_id', class: 'w250', options: JSON.parse(supplier_dropdown) }
            ];

            grid_columns = [
                { title: '<?php echo lang('request_date'); ?>', class: 'w10p' },
                { title: '<?php echo lang('pr_number'); ?>', class: 'w10p' },
                { title: '<?php echo lang('reference_number'); ?>', class: 'w10p' },
                { title: '<?php echo lang('pr_type'); ?>', class: 'w10p' },
                { title: '<?php echo lang('supplier_name'); ?>', class: 'w20p' },
                { title: '<?php echo lang('request_by'); ?>', class: 'w10p' },
                { title: '<?php echo lang('total_amount'); ?>', class: 'text-right w10p' },
                { title: '<?php echo lang('status'); ?>', class: 'w10p' },
                { title: '<i class="fa fa-bars"></i>', class: 'text-center option w5p' }
            ];

            grid_print = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]);
            grid_excel = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]);

            grid_summation = [
                { column: 6, dataType: 'currency' }
            ];
        } else if (active_module == 'payment_voucher') {
            $(".buttons").removeClass('hide');
            $(".buttons li.add a").attr('data-action-url', '<?php echo get_uri('payment_voucher/addedit'); ?>');
            $(".buttons li.add span").append('<?php echo lang('payment_voucher_add'); ?>');

            status_dropdown = '<?php echo $gr_status_dropdown; ?>';
            supplier_dropdown = '<?php echo $supplier_dropdown; ?>';
            type_dropdown = '<?php echo $type_dropdown; ?>';

            grid_filters = [
                { name: 'status', class: 'w150', options: JSON.parse(status_dropdown) },
                { name: 'po_type', class: 'w200', options: JSON.parse(type_dropdown) },
                { name: 'supplier_id', class: 'w250', options: JSON.parse(supplier_dropdown) }
            ];

            grid_columns = [
                { title: '<?php echo lang('request_date'); ?>', class: 'w10p' },
                { title: '<?php echo lang('pr_number'); ?>', class: 'w10p' },
                { title: '<?php echo lang('reference_number'); ?>', class: 'w10p' },
                { title: '<?php echo lang('pr_type'); ?>', class: 'w10p' },
                { title: '<?php echo lang('supplier_name'); ?>', class: 'w20p' },
                { title: '<?php echo lang('request_by'); ?>', class: 'w10p' },
                { title: '<?php echo lang('total_amount'); ?>', class: 'text-right w10p' },
                { title: '<?php echo lang('status'); ?>', class: 'w10p' },
                { title: '<i class="fa fa-bars"></i>', class: 'text-center option w5p' }
            ];

            grid_print = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]);
            grid_excel = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]);

            grid_summation = [
                { column: 6, dataType: 'currency' }
            ];
        } else if (active_module == 'goods_receipt') {
            $(".buttons").addClass('hide');
            $(".buttons li.add a").attr('data-action-url', '<?php echo get_uri('goods_receipt/addedit'); ?>');
            $(".buttons li.add span").append('<?php echo lang('goods_receipt_add'); ?>');

            status_dropdown = '<?php echo $gr_status_dropdown; ?>';
            supplier_dropdown = '<?php echo $supplier_dropdown; ?>';
            type_dropdown = '<?php echo $type_dropdown; ?>';

            grid_filters = [
                { name: 'status', class: 'w150', options: JSON.parse(status_dropdown) },
                { name: 'po_type', class: 'w200', options: JSON.parse(type_dropdown) },
                { name: 'supplier_id', class: 'w250', options: JSON.parse(supplier_dropdown) }
            ];

            grid_columns = [
                { title: '<?php echo lang('request_date'); ?>', class: 'w10p' },
                { title: '<?php echo lang('pr_number'); ?>', class: 'w10p' },
                { title: '<?php echo lang('reference_number'); ?>', class: 'w10p' },
                { title: '<?php echo lang('pr_type'); ?>', class: 'w10p' },
                { title: '<?php echo lang('supplier_name'); ?>', class: 'w20p' },
                { title: '<?php echo lang('request_by'); ?>', class: 'w10p' },
                { title: '<?php echo lang('total_amount'); ?>', class: 'text-right w10p' },
                { title: '<?php echo lang('status'); ?>', class: 'w10p' },
                { title: '<i class="fa fa-bars"></i>', class: 'text-center option w5p' }
            ];

            grid_print = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]);
            grid_excel = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6]);

            grid_summation = [
                { column: 6, dataType: 'currency' }
            ];
        }

        $("#datagrid").appTable({
            source: '<?php echo_uri(); ?>' + active_module,
            order: [[0, 'desc']],
            rangeDatepicker: [{
                startDate: { name: 'start_date', value: '<?php echo date('Y-m-01'); ?>' },
                endDate: { name: 'end_date', value: '<?php echo date("Y-m-d", strtotime('last day of this month', time())); ?>' }
            }],
            destroy: true,
            filterDropdown: grid_filters,
            columns: grid_columns,
            printColumns: grid_print,
            xlsColumns: grid_excel,
            summation: grid_summation
        });

        $("#datagrid").on("draw.dt", function () {
            $(".dropdown_status").on("change", function () {
                let url = '<?php echo_uri(); ?>' + active_module;
                let request = {
                    task: 'update_doc_status',
                    doc_id: $(this).data("doc_id"),
                    update_status_to: $(this).val()
                };

                axios.post(url, request).then((response) => {
                    // console.log(response);
                    
                    let data = response.data;
                    if (data.status == "success") {
                        if (typeof data.task !== "undefined") {
                            if (data.task === 'cancelled_purchase_order') {
                                window.location.reload();
                                return;
                            }

                            if (data.task === 'create_purchase_order') {
                                window.location.href = data.url;
                                return
                            }

                            if (data.task === 'approved_purchase_order') {
                                window.location.href = data.url;
                                return;
                            }

                            if (data.task === 'create_goods_receipt') {
                                window.location.href = data.url;
                                return;
                            }
                        }

                        appAlert.success(data.message, { duration: 3001 });
                    } else {
                        appAlert.error(data.message, { duration: 3001 });
                    }

                    $("#datagrid").appTable({
                        newData: data.dataset,
                        dataId: data.doc_id
                    });
                }).catch((error) => {
                    appAlert.error("500 Internal Server Error.", { duration: 3001 });
                });
            });
        });
    }
</script>
