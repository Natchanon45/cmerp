<!DOCTYPE html>
<html lang="en">
<head>
<title>ติดต่อเรา</title>	
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?php echo get_favicon_url(); ?>" />
<link rel="stylesheet" href="/assets/js/font-awesome/css/font-awesome.min.css">

<link rel="stylesheet" href="/assets/css/color/<?php echo get_setting("default_theme_color"); ?>.css">
<link rel="stylesheet" href="/assets/css/font.css">
<link rel="stylesheet" href="/assets/css/edocs.css">

<style type="text/css">
.body .items table td{
	border: 1px solid #ff0000;
}

.body .items table td:nth-child(1){
	width: 30px;
	text-align: center;
}

.body .items table td:nth-child(2){
	width: calc(100% - 510px);
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
<body>
<header>
	<div class="wrapper">
		<a class="button print"><i class="fa fa-print" aria-hidden="true"></i></a>
	</div>
</header>
<div class="container">
	<div class="paper wrapper">
		<div class="header clear">
			<div class="left">
				<div class="logo"><img src="<?php echo get_file_from_setting("estimate_logo", get_setting('only_file_path')); ?>" /></div>
				<div class="seller">
					<p class="name"><?php echo get_setting("company_name"); ?></p>
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
					<?php if($buyer != null): ?>
						<p class="customer_name"><?php echo $buyer["company_name"] ?></p>
                    	<p><?php if($buyer != null) echo nl2br($buyer["address"]); ?></p>
                    	<p>
	                        <?php
	                            $client_address = $buyer["city"];
	                            if($client_address != "" && $buyer["state"] != "")$client_address .= ", ".$buyer["city"];
	                            elseif($client_address == "" && $buyer["state"] != "")$client_address .= $buyer["city"];
	                            if($client_address != "" && $buyer["zip"] != "") $client_address .= " ".$buyer["zip"];
	                            elseif($client_address == "" && $buyer["zip"] != "") $client_address .= $buyer["zip"];
	                            echo $client_address;
	                        ?>    
	                    </p>
	                    <?php if(trim($buyer["country"]) != ""): ?>
	                        <p><?php echo $buyer["country"]; ?></p>
	                    <?php endif; ?>
	                    <?php if(trim($buyer["vat_number"]) != ""): ?>
	                        <p><?php echo lang("vat_number") . ": " . $buyer["vat_number"]; ?></p>
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
	                        <td><?php echo $doc_number; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">วันที่</td>
	                        <td><?php echo convertDate($doc_date, true); ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">เครดิต</td>
	                        <td><?php echo $credit; ?> วัน</td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">ผู้ขาย</td>
	                        <td><?php if($seller != null) echo $seller["first_name"]." ".$seller["last_name"]; ?></td>
	                    </tr>
	                    <?php if(trim($reference_number) != ""): ?>
	                        <tr>
	                            <td class="custom-color">เลขที่อ้างอิง</td>
	                            <td><?php echo $reference_number; ?></td>
	                        </tr>
	                    <?php endif; ?>
	                </table>
				</div>
				<div class="buyercontact">
					<table>
	                    <tr>
	                        <td class="custom-color">ผู้ติดต่อ</td>
	                        <td><?php if(isset($client_contact)) echo $client_contact["first_name"]." ".$client_contact["last_name"]; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">เบอร์โทร</td>
	                        <td><?php if(isset($client_contact)) echo $client_contact["phone"]; ?></td>
	                    </tr>
	                    <tr>
	                        <td class="custom-color">อีเมล์</td>
	                        <td><?php if(isset($client_contact)) echo $client_contact["email"]; ?></td>
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
		            	<?php if(!empty($items)): ?>
		            		<?php $i = 1; ?>
		            		<?php foreach($items as $item): ?>
				            	<tr>
				                    <td><?php echo $i; ?></td>
				                    <td></td>
				                    <td></td>
				                    <td></td>
				                    <td></td>
				                    <td></td>
				                </tr>
			            	<?php endif; ?>
		            	<?php endif; ?>
		            </tbody>
				</table>
			</div>
			<div class="summary">
				<div class="left">
					<p class="custom-color">หมายเหตุ</p>
            		<p><?php echo nl2br($remark); ?></p>
				</div>
				<div class="right">
					<div class="row">
						<div class="c1"></div>
						<div class="c2"></div>
					</div>
				</div>
			</div>
		</div><!--.body-->
		<div class="footer">
			<div class="remark"></div>
			<div class="signature">
				<div class="left">
					<div class="on_behalf_of">ในนาม <?php if(isset($client["company_name"])) echo $client["company_name"]; ?></div>
					<div class="clear">
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
				<div class="right">
					<div class="on_behalf_of">ในนาม <?php if(isset($client["company_name"])) echo $client["company_name"]; ?></div>
					<div class="clear">
						<div class="name">
		                    <span class="l1"></span>
		                    <span class="l2">ผู้อนุมัติ</span>
		                </div>
		                <div class="date">
		                    <span class="l1"></span>
		                    <span class="l2">วันที่</span>
		                </div>
					</div>
				</div>
			</div>
		</div><!--.footer-->
	</div>
</div>
<footer>
	<div class="wrapper">
		<div class="left"></div>
		<div class="right"></div>
	</div>
</footer>
</body>
</html>