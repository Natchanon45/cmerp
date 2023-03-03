<table id="delivery-item-table" class="table display dataTable text-right strong table-responsive">     
    <tr>
        <!-- <td><?php //echo lang("sub_total"); ?></td>
        <td style="width: 120px;"><?php //echo to_currency($delivery_total_summary->delivery_subtotal, $delivery_total_summary->currency_symbol); ?></td>
        <td style="width: 100px;"> </td> -->
    </tr>

    <?php
    // if ($delivery_total_summary->deposit > 0) {
    //     echo ' 
    //         <tr> <td style="width: 200px;">วางค่ามัดจำแล้ว</td>
    //         <td>' . to_currency($delivery_total_summary->deposit, $delivery_total_summary->currency_symbol) . '</td>
    //         <td class="text-center"></td></tr>';
    // }
    $discount_row = "<tr></tr>";
    // $discount_row = "<tr>
    //                     <td style='padding-top:13px;'>" . lang("discount") . "</td>
    //                     <td style='padding-top:13px;'>" . to_currency($delivery_total_summary->discount_total, $delivery_total_summary->currency_symbol) . "</td>
    //                     <td class='text-center option w100'>" . modal_anchor(get_uri("deliverys/discount_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "data-post-delivery_id" => $delivery_id, "title" => lang('edit_discount'))) . "<span class='p20'>&nbsp;&nbsp;&nbsp;</span></td>
    //                 </tr>";

    if ($delivery_total_summary->delivery_subtotal && (!$delivery_total_summary->discount_total || ($delivery_total_summary->discount_total !== 0 && $delivery_total_summary->discount_type == "before_tax"))) {
        //when there is discount and type is before tax or no discount
        echo $discount_row;
    }
    ?>

    <?php if ($delivery_total_summary->tax) { ?>
        <tr>
            <td><?php echo $delivery_total_summary->tax_name; ?></td>
            <td><?php echo to_currency($delivery_total_summary->tax, $delivery_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>
    <?php if ($delivery_total_summary->tax2) { ?>
        <tr>
            <td><?php echo $delivery_total_summary->tax_name2; ?></td>
            <td><?php echo to_currency($delivery_total_summary->tax2, $delivery_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>

    <?php
    if ($delivery_total_summary->discount_total && $delivery_total_summary->discount_type == "after_tax") {
        //when there is discount and type is after tax
        echo $discount_row;
    }
    ?>

    <tr>
        <!-- <td><?php //echo lang("total"); ?></td>
        <td><?php //echo to_currency($delivery_total_summary->delivery_total, $delivery_total_summary->currency_symbol); ?></td>
        <td></td> -->
    </tr>
</table>