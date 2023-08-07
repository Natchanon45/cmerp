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
				<div class="docname custom-color">ใบเสนอราคา</div>
				<div class="docinfo">
					<table>
	                    <tr>
	                        <td class="custom-color">เลขที่</td>
	                        <td><?php echo $doc["doc_number"]; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">วันที่</td>
	                        <td><?php echo convertDate($doc["doc_date"], true); ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">เครดิต</td>
	                        <td><?php echo $doc["credit"]; ?> วัน</td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">ผู้ขาย</td>
	                        <td><?php if($doc["seller"] != null) echo $doc["seller"]["first_name"]." ".$doc["seller"]["last_name"]; ?></td>
	                    </tr>
	                    <?php if(trim($doc["reference_number"]) != ""): ?>
	                        <tr>
	                            <td class="custom-color">เลขที่อ้างอิง</td>
	                            <td><?php echo $doc["reference_number"]; ?></td>
	                        </tr>
	                    <?php endif; ?>
	                </table>
				</div>
				<div class="buyercontact">
					<table>
	                    <tr>
	                        <td class="custom-color">ผู้ติดต่อ</td>
	                        <td><?php if(isset($doc["buyer_contact"])) echo $doc["buyer_contact"]["first_name"]." ".$doc["buyer_contact"]["last_name"]; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">เบอร์โทร</td>
	                        <td><?php if(isset($doc["buyer_contact"])) echo $doc["buyer_contact"]["phone"]; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">อีเมล์</td>
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
		                    <td>รายละเอียด</td>
		                    <td>จำนวน</td>
		                    <td>หน่วย</td>
		                    <td>ราคาต่อหน่วย</td>
		                    <td>ยอดรวม</td>
		                </tr>
		            </thead>
		            <tbody>
		            	<?php if(!empty($doc["items"])): ?>
		            		<?php $i = 1; ?>
		            		<?php foreach($doc["items"] as $item): ?>
				            	<tr>
				                    <td><?php echo $i++; ?></td>
				                    <td>
				                    	<span class="product_name"><?php echo $item->product_name; ?></span>
				                    	<?php if(trim($item->product_description) != ""): ?>
				                    		<span class="product_description"><?php echo trim($item->product_description); ?></span>
				                    	<?php endif;?>
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
			</div>
			<div class="summary clear">
				<div class="total_in_text"><span><?php echo "(".$doc["total_in_text"].")"; ?></span></div>
				<div class="total_all">
					<div class="row">
						<div class="c1 custom-color">รวมเป็นเงิน</div>
						<div class="c2"><span><?php echo number_format($doc["sub_total_before_discount"], 2); ?></span><span><?php echo lang("THB");?></span></div>
					</div>
					<?php if($doc["discount_amount"] > 0): ?>
						<div class="row">
							<div class="c1 custom-color">ส่วนลด <?php if($doc["discount_type"] == "P") echo number_format_drop_zero_decimals($doc["discount_percent"], 2)."%"; ?></div>
							<div class="c2"><span><?php echo number_format($doc["discount_amount"], 2); ?></span><span><?php echo lang("THB");?></span></div>
						</div>
						<div class="row">
							<div class="c1 custom-color">จำนวนหลังหักส่วนลด</div>
							<div class="c2"><span><?php echo number_format($doc["sub_total"], 2); ?></span><span><?php echo lang("THB");?></span></div>
						</div>
					<?php endif; ?>
					<?php if($doc["vat_inc"] == "Y"): ?>
						<div class="row">
							<div class="c1 custom-color">ภาษีมูลค่าเพิ่ม <?php echo number_format_drop_zero_decimals($doc["vat_percent"], 2)."%";?></div>
							<div class="c2"><span><?php echo number_format($doc["vat_value"], 2); ?></span><span><?php echo lang("THB");?></span></div>
						</div>
					<?php endif; ?>
					<div class="row">
						<div class="c1 custom-color">จำนวนเงินรวมทั้งสิน</div>
						<div class="c2"><span><?php echo number_format($doc["total"], 2); ?></span><span><?php echo lang("THB");?></span></div>
					</div>
					<?php if($doc["wht_inc"] == "Y"): ?>
						<div class="row wht">
							<div class="c1 custom-color">หักภาษี ณ ที่จ่าย <?php echo number_format_drop_zero_decimals($doc["wht_percent"], 2)."%";?></div>
							<div class="c2"><span><?php echo number_format($doc["wht_value"], 2); ?></span><span><?php echo lang("THB");?></span></div>
						</div>
						<div class="row">
							<div class="c1 custom-color">ยอดชำระ</div>
							<div class="c2"><span><?php echo number_format($doc["payment_amount"], 2); ?></span><span><?php echo lang("THB");?></span></div>
						</div>
					<?php endif;?>
				</div>
			</div>
			<?php if(trim($doc["remark"]) != ""): ?>
				<div class="remark clear">
					<div class="l1 custom-color">หมายเหตุ</div>
	            	<div class="l2 clear"><?php echo nl2br($doc["remark"]); ?></div>
				</div>
			<?php endif; ?>
		</div><!--.body-->
		<div class="footer clear">
			<div class="c1">
				<div class="on_behalf_of">ในนาม <?php echo $doc["buyer"]["company_name"] ?></div>
				<div class="signature clear">
					<div class="name">
	                    <span class="l1"></span>
	                    <span class="l2">ผู้สั่งซื้อสินค้า</span>
	                </div>
	                <div class="date">
	                    <span class="l1"></span>
	                    <span class="l2">วันที่</span>
	                </div>
				</div>
			</div>
			<div class="c2">
				<div class="on_behalf_of">ในนาม <?php echo get_setting("company_name"); ?></div>
				<div class="signature clear">
					<div class="name">
	                    <span class="l1">
	                    	<?php if($doc["approved_by"] != null): ?>
	                    		<?php if(null != $signature = $this->Users_m->getSignature($doc["approved_by"])): ?>
	                            	<img src='<?php echo "/".$signature; ?>'>
	                        	<?php endif; ?>
	                        <?php endif; ?>
	                    </span>
	                    <span class="l2">ผู้อนุมัติ</span>
	                </div>
	                <div class="date">
	                    <span class="l1">
	                    	<?php if($doc["approved_by"] != null): ?>
	                            <span class="approved_date"><?php echo convertDate($doc["approved_datetime"], true); ?></span>
	                        <?php endif; ?>
	                    </span>
	                    <span class="l2">วันที่</span>
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


