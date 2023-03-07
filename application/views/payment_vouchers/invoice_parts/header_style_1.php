<?php
	$data = array(
		"client_info" => $client_info,
		"color" => $color,
		"invoice_info" => $invoice_info
	);
            
?>

<table style="color: #444; width: 100%;">
    <tr class="invoice-preview-header-row">
        <td style="width: 10%; vertical-align: top;">
            <?php $this->load->view('payment_vouchers/invoice_parts/company_logo'); ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"><?php
            $this->load->view('payment_vouchers/invoice_parts/bill_from', $data);
            ?></td>
        <td class="invoice-info-container invoice-header-style-one" style="width: 35%; vertical-align: top; text-align: right"><?php
            
            $this->load->view('payment_vouchers/invoice_parts/invoice_info', $data);
            ?>
        </td>
    </tr>
    <tr>
        <td style="padding: 5px;"></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
        </td>
        <td></td>
        <td><?php
            //$this->load->view('payment_vouchers/invoice_parts/bill_to', $data );
            ?>
        </td>
    </tr>
</table>


 