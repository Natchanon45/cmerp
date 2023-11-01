<!DOCTYPE html>
<html lang="en">

<head>
	<?php $this->load->view("edocs/include/head"); ?>
	<title>
		<?php echo get_setting("company_name") . " - " . $doc["doc_number"]; ?>
	</title>
	<style type="text/css">
		.body .items table td:nth-child(1) {
			width: 30px;
			text-align: center;
		}

		.body .items table td:nth-child(2) {
			max-width: calc(100% - 510px);
			text-align: left;
		}

		.body .items table td:nth-child(3) {
			width: 120px;
			text-align: right;
		}

		.body .items table td:nth-child(4) {
			width: 120px;
			text-align: right;
		}

		.body .items table td:nth-child(5) {
			width: 120px;
			text-align: right;
		}

		.body .items table td:nth-child(6) {
			width: 120px;
			text-align: right;
		}

		.body {
			position: relative;
		}

		.payment_info {
			position: absolute;
			width: 100%;
			bottom: 0px;
			padding-top: 1rem;
			padding-bottom: 1rem;
		}

		.payment_info table {
			margin: 1rem 1rem 0 1rem;
			width: calc(100% - 2rem);
			border-top: 1px solid #e7e7e7;
			border-bottom: 1px solid #e7e7e7;
		}

		.payment_number {
			padding: 0 .8rem;
			font-weight: bolder;
		}

		.payment_key {
			padding: .2rem 1rem .1rem 2rem;
			font-weight: bolder;
			vertical-align: text-top;
		}

		.payment_value {
			padding: 0 1rem;
			text-align: right;
			vertical-align: text-top;
		}

		.payment_type {
			width: 40%;
			padding-left: 1.8rem;
		}

		.font-weight-bold {
			font-weight: bold;
		}

		.font-size-bigger {
			font-size: 120%;
		}

		.w-adjust {
			width: 10px !important;
		}
	</style>
</head>
<?php if ($print_mode == "private"): ?>
	<!-- <body onload="window.print()" onfocus="window.close()"> -->
	<body>
		<header>
			<?php $this->load->view("edocs/include/header"); ?>
		</header>
	<?php else: ?>
		<body>
			<header>
				<?php $this->load->view("edocs/include/header"); ?>
			</header>
		<?php endif; ?>
		<div class="container">
			<div class="paper wrapper">
				<div class="header clear">
					<div class="left">
						<div class="logo">
							<?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . get_file_from_setting("estimate_logo", true)) != false): ?>
								<img src="<?php echo get_file_from_setting("estimate_logo", get_setting("only_file_path")); ?>" />
							<?php else: ?>
								<span class="nologo">&nbsp;</span>
							<?php endif; ?>
						</div>
						<div class="seller">
							<p class="custom-color">
								<?php echo lang("payment_voucher_payer"); ?>
							</p>
							<p class="name">
								<?php echo get_setting("company_name"); ?>
							</p>
							<p>
								<?php echo nl2br(get_setting("company_address")); ?>
							</p>
							<?php if (trim(get_setting("company_phone")) != ""): ?>
								<p>
									<?php echo lang("phone") . ": " . get_setting("company_phone"); ?>
								</p>
							<?php endif; ?>
							<?php if (trim(get_setting("company_website")) != ""): ?>
								<p>
									<?php echo lang("website") . ": " . get_setting("company_website"); ?>
								</p>
							<?php endif; ?>
							<?php if (trim(get_setting("company_vat_number")) != ""): ?>
								<p>
									<?php echo lang("vat_number") . ": " . get_setting("company_vat_number"); ?>
								</p>
							<?php endif; ?>
						</div>
						<div class="buyer">
							<p class="custom-color"><?php echo lang("payment_voucher_payee"); ?></p>
							<?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
								<?php if ($doc["seller"] != null): ?>
									<p class="customer_name">
										<?php echo $doc["seller"]["company_name"] ?>
									</p>
									<p>
										<?php if ($doc["seller"] != null) echo nl2br($doc["seller"]["address"]); ?>
									</p>
									<p>
										<?php
											$client_address = $doc["seller"]["city"];
											
											if ($client_address != "" && $doc["seller"]["state"] != "") {
												$client_address .= ", " . $doc["seller"]["state"];
											} elseif ($client_address == "" && $doc["seller"]["state"] != "") {
												$client_address .= $doc["seller"]["state"];
											}
												
											if ($client_address != "" && $doc["seller"]["zip"] != "") {
												$client_address .= " " . $doc["seller"]["zip"];
											} elseif ($client_address == "" && $doc["seller"]["zip"] != "") {
												$client_address .= $doc["seller"]["zip"];
											}
											
											echo $client_address;
										?>
									</p>
									<?php if (trim($doc["seller"]["country"]) != ""): ?>
										<p>
											<?php // echo $doc["seller"]["country"]; ?>
										</p>
									<?php endif; ?>
									<?php if (trim($doc["seller"]["vat_number"]) != ""): ?>
										<p>
											<?php echo lang("vat_number") . ": " . $doc["seller"]["vat_number"]; ?>
										</p>
									<?php endif; ?>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
					<div class="right">
						<div class="docname custom-color">
							<?php echo lang("goods_receipt"); ?>
						</div>
						<div class="docinfo">
							<table>
								<tr>
									<td class="custom-color" <?php echo $additional_style; ?>>
										<?php echo lang("number_of_document"); ?>
									</td>
									<td>
										<?php echo $doc["doc_number"]; ?>
									</td>
								</tr>
								<tr>
									<td class="custom-color">
										<?php echo lang("document_date"); ?>
									</td>
									<td>
										<?php echo convertDate($doc["doc_date"], true); ?>
									</td>
								</tr>
								
								<?php if (isset($doc["supplier_invoice"]) && !empty($doc["supplier_invoice"])): ?>
									<tr>
										<td class="custom-color">
											<?php echo lang("gr_delivery_refer"); ?>
										</td>
										<td>
											<?php echo $doc["supplier_invoice"]; ?>
										</td>
									</tr>
								<?php endif; ?>

								<?php if (isset($doc["references"]) && !empty($doc["references"])): ?>
									<tr>
										<td class="custom-color">
											<?php echo lang("po_no"); ?>
										</td>
										<td>
											<?php echo $doc["references"]; ?>
										</td>
									</tr>
								<?php endif; ?>
							</table>
						</div>
						<div class="buyercontact">
							<table>
								<tr>
									<td class="custom-color" <?php echo $additional_style; ?>>
										<?php echo lang("contact_name"); ?>
									</td>
									<td>
										<?php echo (isset($doc["seller_contact"]) && !empty($doc["seller_contact"])) ? $doc["seller_contact"]["first_name"] . " " . $doc["seller_contact"]["last_name"] : "-"; ?>
									</td>
								</tr>
								<tr>
									<td class="custom-color">
										<?php echo lang("phone"); ?>
									</td>
									<td>
										<?php echo (isset($doc["seller_contact"]) && !empty($doc["seller_contact"])) ? $doc["seller_contact"]["phone"] : "-"; ?>
									</td>
								</tr>
								<tr>
									<td class="custom-color">
										<?php echo lang("email"); ?>
									</td>
									<td>
										<?php echo (isset($doc["seller_contact"]) && !empty($doc["seller_contact"])) ? $doc["seller_contact"]["email"] : "-"; ?>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div><!--.header-->

				<?php $total_quantity = 0; ?>
				<div class="body">
					<div class="items">
						<table>
							<thead>
								<tr>
									<td>#</td>
									<td><?php echo lang("details"); ?></td>
									<td><?php echo lang("quantity"); ?></td>
									<td><?php echo lang("stock_material_unit"); ?></td>
									<td class="w-adjust"></td>
								</tr>
							</thead>

							<tbody>
								<?php if (!empty($doc["items"])): ?>
									<?php $i = 1; ?>
									<?php foreach ($doc["items"] as $item): ?>
										<?php $total_quantity += $item->quantity; ?>
										<tr>
											<td><?php echo $i++; ?></td>
											<td>
												<span class="product_name"><?php echo $item->product_name; ?></span>
												<?php if (!empty($item->product_description) && trim($item->product_description) != ""): ?>
													<span class="product_description">
														<?php echo trim($item->product_description); ?>
													</span>
												<?php endif; ?>
											</td>
											<td><?php echo number_format($item->quantity, 2); ?></td>
											<td><?php echo $item->unit; ?></td>
											<td></td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="2" style="text-align: right;">
										<span class="custom-color" style="padding-right: 50px;">
											<?php echo lang("gr_total_quantity"); ?>
										</span>
									</td>
									<td style="text-align: right; border-bottom: double #cecece">
										<span><?php echo number_format($total_quantity, 2); ?></span>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="summary clear">
						<div class="total_in_text">
							<span><?php // echo "(" . $doc["total_in_text"] . ")"; ?></span>
						</div>
						<div class="total_all">
							<div class="row">
								<div class="c1 custom-color"><?php // echo lang("total_all_item"); ?></div>
								<div class="c2">
									<span><?php // echo number_format($doc["sub_total_before_discount"], 2); ?></span>
									<span><?php // echo lang("THB"); ?></span>
								</div>
							</div>

							<?php if ($doc["discount_amount"] > 0): ?>
								<div class="row">
									<div class="c1 custom-color">
										<?php // echo lang("discount_amount"); ?>
										<?php // if ($doc["discount_type"] == "P") { echo number_format_drop_zero_decimals($doc["discount_percent"], 2) . "%"; } ?>
									</div>
									<div class="c2">
										<span><?php // echo number_format($doc["discount_amount"], 2); ?></span>
										<span><?php // echo lang("THB"); ?></span>
									</div>
								</div>
								<div class="row">
									<div class="c1 custom-color"><?php // echo lang("amount_after_discount"); ?></div>
									<div class="c2">
										<span><?php // echo number_format($doc["sub_total"], 2); ?></span>
										<span><?php // echo lang("THB"); ?></span>
									</div>
								</div>
							<?php endif; ?>

							<?php if ($doc["vat_value"] > 0): ?>
								<div class="row">
									<div class="c1 custom-color">
										<?php // echo lang("value_add_tax"); ?>
										<?php // echo number_format_drop_zero_decimals($doc["vat_percent"], 2) . "%"; ?>
									</div>
									<div class="c2">
										<span><?php // echo number_format($doc["vat_value"], 2); ?></span>
										<span><?php // echo lang("THB"); ?></span>
									</div>
								</div>
							<?php endif; ?>

							<div class="row">
								<div class="c1 custom-color">
									<?php // echo lang("grand_total_price"); ?>
								</div>
								<div class="c2">
									<span><?php // echo number_format($doc["total"], 2); ?></span>
									<span><?php // echo lang("THB"); ?></span>
								</div>
							</div>

							<?php // if ($doc["wht_value"] > 0): ?>
								<!-- <div class="row wht">
									<div class="c1 custom-color">
										<?php // echo lang("with_holding_tax"); ?>
										<?php // echo number_format_drop_zero_decimals($doc["wht_percent"], 2) . "%"; ?>
									</div>
									<div class="c2">
										<span><?php // echo number_format($doc["wht_value"], 2); ?></span>
										<span><?php // echo lang("THB"); ?></span>
									</div>
								</div> -->
								<!-- <div class="row">
									<div class="c1 custom-color">
										<?php // echo lang("payment_amount"); ?>
									</div>
									<div class="c2">
										<span><?php // echo number_format($doc["payment_amount"], 2); ?></span>
										<span><?php // echo lang("THB"); ?></span>
									</div>
								</div> -->
							<?php // endif; ?>
						</div>
					</div>

					<?php if (!empty($doc["remark"]) && trim($doc["remark"]) != ""): ?>
						<br>
						<div class="remark clear">
							<div class="l1 custom-color">
								<?php echo lang("remark"); ?>
							</div>
							<div class="l2 clear">
								<?php echo nl2br($doc["remark"]); ?>
							</div>
						</div>
					<?php endif; ?>
				</div><!--.body-->

				<div class="footer clear">
					<div class="c1">
						<div class="on_behalf_of"></div>
						<div class="signature clear">
							<div class="name">
								<span class="l1">
									<?php $requester_sign = $this->Users_m->getSignature($doc["buyer"]["id"]); ?>
									<?php if ($doc["doc_status"] != "R" && $requester_sign != null): ?>
										<img src='<?php echo str_replace("./", "/", $requester_sign); ?>'>
									<?php endif; ?>
								</span>
								<span class="l2"><?php echo lang("issuer_of_document"); ?></span>
							</div>
							<div class="date">
								<span class="l1">
									<?php if ($doc["doc_date"] != null && $doc["doc_status"] != "R"): ?>
										<span class="approved_date">
											<?php echo convertDate($doc["doc_date"], true); ?>
										</span>
									<?php endif; ?>
								</span>
								<span class="l2">
									<?php echo lang("date_of_issued"); ?>
								</span>
							</div>
						</div>
					</div>
					<div class="c2">
						<div class="on_behalf_of"></div>
						<div class="signature clear">
							<div class="name">
								<span class="l1">
									<?php
										$signature = null;
										if (isset($doc["approved_by"]["id"]) && !empty($doc["approved_by"]["id"])) {
											$signature = $this->Users_m->getSignature($doc["approved_by"]["id"]);
										}
									?>
									<?php if (isset($doc["doc_status"]) && $doc["doc_status"] == "A"): ?>
										<?php if ($signature != null): ?>
											<img src="<?php echo str_replace("./", "/", $signature); ?>">
										<?php endif; ?>
									<?php endif; ?>
								</span>
								<span class="l2"><?php echo lang("approver"); ?></span>
							</div>
							<div class="date">
								<span class="l1">
									<?php if ($doc["doc_status"] == "A"): ?>
										<span class="approved_date">
											<?php echo convertDate($doc["approved_datetime"], true); ?>
										</span>
									<?php endif; ?>
								</span>
								<span class="l2"><?php echo lang("day_of_approved"); ?></span>
							</div>
						</div>
					</div>
				</div><!--.footer-->
			</div><!--.paper-->
		</div><!--.container-->
		<?php if ($print_mode == "public"): ?>
			<footer>
				<?php $this->load->view("edocs/include/footer"); ?>
			</footer>
		<?php endif; ?>
	</body>
</html>