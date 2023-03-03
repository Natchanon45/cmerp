<table style="color: #444; width: 100%;">
    <tr class="invoice-preview-header-row">
        <td style="width: 45%; vertical-align: top;">
            <?php $this->load->view('receipts/receipt_parts/company_logo'); ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td class="invoice-info-container" style="width: 35%; vertical-align: top; text-align: right"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "receipt_info" => $receipt_info
            );
            $this->load->view('receipts/receipt_parts/receipt_info', $data);
            ?>
        </td>
    </tr>
    <tr>
        <td style="padding: 5px;"></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td><?php
            $this->load->view('receipts/receipt_parts/receipt_from', $data);
            ?>
        </td>
        <td></td>
        <td><?php
            $this->load->view('receipts/receipt_parts/receipt_to', $data);
            ?>
        </td>
    </tr>
</table>