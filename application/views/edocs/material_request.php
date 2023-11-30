<!DOCTYPE html>
<html lang="en">

<head>
	<?php $this->load->view("edocs/include/head"); ?>
	<title>
		<?php echo get_setting("company_name") . " - " . $mat_req_info->doc_no; ?>
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

		.text-left {
			text-align: left !important;
		}

		.w220px {
			width: 220px !important;
		}

		.right .docinfo {
			border-bottom: none !important;
		}

		.remark-mt-3 {
			margin-top: 3rem;
		}
	</style>
</head>

<body>
	<header>
		<?php $this->load->view("edocs/include/header"); ?>
	</header>

	<div class="container">
		<div class="paper wrapper">
			<div class="header clear">
				<div class="left">
					<div class="logo">
						<?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . get_file_from_setting("estimate_logo", true)) != false): ?>
							<img
								src="<?php echo get_file_from_setting("estimate_logo", get_setting("only_file_path")); ?>" />
						<?php else: ?>
							<span class="nologo">&nbsp;</span>
						<?php endif; ?>
					</div>
					<div class="seller">
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
				</div>
				<div class="right">
					<div class="docname custom-color">
						<?php echo lang("mr_text"); ?>
					</div>
					<div class="docinfo">
						<table>
							<tr>
								<td class="custom-color">
									<?php echo lang("document_number"); ?>
								</td>
								<td>
									<?php echo $mat_req_info->doc_no; ?>
								</td>
							</tr>
							<tr>
								<td class="custom-color">
									<?php echo lang("material_request_date"); ?>
								</td>
								<td>
									<?php echo convertDate($mat_req_info->mr_date, true); ?>
								</td>
							</tr>
							<tr>
								<td class="custom-color">
									<?php echo lang("material_request_person"); ?>
								</td>
								<td>
									<?php echo $mat_requester_info->first_name . " " . $mat_requester_info->last_name; ?>
								</td>
							</tr>
							<tr>
								<td class="custom-color">
									<?php echo lang("positioning"); ?>
								</td>
								<td>
									<?php echo (isset($mat_requester_info->job_title) && !empty($mat_requester_info->job_title)) ? $mat_requester_info->job_title : "-"; ?>
								</td>
							</tr>
							<tr>
								<td class="custom-color">
									<?php echo lang("project_refer"); ?>
								</td>
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
							<?php if (isset($mat_req_info->sale_order_id) && !empty($mat_req_info->sale_order_id)): ?>
								<tr>
									<td class="custom-color">
										<?php echo lang("sale_order_refer"); ?>
									</td>
									<td>
										<?php echo (isset($mat_req_info->sale_order_no) && !empty($mat_req_info->sale_order_no)) ? $mat_req_info->sale_order_no : "-"; ?>
									</td>
								</tr>
							<?php endif; ?>
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
								<td>
									<?php echo lang("details"); ?>
								</td>
								<td class="text-left w220px" colspan="2">
									<?php echo lang("stock_restock_name"); ?>
								</td>
								<td>
									<?php echo lang("quantity"); ?>
								</td>
								<td>
									<?php echo lang("stock_material_unit"); ?>
								</td>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($mat_item_info)): ?>
								<?php $i = 1; ?>
								<?php foreach ($mat_item_info as $item): ?>
									<tr>
										<td>
											<?php echo $i++; ?>
										</td>
										<td>
											<span class="product_name">
												<?php echo $item->names; ?>
											</span>
											<?php if (trim($item->description) != ""): ?>
												<span class="product_description">
													<?php echo $item->description ? trim($item->description) : "-"; ?>
												</span>
											<?php endif; ?>
										</td>
										<td class="text-left w220px" colspan="2">
											<?php echo $item->stocks->name; ?>
										</td>
										<td>
											<?php echo number_format($item->quantity, 6); ?>
										</td>
										<td>
											<?php echo $item->unit_type; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<?php if (trim($mat_req_info->note) != ""): ?>
					<div class="remark clear remark-mt-3">
						<div class="l1 custom-color">
							<?php echo lang("remark"); ?>
						</div>
						<div class="l2 clear">
							<?php echo nl2br($mat_req_info->note); ?>
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
								<?php if ($mat_req_info->status_id != 4): ?>
									<?php if (!empty($mat_req_info->requester_id) && $mat_req_info->requester_id != null): ?>
										<?php if (null != $requester_sign = $this->Users_m->getSignature($mat_req_info->requester_id)): ?>
											<img src="<?php echo str_replace('./', '/', $requester_sign); ?>" alt="signature">
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
							</span>
							<span class="l2">
								<?php if (isset($mat_req_info->requester_id) && !empty($mat_req_info->requester_id)): ?>
								<?php
									if ($mat_req_info->status_id != 4) {
										echo "( ";
										echo (isset($mat_requester_info->first_name) && !empty($mat_requester_info->first_name)) ? $mat_requester_info->first_name : "";
										echo " ";
										echo (isset($mat_requester_info->last_name) && !empty($mat_requester_info->last_name)) ? $mat_requester_info->last_name : "";
										echo " )";
									} else {
										echo "( " . str_repeat("_", 19) . " )";
									}
								?>
								<?php else: ?>
									<?php echo "( " . str_repeat("_", 19) . " )"; ?>
								<?php endif; ?>
							</span>
							<span class="l2">
								<?php echo lang("material_request_person"); ?>
							</span>
						</div>
						<div class="date">
							<span class="l1">
								<?php if ($mat_req_info->mr_date != null && $mat_req_info->status_id != 4): ?>
									<span class="approved_date">
										<?php echo convertDate($mat_req_info->mr_date, true); ?>
									</span>
								<?php endif; ?>
							</span>
							<span class="l2">
								<?php echo lang("material_request_date"); ?>
							</span>
						</div>
					</div>
				</div>
				<div class="c2">
					<div class="on_behalf_of"></div>
					<div class="signature clear">
						<div class="name">
							<span class="l1">
								<?php if ($mat_req_info->approved_by != null && $mat_req_info->status_id == 3): ?>
									<?php if (null != $signature = $this->Users_m->getSignature($mat_req_info->approved_by)): ?>
										<img src="<?php echo str_replace('./', '/', $signature); ?>" alt="signature">
									<?php endif; ?>
								<?php endif; ?>
							</span>
							<span class="l2">
								<?php if ($mat_req_info->status_id == 3): ?>
									<?php
										echo "( ";
										echo (isset($mat_approver_info->first_name) && !empty($mat_approver_info->first_name)) ? $mat_approver_info->first_name : "";
										echo " ";
										echo (isset($mat_approver_info->last_name) && !empty($mat_approver_info->last_name)) ? $mat_approver_info->last_name : "";
										echo " )";
									?>
								<?php else: ?>
									<?php echo "( " . str_repeat("_", 19) . " )"; ?>
								<?php endif; ?>
							</span>
							<span class="l2">
								<?php echo lang("approver"); ?>
							</span>
						</div>
						<div class="date">
							<span class="l1">
								<?php if ($mat_req_info->approved_date != null && $mat_req_info->status_id == 3): ?>
									<span class="approved_date">
										<?php echo convertDate($mat_req_info->approved_date, true); ?>
									</span>
								<?php endif; ?>
							</span>
							<span class="l2">
								<?php echo lang("day_of_approved"); ?>
							</span>
						</div>
					</div>
				</div>
			</div><!--.footer-->
		</div><!--.paper-->
	</div><!--.container-->
</body>

</html>