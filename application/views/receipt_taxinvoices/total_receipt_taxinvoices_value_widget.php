<?php
$panel = "";
$icon = "";
$value = "";
$lang = "";
$link = "";

if ($type == "receipt_taxinvoices") {
    $lang = lang("receipt_taxinvoice_value");
    $panel = "panel-primary";
    $icon = "fa-file-text";
    $value = to_currency($receipt_taxinvoices_info->receipt_taxinvoices_total);
    $link = get_uri('receipt_taxinvoices/index');
} else if ($type == "payments") {
    $lang = lang("payments");
    $panel = "panel-success";
    $icon = "fa-check-square";
    $value = to_currency($receipt_taxinvoices_info->payments_total);
    $link = get_uri('receipt_taxinvoice_payments/index');
} else if ($type == "due") {
    $lang = lang("due");
    $panel = "panel-coral";
    $icon = "fa-money";
    $value = to_currency(ignor_minor_value($receipt_taxinvoices_info->due));
    $link = get_uri('receipt_taxinvoices/index');
} else if ($type == "draft") {
    $lang = lang("draft_receipt_taxinvoices_total");
    $panel = "panel-orange white-link";
    $icon = "fa-file-text";
    $value = to_currency($receipt_taxinvoices_info->draft_total);
    $link = get_uri('receipt_taxinvoices/index');
}
?>

<div class="panel <?php echo $panel ?>">
    <a href="<?php echo $link; ?>" class="white-link">
        <div class="panel-body ">
            <div class="widget-icon">
                <i class="fa <?php echo $icon; ?>"></i>
            </div>
            <div class="widget-details">
                <h1><?php echo $value; ?></h1>
                <?php echo $lang; ?>
            </div>
        </div>
    </a>
</div>