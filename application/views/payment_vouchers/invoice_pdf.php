<div style=" margin: auto;">
    <?php
    $color = get_setting("invoice_color");
    if (!$color) {
        $color = "#2AA384";
    }
    $invoice_style = get_setting("invoice_style");
    $data = array(
        "client_info" => $client_info,
        "color" => $color,
        "invoice_info" => $invoice_info
    );

    if ($invoice_style === "style_2") {
        $this->load->view('payment_vouchers/invoice_parts/header_style_2.php', $data);
    } else {
        $this->load->view('payment_vouchers/invoice_parts/header_style_1.php', $data);
    }
    ?>
</div>

<br />

<table class="table-responsive" style="width: 100%; color: #444;">            
    <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
        <th style="text-align: center; border-right: 1px solid #eee;">วันที่ใบแจ้งหนี้ ใบกำกับภาษี</th>
        <th style="text-align: center; border-right: 1px solid #eee;">เลขที่ใบแจ้งหนี้ ใบกำกับภาษี</th>
        <th style="text-align: center;  width: 20%; border-right: 1px solid #eee;">รายละเอียด</th>
        <th style="text-align: center;  width: 20%; border-right: 1px solid #eee;">ชำระโดย</th>
        <th style="text-align: center;  width: 10%; ">ยอดที่ชำระ</th>
    </tr>
    <?php
    // var_dump($invoice_items);exit;
    foreach ($invoice_items as $item) {
        // var_dump($item);exit;
        ?>
        <?php
            if($item->invoice1_id == 0 || $item->invoice1_id == '' || $item->invoice1_id == null){
                if($item->payment_method_id == '1'){
                    $method = 'Cash';
                }
                else if($item->payment_method_id == '2'){
                    $method ='Stripe';
                }
                else if($item->payment_method_id == '3'){
                    $method = 'PayPal';
                }
                else if($item->payment_method_id == '4'){
                    $method = 'Paytm';
                }
                else if($item->payment_method_id == '5'){
                    $method = 'เงินโอน';
                }
                else if($item->payment_method_id == '6'){
                    $method = 'บัตรเครดิต/บัตรเดบิต';
                }
                else if($item->payment_method_id == '7'){
                    $method = 'เช็ค';
                }
        ?>
        <tr style="background-color: #f4f4f4; ">
            <td style="text-align: center; border: 1px solid #fff; padding: 10px;"><?php echo format_to_date($item->payment_date, false); ?></td>
            <td style="text-align: center; border: 1px solid #fff; padding: 10px;"><?php echo $item->invoice_id; ?></td>
            <td style="border: 1px solid #fff; padding: 10px;"><?php echo $item->detail; ?></td>
            <td style="text-align: center; border: 1px solid #fff; padding: 10px;"><?php echo $method; ?></td>
            <td style="text-align: right; border: 1px solid #fff; padding: 10px;"><?php echo $item->amount; ?></td>
        </tr>
        <?php }else{
            if($item->payment_method_id == '1'){
                $method = 'Cash';
            }
            else if($item->payment_method_id == '2'){
                $method ='Stripe';
            }
            else if($item->payment_method_id == '3'){
                $method = 'PayPal';
            }
            else if($item->payment_method_id == '4'){
                $method = 'Paytm';
            }
            else if($item->payment_method_id == '5'){
                $method = 'เงินโอน';
            }
            else if($item->payment_method_id == '6'){
                $method = 'บัตรเครดิต/บัตรเดบิต';
            }
            else if($item->payment_method_id == '7'){
                $method = 'เช็ค';
            }
        ?>
        <tr style="background-color: #f4f4f4; ">
        <td style="text-align: center; border: 1px solid #fff; padding: 10px;"><?php echo format_to_date($item->payment_date, false); ?></td>
            <td style="text-align: center; border: 1px solid #fff; padding: 10px;"><?php echo $item->invoice1_id; ?></td>
            <td style="border: 1px solid #fff; padding: 10px;"><?php echo $item->detail; ?></td>
            <td style="text-align: center; border: 1px solid #fff; padding: 10px;"><?php echo $method; ?></td>
            <td style="text-align: right; border: 1px solid #fff; padding: 10px;"><?php echo $item->amount; ?></td>
        </tr>
        <?php } ?>
        
    <?php } ?>
    <tr>
        <td colspan="4" style="text-align: right;"><?php echo lang("total"); ?></td>
        <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
            <?php 
                foreach($invoice_items1 as $item1){
                   echo $item1->tol;
                   break;
                }
            
            ?>
        </td>
    </tr>
    <!-- <?php
    if ($invoice_total_summary->discount_total && $invoice_total_summary->discount_type == "before_tax") {
        echo $discount_row;
    }
    ?>    
    <?php if ($invoice_total_summary->tax) { ?>
        <tr>
            <td colspan="3" style="text-align: right;"><?php echo $invoice_total_summary->tax_name; ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($invoice_total_summary->tax, $invoice_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php if ($invoice_total_summary->tax2) { ?>
        <tr>
            <td colspan="3" style="text-align: right;"><?php echo $invoice_total_summary->tax_name2; ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($invoice_total_summary->tax2, $invoice_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php if ($invoice_total_summary->tax3) { ?>
        <tr>
            <td colspan="3" style="text-align: right;"><?php echo $invoice_total_summary->tax_name3; ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($invoice_total_summary->tax3, $invoice_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?> -->
    <!-- <?php
    if ($invoice_total_summary->discount_total && $invoice_total_summary->discount_type == "after_tax") {
        echo $discount_row;
    }
    ?>  -->
    <!-- <?php if ($invoice_total_summary->total_paid) { ?>     
        <tr>
            <td colspan="3" style="text-align: right;"><?php echo lang("paid"); ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                
            </td>
        </tr>
    <?php } ?> -->
    <!-- <tr>
        <td colspan="3" style="text-align: right;"><?php echo lang("balance_due"); ?></td>
        <td style="text-align: right; width: 20%; background-color: <?php echo $color; ?>; color: #fff;">
            
        </td>
    </tr> -->
</table>
<?php if ($invoice_info->note) { ?>
    <br />
    <br />
    <div style="border-top: 2px solid #f2f2f2; color:#444; padding:0 0 20px 0;"><br /><?php echo nl2br($invoice_info->note); ?></div>
<?php } else { ?> <!-- use table to avoid extra spaces -->
    <br /><br /><table class="invoice-pdf-hidden-table" style="border-top: 2px solid #f2f2f2; margin: 0; padding: 0; display: block; width: 100%; height: 10px;"></table>
<?php } ?>
<!-- <span style="color:#444; line-height: 14px;">
    <?php echo get_setting("invoice_footer"); ?>
</span> -->

