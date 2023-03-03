<span class="receipt_taxinvoice-info-title" style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo get_receipt_taxinvoice_id($receipt_taxinvoice_info->doc_no); ?>&nbsp;</span>
<div style="line-height: 10px;"></div><?php
if (isset($receipt_taxinvoice_info->custom_fields) && $receipt_taxinvoice_info->custom_fields) {
    foreach ($receipt_taxinvoice_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . $this->load->view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value), true) . "</span><br />";
        }
    }
}
?>
<span><?php echo lang("bill_date") . ": " . format_to_date($receipt_taxinvoice_info->bill_date, false); ?></span><br />
<span><?php echo lang("due_date") . ": " . format_to_date($receipt_taxinvoice_info->due_date, false); ?></span><br/>
<?php //var_dump($receipt_taxinvoice_info); ?>
<span>อ้างอิงจาก : <?php echo $receipt_taxinvoice_info->es_doc_no ?></span>