<!DOCTYPE html>
<html lang="en">
<head>
<?php $this->load->view("edocs/include/head"); ?>
<title><?php echo get_setting("company_name") . " - " . $mat_req_info->doc_no; ?></title>
<style type="text/css">
.body .items table td:nth-child(1){
	width: 30px;
	text-align: center;
}

.body .items table td:nth-child(2){
	max-width: calc(100% - 510px);
    text-align: left;
}

.body .items table td:nth-child(3){
	width: 120px;
    text-align: right;
}

.body .items table td:nth-child(4){
	width: 120px;
    text-align: right;
}

.body .items table td:nth-child(5){
	width: 120px;
    text-align: right;
}

.body .items table td:nth-child(6){
	width: 120px;
    text-align: right;
}

.text-left {
    text-align: left !important;
}

.w220px {
    width: 220px !important;
}

.right .docinfo {
    border-bottom: none !important;
}
</style>
</head>
<?php if($print_mode == "private"): ?>
<body onload="window.print()" onfocus="window.close()">
<?php else: ?>
<body>
<header><?php $this->load->view("edocs/include/header"); ?></header>
<?php endif;?>
<div class="container">
	<div class="paper wrapper">
		<div class="header clear">
			<div class="left">
				<div class="logo">
	                <?php if(file_exists($_SERVER['DOCUMENT_ROOT'].get_file_from_setting("estimate_logo", true)) != false): ?>
	                    <img src="<?php echo get_file_from_setting("estimate_logo", get_setting('only_file_path')); ?>" />
	                <?php else: ?>
	                    <span class="nologo">&nbsp;</span>
	                <?php endif; ?>
	            </div>
				<div class="seller">
					<p class="name"><?php echo get_setting("company_name"); ?></p>
					<p><?php echo nl2br(get_setting("company_address")); ?></p>
					<?php if(trim(get_setting("company_phone")) != ""): ?>
	                    <p><?php echo lang("phone") . ": ".get_setting("company_phone"); ?></p>
	                <?php endif;?>
	                <?php if(trim(get_setting("company_website")) != ""): ?>
	                    <p><?php echo lang("website") . ": ".get_setting("company_website"); ?></p>
	                <?php endif;?>
	                <?php if(trim(get_setting("company_vat_number")) != ""): ?>
	                    <p><?php echo lang("vat_number") . ": ".get_setting("company_vat_number"); ?></p>
	                <?php endif;?>
				</div>
			</div>
			<div class="right">
				<div class="docname custom-color"><?php echo $mat_req_info->mr_type == 1 ? lang('material_request_document') : lang('fg_request_document'); ?></div>
				<div class="docinfo">
					<table>
                        <tr>
                            <td class="custom-color"><?php echo lang("document_number"); ?></td>
                            <td><?php echo $mat_req_info->doc_no; ?></td>
                        </tr>
	                    <tr>
                            <td class="custom-color"><?php echo lang("material_request_date"); ?></td>
                            <td><?php echo convertDate($mat_req_info->mr_date, true); ?></td>
                        </tr>
	                    <tr>
                            <td class="custom-color"><?php echo lang("material_request_person"); ?></td>
                            <td><?php echo $mat_requester_info->first_name . " " . $mat_requester_info->last_name; ?></td>
                        </tr>
                        <tr>
                            <td class="custom-color"><?php echo lang("positioning"); ?></td>
                            <td><?php echo $mat_requester_info->job_title; ?></td>
                        </tr>
	                    <tr>
                            <td class="custom-color"><?php echo lang("project_refer"); ?></td>
                            <td>
								<?php
								if (isset($mat_project_info->title) && !empty($mat_project_info->title)) {
									echo $mat_project_info->title;
								} else {
									echo "-";
								}
								?>
							</td>
                        </tr>
	                </table>
				</div>
			</div>
		</div><!--.header-->
		<div class="body">
			<div class="items">
				<table>
					<thead>
		                <tr>
		                    <td>#</td>
		                    <td>รายละเอียด</td>
		                    <td class="text-left w220px" colspan="2">ชื่อการนำเข้า</td>
		                    <td>จำนวน</td>
		                    <td>หน่วย</td>
		                </tr>
		            </thead>
		            <tbody>
		            	<?php if(!empty($mat_item_info)): ?>
		            		<?php $i = 1; ?>
		            		<?php foreach($mat_item_info as $item): ?>
				            	<tr>
				                    <td><?php echo $i++; ?></td>
				                    <td>
				                    	<span class="product_name"><?php echo $item->code . ' - ' . $item->title; ?></span>
				                    	<?php if(trim($item->description) != ""): ?>
				                    		<span class="product_description"><?php echo trim($item->description); ?></span>
				                    	<?php endif;?>
				                    </td>
				                    <td class="text-left w220px" colspan="2"><?php echo $item->stock_group_name; ?></td>
				                    <td><?php echo number_format($item->quantity, 2); ?></td>
                                    <td><?php echo $item->unit_type; ?></td>
				                </tr>
			            	<?php endforeach; ?>
		            	<?php endif; ?>
		            </tbody>
				</table>
			</div>
			
			<?php if(trim($mat_req_info->note) != ""): ?>
				<div class="remark clear">
					<div class="l1 custom-color"><?php echo lang('remark'); ?></div>
	            	<div class="l2 clear"><?php echo nl2br($mat_req_info->note); ?></div>
				</div>
			<?php endif; ?>
		</div><!--.body-->
		<div class="footer clear">
			<div class="c1">
				<div class="on_behalf_of"><?php // echo $doc["seller"]["company_name"]; ?></div>
				<div class="signature clear">
					<div class="name">
	                    <span class="l1">
                            <?php if ($mat_req_info->status_id != 4): if($mat_req_info->requester_id != null): if (null != $requester_sign = $this->Users_m->getSignature($mat_req_info->requester_id)): ?>
                                <img src='<?php echo str_replace("./", "/", $requester_sign); ?>'>
                            <?php endif; endif; endif; ?>
                        </span>
	                    <span class="l2"><?php echo lang('material_request_person'); ?></span>
	                </div>
	                <div class="date">
	                    <span class="l1">
                            <?php if($mat_req_info->mr_date != null && $mat_req_info->status_id != 4): ?>
                                <span class="approved_date"><?php echo convertDate($mat_req_info->mr_date, true); ?></span>
                            <?php endif; ?>
                        </span>
	                    <span class="l2"><?php echo lang("date"); ?></span>
	                </div>
				</div>
			</div>
			<div class="c2">
				<div class="on_behalf_of"><?php // echo get_setting("company_name"); ?></div>
				<div class="signature clear">
					<div class="name">
	                    <span class="l1">
	                    	<?php if ($mat_req_info->approved_by != null && $mat_req_info->status_id == 3): if (null != $signature = $this->Users_m->getSignature($mat_req_info->approved_by)): ?>
	                            <img src='<?php echo str_replace("./", "/", $signature); ?>'>
	                        <?php endif; endif; ?>
	                    </span>
	                    <span class="l2"><?php echo lang("approver"); ?></span>
	                </div>
	                <div class="date">
	                    <span class="l1">
	                    	<?php if ($mat_req_info->approved_date != null && $mat_req_info->status_id == 3): ?>
	                            <span class="approved_date"><?php echo convertDate($mat_req_info->approved_date, true); ?></span>
	                        <?php endif; ?>
	                    </span>
	                    <span class="l2"><?php echo lang("date"); ?></span>
	                </div>
				</div>
			</div>
		</div><!--.footer-->	
	</div><!--.paper-->
</div><!--.container-->
</body>
</html>