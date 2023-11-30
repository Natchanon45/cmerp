<!DOCTYPE html>
<html lang="en">

<head>
	<?php $this->load->view("edocs/include/head"); ?>
	<title>
		<?php echo get_setting("company_name") . " - " . $mr_header->doc_no; ?>
	</title>
	<style type="text/css">
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

        .item-list {
            margin-top: 15px;
        }

        .table-category {
            width: 100%;
            margin-bottom: 19px;
        }

        .table-material {
            width: 100%;
            margin-bottom: 19px;
        }

        .table-category thead th {
            padding: 7px 15px;
            border: 1px solid rgb(206, 206, 206);
        }

        .table-category tbody th {
            text-align: center;
            padding: 5px 5px;
            border: 1px solid rgb(206, 206, 206);
            background-color: rgb(206, 206, 206, .2);
        }

        .table-category tbody td {
            border: 1px 1px;
            padding: 2px 10px;
            border: 1px solid rgb(206, 206, 206);
        }

        .table-category tbody td>p:first-child {
            font-weight: bolder;
        }

        .table-category tfoot th {
            border: 1px 1px;
            padding: 5px 10px;
            border: 1px solid rgb(206, 206, 206);
            background-color: rgb(206, 206, 206, .2);
        }

        .w-stockname {
            width: 230px;
        }

        .w-quantity {
            width: 180px;
            text-align: right;
        }

        .w-unit {
            width: 90px;
            text-align: center;
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
									<?php echo $mr_header->doc_no; ?>
								</td>
							</tr>
							<tr>
								<td class="custom-color">
									<?php echo lang("material_request_date"); ?>
								</td>
								<td>
									<?php echo convertDate($mr_header->mr_date, true); ?>
								</td>
							</tr>
							<tr>
								<td class="custom-color">
									<?php echo lang("material_request_person"); ?>
								</td>
								<td>
									<?php echo $requester_info->first_name . " " . $requester_info->last_name; ?>
								</td>
							</tr>
							<tr>
								<td class="custom-color">
									<?php echo lang("positioning"); ?>
								</td>
								<td>
									<?php echo (isset($requester_info->job_title) && !empty($requester_info->job_title)) ? $requester_info->job_title : "-"; ?>
								</td>
							</tr>
							<tr>
								<td class="custom-color">
									<?php echo lang("project_refer"); ?>
								</td>
								<td>
									<?php
									if (isset($mr_header->project_name) && !empty($mr_header->project_name)) {
										echo $mr_header->project_name;
									} else {
										echo "-";
									}
									?>
								</td>
							</tr>
							<?php if (isset($mr_header->sale_order_id) && !empty($mr_header->sale_order_id)): ?>
								<tr>
									<td class="custom-color">
										<?php echo lang("sale_order_refer"); ?>
									</td>
									<td>
										<?php echo (isset($mr_header->sale_order_no) && !empty($mr_header->sale_order_no)) ? $mr_header->sale_order_no : "-"; ?>
									</td>
								</tr>
							<?php endif; ?>
						</table>
					</div>
				</div>
			</div><!--.header-->

			<div class="body">
				<div class="item-list">
					<table>
                        <tr>
                            <td>

                                <?php if (isset($mr_detail["categories"]) && !empty($mr_detail["categories"])): ?>
                                    <?php foreach ($mr_detail["categories"] as $category): ?>
                                        <table border="1" class="table-category">
                                            <thead>
                                                <tr style="height: 38px;">
                                                    <th colspan="5" class="text-left">
                                                        <?php echo lang("category"); ?>
                                                        <span>
                                                            <?php echo $category->item_type . ': ' . $category->title; ?>
                                                        </span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th class="text-center" style="width: 50px;">#</th>
                                                    <th>
                                                        <?php echo lang("details"); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo lang("stock_restock_name"); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo lang("quantity"); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo lang("stock_material_unit"); ?>
                                                    </th>
                                                </tr>
                                                <?php $sub_total = 0;
                                                $index = 0;
                                                foreach ($mr_detail["mr_detail"] as $data): ?>
                                                    <?php if ($category->id == $data->category_in_bom): ?>
                                                        <tr>
                                                            <td class="text-center">
                                                                <?php echo ++$index; ?>
                                                            </td>
                                                            <td>
                                                                <p>
                                                                    <?php echo mb_strimwidth($data->code . ' - ' . $data->title, 0, 50, "..."); ?>
                                                                </p>
                                                                <p>
                                                                    <?php echo mb_strimwidth($data->description, 0, 50, "..."); ?>
                                                                </p>
                                                            </td>
                                                            <td class="w-stockname custom-color">
                                                                <?php
                                                                    if (isset($data->stock_info->group_info->id) && !empty($data->stock_info->group_info->id)) {
                                                                        echo $data->stock_info->group_info->name;
                                                                    } else {
                                                                        echo "-";
                                                                    }
                                                                ?>
                                                            </td>
                                                            <td class="text-right w-quantity">
                                                                <?php echo number_format($data->quantity, 6);
                                                                $sub_total += $data->quantity; ?>
                                                            </td>
                                                            <td class="text-center w-unit">
                                                                <?php echo $data->unit_type; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3"></th>
                                                    <th class="text-right">
                                                        <?php echo number_format($sub_total, 6); ?>
                                                    </th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (sizeof($mr_list["rm_list"])): ?>
                                    <table border="1" class="table-category">
                                        <thead>
                                            <tr style="height: 38px;">
                                                <th colspan="4" class="text-left">
                                                    <?php echo lang("mr_total_raw_material"); ?>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>
                                                    <?php echo lang("details"); ?>
                                                </th>
                                                <th>
                                                    <?php echo lang("quantity"); ?>
                                                </th>
                                                <th>
                                                    <?php echo lang("stock_material_unit"); ?>
                                                </th>
                                            </tr>
                                            <?php $index = 0; $sub_total = 0; foreach ($mr_list["rm_list"] as $rm): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo ++$index; ?></td>
                                                    <td>
                                                        <p><?php echo mb_strimwidth($rm->material_info->name . ' - ' . $rm->material_info->production_name, 0, 60, "...")?></p>
                                                        <p><?php echo mb_strimwidth($rm->material_info->description, 0, 60, "..."); ?></p>
                                                    </td>
                                                    <td class="text-right w-quantity">
                                                        <?php echo number_format($rm->quantity, 6); ?>
                                                        <?php $sub_total += $rm->quantity; ?>
                                                    </td>
                                                    <td class="text-center w-unit">
                                                        <?php echo $rm->material_info->unit; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2"></th>
                                                <th class="text-right">
                                                    <?php echo number_format($sub_total, 6); ?>
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php endif; ?>

                                <?php if (sizeof($mr_list["sfg_list"])): ?>
                                    <table border="1" class="table-category">
                                        <thead>
                                            <tr style="height: 38px;">
                                                <th colspan="4" class="text-left">
                                                    <?php echo lang("mr_total_semi_finished_goods"); ?>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>
                                                    <?php echo lang("details"); ?>
                                                </th>
                                                <th>
                                                    <?php echo lang("quantity"); ?>
                                                </th>
                                                <th>
                                                    <?php echo lang("stock_material_unit"); ?>
                                                </th>
                                            </tr>
                                            <?php $index = 0; $sub_total = 0; foreach ($mr_list["sfg_list"] as $sfg): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo ++$index; ?></td>
                                                    <td>
                                                        <p><?php echo mb_strimwidth($sfg->item_info->item_code . ' - ' . $sfg->item_info->title, 0, 60, "...")?></p>
                                                        <p><?php echo mb_strimwidth($sfg->item_info->description, 0, 60, "..."); ?></p>
                                                    </td>
                                                    <td class="text-right w-quantity">
                                                        <?php echo number_format($sfg->quantity, 6); ?>
                                                        <?php $sub_total += $sfg->quantity; ?>
                                                    </td>
                                                    <td class="text-center w-unit">
                                                        <?php echo $sfg->item_info->unit_type; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2"></th>
                                                <th class="text-right">
                                                    <?php echo number_format($sub_total, 6); ?>
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php endif; ?>

                            </td>
                        </tr>
					</table>
				</div>

				<?php if (trim($mr_header->note) != ""): ?>
					<div class="remark clear remark-mt-3">
						<div class="l1 custom-color">
							<?php echo lang("remark"); ?>
						</div>
						<div class="l2 clear">
							<?php echo nl2br($mr_header->note); ?>
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
								<?php if ($mr_header->status_id != 4): ?>
									<?php if (!empty($mr_header->requester_id) && $mr_header->requester_id != null): ?>
										<?php if (null != $requester_sign = $this->Users_m->getSignature($mr_header->requester_id)): ?>
											<img src="<?php echo str_replace('./', '/', $requester_sign); ?>" alt="signature">
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
							</span>
							<span class="l2">
								<?php if (isset($mr_header->requester_id) && !empty($mr_header->requester_id)): ?>
								<?php
									if ($mr_header->status_id != 4) {
										echo "( ";
										echo (isset($requester_info->first_name) && !empty($requester_info->first_name)) ? $requester_info->first_name : "";
										echo " ";
										echo (isset($requester_info->last_name) && !empty($requester_info->last_name)) ? $requester_info->last_name : "";
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
								<?php if ($mr_header->mr_date != null && $mr_header->status_id != 4): ?>
									<span class="approved_date">
										<?php echo convertDate($mr_header->mr_date, true); ?>
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
								<?php if ($mr_header->approved_by != null && $mr_header->status_id == 3): ?>
									<?php if (null != $signature = $this->Users_m->getSignature($mr_header->approved_by)): ?>
										<img src="<?php echo str_replace('./', '/', $signature); ?>" alt="signature">
									<?php endif; ?>
								<?php endif; ?>
							</span>
							<span class="l2">
								<?php if ($mr_header->status_id == 3): ?>
									<?php
										echo "( ";
										echo (isset($approver_info->first_name) && !empty($approver_info->first_name)) ? $approver_info->first_name : "";
										echo " ";
										echo (isset($approver_info->last_name) && !empty($approver_info->last_name)) ? $approver_info->last_name : "";
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
								<?php if ($mr_header->approved_date != null && $mr_header->status_id == 3): ?>
									<span class="approved_date">
										<?php echo convertDate($mr_header->approved_date, true); ?>
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