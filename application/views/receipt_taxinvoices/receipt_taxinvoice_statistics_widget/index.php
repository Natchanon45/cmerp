<div id="receipt_taxinvoice-payment-statistics-container">
    <?php $this->load->view("receipt_taxinvoices/receipt_taxinvoice_statistics_widget/widget_data"); ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".load-currency-wise-data").click(function () {
            var currencyValue = $(this).attr("data-value");

            $.ajax({
                url: "<?php echo get_uri('receipt_taxinvoices/load_statistics_of_selected_currency') ?>" + "/" + currencyValue,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        $("#receipt_taxinvoice-payment-statistics-container").html(result.statistics);
                    }
                }
            });
        });
    });
</script>    

