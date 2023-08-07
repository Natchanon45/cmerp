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
</style>
<a id="popup" data-act="ajax-modal" class="btn ajax-modal"></a>
<div id="page-content" class="p20 clearfix">
    <ul class="nav nav-tabs bg-white title" role="tablist">
        <!--<li><a href="#">ผังบัญชี</a></li>-->
        <li class="active"><a>บัญชีขาย</a></li>
        <?php if ($this->login_user->is_admin): ?>
        <li><a href="<?php echo get_uri("accounting/buy"); ?>">บัญชีซื้อ</a></li>
        <?php endif; ?>
    </ul>
    <div class="panel panel-default">
        <div class="table-responsive pb50">
            <div id="accounting_navs">
                <ul class="tabs">
                    <?php $number_of_enable_module = 0; ?>
                    <?php if($this->Permission_m->accounting["quotation"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="quotations" class="<?php if($module == "quotations") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "quotations") echo 'custom-color'; ?>">ใบเสนอราคา</a>
                        </li>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["billing_note"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="billing-notes" class="<?php if($module == "billing-notes") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "billing-notes") echo 'custom-color'; ?>">ใบวางบิล</a>
                        </li>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["invoice"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="invoices" class="<?php if($module == "invoices") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "invoices") echo 'custom-color'; ?>">ใบกำกับภาษี</a>
                        </li>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["receipt"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="receipts" class="<?php if($module == "receipts") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "receipts") echo 'custom-color'; ?>">ใบเสร็จรับเงิน</a>
                        </li>
                    <?php endif; ?>

                    <?php if($this->Permission_m->accounting["credit_note"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="credit-notes" class="<?php if($module == "credit-notes") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "credit_notes") echo 'custom-color'; ?>">ใบลดหนี้</a>
                        </li>
                    <?php endif; ?>
                    <?php if($this->Permission_m->accounting["debit_note"]["access"] == true): ?>
                        <?php $number_of_enable_module++; ?>
                        <li data-module="debit-notes" class="<?php if($module == "debit-notes") echo 'active custom-bg01'; ?>">
                            <a class="<?php if($module == "debit_notes") echo 'custom-color'; ?>">ใบเพิ่มหนี้</a>
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
    $("#datagrid_wrapper").empty();
    $("#accounting_navs .buttons li.add span").empty();
    $("<table id='datagrid' class='display' cellspacing='0' width='100%''></table>").insertAfter("#accounting_navs");
    var doc_status = null;
    var grid_columns = [
                            {title: "วันที่", "class":"w10p"},
                            {title: "เลขที่เอกสาร", "class":"w10p"},
                            {title: "เลขที่อ้างอิง", "class":"w10p"},
                            {title: "ลูกค้า", "class":"w25p"},
                            {title: "ครบกำหนด", "class":"text-left w10p"},
                            {title: "ยอดรวมสุทธิ", "class":"text-right w10p"},
                            {title: "สถานะ", "class":"text-left w15p"},
                            {title: "<i class='fa fa-bars'></i>", "class":"text-center option w10p"}
                        ];

    var summation_column = 5;
    $(".buttons li.add").css("display", "block");

    if(active_module == "quotations"){
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("quotations/addedit"); ?>");
        $(".buttons li.add span").append("เพิ่มใบเสนอราคา");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"รออนุมัติ"}, {id:"A", text:"อนุมัติ"}, {id:"P", text:"แบ่งจ่าย"}, {id:"I", text:"ดำเนินการแล้ว"}, {id:"R", text:"ไม่อนุมัติ"}];
    }else if(active_module == "billing-notes"){
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("billing-notes/addedit"); ?>");
        $(".buttons li.add span").append("เพิ่มใบวางบิล");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"รอวางบิล"}, {id:"A", text:"วางบิลแล้ว"}, {id:"I", text:"เปิดบิลแล้ว"}, {id:"V", text:"ยกเลิก"}];
    }else if(active_module == "invoices"){
        $(".buttons li.add").css("display", "none");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"P", text:"รอเก็บเงิน"}, {id:"R", text:"เปิดใบเสร็จแล้ว"}, {id:"V", text:"ยกเลิก"}];
    }else if(active_module == "receipts"){
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("receipts/addedit"); ?>");
        $(".buttons li.add span").append("เพิ่มใบเสร็จรับเงิน");
        doc_status = [{id:"", text:"-- <?php echo lang("status"); ?> --"}, {id:"W", text:"รอดำเนินการ"}, {id:"P", text:"เก็บเงินแล้ว"}, {id:"V", text:"ยกเลิก"}];
        grid_columns = [
                            {title: "วันที่", "class":"w10p"},
                            {title: "เลขที่เอกสาร", "class":"w10p"},
                            {title: "เลขที่อ้างอิง", "class":"w10p"},
                            {title: "ลูกค้า", "class":"w30p"},
                            {title: "ยอดรวมสุทธิ", "class":"text-right w15p"},
                            {title: "สถานะ", "class":"text-left w15p"},
                            {title: "<i class='fa fa-bars'></i>", "class":"text-center option w10p"}
                        ];

        summation_column = 4;
    }else if(active_module == "credit-notes"){
        $(".buttons li.add").css("display", "block");
        $(".buttons li.add a").attr("data-title", "เลือกเอกสารที่เกี่ยวข้อง");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("credit-notes/addedit"); ?>");

        $(".buttons li.add span").append("เพิ่มใบลดหนี้");
        doc_status = [
                        {id:"", text:"-- <?php echo lang("status"); ?> --"},
                        {id:"P", text:"รอดำเนินการ"},
                        {id:"R", text:"คืนเงิน"}
                    ];
    }else if(active_module == "debit-notes"){
        $(".buttons li.add").css("display", "block");
        $(".buttons li.add a").attr("data-title", "เลือกเอกสารที่เกี่ยวข้อง");
        $(".buttons li.add a").attr("data-action-url", "<?php echo get_uri("debit-notes/addedit"); ?>");

        $(".buttons li.add span").append("เพิ่มใบเพิ่มหนี้");
        doc_status = [
                        {id:"", text:"-- <?php echo lang("status"); ?> --"},
                        {id:"W", text:"รอดำเนินการ"},
                        {id:"R", text:"รอเก็บเงิน"}
                    ];
    }

    $("#datagrid").appTable({
        source: "<?php echo_uri(); ?>"+active_module,
        order: [[1, "desc"]],
        rangeDatepicker: [
            {
                startDate: { name: "start_date", value: "<?php echo date('Y-m-d', strtotime('-1 month')); ?>" }, 
                endDate: { name: "end_date", value: "<?php echo date("Y-m-d"); ?>" }
            }
        ],
        destroy: true,
        filterDropdown: [{name: "client_id", class: "w150", options: <?php echo $client_ids; ?>}, {name: "status", class: "w150", options: doc_status}],
        columns: grid_columns,
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        summation: [
            {column: summation_column, dataType: 'currency'}
        ]
    });

    $("#datagrid").on("draw.dt", function () {
        $(".dropdown_status").on( "change", function() {
            if(active_module == "quotations" && $(this).val() == "P"){
                var total_billing_note = 0;
                axios.post("<?php echo_uri(); ?>"+active_module, {
                    task: 'get_partial_billing_note',
                    doc_id: $(this).data("doc_id")
                }).then(function (response) {
                    total_billing_note = response.data.total_billing_note;
                }).finally(() => {
                    if(total_billing_note <= 0){
                        $("#popup").attr("data-title", "แบ่งจ่ายใบวางบิล");
                        $("#popup").attr("data-action-url", "<?php echo_uri("quotations/partial-payment-type/"); ?>"+$(this).data("doc_id")).trigger( "click" );
                    }else{
                        updateStatus($(this).data("doc_id"), $(this).val());
                    }
                });
            }else{
                updateStatus($(this).data("doc_id"), $(this).val());
            }
        });
    });
}

function updateStatus(docId, updateStatusTo){
    axios.post("<?php echo_uri(); ?>"+active_module, {
        task: 'update_doc_status',
        doc_id: docId,
        update_status_to: updateStatusTo,
    }).then(function (response) {
        data = response.data;
        if(data.status == "success"){
            if(typeof data.task !== 'undefined') {
                location.href = data.url;
                return;
            }
            appAlert.success(data.message, {duration: 8000});
        }else{
            appAlert.error(data.message, {duration: 8000});
        }

        $("#datagrid").appTable({newData: data.dataset, dataId: data.doc_id});
    }).catch(function (error) {});
}
</script>
