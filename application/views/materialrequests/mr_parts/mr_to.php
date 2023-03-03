<div><b><?php echo lang("order_from"); ?></b></div>
<div style="line-height: 2px; border-bottom: 1px solid #f2f2f2;"> </div>
<div style="line-height: 3px;"> </div>
<strong><?php echo $client_info->first_name.' '.$client_info->last_name; ?> </strong>
<div style="line-height: 3px;"> </div>
<span class="invoice-meta" style="font-size: 90%; color: #666;">
    <?php if ($client_info->address) { ?>
        <div>
            <?php echo nl2br($client_info->address); ?>
            <?php echo nl2br($client_info->phone); ?>
        </div>
    <?php } ?>
</span>