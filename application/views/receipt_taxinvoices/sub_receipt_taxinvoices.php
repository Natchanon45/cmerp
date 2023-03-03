<div class="panel clearfix">
    <div class="table-responsive">
        <table id="sub-receipt_taxinvoice-table" class="display" cellspacing="0" width="100%">   
        </table>
    </div>

</div>

<script type="text/javascript">

    $(document).ready(function () {

        $("#sub-receipt_taxinvoice-table").appTable({
            source: '<?php echo_uri("receipt_taxinvoices/sub_receipt_taxinvoices_list_data/" . $recurring_receipt_taxinvoice_id) ?>',
            order: [[0, "desc"]],
            columns: [
                {title: "<?php echo lang("receipt_taxinvoice_id") ?>", "class": "w10p"},
                {visible: false},
                {visible: false},
                {visible: false, searchable: false},
                {title: "<?php echo lang("bill_date") ?>", "class": "w10p", "iDataSort": 3},
                {visible: false, searchable: false},
                {title: "<?php echo lang("due_date") ?>", "class": "w10p", "iDataSort": 5},
                {title: "<?php echo lang("receipt_taxinvoice_value") ?>", "class": "w10p text-right"},
                {title: "<?php echo lang("payment_received") ?>", "class": "w10p text-right"},
                {title: "<?php echo lang("status") ?>", "class": "w10p text-center"}
            ],
            summation: [{column: 7, dataType: 'currency', currencySymbol: "none"}, {column: 8, dataType: 'currency', currencySymbol: "none"}]
        });

    });
</script>