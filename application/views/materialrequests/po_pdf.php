<style>
.invoice-preview table{
    margin-top:0;
}
.po-table th, .po-table tbody tr td{
    border:1px solid #000000;
    background-color:transparent;
}
</style><div style=" margin: auto;">
    <?php
    $color = get_setting("pr_color");
    if (!$color) {
        $color = get_setting("invoice_color") ? get_setting("invoice_color") : "#2AA384";
    }
    $style = get_setting("invoice_style");
    ?>
    <?php
    $data = array(
        "client_info" => $client_info,
        "color" => $color,
        "pr_info" => $pr_info,
        "supplier"=>$supplier,
        "supplier_address"=>@$suppliers[$supplier]
    );
    if ($style === "style_2") {
        $this->load->view('purchaserequests/po_parts/header_style_2.php', $data);
    } else {
        $this->load->view('purchaserequests/po_parts/header_style_1.php', $data);
    }
    ?>
</div>



<br />

<table class="table-responsiveX po-table" style="width: 100%; color: #444;margin-top:0;" cellpadding="0" cellspacing="0">            
    <tr style="font-weight: bold;">
        <th style="width: 65%;"> <?php echo lang("item"); ?> </th>
        <th style="text-align: center;  width: 15%;"> <?php echo lang("quantity"); ?></th>
        <th style="text-align: right;  width: 10%;"> <?php echo lang("rate"); ?></th>
        <th style="text-align: right;  width: 10%; "> <?php echo lang("total"); ?></th>
    </tr>
    <?php
    $count = 0;
    foreach ($pr_items as $item) {
    ?>
        <tr >
            <td style="width: 65%; padding: 10px;"><?php echo $item->title; ?>
                <br />
                <span style="color: #888; font-size: 90%;"><?php echo nl2br($item->description); ?></span>
            </td>
            <td style="text-align: center; width: 15%;"> <?php echo $item->quantity . " " . $item->unit_type; ?></td>
            <td style="text-align: right; width: 10%;"> <?php echo to_currency($item->rate, $item->currency_symbol); ?></td>
            <td style="text-align: right; width: 10%;"> <?php echo to_currency($item->total, $item->currency_symbol); ?></td>
        </tr>
    <?php
        $count++;
    }
    if($count<$items_per_page) {
        for($count;$count<=$items_per_page;$count++) {?>
        <tr style="">
            <td style="width: 65%; padding: 10px;">
                <br />
                <span style="color: #888; font-size: 90%;"></span>
            </td>
            <td style="text-align: center; width: 15%;"></td>
            <td style="text-align: right; width: 10%;"></td>
            <td style="text-align: right; width: 10%;"></td>
        </tr>
    <?php }
    } ?>
    </table>
    <table class="table-responsiveX" style="width: 100%; color: #444;margin-top:0;" cellpadding="0" cellspacing="0"> 
    <tr>
        <td colspan="3" style="text-align: right;"><?php echo lang("sub_total"); ?></td>
        <td style="text-align: right; width: 10%;">
            <?php echo to_currency($pr_total_summary->pr_subtotal, $pr_total_summary->currency_symbol); ?>
        </td>
    </tr>
    <?php
    if ($pr_total_summary->discount_type == "before_tax") {
    ?> 
    <tr>
        <td colspan="3" style="text-align: right;"><?php echo lang("discount");?></td>
        <td style="text-align: right; width: 20%;"><?php
        if ($pr_total_summary->discount_total) {
            echo to_currency($pr_total_summary->discount_total, $pr_total_summary->currency_symbol);
        }else echo 0;
        ?></td>
    </tr>
    <?php } ?>
    <tr>
        <td colspan="3" style="text-align: right;"><?php echo $pr_total_summary->tax_name; ?></td>
        <td style="text-align: right; width: 10%;">
            <?php
            if ($pr_total_summary->tax) {
                echo to_currency($pr_total_summary->tax, $pr_total_summary->currency_symbol);
            };?>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="text-align: right;"><?php echo $pr_total_summary->tax_name2; ?></td>
        <td style="text-align: right; width: 10%;">
            <?php
            if ($pr_total_summary->tax2) {
            echo to_currency($pr_total_summary->tax2, $pr_total_summary->currency_symbol);
            }?>
        </td>
    </tr>
    <?php
    if ($pr_total_summary->discount_type == "after_tax") {
    ?> 
    <tr>
        <td colspan="3" style="text-align: right;"><?php echo lang("discount");?></td>
        <td style="text-align: right; width: 20%;"><?php
        if ($pr_total_summary->discount_total) {
            echo to_currency($pr_total_summary->discount_total, $pr_total_summary->currency_symbol);
        }else echo 0;
        ?></td>
    </tr>
    <?php } ?>
    <tr>
        <td colspan="3" style="text-align: right;"><?php echo lang("total"); ?></td>
        <td style="text-align: right; width: 10%; background-color: <?php echo $color; ?>; color: #fff;">
            <?php echo to_currency($pr_total_summary->pr_total, $pr_total_summary->currency_symbol); ?>
        </td>
    </tr>
</table>
<?php if ($pr_info->note) { ?>
    <br />
    <br />
    <div style="border-top: 2px solid #f2f2f2; color:#444; padding:0 0 20px 0;"><br /><?php echo nl2br($pr_info->note); ?></div>
<?php } else { ?><!-- use table to avoid extra spaces -->
    <br /><br /><table class="invoice-pdf-hidden-table" style="border-top: 2px solid #f2f2f2; margin: 0; padding: 0; display: block; width: 100%; height: 10px;"></table>
<?php } ?>
<table width="100%">
    <tr style="padding-bottom:0px !important;margin-bottom:0px !important;">
        <td width="50%" style="padding-bottom:0px !important;margin-bottom:0px important;">&nbsp;</td>
        <td style="text-align:center;padding-bottom:0px !important;margin-bottom:0px important;"><?php
            if(isset($usgn->signature)){?>
            <img src="<?php echo base_url().$usgn->signature;?>" style="height:100px;width:auto;" />
            <?php } ?>
        </td>
    </tr>
    <tr style="line-height: 2px !important;height:2px !important;">
        <td width="50%" style="line-height: 2px !important;height:2px !important;padding-top:0px !important;padding-bottom:0px !important;margin-top:0px important;margin-bottom:0px important;">&nbsp;</td>
        <td style="text-align:center;line-height: 2px !important;height:2px !important;padding-top:0px !important;padding-bottom:0px !important;margin-top:0px important;margin-bottom:0px important;">.................................................</td>
    </tr>
    <tr style="margin:0 !important;padding:0 !important;">
        <td style="margin:0 !important;padding:0 !important;">&nbsp;</td>
        <td style="text-align:center;margin:0 !important;padding:0 !important;"><?php echo @$approver->first_name.' '.@$approver->last_name;?></td>
    </tr>
    <tr style="margin:0 !important;padding:0 !important;">
        <td style="margin:0 !important;padding:0 !important;">&nbsp;</td>
        <td style="text-align:center;margin:0 !important;padding:0 !important;">(<?php echo lang('approver');?>)</td>
    </tr>
</table>
<span style="color:#444; line-height: 14px;">
    <?php echo get_setting("pr_footer"); ?>
</span>