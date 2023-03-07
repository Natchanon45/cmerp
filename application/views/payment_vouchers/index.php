 

<div id="page-content" class="clearfix p20">
    <div class="panel clearfix">
        <ul id="invoices-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
           
		   <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo lang("payment_vouchers"); ?></h4></li>
			
			
            <li><a id="monthly-expenses-button"  role="presentation" class="active" href="javascript:;" data-target="#monthly-invoices"><?php echo lang("monthly"); ?></a></li>
			
			
            <li><a role="presentation" href="<?php echo_uri("payment_vouchers/yearly/"); ?>" data-target="#yearly-invoices"><?php echo lang('yearly'); ?></a></li>
            <li><a role="presentation" href="<?php echo_uri("payment_vouchers/custom/"); ?>" data-target="#custom-invoices"><?php echo lang('custom'); ?></a></li>
            <li><a role="presentation" href="<?php echo_uri("payment_vouchers/recurring/"); ?>" data-target="#recurring-invoices"><?php echo lang('recurring'); ?></a></li>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <?php //if ( $can_edit_invoices ) { 
						
						echo $buttonTop;
						
						
						
					//} ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-invoices">
                <div class="table-responsive">
                    <table id="monthly-invoice-table" class="display" cellspacing="0" width="100%">   
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-invoices"></div>
            <div role="tabpanel" class="tab-pane fade" id="custom-invoices"></div>
            <div role="tabpanel" class="tab-pane fade" id="recurring-invoices"></div>
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
    if ("<?php echo $can_edit_invoices ?>") {
    optionVisibility = true;
    }

    $(selector).appTable({
    source: '<?php echo_uri("payment_vouchers/list_data") ?>',
            dateRangeType: dateRange,
            order: [[0, "desc"]],
            filterDropdown: [
            {name: "status", class: "w150", options: <?php $this->load->view("payment_vouchers/invoice_statuses_dropdown"); ?>},
<?php if ($currencies_dropdown) { ?>
                
<?php } ?>
            ],
            rangeDatepicker: customDatePicker,
            columns: [
				{title: "<?php echo lang("payment_voucher_id") ?>", "class": "w15p"},
                {title: "<?php echo lang("project") ?>", "class": "w15p"},
				{visible: false, searchable: false},
				{title: "<?php echo lang("bill_date") ?>", "iDataSort": 3},
				{visible: false, searchable: false},
				{title: "<?php echo lang("due_date") ?>", "iDataSort": 5},
			   
			
				{title: "<?php echo lang("status") ?>", "class": "text-center"},
				{title: '<i class="fa fa-bars"></i>', "class": "text-center dropdown-option w100", visible: optionVisibility}
            ],
            printColumns: combineCustomFieldsColumns([0], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0], '<?php echo $custom_field_headers; ?>'),
            summation: []
    });
    };
    $(document).ready(function () {
    loadInvoicesTable("#monthly-invoice-table", "monthly");
    });
</script>