<!DOCTYPE html>
<html lang="en">
<head>
<?php $this->load->view("edocs/include/head"); ?>
<title><?php echo get_setting("company_name")." - ".$doc["doc_number"]; ?></title>	
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

.body .items table td img{
	display: inline-block;
	width: 80px;
	height: auto;
	vertical-align: top;
}

.body .items table td .txt{
	display: inline-block;
	vertical-align: top;
}

.task_list{
	margin-top: 50px;
}

.task_list table{
    width: 100%;
}

.task_list thead td{
    font-weight: bold;
}

.task_list td{
    border-bottom: 1px solid #cecece;
    /*border: 1px solid #cecece;*/
    vertical-align: top;
    padding-top: 6px;
    padding-bottom: 6px;
}

.task_list td.task_number{
    width: 4%;
    text-align: center;
}

.task_list td.task_title{
    width: 40%;
}

.task_list td.task_collaborators{
    width: 26%;
}

.task_list td.task_collaborators{
    width: 30%;
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
				<div class="buyer">
					<p class="custom-color"><?php echo lang("client"); ?></p>
					<?php if($doc["buyer"] != null): ?>
						<p class="customer_name"><?php echo $doc["buyer"]["company_name"] ?></p>
                    	<p><?php if($doc["buyer"] != null) echo nl2br($doc["buyer"]["address"]); ?></p>
                    	<p>
	                        <?php
	                            $client_address = $doc["buyer"]["city"];
	                            if($client_address != "" && $doc["buyer"]["state"] != "")$client_address .= ", ".$doc["buyer"]["state"];
	                            elseif($client_address == "" && $doc["buyer"]["state"] != "")$client_address .= $doc["buyer"]["state"];
	                            if($client_address != "" && $doc["buyer"]["zip"] != "") $client_address .= " ".$doc["buyer"]["zip"];
	                            elseif($client_address == "" && $doc["buyer"]["zip"] != "") $client_address .= $doc["buyer"]["zip"];
	                            echo $client_address;
	                        ?>    
	                    </p>
	                    <?php if(trim($doc["buyer"]["country"]) != ""): ?>
	                        <p><?php //echo $doc["buyer"]["country"]; ?></p>
	                    <?php endif; ?>
	                    <?php if(trim($doc["buyer"]["vat_number"]) != ""): ?>
	                        <p><?php echo lang("vat_number") . ": " . $doc["buyer"]["vat_number"]; ?></p>
	                    <?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="right">
				<div class="docname custom-color"><?php echo $doc["purpose"] == "P"?lang("account_docname_production_order"):lang("account_docname_sales_order");?></div>
				<div class="docinfo">
					<table>
	                    <tr>
	                        <td class="custom-color"><?php echo lang("account_short_document_no"); ?></td>
	                        <td><?php echo $doc["doc_number"]; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color"><?php echo lang("account_date"); ?></td>
	                        <td><?php echo convertDate($doc["doc_date"], true); ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color"><?php echo lang("account_seller"); ?></td>
	                        <td><?php if($doc["seller"] != null) echo $doc["seller"]["first_name"]." ".$doc["seller"]["last_name"]; ?></td>
	                    </tr>
	                    <?php if(trim($doc["reference_number"]) != ""): ?>
	                        <tr>
	                            <td class="custom-color"><?php echo lang("account_refernce_no"); ?></td>
	                            <td><?php echo $doc["reference_number"]; ?></td>
	                        </tr>
	                    <?php endif; ?>
	                </table>
				</div>
				<div class="buyercontact">
					<table>
	                    <tr>
	                        <td class="custom-color"><?php echo lang("account_contact"); ?></td>
	                        <td><?php if(isset($doc["buyer_contact"])) echo $doc["buyer_contact"]["first_name"]." ".$doc["buyer_contact"]["last_name"]; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color"><?php echo lang("account_phone"); ?></td>
	                        <td><?php if(isset($doc["buyer_contact"])) echo $doc["buyer_contact"]["phone"]; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color"><?php echo lang("account_email"); ?></td>
	                        <td><?php if(isset($doc["buyer_contact"])) echo $doc["buyer_contact"]["email"]; ?></td>
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
		                    <td><?php echo lang("account_item_description"); ?></td>
		                    <td><?php echo lang("account_item_quantity"); ?></td>
		                    <td><?php echo lang("account_item_unit"); ?></td>
		                    <td><?php echo lang("account_item_unit_price"); ?></td>
		                    <td><?php echo lang("account_item_total"); ?></td>
		                </tr>
		            </thead>
		            <tbody>
		            	<?php if(!empty($doc["items"])): ?>
		            		<?php $i = 1; ?>
		            		<?php foreach($doc["items"] as $item): ?>
				            	<tr>
				                    <td><?php echo $i++; ?></td>
				                    <td>
				                    	<?php
				                    		$product_image_file = $this->Products_m->getImage($item->product_id);
				                    		if($product_image_file != null) echo "<img src='".$product_image_file."'>";
				                    	?>
				                    	<span class="txt">
					                    	<span class="product_name"><?php echo $item->product_name; ?></span>
					                    	<?php if(trim($item->product_description) != ""): ?>
					                    		<span class="product_description"><?php echo trim($item->product_description); ?></span>
					                    	<?php endif;?>
					                    	<?php if(trim($item->item_mixing_groups_id) != ""): ?>
					                    		<span class="product_description"><?php echo $this->Bom_item_m->getMixingGroupsInfoById($item->item_mixing_groups_id)["name"]; ?></span>
					                    	<?php endif;?>
				                    	</span>
				                    </td>
				                    <td><?php echo $item->quantity; ?></td>
				                    <td><?php echo $item->unit; ?></td>
				                    <td><?php echo number_format($item->price, 2); ?></td>
				                    <td><?php echo number_format($item->total_price, 2); ?></td>
				                </tr>
			            	<?php endforeach; ?>
		            	<?php endif; ?>
		            </tbody>
				</table>
			</div><!--.items-->
			<div class="task_list">
		        <h6 class="custom-color">ข้อมูลรายการงาน</h6>
		        <?php if(!empty($doc["sotrows"])): ?>
		            <table>
		                <thead>
		                    <tr>
		                        <td class="task_number">#</td>
		                        <td class="task_title">รายการงาน</td>
		                        <td class="task_assigned_to">ผู้ได้รับมอบหมาย</td>
		                        <td class="task_collaborators">ผู้ร่วมงาน</td>
		                    </tr>
		                </thead>
		                <?php $tnumber = 0; ?>
		                <?php foreach($doc["sotrows"] as $sotrow): ?>
		                    <?php
		                        $task_assigned_to = "";
		                        $task_collaborators = "";
		                        $urow = $this->Users_m->getRow($sotrow->task_assigned_to, ["first_name", "last_name"]);
		                        if($urow != null){
		                            $task_assigned_to = $urow->first_name." ".$urow->last_name;
		                        }

		                        if($sotrow->task_collaborators != ""){
		                            $uids = explode(",", $sotrow->task_collaborators);
		                            foreach($uids as $uid){
		                                $urow = $this->Users_m->getRow($uid, ["first_name", "last_name"]);
		                                if($urow != null){
		                                    $task_collaborators .= $urow->first_name." ".$urow->last_name.", ";
		                                }
		                            }
		                        }

		                        $task_collaborators = substr($task_collaborators, 0, -2);
		                    ?>
		                    <tr>
		                        <td class="task_number"><?php echo ++$tnumber; ?></td>
		                        <td class="task_title"><?php echo $sotrow->task_title; ?></td>
		                        <td class="task_assigned_to"><?php echo $task_assigned_to; ?></td>
		                        <td class="task_collaborators"><?php echo $task_collaborators; ?></td>
		                    </tr>
		                <?php endforeach;?>
		            </table>
		        <?php endif; ?>
		    </div><!--.task_list-->
			<div class="summary clear">
				<div class="total_in_text"><span></span></div>
				<div class="total_all"></div>
			</div>
			<?php if(trim($doc["remark"]) != ""): ?>
				<div class="remark clear">
					<div class="l1 custom-color"><?php echo lang("account_remarks"); ?></div>
	            	<div class="l2 clear"><?php echo nl2br($doc["remark"]); ?></div>
				</div>
			<?php endif; ?>
		</div><!--.body-->
		<div class="footer clear">
			<div class="c1">
				<div class="company_stamp"></div>
				<div class="on_behalf_of"></div>
				<div class="signature clear">
					<div class="name">
	                    <span class="l1">
	                    	<?php if(null != $signature = $this->Users_m->getSignature($doc["created_by"])): ?>
                            	<img src='<?php echo "/".$signature; ?>'>
                        	<?php endif; ?>
	                    </span>
	                    <span class="l2">(<?php echo $doc["created"]["first_name"]." ".$doc["created"]["last_name"]; ?></span>
	                    <span class="l3"><?php echo lang("account_created_by"); ?></span>
	                </div>
	                <div class="date">
	                    <span class="l1"><span class="created_date"><?php echo convertDate($doc["created_datetime"], true); ?></span></span>
	                    <span class="l2"><?php echo lang("account_date"); ?></span>
	                </div>
				</div>
			</div>
			<div class="c2">
				<div class="company_stamp"></div>
				<div class="on_behalf_of"></div>
				<div class="signature clear">
					<div class="name">
	                    <span class="l1">
                    		<?php if(null != $signature = $this->Users_m->getSignature($doc["approved_by"])): ?>
                            	<img src='<?php echo "/".$signature; ?>'>
                        	<?php endif; ?>
	                    </span>
	                    <span class="l2"><?php if(isset($doc["approved"])) echo "(".$doc["approved"]["first_name"]." ".$doc["approved"]["last_name"].")"; ?></span>
	                    <span class="l3"><?php echo lang("account_approved_by"); ?></span>
	                </div>
	                <div class="date">
	                    <span class="l1"><span class="approved_date"><?php echo convertDate($doc["approved_datetime"], true); ?></span></span>
	                    <span class="l2"><?php echo lang("account_date"); ?></span>
	                </div>
				</div>
			</div>
		</div><!--.footer-->	
	</div><!--.paper-->
</div><!--.container-->
<?php if($print_mode == "public"): ?>
<footer><?php $this->load->view("edocs/include/footer"); ?></footer>
<?php endif; ?>
</body>
</html>