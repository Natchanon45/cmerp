<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time();?>">

<style type="text/css">
#item-table-list {
	font-size: small;
	margin: 0;
	padding: 0;
	width: 100%;
}

#item-table-list th {
	border-top: 1px solid #c3c3c3 !important;
	border-bottom: 1px solid #c3c3c3 !important;
	border-left: none;
	border-right: none;
}

#item-table-list td {
	border-bottom: 1px solid #c3c3c3 !important;
}

.mr-list {
	height: 44px;
}

.mr-list td {
	padding: 4px !important;
}

.page-absolute {
	position: absolute;
}

.page-relative {
	position: relative;
}

.rejected-logo {
	right: 20rem;
	bottom: 15rem;
	font-size: 2rem;
	color: red;
	opacity: 0.5;
	transform: rotate(-40deg);
	border: 3px solid;
    padding: 0.5rem 1rem;
}

.item-top td {
	vertical-align: top;
}
</style>



<div id="dcontroller" class="clearfix">
	<div class="page-title clearfix mt15">
		<!-- Left top -->
		<h1><?php echo lang("mr_number") . $mat_req_info->doc_no; ?></h1>

		<!-- Right top -->
		<div class="title-button-group">
			<a href="<?php echo get_uri("materialrequests"); ?>" style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn">
				<i class="fa fa-hand-o-left" aria-hidden="true"></i>
				<?php echo lang("back"); ?>
			</a>

			<?php if ($approve_material_request): ?>
				<?php if ($mat_req_info->status_id == 1 || $mat_req_info->status_id == 2): ?>
					<a href="<?php echo get_uri("materialrequests/approve/" . $mat_req_info->id); ?>" class="btn btn-info mt0 mb0 approval-btn approve-btn"><?php echo lang("status_already_approved"); ?></a>
					<a href="<?php echo get_uri("materialrequests/disapprove/" . $mat_req_info->id); ?>" class="btn btn-danger mt0 mb0 approval-btn approve-btn"><?php echo lang("status_already_rejected"); ?></a>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($mat_req_info->status_id != 4): ?>
				<span class="dropdown inline-block">
					<button class="btn btn-info dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
						<i class="fa fa-cogs"></i>
						<?php echo lang("actions"); ?>
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<?php if ($update_material_request): ?>
							<?php if ($mat_req_info->status_id == 1 || $mat_req_info->status_id == 2): ?>
								<li role="presentation">
									<?php echo modal_anchor(get_uri("materialrequests/modal_form"), "<i class='fa fa-edit'></i> " . lang('edit_materialrequest'), array("title" => lang('edit_materialrequest'), "data-post-id" => $mat_req_info->id, "role" => "menuitem", "tabindex" => "-1")); ?>
								</li>
							<?php endif; ?>
							<li role="presentation" id="btn-print"><?php echo js_anchor("<i class='fa fa-print'></i> " . lang("print")); ?></li>
						<?php endif; ?>
					</ul>
				</span>
			<?php endif; ?>
		</div>
	</div>

	<!-- Status Line -->
	<div class="panel panel-default  p15 no-border m0">
		<b><?php echo lang("status"); ?></b>
		<?php if ($mat_req_info-> status_id == 1 || $mat_req_info->status_id == 2): ?>
			<span class="mr10">
				<span class="mt0 label label-default large" style="background-color: #efc050"><?php echo lang("status_waiting_for_approve"); ?></span>
			</span>
		<?php elseif ($mat_req_info->status_id == 3): ?>
			<span class="mr10">
				<span class="mt0 label label-default large" style="background-color: #009b77"><?php echo lang("status_already_approved"); ?></span>
			</span>
		<?php elseif ($mat_req_info->status_id == 4): ?>
			<span class="mr10">
				<span class="mt0 label label-default large" style="background-color: #ff1a1a"><?php echo lang("status_already_rejected"); ?></span>
			</span>
		<?php endif; ?>
	</div>
	<div id="not-enough" class="alert alert-danger mt15 mb0 hide" role="alert"><?php echo $error_message; ?></div>
	<div id="approved-success" class="alert alert-success mt15 mb0 hide" role="alert"><?php echo $success_message; ?></div>
	<div id="reject-message" class="alert alert-danger mt15 mb0 hide" role="alert"><?php echo $reject_message; ?></div>
</div>

<div id="printd" class="clear page-relative">
	<!-- Document Header -->
	<div class="docheader clear">
		<!-- Header Left -->
		<div class="l">
			<div class="logo">
				<img src="<?php echo get_file_from_setting("estimate_logo", get_setting('only_file_path')); ?>" alt="logo">
			</div>

			<div class="company">
				<p class="company_name"><?php echo get_setting("company_name"); ?></p>
				<p class=""><?php echo get_setting("company_address"); ?></p>

				<?php if (get_setting("company_phone")): ?>
					<p><?php echo lang("phone") . ": " . get_setting("company_phone"); ?></p>
				<?php endif; ?>

				<?php if (get_setting("company_website")): ?>
					<p><?php echo lang("website") . ": " . get_setting("company_website"); ?></p>
				<?php endif; ?>

				<?php if (get_setting("company_vat_number")): ?>
					<p><?php echo lang("vat_number") . ": " . get_setting("company_vat_number"); ?></p>
				<?php endif; ?>
			</div>

			<!-- Customer -->
			<div class="customer">
				<?php if ($mat_client_info): ?>
					<p class="custom-color"><?php echo lang("client"); ?></p>
					<p class="custom-name"><?php echo $mat_client_info->company_name; ?></p>
					<?php echo $mat_client_info->address ? "<p>" . $mat_client_info->address . "</p>" : ""; ?>
					<?php echo $mat_client_info->city ? "<p>" . lang("city") . " " . $mat_client_info->city . "</p>" : ""; ?>
					<?php echo $mat_client_info->state ? "<p>" . lang("state") . $mat_client_info->state . " " . $mat_client_info->zip . "</p>" : ""; ?>
					<?php echo $mat_client_info->vat_number ? "<p>" . lang("vat_number") . ": " . $mat_client_info->vat_number . "</p>" : ""; ?>
				<?php endif; ?>
			</div>
		</div>

		<!-- Header Right -->
		<div class="r">
			<h1 class="document_name custom-color"><?php echo lang("materialrequests"); ?></h1>
			<div class="about_company">
				<table>
					<tr class="item-top">
						<td class="custom-color common-text" style="padding-right: 1rem;"><?php echo lang("document_number"); ?></td>
						<td class="common-text"><?php echo $mat_req_info->doc_no; ?></td>
					</tr>
					<tr class="item-top">
						<td class="custom-color common-text"><?php echo lang("project_name"); ?></td>
						<td class="common-text"><?php echo $mat_project_info?->title; ?></td>
					</tr>
					<tr class="item-top">
						<td class="custom-color common-text"><?php echo lang("material_request_date"); ?></td>
						<td class="common-text"><?php echo format_to_date($mat_req_info->mr_date); ?></td>
					</tr>
					<tr class="item-top">
						<td class="custom-color common-text"><?php echo lang("material_request_person"); ?></td>
						<td class="common-text"><?php echo $mat_requester_info->first_name . " " . $mat_requester_info->last_name; ?></td>
					</tr>
					<tr class="item-top">
						<td class="custom-color common-text"><?php echo lang("positioning"); ?></td>
						<td class="common-text"><?php echo $mat_requester_info->job_title; ?></td>
					</tr>
				</table>
			</div>
			<div class="about_customer">
				<table>
					<tr>
						<td class="custom-color"><?php echo lang("contact_name"); ?></td>
						<td>
							<?php
							if ($mat_client_contact) {
								echo $mat_client_contact->first_name ? $mat_client_contact->first_name : "";
								echo $mat_client_contact->last_name ? $mat_client_contact->last_name : "";
							} else {
								echo "-";
							}
							?>
						</td>
					</tr>
					<tr>
						<td class="custom-color"><?php echo lang("phone"); ?></td>
						<td>
							<?php
							if ($mat_client_contact) {
								echo $mat_client_contact->phone ? $mat_client_contact->phone : "-";
							} else {
								echo "-";
							}
							?>
						</td>
					</tr>
					<tr>
						<td class="custom-color"><?php echo lang("email"); ?></td>
						<td>
							<?php
							if ($mat_client_contact) {
								echo $mat_client_contact->email ? $mat_client_contact->email : "-";
							} else {
								echo "-";
							}
							?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<!-- Document Item -->
	<div class="docitem" style="margin: 2rem 0;">
		<table id="item-table-list">
			<thead>
				<tr style="height: 40px;">
					<th width="5%" style="text-align: center;">#</th>
					<th width="45%" style="text-align: center;"><?php echo lang("product_material_record"); ?></th>
					<th width="25%" style="text-align: center;"><?php echo lang("stock_restock_name"); ?></th>
					<th width="15%" style="text-align: right;"><?php echo lang("quantity"); ?></th>
					<th width="9%" style="text-align: center;"><?php echo lang("stock_material_unit"); ?></th>
					<th width="1%"></th>
				</tr>
			</thead>
			<tbody>
				<?php if (sizeof($mat_items_info)): ?>
					<?php foreach($mat_items_info as $key => $item): ?>
						<tr class="mr-list">
							<td style="text-align: center;"><?php echo $key + 1; ?></td>
							<td>
								<b><?php echo mb_strimwidth($item->code . " : " . $item->title, 0, 55, "..."); ?></b><br />
								<p style="color: #9a9797;"><?php echo nl2br(mb_strimwidth($item->description, 0, 55, "...")); ?></p>
							</td>
							<td style="text-align: left;" class="custom-color"><?php echo isset($item->stock_group_id) && !empty($item->stock_group_id) ? anchor(get_uri("stock/restock_view/" . $item->stock_group_id), $item->stock_group_name, array("target" => "_blank")) : '-'; ?></td>
							<td style="text-align: right;"><?php echo to_decimal_format3($item->quantity); ?></td>
							<td style="text-align: center;"><?php echo strtoupper($item->unit_type); ?></td>
							<td></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<!-- Remark -->
	<div class="remark clear">
		<p class="custom-color"><?php echo lang("remark"); ?></p>
		<p><?php echo $mat_req_info->note; ?></p>
	</div>

	<!-- Document Footer -->
	<div class="docsignature clear">

		<div class="customer">
			<div class="on_behalf_of"></div>
			<div class="clear">
				<div class="name">
					<span class="l1" style="height: 30px !important;"><?php echo $mat_requester_info->first_name . " " . $mat_requester_info->last_name; ?></span>
					<span class="l2"><?php echo lang("material_request_person"); ?></span>
				</div>
				<div class="date">
					<span class="l1" style="height: 30px !important;"><?php echo format_to_date($mat_req_info->mr_date); ?></span>
					<span class="l2"><?php echo lang("material_request_date"); ?></span>
				</div>
			</div>
		</div>

		<?php if ($mat_req_info->status_id == 4): ?>
			<div class="company">
				<div class="on_behalf_of"></div>
				<div class="clear">
					<div class="name">
						<span class="l1" style="height: 30px !important;"><?php echo $mat_req_info->approved_by ? $mat_req_info->approved_by_name : ""; ?></span>
						<span class="l2"><?php echo lang("rejecter"); ?></span>
					</div>
					<div class="date">
						<span class="l1" style="height: 30px !important;"><?php echo $mat_req_info->approved_date ? format_to_date($mat_req_info->approved_date) : ""; ?></span>
						<span class="l2"><?php echo lang("date_of_rejected"); ?></span>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="company">
				<div class="on_behalf_of"></div>
				<div class="clear">
					<div class="name">
						<span class="l1" style="height: 30px !important;"><?php echo $mat_req_info->approved_by ? $mat_req_info->approved_by_name : ""; ?></span>
						<span class="l2"><?php echo lang("approver"); ?></span>
					</div>
					<div class="date">
						<span class="l1" style="height: 30px !important;"><?php echo $mat_req_info->approved_date ? format_to_date($mat_req_info->approved_date) : ""; ?></span>
						<span class="l2"><?php echo lang("day_of_approved"); ?></span>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</div>
	<div class="page-absolute rejected-logo hide"><?php echo lang('status_already_rejected'); ?></div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$("#item-table-list").DataTable({
		"bPaginate": false,
		"bLengthChange": false,
		"bFilter": false,
		"bSort": false,
		"bInfo": false,
		"bAutoWidth": false
	});

	<?php if (isset($error_message) && $error_message): ?>
		$("#not-enough").removeClass('hide');

		setTimeout(function(e) {
			$("#not-enough").addClass('hide');
		}, 5101);
	<?php endif; ?>

	<?php if (isset($success_message) && $success_message): ?>
		$("#approved-success").removeClass('hide');

		setTimeout(function(e) {
			$("#approved-success").addClass('hide');
		}, 5101);
	<?php endif; ?>

	<?php if (isset($reject_message) && $reject_message): ?>
		$("#reject-message").removeClass('hide');

		setTimeout(function(e) {
			$("#reject-message").addClass('hide');
		}, 5101);
	<?php endif; ?>
});

const btnPrint = document.querySelector("#btn-print");
btnPrint.addEventListener("click", () => {
	window.print();
});
</script>