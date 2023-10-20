<style>
#accounting_navs:after, .tabs:after, .buttons:after{
    display: block;
    clear: both;
    content: '';
}

#accounting_navs .tabs{
    width: calc(100% - 100px);
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
    float: right;
    text-align: right;
    list-style: none;
    margin-top: 18px;
    margin-right: 18px;
    width: 1px;
    position: relative;
}

#accounting_navs .buttons li{
    position: absolute;
    right: 0;
}

#accounting_navs .buttons .add a i{
    margin-right: 5px;
}

.p20 {
    padding-top: 0 !important;
}
</style>
<a id="popup" data-act="ajax-modal" class="btn ajax-modal"></a>
<div id="page-content" class="p20 clearfix">
    <ul class="nav nav-tabs bg-white title" role="tablist">
        <!--<li><a href="#">ผังบัญชี</a></li>-->
        <li class="active"><a><?php echo lang('sell_account'); ?></a></li>
        <li><a href="<?php echo get_uri("accounting/buy"); ?>"><?php echo lang('buy_account'); ?></a></li>
    </ul>
    <div class="panel panel-default">
        <div class="table-responsive pb50">
            <div id="accounting_navs">
                <ul class="tabs">
                    <?php $number_of_enable_module = 0; ?>
                    <?php if($this->Permission_m->accounting["sales_order"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="sales-orders" class="<?php if($module == "sales-orders") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "sales-orders") echo 'custom-color'; ?>"><?php echo lang('account_docname_sales_order'); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["quotation"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="quotations" class="<?php if($module == "quotations") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "quotations") echo 'custom-color'; ?>"><?php echo lang('account_docname_quotation'); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["invoice"]["access"] == true): ?>
                        <?php if($billing_type == 1 || $billing_type == 2 || $billing_type == 4 || $billing_type == 5): ?>
                            <?php $number_of_enable_module++; ?>
                            <li data-module="invoices" class="<?php if($module == "invoices") echo 'active custom-bg01'; ?>">
                                <a class="<?php if($module == "invoices") echo 'custom-color'; ?>">
                                    <?php
                                        if($billing_type== "2") echo lang('account_docname_invoice')."/".lang('account_docname_billing_note');
                                        elseif($billing_type == "5") echo lang('account_docname_invoice')."/".lang('account_docname_billing_note');
                                        else echo lang('account_docname_invoice');
                                    ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["billing_note"]["access"] == true): ?>
                        <?php if($billing_type == 1 || $billing_type == 4): ?>
                            <?php $number_of_enable_module++; ?>
                            <li data-module="billing-notes" class="<?php if($module == "billing-notes") echo 'active custom-bg01'; ?>">
                                <a class="<?php if($module == "billing-notes") echo 'custom-color'; ?>"><?php echo lang('account_docname_billing_note'); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["tax_invoice"]["access"] == true && $company_setting["company_vat_registered"] == "Y"): ?>
                        <?php if($billing_type == 1): ?>
                            <?php $number_of_enable_module++; ?>
                            <li data-module="tax-invoices" class="<?php if($module == "tax-invoices") echo 'active custom-bg01'; ?>">
                                <a class="<?php if($module == "tax-invoices") echo 'custom-color'; ?>"><?php echo lang('account_docname_sales_tax_invoice'); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["receipt"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="receipts" class="<?php if($module == "receipts") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "receipts") echo 'custom-color'; ?>">
                                <?php
                                    if($billing_type == "2") echo lang('account_docname_sales_tax_invoice')."/".lang('account_docname_receipt');
                                    elseif($billing_type == "3") echo lang('account_docname_invoice')."/".lang('account_docname_billing_note')."/".lang('account_docname_sales_tax_invoice')."/".lang('account_docname_receipt')."/".lang('account_docname_delivery_note');
                                    elseif($billing_type == "6") echo lang('account_docname_invoice')."/".lang('account_docname_billing_note')."/".lang('account_docname_receipt')."/".lang('account_docname_delivery_note');
                                    else echo lang('account_docname_receipt');
                                ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["credit_note"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="credit-notes" class="<?php if($module == "credit-notes") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "credit_notes") echo 'custom-color'; ?>"><?php echo lang('account_docname_credit_note'); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["debit_note"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="debit-notes" class="<?php if($module == "debit-notes") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "debit_notes") echo 'custom-color'; ?>"><?php echo lang('account_docname_debit_note'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="buttons">
                    <li class="add"><a data-act='ajax-modal' class='btn btn-default'><i class='fa fa-plus-circle'></i><span></span></a></li>
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
var active_module = "<?php echo $module; ?>";

$(document).ready(function () {
    loadDataGrid();
    $(".tabs li").click(function(){
        $(".tabs li").removeClass("active, custom-bg01");
        $(".tabs li a").removeClass("custom-color");
        $(this).addClass("active, custom-bg01");
        $(this).children("a").addClass("custom-color");

        if($(this).data("module") == active_module) return;

        active_module = $(this).data("module");
        loadDataGrid();
    });
});

function loadDataGrid(){
    $(".modal-content").css("height", "auto");
    $("#datagrid_wrapper").empty();
    $("#accounting_navs .buttons li.add span").empty();
    $("<table id='datagrid' class='display' cellspacing='0' width='100%''></table>").insertAfter("#accounting_navs");
    var doc_status = null;
    var extension_filters = null;
    var grid_columns = [
                            {title: "<?php echo lang('account_issue_date'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_short_document_no'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_refernce_no'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_customer'); ?>", "class":"w20p"},
                            {title: "<?php echo lang('account_customer_group'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_due_date'); ?>", "class":"text-left w10p"},
                            {title: "<?php echo lang('account_net_total'); ?>", "class":"text-right w10p"},
                            {title: "<?php echo lang('account_status'); ?>", "class":"text-left w10p"},
                            {title: "<i class='fa fa-bars'></i>", "class":"text-center option w10p"}
                        ];

    var summation_column = 6;
    $(".buttons li.add").css("display", "block");

    if(active_module == "sales-orders"){
        $(".buttons li.add a").attr("data-title", "<?php echo lang('account_button_create_sales_order'); ?>");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("sales-orders/addedit"); ?>");
        $(".buttons li.add span").append("<?php echo lang('account_button_create_sales_order'); ?>");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"<?php echo lang('account_status_awaiting'); ?>"}, {id:"A", text:"<?php echo lang('account_status_approved'); ?>"}, {id:"R", text:"<?php echo lang('account_status_rejected'); ?>"}];

        grid_columns = [
                            {title: "<?php echo lang('account_issue_date'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_short_document_no'); ?>", "class":"w15p"},
                            {title: "<?php echo lang('account_refernce_no'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_purpose'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_customer'); ?>", "class":"w20p"},
                            {title: "<?php echo lang('account_customer_group'); ?>", "class":"w15p"},
                            {title: "<?php echo lang('account_status'); ?>", "class":"text-left w10p"},
                            {title: "<i class='fa fa-bars'></i>", "class":"text-center option w10p"}
                        ];

        summation_column = null;

    }else if(active_module == "quotations"){
        $(".buttons li.add a").attr("data-title", "<?php echo lang('account_button_create_quotation'); ?>");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("quotations/addedit"); ?>");
        $(".buttons li.add span").append("<?php echo lang('account_button_create_quotation'); ?>");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"รออนุมัติ"}, {id:"A", text:"อนุมัติ"}, {id:"I", text:"ดำเนินการแล้ว"}, {id:"R", text:"ไม่อนุมัติ"}];
    }else if(active_module == "invoices"){
        $(".buttons li.add a").attr("data-title", "<?php echo lang('account_button_create_invoice'); ?>");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("invoices/addedit"); ?>");
        $(".buttons li.add span").append("<?php echo lang('account_button_create_invoice'); ?>");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"รออนุมัติ"}, {id:"O", text:"รอรับชำระ"}, {id:"P", text:"ชำระเงินแล้ว"}, {id:"V", text:"ยกเลิก"}];
    }else if(active_module == "billing-notes"){
        $(".buttons li.add a").attr("data-title", "<?php echo lang('account_button_create_biling_note'); ?>");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("billing-notes/addedit"); ?>");
        $(".buttons li.add span").append("<?php echo lang('account_button_create_biling_note'); ?>");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"รออนุมัติ"}, {id:"A", text:"อนุมัติ"}, {id:"V", text:"ยกเลิก"}];

        grid_columns = [
                            {title: "<?php echo lang('account_issue_date'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_short_document_no'); ?>", "class":"w15p"},
                            {title: "<?php echo lang('account_customer'); ?>", "class":"w20p"},
                            {title: "<?php echo lang('account_customer_group'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_due_date'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_net_total'); ?>", "class":"text-right w15p"},
                            {title: "<?php echo lang('account_status'); ?>", "class":"text-left w10p"}
                        ];

        summation_column = 5;
    }else if(active_module == "tax-invoices"){
        $(".buttons li.add a").attr("data-title", "<?php echo lang('account_button_create_sales_tax_invoice'); ?>");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("tax-invoices/addedit"); ?>");
        $(".buttons li.add span").append("<?php echo lang('account_button_create_sales_tax_invoice'); ?>");

        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"รออนุมัติ"}, {id:"A", text:"อนุมัติ"}, {id:"V", text:"ยกเลิก"}];

    }else if(active_module == "receipts"){
        $(".buttons li.add a").attr("data-title", "<?php echo lang('account_button_create_receipt'); ?>");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("receipts/addedit"); ?>");
        $(".buttons li.add span").append("<?php echo lang('account_button_create_receipt'); ?>");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"รอดำเนินการ"}, {id:"P", text:"เก็บเงินแล้ว"}, {id:"V", text:"ยกเลิก"}];

        grid_columns = [
                            {title: "<?php echo lang('account_issue_date'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_short_document_no'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_refernce_no'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_payment_receive_method'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_customer'); ?>", "class":"w15p"},
                            {title: "<?php echo lang('account_customer_group'); ?>", "class":"w10p"},
                            {title: "<?php echo lang('account_net_total'); ?>", "class":"text-right w15p"},
                            {title: "<?php echo lang('account_status'); ?>", "class":"text-left w10p"},
                            {title: "<i class='fa fa-bars'></i>", "class":"text-center option w10p"}
                        ];

    }else if(active_module == "credit-notes"){
        $(".buttons li.add").css("display", "block");
        $(".buttons li.add a").attr("data-title", "<?php echo lang('account_button_create_credit_note'); ?>");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("credit-notes/addedit"); ?>");
        $(".buttons li.add span").append("<?php echo lang('account_button_create_credit_note'); ?>");
        doc_status = [
                        {id:"", text:"-- <?php echo lang("status"); ?> --"},
                        {id:"P", text:"รอดำเนินการ"},
                        {id:"R", text:"คืนเงิน"}
                    ];
    }else if(active_module == "debit-notes"){
        $(".buttons li.add").css("display", "block");
        $(".buttons li.add a").attr("data-title", "<?php echo lang('account_button_create_debit_note'); ?>");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("debit-notes/addedit"); ?>");

        $(".buttons li.add span").append("<?php echo lang('account_button_create_debit_note'); ?>");
        doc_status = [
                        {id:"", text:"-- <?php echo lang("status"); ?> --"},
                        {id:"W", text:"รอดำเนินการ"},
                        {id:"R", text:"รอเก็บเงิน"}
                    ];
    }

    filterDropdown = [{name: "client_group_id", class: "w120", options: <?php echo $client_group_ids; ?>}, {name: "client_id", class: "w120", options: <?php echo $client_ids; ?>}, {name: "status", class: "w120", options: doc_status}];

    datagrid_data = {
        source: "<?php echo_uri(); ?>"+active_module,
        rangeDatepicker: [
            {
                startDate: { name: "start_date", value: "<?php echo date('Y-m-1'); ?>" }, 
                endDate: { name: "end_date", value: "<?php echo date("Y-m-d", strtotime('last day of this month', time())); ?>" }
            }
        ],
        destroy: true,
        filterDropdown: filterDropdown,
        columns: grid_columns,
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
    };

    if(summation_column != null) datagrid_data.summation = [{column: summation_column, dataType: 'currency'}];
    
    $("#datagrid").appTable(datagrid_data);

    $("#datagrid").on("draw.dt", function () {
        $(".dropdown_status").off("change").on( "change", function() {
            var doc_id = $(this).data("doc_id");
            var doc_number = $(this).data("doc_number");

            updateStatus($(this).data("doc_id"), $(this).val());
        });

        /*$(".copy").click(function(){
            let doc_id = $(this).data("doc_id");
            let doc_name = $(this).data("doc_name");
            let doc_number = $(this).data("doc_number");

            if(confirm("ยืนยันการคัดลอก"+doc_name+"จาก "+doc_number)){
                axios.post("<?php echo_uri(); ?>"+active_module, {
                    task: 'copy_doc',
                    doc_id: doc_id
                }).then(function (response) {
                    data = response.data;
                    if(data.status != "success"){
                        alert(data.message);
                        return;
                    }

                    location.href = data.target;
                }).catch(function (error) {});
            }
        });*/
    });
}

function updateRow(doc_id){
    axios.post("<?php echo_uri(); ?>"+active_module, {
        task: 'update_grid_row',
        doc_id: doc_id
    }).then(function (response) {
        data = response.data;
        if(data.status == "success") $("#datagrid").appTable({newData: data.dataset, dataId: data.doc_id});
    }).catch(function (error) {});
}

function updateStatus(docId, updateStatusTo){
    axios.post("<?php echo_uri(); ?>"+active_module, {
        task: 'update_doc_status',
        doc_id: docId,
        update_status_to: updateStatusTo,
    }).then(function (response) {
        data = response.data;
        $(".modal-content").css("height", "auto");
        
        if(data.status == "success"){
            if(typeof data.task !== 'undefined') {
                if(data.task == "popup"){
                    $("#popup").attr("data-post-id", data.popup_doc_id)
                    $("#popup").attr("data-title", data.popup_title);
                    $("#popup").attr("data-action-url", data.popup_url);
                    $("#popup").trigger("click");
                }else{
                    location.href = data.url;
                }
            }else{
                appAlert.success(data.message, {duration: 6000});    
            }

            $("#datagrid").appTable({newData: data.dataset, dataId: data.doc_id});
        }else if(data.status == "notchange"){
            $("#datagrid").appTable({newData: data.dataset, dataId: data.doc_id});
        }else{
            appAlert.error(data.message, {duration: 6000});
            $("#datagrid").appTable({newData: data.dataset, dataId: data.doc_id});
        }

    }).catch(function (error) {});
}
</script>
