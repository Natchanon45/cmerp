<style>
#accounting_navs:after, .tabs:after, .buttons:after{
    display: block;
    clear: both;
    content: '';
}

#accounting_navs .tabs{
    width: 70%;
    float: left;
    list-style: none;
    margin-top: 18px;
    margin-left: 18px;
    padding: 0;
}

#accounting_navs .tabs li{
    float: left;
    width: fit-content;
    border: 1px solid #ccc;
    border-right: 0;
}

#accounting_navs .tabs li:first-child{
    border-radius: 22px 0 0 22px;
}

#accounting_navs .tabs li:last-child{
    border-right: 1px solid #ccc;
    border-radius: 0 22px 22px 0;
}

#accounting_navs .tabs li a{
    display: block;
    padding: 5px 16px;
    padding-top: 6px;
    color: #333;
}

#accounting_navs .tabs li a:hover{
    cursor: pointer;
}

#accounting_navs .tabs li a.custom-color{
    cursor: default;
}

.dropdown_status{
    border: 1px solid #ccc;
    padding: 4px 5px;
}

#accounting_navs .buttons{
    width: 20%;
    float: right;
    text-align: right;
    list-style: none;
    margin-top: 18px;
    margin-right: 18px;
}

#accounting_navs .buttons .add a i{
    margin-right: 5px;
}

#datagrid {
    font-size: normal;
}

.select-status {
    padding: auto .5rem;
    width: 100%;
    outline: none;
    border-radius: .2rem;
    cursor: pointer;
    background-color: transparent;
}
</style>

<?php $modal_header = str_replace("https:", "", str_replace("http:", "", str_replace("/", "", base_url()))); ?>
<a id="popup" data-act="ajax-modal" class="btn ajax-modal"></a>
<div id="page-content" class="p20 clearfix">
    <ul class="nav nav-tabs bg-white title" role="tablist">
        <!-- <li><a href="#">ผังบัญชี</a></li> -->
        <li><a href="<?php echo get_uri("accounting/sell"); ?>">บัญชีขาย</a></li>
        <li class="active"><a>บัญชีซื้อ</a></li>
    </ul>
    <div class="panel panel-default">
        <div class="table-responsive pb50">
            <div id="accounting_navs">
                <ul class="tabs">
                    <?php $number_of_enable_module = 0; ?>
                    <?php if ($this->Permission_m->access_purchase_request): $number_of_enable_module++; ?>
                        <li data-module="purchase_request" class="<?php if($module == "purchase_request") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "purchase_request") echo 'custom-color'; ?>"><?php echo lang('purchase_request'); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($this->Permission_m->access_purchase_request): $number_of_enable_module++; ?>
                        <li data-module="purchase_order" class="<?php if($module == "purchase_order") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "purchase_order") echo 'custom-color'; ?>"><?php echo lang('purchase_order'); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php // if ($this->Permission_m->access_purchase_request): $number_of_enable_module++; ?>
                        <!-- <li data-module="goods_receipt" class="<?php // if($module == "goods_receipt") echo 'active custom-bg01'; ?>">
                            <a class="<?php // if($module == "goods_receipt") echo 'custom-color'; ?>"><?php // echo lang('goods_receipt'); ?></a>
                        </li> -->
                    <?php // endif; ?>
                    <?php // if ($this->Permission_m->access_purchase_request): $number_of_enable_module++; ?>
                        <!-- <li data-module="payment_voucher" class="<?php // if($module == "payment_voucher") echo 'active custom-bg01'; ?>">
                            <a class="<?php // if($module == "payment_voucher") echo 'custom-color'; ?>"><?php // echo lang('payment_voucher'); ?></a>
                        </li> -->
                    <?php // endif; ?>
                </ul>
                <ul class="buttons">
                    <li class="add"><a data-act='ajax-modal' class='btn btn-default' data-title='<?php echo $modal_header; ?>'><i class='fa fa-plus-circle'></i><span></span></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
<?php if($number_of_enable_module <= 1): ?>
#accounting_navs .tabs li{
    border-radius: 22px !important;
}
<?php endif; ?>
</style>

<script type="text/javascript">
var active_module = '<?php echo $module; ?>';
$(document).ready(function() {
    loadDataGrid();
    $(".tabs li").click(function() {
        $(".tabs li").removeClass("active, custom-bg01");
        $(".tabs li a").removeClass("custom-color");
        $(this).addClass("active, custom-bg01");
        $(this).children("a").addClass("custom-color");

        if($(this).data("module") == active_module) return;

        active_module = $(this).data("module");
        loadDataGrid();
    });
});

function loadDataGrid() {
    $("#datagrid_wrapper").empty();
    $("#accounting_navs .buttons li.add span").empty();
    $("<table id='datagrid' class='display' cellspacing='0' width='100%''></table>").insertAfter("#accounting_navs");
    
    var status_dropdown = null;
    var supplier_dropdown = null;
    var type_dropdown = null;
    var grid_filters = null;
    var grid_columns = null;
    var grid_print = null;
    var grid_excel = null;
    var grid_summation = null;

    if (active_module == 'purchase_request') {
        $(".buttons").removeClass('hide');
        $(".buttons li.add a").attr('data-action-url', '<?php echo get_uri('purchase_request/addedit'); ?>');
        $(".buttons li.add span").append('<?php echo lang('purchase_request_add'); ?>');

        status_dropdown = '<?php echo $status_dropdown; ?>';
        supplier_dropdown = '<?php echo $supplier_dropdown; ?>';
        type_dropdown = '<?php echo $type_dropdown; ?>';

        grid_filters = [
            { name: 'status', class: 'w150', options: JSON.parse(status_dropdown) },
            { name: 'pr_type', class: 'w200', options: JSON.parse(type_dropdown) },
            { name: 'supplier_id', class: 'w250', options: JSON.parse(supplier_dropdown) }
        ];
        
        grid_columns = [
            { title: '<?php echo lang('request_date'); ?>', class: 'w10p' },
            { title: '<?php echo lang('pr_no'); ?>', class: 'w10p' },
            { title: '<?php echo lang('pr_type'); ?>', class: 'w10p' },
            { title: '<?php echo lang('supplier_name'); ?>', class: 'w25p' },
            { title: '<?php echo lang('request_by'); ?>', class: 'w15p' },
            { title: '<?php echo lang('total_amount'); ?>', class: 'text-right w15p' },
            { title: '<?php echo lang('status'); ?>', class: 'option w10p' },
            { title: '<i class="fa fa-bars"></i>', class: 'text-center option w10p' }
        ];

        grid_print = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]);
        grid_excel = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]);

        grid_summation = [
            { column: 5, dataType: 'currency' }
        ];
    } else if (active_module == 'purchase_order') {
        $(".buttons").addClass('hide');
        $(".buttons li.add a").attr('data-action-url', '<?php echo get_uri('purchase_order/addedit'); ?>');
        $(".buttons li.add span").append('<?php echo lang('purchase_order_add'); ?>');

        status_dropdown = '<?php echo $status_dropdown; ?>';
        supplier_dropdown = '<?php echo $supplier_dropdown; ?>';
        type_dropdown = '<?php echo $type_dropdown; ?>';

        grid_filters = [
            { name: 'status', class: 'w150', options: JSON.parse(status_dropdown) },
            { name: 'po_type', class: 'w200', options: JSON.parse(type_dropdown) },
            { name: 'supplier_id', class: 'w250', options: JSON.parse(supplier_dropdown) }
        ];
        
        grid_columns = [
            { title: '<?php echo lang('request_date'); ?>', class: 'w10p' },
            { title: '<?php echo lang('po_no'); ?>', class: 'w10p' },
            { title: '<?php echo lang('reference_number'); ?>', class: 'w10p' },
            { title: '<?php echo lang('po_type'); ?>', class: 'w10p' },
            { title: '<?php echo lang('supplier_name'); ?>', class: 'w20p' },
            { title: '<?php echo lang('request_by'); ?>', class: 'w10p' },
            { title: '<?php echo lang('total_amount'); ?>', class: 'text-right w10p' },
            { title: '<?php echo lang('status'); ?>', class: 'option w10p' },
            { title: '<i class="fa fa-bars"></i>', class: 'text-center option w5p' }
        ];

        grid_print = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]);
        grid_excel = combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]);

        grid_summation = [
            { column: 6, dataType: 'currency' }
        ];
    } 
    // else if (active_module == 'goods_receipt') {
        // $(".buttons").removeClass('hide');
        // $(".buttons li.add a").attr('data-action-url', '<?php // echo get_uri('goods_receipt/addedit'); ?>');
        // $(".buttons li.add span").append('<?php // echo lang('goods_receipt_add'); ?>');
    // } else if (active_module == 'payment_voucher') {
        // $(".buttons").removeClass('hide');
        // $(".buttons li.add a").attr('data-action-url', '<?php // echo get_uri('payment_voucher/addedit'); ?>');
        // $(".buttons li.add span").append('<?php // echo lang('payment_voucher_add'); ?>');

        // grid_filters = null;

        // grid_columns = [
        //     { title: "วันที่", "class":"w10p" },
        //     { title: "เลขที่เอกสาร", "class":"w10p" },
        //     { title: "ลูกค้า", "class":"w30p" },
        //     { title: "ยอดรวมสุทธิ", "class":"text-right w15p" },
        //     { title: "สถานะ", "class":"text-left w15p" },
        //     { title: "<i class='fa fa-bars'></i>", "class":"text-center option w10p" }
        // ];
    // }

    $("#datagrid").appTable({
        source: '<?php echo_uri(); ?>' + active_module,
        order: [[0, 'desc']],
        rangeDatepicker: [{
            startDate: { name: 'start_date', value: '<?php echo date('Y-m-d', strtotime('-1 month')); ?>' },
            endDate: { name: 'end_date', value: '<?php echo date("Y-m-d"); ?>' }
        }],
        destroy: true,
        filterDropdown: grid_filters,
        columns: grid_columns,
        printColumns: grid_print,
        xlsColumns: grid_excel,
        summation: grid_summation
    });

    $("#datagrid").on("draw.dt", function() {
        $(".dropdown_status").on("change", function() {
            if (active_module == "quotations") {
                if($(this).val() == "B-2"){
                    $("#popup").attr("data-action-url", "<?php echo get_uri("quotations/test"); ?>").trigger("click");  
                }
            }

            axios.post("<?php echo_uri(); ?>" + active_module, {
                task: 'update_doc_status',
                doc_id: $(this).data("doc_id"),
                update_status_to: $(this).val(),
            }).then(function(response) {
                data = response.data;

                if (data.status == "success") {
                    if (typeof data.task !== 'undefined') {
                        location.href = data.url;
                        return;
                    }
                    appAlert.success(data.message, {duration: 5000});
                } else {
                    appAlert.error(data.message, {duration: 5000});
                }

                $("#datagrid").appTable({
                    newData: data.dataset, 
                    dataId: data.doc_id
                });
            }).catch(function(error) {
                console.log(error);
            });
        });
    });
}
</script>