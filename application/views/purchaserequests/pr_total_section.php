<table id="pr-item-table" class="table display dataTable text-right strong table-responsive">     
    <tr>
        <td><?php echo lang("sub_total"); ?></td>
        <td style="width: 120px;"><?php echo to_currency($pr_total_summary->pr_subtotal, $pr_total_summary->currency_symbol); ?></td>
        <td style="width: 100px;"> </td>
    </tr>

    <?php
    if($this->Permission_m->update_purchase_request == true && $pr_info->status_id == 1){
        $discount_row = "<tr>
                            <td style='padding-top:13px;'>" . lang("discount") . "</td>
                            <td style='padding-top:13px;'>" . to_currency($pr_total_summary->discount_total, $pr_total_summary->currency_symbol) . "</td>
                            <td class='text-center option w100'>" . ($edit_row?modal_anchor(get_uri("purchaserequests/discount_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "data-post-pr_id" => $pr_id, "title" => lang('edit_discount'))):'') . "<span class='p20'>&nbsp;&nbsp;&nbsp;</span></td>
                        </tr>";

        if ($pr_total_summary->pr_subtotal && (!$pr_total_summary->discount_total || ($pr_total_summary->discount_total !== 0 && $pr_total_summary->discount_type == "before_tax"))) {
            //when there is discount and type is before tax or no discount
            echo $discount_row;
        }
    }
    ?>

    <?php if ($pr_total_summary->tax) { ?>
        <tr>
            <td><?php echo $pr_total_summary->tax_name; ?></td>
            <td><?php echo to_currency($pr_total_summary->tax, $pr_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>
    <?php if ($pr_total_summary->tax2) { ?>
        <tr>
            <td><?php echo $pr_total_summary->tax_name2; ?></td>
            <td><?php echo to_currency($pr_total_summary->tax2, $pr_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>

    <?php
        if ($pr_total_summary->discount_total && $pr_total_summary->discount_type == "after_tax") {
            //when there is discount and type is after tax
            echo $discount_row;
        }
    ?>

    <tr>
        <td><?php echo lang("total"); ?></td>
        <td><?php echo to_currency($pr_total_summary->pr_total, $pr_total_summary->currency_symbol); ?></td>
        <td></td>
    </tr>
</table>