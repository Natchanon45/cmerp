<div><b><?php echo lang("pr_to"); ?></b></div>
<div style="line-height: 2px; border-bottom: 1px solid #f2f2f2;"> </div>
<div style="line-height: 3px;"> </div>
<strong><?php echo $supplier; ?> </strong>
<div style="line-height: 3px;"> </div>
<span class="invoice-meta" style="font-size: 90%; color: #666;">
    <?php if ($supplier_address) { ?>
        <div>
            <?php echo nl2br($supplier_address); ?>
        </div>
    <?php } ?>
</span>