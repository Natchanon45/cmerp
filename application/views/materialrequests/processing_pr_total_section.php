<table id="pr-item-table" class="mt0 table display dataTable text-right strong table-responsive">
    <tr>
        <td><?php echo lang("sub_total"); ?></td>
        <td style="width: 118px;"><?php echo to_currency($mr_total_summary->pr_subtotal, $mr_total_summary->currency_symbol); ?></td>
        <td style="width: 100px;"> </td>
    </tr>

    <?php if ($mr_total_summary->tax) { ?>
        <tr>
            <td><?php echo $mr_total_summary->tax_name; ?></td>
            <td><?php echo to_currency($mr_total_summary->tax, $mr_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>
    <?php if ($mr_total_summary->tax2) { ?>
        <tr>
            <td><?php echo $mr_total_summary->tax_name2; ?></td>
            <td><?php echo to_currency($mr_total_summary->tax2, $mr_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>

    <tr>
        <td><?php echo lang("total"); ?></td>
        <td><?php echo to_currency($mr_total_summary->pr_total, $mr_total_summary->currency_symbol); ?></td>
        <td></td>
    </tr>
</table>