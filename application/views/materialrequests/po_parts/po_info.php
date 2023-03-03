<span style="font-size:20px; font-weight: bold;">&nbsp;<?php echo lang('purchase_order_bill');//echo get_po_id($pr_info->id); ?>&nbsp;</span>
<div style="line-height: 10px;"></div><?php
if (isset($pr_info->custom_fields) && $pr_info->custom_fields) {
    foreach ($pr_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . $this->load->view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value), true) . "</span><br />";
        }
    }
}
?>
<span><?php echo lang("po_date") . ": " . format_to_date($pr_info->pr_date, false); ?></span>