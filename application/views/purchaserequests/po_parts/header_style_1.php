<table style="color: #444; width: 100%;">
    <tr class="invoice-preview-header-row">
        <td style="width: 45%; vertical-align: top;">
            <?php $this->load->view('purchaserequests/pr_parts/company_logo'); ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td class="invoice-info-container" style="width: 35%; vertical-align: top; text-align: right"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "pr_info" => $pr_info
            );
            $this->load->view('purchaserequests/po_parts/po_info', $data);
            ?>
        </td>
    </tr>
    <tr style="padding: 5px 5px 0px 5px;">
        <td><b><?php echo lang('buyer_org');?></b></td>
        <td></td>
        <td><b><?php echo lang('supplier_org');?></b></td>
    </tr>
    <tr>
        <td><?php
            $this->load->view('purchaserequests/po_parts/po_from', $data);
            ?>
        </td>
        <td></td>
        <td style="vertical-align:top">
			<div><b><?php echo $supplier;?></b></div>
            <span class="invoice-meta" style="font-size: 90%; color: #666;">
                <?php if ($supplier_address) { ?>
                    <div>
                        <?php echo nl2br($supplier_address); ?>
                    </div>
                <?php } ?>
            </span>
        </td>
    </tr>
</table>