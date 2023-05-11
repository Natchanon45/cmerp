<link rel="stylesheet" href="/assets/css/datatable.css?t=<?php echo time();?>">
<div id="page-content" class="p20 clearfix">
    <div class="panel clearfix">
        <ul id="estimate-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo lang('estimates'); ?></h4></li>
            <li><a id="monthly-estimate-button" class="active" role="presentation" href="javascript:;" data-target="#monthly-estimates"><?php echo lang("monthly"); ?></a></li>
            <li><a role="presentation" href="<?php echo_uri("estimates/yearly/"); ?>" data-target="#yearly-estimates"><?php echo lang('yearly'); ?></a></li>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <a data-action-url='<?php echo get_uri("quotations/doc"); ?>' data-act='ajax-modal' class='btn btn-default'><i class='fa fa-plus-circle'></i>เพิ่มใบเสนอราคา</a>
                </div>
            </div>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-estimates">
                <div class="table-responsive">
                    <!--<table id="datagrid" class="display" cellspacing="0" width="100%"></table>-->
                    <table id="datagrid" class="display" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>ใบเสนอราคา (QT)</th>
                                <th>ลูกค้า</th>
                                <th>วันที่เสนอราคา</th>
                                <th>ราคา</th>
                                <th>สถานะ</th>
                                <th><i class='fa fa-bars'></i></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-estimates"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
let fstatus = [
            {id:"", text:"-<?php echo lang("status"); ?>-"},
            {id:"draft", text:"<?php echo lang("draft"); ?>"},
            {id:"sent", text:"<?php echo lang("sent"); ?>"},
            {id:"accepted", text:"<?php echo lang("accepted"); ?>"},
            {id:"declined", text:"<?php echo lang("declined"); ?>"}
        ];

$(document).ready(function () {
    $("#datagrid").appTable({
        source: '<?php echo current_url(); ?>',
        order: [[0, "desc"]],
        dateRangeType: "monthly",
        filterDropdown: [{name: "status", class: "w150", options: fstatus}],
        columns: [
            {title: "<?php echo lang("estimate") ?> ", "class": "w15p"},
            {title: "<?php echo lang("client") ?>"},
            {title: "<?php echo lang("estimate_date") ?>", "iDataSort": 2, "class": "w20p"},
            {title: "<?php echo lang("amount") ?>", "class": "text-right w20p"},
            {title: "<?php echo lang("status") ?>", "class": "text-center"},
            {title: "<i class='fa fa-bars'></i>", "class": "text-center option w100"}
        ],
        printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5]),
        xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5])
    });

    $("#datagrid").on("draw.dt", function () {
        $(".dropdown_status").on( "change", function() {
            
            //$("#datagrid").appTable({newData: result.data, dataId: result.id});
            axios.post('<?php echo current_url(); ?>', {
                task: 'update_doc_status',
                doc_id: $(this).data("doc_id"),
                update_status_to: $(this).val(),
            }).then(function (response) {
                data = response.data;
                if(data.process == "success"){
                    appAlert.success("HHHH Yes", {duration: 5000});
                }else{
                    appAlert.error(data.message, {duration: 5000});
                }
            }).catch(function (error) {});
        });
    });


    //appAlert.error("HH");
    //appAlert.success("HHHH Yes", {duration: 10000});



    /*$(".dropdown_status").on( "change", function() {
        alert( "Handler for `change` called." );
    } );*/





    /*$('#datagrid').DataTable({
        processing : true,
        serverSide : true,
        order : [],
        dateRangeType: "monthly",
        filterDropdown: [{name: "status", class: "w150", options: fstatus}],
        retrieve: true,
        ajax : {
            url:"<?php echo current_url(); ?>",
            type:"POST"
        }
    });*/

});

/*function load_data(start, length)
    {
        var dataTable = $('#customer_table').DataTable({
            "processing" : true,
            "serverSide" : true,
            "order" : [],
            "retrieve": true,
            "ajax" : {
                url:"<?php echo_uri("quotations/index/igrid") ?>",
                method:"POST",
                data:{start:start, length:length}
            }
        });
    }

    load_data();*/


</script>