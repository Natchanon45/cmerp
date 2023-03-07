<table style="color: #444; width: 100%;">
    <tr class="invoice-preview-header-row">
        <td class="invoice-info-container" style="width: 40%; vertical-align: top;"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "receipts_info" => $receipts_info
            );
            $this->load->view('receiptss/receipts_parts/receipts_info', $data);
            ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td style="width: 40%; vertical-align: top;">
            <?php $this->load->view('receiptss/receipts_parts/company_logo'); ?>
        </td>
    </tr>
    <tr>
        <td style="padding: 5px;"></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td><?php
            $this->load->view('receiptss/receipts_parts/receipts_to', $data);
            ?>
        </td>
        <td></td>
        <td><?php
            $this->load->view('receiptss/receipts_parts/receipts_from', $data);
            ?>
        </td>
    </tr>
</table>