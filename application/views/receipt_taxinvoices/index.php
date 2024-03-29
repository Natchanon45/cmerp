<div id="page-content" class="clearfix p20">
    <div class="panel clearfix">
        <ul id="receipt_taxinvoices-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
           
		   <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo lang("receipt_taxinvoices"); ?></h4></li>
			
			
            <li><a id="monthly-expenses-button"  role="presentation" class="active" href="javascript:;" data-target="#monthly-receipt_taxinvoices"><?php echo lang("monthly"); ?></a></li>
			
			
            <li><a role="presentation" href="<?php echo_uri("receipt_taxinvoices/yearly/"); ?>" data-target="#yearly-receipt_taxinvoices"><?php echo lang('yearly'); ?></a></li>
            <li><a role="presentation" href="<?php echo_uri("receipt_taxinvoices/custom/"); ?>" data-target="#custom-receipt_taxinvoices"><?php echo lang('custom'); ?></a></li>
            <li><a role="presentation" href="<?php echo_uri("receipt_taxinvoices/recurring/"); ?>" data-target="#recurring-receipt_taxinvoices"><?php echo lang('recurring'); ?></a></li>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <?php echo $buttonTop; ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-receipt_taxinvoices">
                <div class="table-responsive">
                    <table id="monthly-receipt_taxinvoice-table" class="display" cellspacing="0" width="100%">   
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-receipt_taxinvoices"></div>
            <div role="tabpanel" class="tab-pane fade" id="custom-receipt_taxinvoices"></div>
            <div role="tabpanel" class="tab-pane fade" id="recurring-receipt_taxinvoices"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadInvoicesTable = function (selector, dateRange) {
    var customDatePicker = "";
    if (dateRange === "custom") {
    customDatePicker = [{startDate: {name: "start_date", value: moment().format("YYYY-MM-DD")}, endDate: {name: "end_date", value: moment().format("YYYY-MM-DD")}, showClearButton: true}];
    dateRange = "";
    }

    var optionVisibility = false;
    if ("<?php echo $can_edit_receipt_taxinvoices ?>") {
    optionVisibility = true;
    }

    $(selector).appTable({
    source: '<?php echo_uri("receipt_taxinvoices/list_data") ?>',
            dateRangeType: dateRange,
            order: [[0, "desc"]],
            filterDropdown: [
            {name: "status", class: "w150", options: <?php $this->load->view("receipt_taxinvoices/receipt_taxinvoice_statuses_dropdown"); ?>},
<?php if ($currencies_dropdown) { ?>
                {name: "currency", class: "w150", options: <?php echo $currencies_dropdown; ?>}
<?php } ?>
            ],
            rangeDatepicker: customDatePicker,
            columns: [
            {title: "<?php echo lang("receipt_taxinvoice_id") ?>", "class": "w10p"},
            {title: "<?php echo lang("client") ?>", "class": ""},
            {title: "<?php echo lang("project") ?>", "class": "w15p"},
            {visible: false, searchable: false},
            {title: "<?php echo lang("bill_date") ?>", "class": "w10p", "iDataSort": 3},
            {visible: false, searchable: false},
            {title: "<?php echo lang("due_date") ?>", "class": "w10p", "iDataSort": 5},
            {title: "<?php echo lang("invoice_value") ?>", "class": "w10p text-right"},
            {title: "<?php echo lang("payment_received") ?>", "class": "w10p text-right"},
            {title: "<?php echo lang("status") ?>", "class": "w10p text-center"}
<?php echo $custom_field_headers; ?>,
            {title: '<i class="fa fa-bars"></i>', "class": "text-center dropdown-option w100", visible: optionVisibility}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 7, dataType: 'number'}, {column: 8, dataType: 'number'}]
    });
    };
    $(document).ready(function () {
    loadInvoicesTable("#monthly-receipt_taxinvoice-table", "monthly");
    });
</script>