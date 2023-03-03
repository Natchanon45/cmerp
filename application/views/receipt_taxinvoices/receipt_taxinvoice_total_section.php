

<table id="receipt_taxinvoice-item-table" class="table display dataTable text-right strong table-responsive">
    <tr>
        <td style="width: 170px;"><?php echo lang("sub_total"); ?></td>
        <td style="width: 120px;"><?php echo to_currency($receipt_taxinvoice_total_summary->receipt_taxinvoice_subtotal, $receipt_taxinvoice_total_summary->currency_symbol); ?></td>
        <?php if ($can_edit_receipt_taxinvoices) { ?>
            <td style="width: 100px;"> </td>
        <?php } ?>
    </tr>
    <!-- <?php
        if($receipt_taxinvoice_total_summary->include_deposit == 1){
            if($receipt_taxinvoice_total_summary->deposit > 0){
                echo ' 
                <tr> <td style="width: 200px;">วางค่ามัดจำแล้ว</td>
                <td>'. to_currency( $receipt_taxinvoice_total_summary->deposit, $receipt_taxinvoice_total_summary->currency_symbol) .'</td>
                <td class="text-center"></td></tr>';
            }
            
        }
    ?> -->

    <?php
    $discount_edit_btn = "";
    if ($can_edit_receipt_taxinvoices) {
    //    $discount_edit_btn = "<td class='text-center option w100'>" . modal_anchor(get_uri("receipt_taxinvoices/discount_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "data-post-receipt_taxinvoice_id" => $receipt_taxinvoice_id, "title" => lang('edit_discount'))) . "<span class='p20'>&nbsp;&nbsp;&nbsp;</span></td>";
    }
    $discount_row = "<tr>
                        <td style='padding-top:13px;'>" . lang("discount") . "</td>
                        <td style='padding-top:13px;'>" . to_currency($receipt_taxinvoice_total_summary->discount_total, $receipt_taxinvoice_total_summary->currency_symbol) . "</td>
                        $discount_edit_btn
                    </tr>";

    if ($receipt_taxinvoice_total_summary->receipt_taxinvoice_subtotal && (!$receipt_taxinvoice_total_summary->discount_total || ($receipt_taxinvoice_total_summary->discount_total !== 0 && $receipt_taxinvoice_total_summary->discount_type == "before_tax"))) {
        //when there is discount and type is before tax or no discount
        echo $discount_row;
    }
    // var_dump($receipt_taxinvoice_total_summary);
       if($receipt_taxinvoice_total_summary->include_deposit == 2){
           $deposit = 0;
          
       }else{
            
            $deposit =  $receipt_taxinvoice_total_summary->deposit;
       }
		
		$test['name'] = 'pay_type';
        if(isset($receipt_taxinvoice_info->pay_type)){
		    $test['selected'] = $receipt_taxinvoice_info->pay_type;
        }
        $discount_percentage_dropdown = array( "percentage" => '%', "fixed_amount" => $receipt_taxinvoice_total_summary->currency_symbol);
    ?>


    <?php if ($receipt_taxinvoice_total_summary->tax) { ?>
        <tr>
            <td><?php echo $receipt_taxinvoice_total_summary->tax_name; ?></td>
            <td><?php echo to_currency($receipt_taxinvoice_total_summary->tax, $receipt_taxinvoice_total_summary->currency_symbol); ?></td>
            <?php if ($can_edit_receipt_taxinvoices) { ?>
                <td></td>
            <?php } ?>
        </tr>
    <?php } ?>
    <?php if ($receipt_taxinvoice_total_summary->tax2) { ?>
        <tr>
            <td><?php echo $receipt_taxinvoice_total_summary->tax_name2; ?></td>
            <td><?php echo to_currency($receipt_taxinvoice_total_summary->tax2, $receipt_taxinvoice_total_summary->currency_symbol); ?></td>
            <?php if ($can_edit_receipt_taxinvoices) { ?>
                <td></td>
            <?php } ?>
        </tr>
    <?php } ?>
    <?php if ($receipt_taxinvoice_total_summary->tax3) { ?>
        <tr>
            <td><?php echo $receipt_taxinvoice_total_summary->tax_name3; ?></td>
            <td><?php echo to_currency($receipt_taxinvoice_total_summary->tax3, $receipt_taxinvoice_total_summary->currency_symbol); ?></td>
            <?php if ($can_edit_receipt_taxinvoices) { ?>
                <td></td>
            <?php } ?>
        </tr>
    <?php } ?>
    <?php
    if ($receipt_taxinvoice_total_summary->discount_total && $receipt_taxinvoice_total_summary->discount_type == "after_tax") {
        //when there is discount and type is after tax
        echo $discount_row;
    }
    ?>
    <?php if ($receipt_taxinvoice_total_summary->total_paid) { ?>
        <tr>
            <td><?php echo lang("paid"); ?></td>
            <td><?php echo to_currency($receipt_taxinvoice_total_summary->total_paid, $receipt_taxinvoice_total_summary->currency_symbol); ?></td>
            <?php if ($can_edit_receipt_taxinvoices) { ?>
                <td></td>
            <?php } ?>
        </tr>
    <?php } ?>
    <tr>
        <td style="width: 170px;">ยอดรวมภาษีมูลค่าเพิ่ม</td>
        <td><?php 
            echo to_currency($receipt_taxinvoice_total_summary->balance_due - $deposit, $receipt_taxinvoice_total_summary->currency_symbol); 
            ?></td>
        <?php if ($can_edit_receipt_taxinvoices) { ?>
            <td></td>
        <?php } ?>
    </tr>

    <?php 
    if(isset($receipt_taxinvoice_total_summary->pay_spilter)){
        $val1 = ($receipt_taxinvoice_total_summary->pay_spilter*$receipt_taxinvoice_total_summary->tax_percentage)/100;
        $val2 = ($receipt_taxinvoice_total_summary->pay_spilter*$receipt_taxinvoice_total_summary->tax_percentage2)/100;
        if($receipt_taxinvoice_total_summary->tax_percentage == 7 ){           
            $ture_val = $receipt_taxinvoice_total_summary->pay_spilter + $val1 - $val2;
        }else{
            $ture_val = $receipt_taxinvoice_total_summary->pay_spilter - $val1 + $val2;
            
            
        }
    }

    // var_dump($receipt_taxinvoice_total_summary);

	if(true) {  
        if($receipt_taxinvoice_total_summary->include_deposit == 2){

        }else{            
    
            if($receipt_taxinvoice_total_summary->tax){            
                echo '<tr> <td style="width: 250px; border-top: 2px solid #999; ">แบ่งชำระ'.$receipt_taxinvoice_total_summary->tax_name.' </td>
                <td style="border-top: 2px solid #999; ">'. to_currency( $val1, $receipt_taxinvoice_total_summary->currency_symbol) .'</td>
                <td class="text-center" style="border-top: 2px solid #999; "></td></tr>';

               
            }
    
            if($receipt_taxinvoice_total_summary->tax2){
                echo '<tr> <td style="width: 250px;">แบ่งชำระ'.$receipt_taxinvoice_total_summary->tax_name2.' </td>
                <td>'. to_currency( $val2, $receipt_taxinvoice_total_summary->currency_symbol) .'</td>
                <td class="text-center"></td></tr>';

            }

            
                echo ' 
                <tr> <td style="width: 200px;">แบ่งชำระ </td>
                <td>'. to_currency( $ture_val-$receipt_taxinvoice_total_summary->total_paid, $receipt_taxinvoice_total_summary->currency_symbol) .'</td>
                <td class="text-center option w100">'.modal_anchor(get_uri("receipt_taxinvoices/pay_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "data-post-receipt_taxinvoice_id" => $receipt_taxinvoice_id, "title" => 'แก้ไขแบ่งชำระ')).'<span class="p20">&nbsp;&nbsp;&nbsp;</span></td></tr>';
            }
            
            
            
	}
	?>
   
       
    
	

</table>

<!-- <?php echo lang("balance_due"); ?> -->
<!-- ?pay_sp=2.3&pay_type=1 -->