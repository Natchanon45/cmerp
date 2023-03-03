<?php
$order_logo = "order_logo";
if (!get_setting($order_logo)) {
    $order_logo = "invoice_logo";
}
?>

<img style="width: auto;height: 80px;" src="<?php echo get_file_from_setting($order_logo, get_setting('only_file_path')); ?>" />