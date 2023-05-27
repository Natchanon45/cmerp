<div id="page-content" class="p20 clearfix">
    <div class="panel clearfix">
        <ul id="quotation-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15">ใบเสร็จรับเงิน</h4></li>
            <li><a id="monthly-quotation-button" class="active" role="presentation" href="javascript:;" data-target="#monthly-quotations"><?php echo lang("monthly"); ?></a></li>
            <!--<li><a role="presentation" href="<?php echo_uri("estimates/yearly/"); ?>" data-target="#yearly-estimates"><?php echo lang('yearly'); ?></a></li>-->
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <a data-action-url='<?php echo get_uri("receipts/addedit"); ?>' data-act='ajax-modal' class='btn btn-default'><i class='fa fa-plus-circle'></i> เพิ่มใบเสร็จรับเงิน</a>
                </div>
            </div>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-quotations">
                <div class="table-responsive">
                    <table id="datagrid" class="display datatable" cellspacing="0" width="100%"></table>
                </div>
            </div>
            <!--<div role="tabpanel" class="tab-pane fade" id="yearly-estimates"></div>-->
        </div>
    </div>
</div>
<script type="text/javascript">
let doc_status = [{id:"", text:"-<?php echo lang("status"); ?>-"}, {id:"W", text:"รอดำเนินการ"}, {id:"P", text:"เก็บเงินแล้ว"}, {id:"V", text:"ยกเลิก"}];
$(document).ready(function () {
    $("#datagrid").appTable({
        source: '<?php echo current_url(); ?>',
        order: [[0, "desc"]],
        dateRangeType: "monthly",
        filterDropdown: [{name: "status", class: "w150", options: doc_status}],
        columns: [
            {title: "วันที่", "class": "w10p"},
            {title: "เลขที่เอกสาร", "class": "w15p"},
            {title: "ชื่อลูกค้า", "class": "w35p"},
            {title: "ยอดรวมสุทธิ", "class": "text-right w15p"},
            {title: "สถานะ", "class": "text-left w15p"},
            {title: "<i class='fa fa-bars'></i>", "class": "text-center option w10p"}
        ],
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        summation: [
            {column: 3, dataType: 'currency'}
        ]
    });

    $("#datagrid").on("draw.dt", function () {
        $(".dropdown_status").on( "change", function() {
            axios.post('<?php echo current_url(); ?>', {
                task: 'update_doc_status',
                doc_id: $(this).data("doc_id"),
                update_status_to: $(this).val(),
            }).then(function (response) {
                data = response.data;
                if(data.status == "success"){
                    appAlert.success(data.message, {duration: 5000});
                }else{
                    appAlert.error(data.message, {duration: 5000});
                }

                $("#datagrid").appTable({newData: data.dataset, dataId: data.doc_id});
            }).catch(function (error) {});
        });
    });
});
</script>
