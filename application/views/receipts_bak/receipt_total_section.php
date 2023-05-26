<?php 

$trs[] = ' 
	<tr>
        <td>'. lang("sub_total").'</td>
        <td style="width: 120px;">'. to_currency( $receipt_total_summary->receipt_subtotal, $receipt_total_summary->currency_symbol ) .'</td>
        <td style="width: 100px;"> </td>
    </tr>
';

$discount_row = "
	<tr>
		<td style='padding-top:13px;'>" . lang("discount") . "</td>
		<td style='padding-top:13px;'>" . to_currency($receipt_total_summary->discount_total, $receipt_total_summary->currency_symbol) . "</td>
	</tr>
";

// <td class='text-center option w100'>" . modal_anchor(get_uri("receipts/discount_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "data-post-receipt_id" => $receipt_id, "title" => lang('edit_discount'))) . "<span class='p20'>&nbsp;&nbsp;&nbsp;</span></td>

if ($receipt_total_summary->receipt_subtotal && (!$receipt_total_summary->discount_total || ($receipt_total_summary->discount_total !== 0 && $receipt_total_summary->discount_type == "before_tax"))) {
	//when there is discount and type is before tax or no discount
	$trs[] = $discount_row;
}

if ( $receipt_total_summary->tax ) { 
	$trs[] = '
		<tr>
            <td>'. $receipt_total_summary->tax_name .'</td>
            <td>'. to_currency( $receipt_total_summary->tax, $receipt_total_summary->currency_symbol ) .'</td>
            <td></td>
        </tr>
	';       
 }
 

if ($receipt_total_summary->tax2) {
   
	$trs[] = '
		<tr>
			<td>'. $receipt_total_summary->tax_name2 .'</td>
			<td>'. to_currency( $receipt_total_summary->tax2, $receipt_total_summary->currency_symbol ) .'</td>
			<td></td>
		</tr>
	';
} 
if ( $receipt_total_summary->discount_total && $receipt_total_summary->discount_type == "after_tax" ) {

   $trs[] = $discount_row;
}

$trs[] = '
	<tr>
		<td>'. lang("total") .'</td>
		<td>'. to_currency( $receipt_total_summary->receipt_total, $receipt_total_summary->currency_symbol ) .'</td>
		<td></td>
	</tr>
';

if( !empty( $receipt_total_summary->payment ) ) {
	$trs[] = '
		<tr>
			<td>ชำระเงินแล้ว</td>
			<td>'. to_currency( $receipt_total_summary->payment, $receipt_total_summary->currency_symbol ) .'</td>
			<td></td>
		</tr>';
		
		
		
	$trs[] = '
		<tr>
			<td>'. $receipt_total_summary->paymentStatus .'</td>
			<td>'. $receipt_total_summary->notPaid .'</td>
			<td></td>
		</tr>
	';
	
}

?>

<table id="receipt-item-table" class="table display dataTable text-right strong table-responsive">     
   <?php echo implode( '', $trs ) ?>


   
</table>