<div id="page-content" class="p20 clearfix">
    <?php if($add_row){?>
    <!-- <div class="panel clearfix" id="low-project"><table id="low-project-table" class="display" cellspacing="0" width="100%"></table></div>
    <div class="panel clearfix" id="low-stock"><table id="low-stock-table" class="display" cellspacing="0" width="100%"></table></div> -->
    <?php } ?>
    <div class="panel clearfix">
        <ul id="pr-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15">ใบสั่งซื้อ (PO)</h4></li>
            <li><a id="monthly-pr-button" class="active" role="presentation" href="javascript:;" data-target="#monthly-purchaserequests"><?php echo lang("monthly"); ?></a></li>
            <li><a role="presentation" href="<?php echo_uri("purchaserequests/yearly/"); ?>" data-target="#yearly-purchaserequests"><?php echo lang('yearly'); ?></a></li>

            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
					<!-- <?php echo $add_row?$buttonTops:''; ?> -->
                    <?php /*<?php echo js_anchor("<i class='fa fa-plus-circle'></i> " . lang('add_pr2'), array("class" => "btn btn-default", "id" => "add-pr-btn2")); ?>*/?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-purchaserequests">
                <div class="table-responsive">
                    <table id="monthly-pr-table" class="display" cellspacing="0" width="100%">   
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-purchaserequests"></div>
        </div>
    </div>
</div>

<style>
#low-project{
    padding-left:10px;
    padding-right:10px;
    padding-bottom:0;
    margin-bottom:0;
}
#low-project-table .col-name-danger{
    color:red;
    font-weight:bold;
}
#low-project-table .col-name-warning{
    color:orange;
    font-weight:bold;
}

#low-stock{
    padding:10px;
    padding-bottom:5px;
    margin-bottom:5px;
}
#low-stock-table .col-name-danger{
    color:red;
    font-weight:bold;
}
#low-stock-table .col-name-warning{
    color:orange;
    font-weight:bold;
}
#low-project-table_wrapper .datatable-tools,
#low-stock-table_wrapper .datatable-tools{
    display:none;
}
#monthly-pr-table_length, #yearly-pr-table_length{
    display:none;
}
</style>
<script type="text/javascript">
    loadPrTable = function (selector, dateRange) {
        $(selector).appTable({
            source: '<?php echo_uri("purchaserequests/PO_data") ?>',
            order: [[0, "desc"]],
            dateRangeType: dateRange,
            filterDropdown: [
                {name: "status_id", class: "w150", options: <?php $this->load->view("purchaserequests/pr_statuses_dropdown"); ?>}
                ,{name: "supplier_id", class: "w150", options: <?php $this->load->view("purchaserequests/pr_suppliers_dropdown"); ?>}
                ,{name: "limit", class: "w100", options: [{"id":10,"text":"รายการ"},{"id":10,"text":"10"},{"id":25,"text":"25"},{"id":50,"text":"50"},{"id":100,"text":"100"},{"id":500,"text":"500"}]}
            ],
            //filterParams: {datatable: true, test:5}, // work
            //stateSave: true, // work
            //displayLength:25, // work
            //lengthMenu: [[10, 25, 50, 500], [10, 25, 50, "500"]], // not work
            columns: [
                {title: "<?php echo lang("purchaserequests_") ?>"},
                {title: "<?php echo lang("po") ?>"},
                {title: "<?php echo lang("category_name") ?>", "class": "text-left w15p"},
                {title: "<?php echo lang("project_name") ?>", "class": "text-left w10p"},
                {title: "<?php echo lang("buyer_org") ?>", "class": "text-left w10p"},
                {visible: false, searchable: false},
                {title: "<?php echo lang("pr_date") ?>", "iDataSort": 2, "class": "w10p"},
                {title: "<?php echo lang("amount") ?>", "class": "text-right w10p"},
                {title: "<?php echo lang("currency") ?>", "class": "text-right"},
                {title: "<?php echo lang("status") ?>", "class": "text-center w10p"}
                <?php echo $custom_field_headers; ?>,
                {title: "<i class='fa fa-bars'></i>", "class": "text-center option w10p"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 7, dataType: 'number', currencySymbol: AppHelper.settings.currencySymbol}]
        });
    };
    <?php if($add_row){?>
    loadLowProjectTable = function(selector) {
        $(selector).appTable({
            source: '<?php echo_uri("purchaserequests/list_lacked_project_materials") ?>',
            order: [[0, "desc"]],
            //filterDropdown: [{name: "status_id", class: "w150", options: <?php //$this->load->view("purchaserequests/pr_statuses_dropdown"); ?>}],
            columns: [
                {title: "<?php echo lang("ID") ?> ", "class": "w5p"},
                {title: "<?php echo lang("project_name") ?> "},
                {title: "<?php echo lang("material_amount") ?>", "class": "w15p"},
                {title: "<?php echo lang("action") ?>", "class": "w20p"}
            ],
            sDefaultContent:"Not found any data",
            searchPanes: {
                controls: false
            },
            dom: 'Plfrtip'
        });
    }
    loadLowStockTable = function(selector) {
        $(selector).appTable({
            source: '<?php echo_uri("purchaserequests/list_lacked_stock_materials") ?>',
            order: [[0, "desc"]],
            //filterDropdown: [{name: "supplier_id", class: "w150", options: <?php //$this->load->view("purchaserequests/pr_suppliers_dropdown"); ?>}],
            columns: [
                {title: "<?php echo lang("ID") ?> ", "class": "w5p"},
                {title: "<?php echo lang("import_name") ?> "},
                {title: "<?php echo lang("material_amount") ?>", "class": "w15p"},
                {title: "<?php echo lang("action") ?>", "class": "w20p"}
            ],
            sDefaultContent:"Not found any data",
            searchPanes: {
                controls: false
            },
            dom: 'Plfrtip'
        });
    }
    function purchaseRequest(btn_selector, prefix){
        var clicked_btn =  jQuery(btn_selector);
        var materials = [];
        var lacked_materials = jQuery('.'+prefix+'lacked_material');
        for(var i=0;i<lacked_materials.length;i++) {
            var material_id = jQuery(lacked_materials[i]).attr('data-material-id');
            var amount = parseFloat(jQuery(lacked_materials[i]).attr('data-lacked-amount'));
            var unit = jQuery(lacked_materials[i]).attr('data-unit');
            var supplier_name = jQuery(lacked_materials[i]).attr('data-supplier-name');
            var supplier_id = jQuery(lacked_materials[i]).attr('data-supplier-id');
            var price = jQuery(lacked_materials[i]).attr('data-price');
            var currency = jQuery(lacked_materials[i]).attr('data-currency');
            var currency_symbol = jQuery(lacked_materials[i]).attr('data-currency-symbol');
            if(materials[material_id]!=undefined) {
                materials[material_id].amount = amount;
            }else if(material_id) {
                materials[material_id] = {'id':material_id,'amount':amount,'unit':unit,'supplier_id':supplier_id,'supplier_name':supplier_name,'price':price,'currency':currency,'currency_symbol':currency_symbol};
            }
        }
        materials = materials.filter(function(ele){
            if(ele!=null) return ele;
        });
        //alert(JSON.stringify(materials));
        var form = jQuery('<form id="add-pr-form" method="post" action="<?php echo_uri("purchaserequests/add_pr_material_to_cart");?>"></form>');
        jQuery.each( materials, function( key, material ) {
            form.append('<input type="hidden" name="materials['+key+'][id]" value="'+material.id+'" />');
            form.append('<input type="hidden" name="materials['+key+'][amount]" value="'+material.amount+'" />');
            form.append('<input type="hidden" name="materials['+key+'][unit]" value="'+material.unit+'" />');
            form.append('<input type="hidden" name="materials['+key+'][supplier_id]" value="'+material.supplier_id+'" />');
            form.append('<input type="hidden" name="materials['+key+'][supplier_name]" value="'+material.supplier_name+'" />');
            form.append('<input type="hidden" name="materials['+key+'][price]" value="'+material.price+'" />');
            form.append('<input type="hidden" name="materials['+key+'][currency]" value="'+material.currency+'" />');
            form.append('<input type="hidden" name="materials['+key+'][currency_symbol]" value="'+material.currency_symbol+'" />');
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
        if(parentform.length>0)
            parentform.after(form);
        else
            jQuery(clicked_btn).after(form);
        jQuery('#add-pr-form').submit();
    };
    <?php } ?>
    $(document).ready(function () {
        loadPrTable("#monthly-pr-table", "monthly");

        <?php if($add_row){?>
        loadLowProjectTable("#low-project-table");
        loadLowStockTable("#low-stock-table");
        <?php } ?>

        $("#add-pr-btn").click(function () {
            //window.location.href = "<?php //echo get_uri("pr_items/grid_view"); ?>";
            window.location.href = "<?php echo get_uri("purchaserequests/process_pr");?>";
        });

        $("#cat-mng-btn").on('click', function () {
            window.location.href = "<?php echo get_uri("purchaserequests/categories");?>";
        });
    });

</script>

<?php $this->load->view("purchaserequests/update_pr_status_script"); ?>