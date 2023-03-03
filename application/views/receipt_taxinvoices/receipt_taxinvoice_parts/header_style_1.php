<table style="color: #444; width: 100%;">
    <tr class="receipt_taxinvoice-preview-header-row">
        <td style="width: 45%; vertical-align: top;">
            <?php $this->load->view('receipt_taxinvoices/receipt_taxinvoice_parts/company_logo'); ?>
        </td>
        <td class="hidden-receipt_taxinvoice-preview-row" style="width: 20%;"></td>
        <td class="receipt_taxinvoice-info-container receipt_taxinvoice-header-style-one" style="width: 35%; vertical-align: top; text-align: right"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "receipt_taxinvoice_info" => $receipt_taxinvoice_info
            );
            $this->load->view('receipt_taxinvoices/receipt_taxinvoice_parts/receipt_taxinvoice_info', $data);
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
            $this->load->view('receipt_taxinvoices/receipt_taxinvoice_parts/bill_from', $data);
            ?>
        </td>
        <td></td>
        <td><?php
            $this->load->view('receipt_taxinvoices/receipt_taxinvoice_parts/bill_to', $data);
            ?>
        </td>
    </tr>
</table>