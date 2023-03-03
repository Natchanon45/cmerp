<span style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo get_receipt_id($receipt_info->doc_no); ?>&nbsp;</span>

<div style="line-height: 10px;"></div><?php
if (isset($receipt_info->custom_fields) && $receipt_info->custom_fields) {
    foreach ($receipt_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . $this->load->view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value), true) . "</span><br />";
        }
    }
}
?>
<span><?php echo lang("receipt_date") . ": " . format_to_date($receipt_info->receipt_date, false); ?></span>
<br/>
<?php $po_ref = isset($po_ref->po_no) ? $po_ref->po_no : '-'?>
<span><?php echo lang("po_ref2"). ": ". $po_ref ?></span>
<br/>
<span><?php echo $receipt_info->tax_ref != NULL ? lang("tax_ref"). ": ".$receipt_info->tax_ref : lang("tax_ref"). ": -" ; ?></span>