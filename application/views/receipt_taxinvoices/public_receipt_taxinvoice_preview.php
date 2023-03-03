<div id="page-content" class="p20 clearfix public-receipt_taxinvoice-preview">
    <?php
    $this->load->view('includes/head');

    load_css(array(
        "assets/css/receipt_taxinvoice.css",
    ));
    ?>

    <div class="receipt_taxinvoice-preview">
        <?php if ($receipt_taxinvoice_total_summary->balance_due >= 1 && count($payment_methods) && !$client_info->disable_online_payment) { ?>
            <div class="panel panel-default  p15 no-border clearfix">
                <div class="inline-block strong pull-left pt5 pr15">
                    <?php echo lang("pay_receipt_taxinvoice"); ?>:
                </div>
                <div class="mr15 strong pull-left general-form pull-left" style="width: 145px;" >
                    <?php if (get_setting("allow_partial_receipt_taxinvoice_payment_from_clients")) { ?>
                        <span style="background-color: #f6f8f9; display: inline-block; padding: 7px 2px 7px 10px;"><?php echo $receipt_taxinvoice_total_summary->currency; ?></span><input type="text" id="payment-amount" value="<?php echo to_decimal_format($receipt_taxinvoice_total_summary->balance_due); ?>" class="form-control inline-block" style="padding-left: 3px; width: 100px" />
                    <?php } else { ?>
                        <span class="pt5 inline-block">
                            <?php echo to_currency($receipt_taxinvoice_total_summary->balance_due, $receipt_taxinvoice_total_summary->currency . " "); ?>
                        </span>
                    <?php } ?>
                </div>

                <?php
                foreach ($payment_methods as $payment_method) {

                    $method_type = get_array_value($payment_method, "type");

                    $pass_variables = array(
                        "payment_method" => $payment_method,
                        "balance_due" => $receipt_taxinvoice_total_summary->balance_due,
                        "currency" => $receipt_taxinvoice_total_summary->currency,
                        "receipt_taxinvoice_info" => $receipt_taxinvoice_info,
                        "receipt_taxinvoice_id" => $receipt_taxinvoice_id,
                        "paypal_url" => $paypal_url,
                        "contact_user_id" => $contact_id,
                        "verification_code" => $verification_code);

                    if ($receipt_taxinvoice_total_summary->balance_due >= get_array_value($payment_method, "minimum_payment_amount")) {
                        if ($method_type == "stripe") {
                            $this->load->view("receipt_taxinvoices/_stripe_payment_form", $pass_variables);
                        } else if ($method_type == "paypal_payments_standard") {
                            $this->load->view("receipt_taxinvoices/_paypal_payments_standard_form", $pass_variables);
                        } else if ($method_type == "paytm") {
                            $this->load->view("receipt_taxinvoices/_paytm_payment_form", $pass_variables);
                        }
                    }
                }
                ?>
            </div>
        <?php } ?>

        <div class="receipt_taxinvoice-preview-container bg-white mt15">
            <div class="col-md-12">
                <div class="ribbon"><?php echo $receipt_taxinvoice_status_label; ?></div>
            </div>

            <?php
            echo $receipt_taxinvoice_preview;
            ?>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#payment-amount").change(function () {
            var value = $(this).val();
            $(".payment-amount-field").each(function () {
                $(this).val(value);
            });
        });
    });

    $("html, body").css({"overflow-y": "auto"});

</script>
