<span style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo $pr_info->doc_no?$pr_info->doc_no:lang('no_have_doc_no').':'.$pr_info->id; ?>&nbsp;</span>
<div style="line-height: 10px;"></div><?php
if (isset($order_info->custom_fields) && $order_info->custom_fields) {
    foreach ($order_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . $this->load->view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value), true) . "</span><br />";
        }
    }
}
?>
<span><?php echo lang("pr_date") . ": " . format_to_date($pr_info->pr_date, false); ?></span>