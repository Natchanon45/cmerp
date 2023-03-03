<table style="color: #444; width: 100%;">
    <tr class="invoice-preview-header-row">
        <td class="invoice-info-container" style="width: 40%; vertical-align: top;"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "order_info" => $order_info
            );
            $this->load->view('purchaserequests/po_parts/po_info', $data);
            ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td style="width: 40%; vertical-align: top;">
            <?php $this->load->view('purchaserequests/po_parts/company_logo'); ?>
        </td>
    </tr>
    <tr style="padding: 5px 5px 0px 5px;">
        <td><b><?php echo lang('buyer_org');?></b></td>
        <td></td>
        <td><b><?php echo lang('supplier_org');?></b></td>
    </tr>
    <tr>
        <td><?php
            $this->load->view('purchaserequests/po_parts/po_to', $data);
            ?>
        </td>
        <td></td>
        <td><?php
            $this->load->view('purchaserequests/po_parts/po_from', $data);
            ?>
        </td>
    </tr>
</table>