<?php
$delivery_logo = "delivery_logo";
if (!get_setting($delivery_logo)) {
    $delivery_logo = "invoice_logo";
}
?>

<img src="<?php echo get_file_from_setting($delivery_logo, get_setting('only_file_path')); ?>" />