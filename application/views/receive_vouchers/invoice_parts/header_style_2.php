<table style="color: #444; width: 100%;">
    <tr class="invoice-preview-header-row">
        <td class="invoice-info-container invoice-header-style-two" style="width: 40%; vertical-align: top;"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "invoice_info" => $invoice_info
            );
            $this->load->view('payment_vouchers/invoice_parts/invoice_info', $data);
            ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td style="width: 40%; vertical-align: top;">
            <?php $this->load->view('payment_vouchers/invoice_parts/company_logo'); ?>
        </td>
    </tr>
    <tr>
        <td style="padding: 5px;"></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td><?php
            $this->load->view('payment_vouchers/invoice_parts/bill_to', $data);
            ?>
        </td>
        <td></td>
        <td><?php
            $this->load->view('payment_vouchers/invoice_parts/bill_from', $data);
            ?>
        </td>

    </tr>
</table>