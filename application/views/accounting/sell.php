<style>
#accounting_tabs:after{
    display: block;
    clear: both;
    content: '';
}

#accounting_tabs{    
    list-style: none;
    margin-top: 18px;
    margin-left: 18px;
    padding: 0;
}

#accounting_tabs li{
    float: left;
    width: fit-content;
    border: 1px solid #ccc;
    border-right: 0;
}

#accounting_tabs li:first-child{
    border-radius: 22px 0 0 22px;
}

#accounting_tabs li:last-child{
    border-right: 1px solid #ccc;
    border-radius: 0 22px 22px 0;
}

#accounting_tabs li a{
    display: block;
    padding: 5px 16px;
    padding-top: 6px;
    color: #333;
}

#accounting_tabs li a:hover{
    cursor: pointer;
}

#accounting_tabs li a.custom-color{
    cursor: default;
}

.dropdown_status{
    border: 1px solid #ccc;
    padding: 4px 5px;
}
</style>
<div id="page-content" class="p20 clearfix">
    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="active" data-tab="tickets_list"><a href="<?php echo_uri('tickets/index/'); ?>">บัญชีขาย</a></li>
        <li class="" data-tab="tickets_own_list"><a href="<?php echo_uri('tickets/ticket_own/'); ?>">บัญชีซื้อ</a></li>
    </ul>
    <div class="panel panel-default">
        <div class="table-responsive pb50">
            <ul id="accounting_tabs">
                <li data-module="quotations" class="active custom-bg01"><a class="custom-color">ใบเสนอราคา</a></li>
                <li data-module="billing-notes"><a>ใบวางบิล</a></li>
                <li data-module="invoices"><a>ใบกำกับภาษี</a></li>
                <li data-module="receipts"><a>ใบเสร็จรับเงิน</a></li>
            </ul>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function () {
    loadDataGrid("quotations");
    $("#accounting_tabs li").click(function(){
        $("#accounting_tabs li").removeClass("active, custom-bg01");
        $("#accounting_tabs li a").removeClass("custom-color");
        $(this).addClass("active, custom-bg01");
        $(this).children("a").addClass("custom-color");
        loadDataGrid($(this).data("module"));
    });
});


function loadDataGrid(module){
    $("#datagrid_wrapper").empty();
    $("<table id='datagrid' class='display' cellspacing='0' width='100%''></table>").insertAfter("#accounting_tabs");
    let doc_status = [{id:"", text:"-<?php echo lang("status"); ?>-"}, {id:"W", text:"รออนุมัติ"}, {id:"A", text:"อนุมัติ"}, {id:"P", text:"ดำเนินการแล้ว"}, {id:"R", text:"ไม่อนุมัติ"}];
    let grid_columns = [
                            {title: "วันที่", "class":"w10p"},
                            {title: "เลขที่เอกสาร", "class":"w10p"},
                            {title: "เลขที่อ้างอิง", "class":"w10p"},
                            {title: "ลูกค้า", "class":"w25p"},
                            {title: "ครบกำหนด", "class":"text-left w10p"},
                            {title: "ยอดรวมสุทธิ", "class":"text-right w10p"},
                            {title: "สถานะ", "class":"text-left w15p"},
                            {title: "<i class='fa fa-bars'></i>", "class":"text-center option w10p"}
                        ];

    let summation_column = 5;

    if(module == "receipts"){
        let grid_columns = [
                            {title: "วันที่", "class": "w10p"},
                            {title: "เลขที่เอกสาร", "class": "w10p"},
                            {title: "เลขที่อ้างอิง", "class":"w10p"},
                            {title: "ลูกค้า", "class": "w25p"},
                            {title: "ยอดรวมสุทธิ", "class": "text-right w15p"},
                            {title: "สถานะ", "class": "text-left w15p"},
                            {title: "<i class='fa fa-bars'></i>", "class": "text-center option w10p"}
                        ];

        let summation_column = 4;
    }

    
    

    $("#datagrid").appTable({
        source: "<?php echo_uri(); ?>"+module,
        order: [[0, "desc"]],
        dateRangeType: "monthly",
        destroy: true,
        filterDropdown: [{name: "status", class: "w150", options: doc_status}],
        columns: grid_columns,
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        summation: [
            {column: summation_column, dataType: 'currency'}
        ]
    });

    /*$("#datagrid").on("draw.dt", function () {
        $(".dropdown_status").on( "change", function() {
            axios.post("<?php echo_uri(); ?>"+module, {
                task: 'update_doc_status',
                doc_id: $(this).data("doc_id"),
                update_status_to: $(this).val(),
            }).then(function (response) {
                data = response.data;
                if(data.status == "success"){
                    if(typeof data.task !== 'undefined') {
                        location.href = data.url;
                        return;
                    }
                    appAlert.success(data.message, {duration: 5000});
                }else{
                    appAlert.error(data.message, {duration: 5000});
                }

                $("#datagrid").appTable({newData: data.dataset, dataId: data.doc_id});
            }).catch(function (error) {});
        });
    });*/
}
</script>