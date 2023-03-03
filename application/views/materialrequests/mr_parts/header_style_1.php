<table style="color: #444; width: 100%;">
    <tr class="invoice-preview-header-row">
        <td style="width: 45%; vertical-align: top;">
            <?php $this->load->view('materialrequests/mr_parts/company_logo'); ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td class="invoice-info-container" style="width: 35%; vertical-align: top; text-align: right"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "mr_info" => $mr_info
            );
            $this->load->view('materialrequests/mr_parts/mr_info', $data);
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
            $this->load->view('materialrequests/mr_parts/mr_from', $data);
            ?>
        </td>
        <td></td>
        <td>
			
			
			<div><b><?php echo lang('withdrawal'); ?></b> <strong><?php echo $client_info->first_name.' '.$client_info->last_name; ?> </strong></div>



<span class="invoice-meta" style="font-size: 90%; color: #666;">
    <?php if ($client_info->address) { ?>
        <div>
            <?php echo nl2br($client_info->address); ?>
            <?php echo nl2br($client_info->phone); ?>
        </div>
    <?php } ?>
</span>
        </td>
    </tr>
</table>