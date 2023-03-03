<span style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo get_delivery_id($delivery_info->doc_no); ?>&nbsp;</span>
<div style="line-height: 10px;"></div><?php
if (isset($delivery_info->custom_fields) && $delivery_info->custom_fields) {
    foreach ($delivery_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . $this->load->view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value), true) . "</span><br />";
        }
    }
}
?>
<hr/>
<span><?php echo lang("delivery_date") . ": " . format_to_date($delivery_info->delivery_date, false); ?></span><br />
<!-- <span><?php //echo lang("valid_until") . ": " . format_to_date($delivery_info->valid_until, false); ?></span> -->
