<div><b><?php echo lang("receipt_from"); ?></b></div>
<div style="line-height: 2px; border-bottom: 1px solid #f2f2f2;"> </div>
<div style="line-height: 3px;"> </div>

<strong><?php echo $receipt_info->su_comname.' '; 
//    echo modal_anchor(get_uri("receipts/modal_form"), "<i class='fa fa-edit ' style='color: blue' ></i> " , array("title" => lang('edit_receipt'), "data-post-id" => $receipt_info->id, "role" => "menuitem", "tabindex" => "-1"));

?> </strong>
<div style="line-height: 3px;"> </div>
<span class="invoice-meta" style="font-size: 90%; color: #666;">
<!-- <?php var_dump($receipt_info);?> -->
        <div><?php echo nl2br($receipt_info->su_add); ?>
            
                <br /> <?php echo $receipt_info->su_city; ?>
            
            
                , <?php echo $receipt_info->su_state; ?>
           
            
                <?php echo $receipt_info->su_zip; ?>
            
            
                <br /><?php echo $receipt_info->su_country; ?>
            
                
                <br/><?php echo lang("vat_number") . ": " . $receipt_info->su_vat_number; ?>
            
        </div>
</span>

<!-- <?php if ($receipt_info->su_add) { ?>
        <div><?php echo nl2br($receipt_info->su_add); ?>
            <?php if ($receipt_info->su_city) { ?>
                <br /> <?php echo $receipt_info->su_city; ?>
            <?php } ?>
            <?php if ($receipt_info->su_state) { ?>
                , <?php echo $receipt_info->su_state; ?>
            <?php } ?>
            <?php if ($receipt_info->su_zip) { ?>
                <?php echo $receipt_info->su_zip; ?>
            <?php } ?>
            <?php if ($receipt_info->su_country) { ?>
                <br /><?php echo $receipt_info->su_country; ?>
            <?php } ?>
            <?php if ($receipt_info->su_vat_number) { ?>
                <br /><?php echo lang("vat_number") . ": " . $receipt_info->su_vat_number; ?>
            <?php } ?>
        </div>
    <?php } ?> -->