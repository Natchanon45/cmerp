<div id="page-content" class="p20 clearfix">
    <div class="panel clearfix">
        <ul id="receipt-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo lang('receipts'); ?></h4></li>
            <li><a id="monthly-receipt-button" class="active" role="presentation" href="javascript:;" data-target="#monthly-receipts"><?php echo lang("monthly"); ?></a></li>
            <li><a role="presentation" href="<?php echo_uri("receipts/yearly/"); ?>" data-target="#yearly-receipts"><?php echo lang('yearly'); ?></a></li>

            <div class="tab-title clearfix no-breceipt">
                <div class="title-button-group">
                    <?php 
					if(!empty( $this->getRolePermission['add_row'] ) ) {
						// echo js_anchor("<i class='fa fa-plus-circle'></i> " . lang('add_receipt'), array("class" => "btn btn-default", "id" => "add-receipt-btn"));
                        echo modal_anchor(get_uri("receipts/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("class" => "btn btn-default", "title" => lang('add_item')));

					} 
					
					
					
					 ?>           
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-receipts">
                <div class="table-responsive">
                    <table id="monthly-receipt-table" class="display" cellspacing="0" width="100%">   
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-receipts"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadreceiptsTable = function (selector, dateRange) {
        $(selector).appTable({
            source: '<?php echo_uri("receipts/list_data") ?>',
            receipt: [[0, "desc"]],
            dateRangeType: dateRange,
            filterDropdown: [{name: "status_id", class: "w150", options: <?php $this->load->view("receipts/receipt_statuses_dropdown"); ?>}],
            columns: [
                {title: "<?php echo lang("receipt") ?> ", "class": "w15p"},
                {title: "<?php echo lang("store") ?>"},
                {visible: false, searchable: false},
                {title: "<?php echo lang("receipt_date") ?>", "iDataSort": 2, "class": "w20p"},
                {title: "<?php echo lang("amount") ?>", "class": "text-right w20p"},
                {title: "<?php echo lang("status") ?>", "class": "text-center"}
<?php echo $custom_field_headers; ?>,
                {title: "<i class='fa fa-bars'></i>", "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 4, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol}]
        });
    };

    $(document).ready(function () {
        loadreceiptsTable("#monthly-receipt-table", "monthly");

        $("#add-receipt-btn").click(function () {
            window.location.href = "<?php echo get_uri("receipts/item_modal_form"); ?>";
        });
    });

</script>

<?php $this->load->view("receipts/update_receipt_status_script"); ?>