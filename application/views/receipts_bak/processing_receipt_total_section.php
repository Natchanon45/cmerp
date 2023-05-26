<table id="receipt-item-table" class="mt0 table display dataTable text-right strong table-responsive">
    <tr>
        <td><?php echo lang("sub_total"); ?></td>
        <td style="width: 118px;"><?php echo to_currency($receipt_total_summary->receipt_subtotal, $receipt_total_summary->currency_symbol); ?></td>
        <td style="width: 100px;"> </td>
    </tr>

    <?php if ($receipt_total_summary->tax) { ?>
        <tr>
            <td><?php echo $receipt_total_summary->tax_name; ?></td>
            <td><?php echo to_currency($receipt_total_summary->tax, $receipt_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>
    <?php if ($receipt_total_summary->tax2) { ?>
        <tr>
            <td><?php echo $receipt_total_summary->tax_name2; ?></td>
            <td><?php echo to_currency($receipt_total_summary->tax2, $receipt_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>

    <tr>
        <td><?php echo lang("total"); ?></td>
        <td><?php echo to_currency($receipt_total_summary->receipt_total, $receipt_total_summary->currency_symbol); ?></td>
        <td></td>
    </tr>
</table>