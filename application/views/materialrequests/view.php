<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time();?>">

<style type="text/css">
.page-title .title-button-group {
    float: right !important;
    margin: 10px 15px 0px 15px !important;
}

.docitem {
    margin-top: 1.2rem !important;
}

.text-left {
    text-align: left !important;
}

.w220px {
    width: 220px !important;
}

.mt2r {
    margin-top: 2rem !important;
}

.page-absolute {
	position: absolute;
}

.page-relative {
	position: relative;
}

.rejected-logo {
	right: 20rem;
	bottom: 25rem;
	font-size: 3.5rem;
	color: red;
	opacity: 0.5;
	transform: rotate(-35deg);
	border: 3px solid;
    padding: 0.5rem 1rem;
}

.doc-detail td:first-child {
    width: fit-content !important;
    padding-right: 10px;
}
</style>

<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15 clear">
        <h1><?php echo lang('material_request_no'); ?> <?php echo $mat_req_info->doc_no;?></h1>
        <div class="title-button-group">
            <a href="<?php echo get_uri("materialrequests"); ?>" style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn">
				<i class="fa fa-hand-o-left" aria-hidden="true"></i>
				<?php echo lang("back"); ?>
			</a>

            <?php if ($approve_material_request): ?>
				<?php if ($mat_req_info->status_id == 1 || $mat_req_info->status_id == 2): ?>
					<a href="<?php echo get_uri('materialrequests/approve/' . $mat_req_info->id); ?>" class="btn btn-info mt0 mb0 approval-btn approve-btn"><?php echo lang("status_already_approved"); ?></a>
					<a href="<?php echo get_uri('materialrequests/disapprove/' . $mat_req_info->id); ?>" class="btn btn-danger mt0 mb0 approval-btn approve-btn"><?php echo lang("status_already_rejected"); ?></a>
				<?php endif; ?>
			<?php endif; ?>

            <?php if ($mat_req_info->status_id != 4): ?>
                <a onclick="window.open('<?php echo get_uri('materialrequests/print/' . $mat_req_info->id);?>', '' ,'width=980,height=720');" class="btn btn-default mt0 mb0">
                    <i class='fa fa-print'></i> <?php echo lang('print'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="not-enough" class="alert alert-danger mt15 mb0 hide" role="alert"><?php echo @$error_message; ?></div>
	<div id="approved-success" class="alert alert-success mt15 mb0 hide" role="alert"><?php echo @$success_message; ?></div>
	<div id="reject-message" class="alert alert-danger mt15 mb0 hide" role="alert"><?php echo @$reject_message; ?></div>
</div><!--#dcontroller-->

<div id="printd" class="clear page-relative">
    <div class="docheader clear">
        <div class="l">
            <div class="logo">
                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . get_file_from_setting('estimate_logo', true)) != false): ?>
                    <img src="<?php echo get_file_from_setting('estimate_logo', get_setting('only_file_path')); ?>" />
                <?php else: ?>
                    <span class="nologo">&nbsp;</span>
                <?php endif; ?>
            </div>

            <div class="company">
                <p class="company_name"><?php echo get_setting('company_name'); ?></p>
                <p><?php echo nl2br(get_setting('company_address')); ?></p>
                <?php if (trim(get_setting('company_phone')) != ''): ?>
                    <p><?php echo lang('phone') . ': ' . get_setting('company_phone'); ?></p>
                <?php endif; ?>
                <?php if (trim(get_setting('company_website')) != ''): ?>
                    <p><?php echo lang('website') . ': ' . get_setting('company_website'); ?></p>
                <?php endif; ?>
                <?php if (trim(get_setting('company_vat_number')) != 'company_vat_number'): ?>
                    <p><?php echo lang('vat_number') . ': ' . get_setting('company_vat_number'); ?></p>
                <?php endif; ?>
            </div><!-- .company -->
        </div><!--.l-->

        <div class="r">
            <h1 class="document_name custom-color"><?php echo $mat_req_info->mr_type == 1 ? lang('material_request_document') : lang('fg_request_document'); ?></h1>
            <div class="about_company">
                <table class="doc-detail">
                    <tr>
                        <td class="custom-color"><?php echo lang('document_number'); ?></td>
                        <td><?php echo (isset($mat_req_info->doc_no) && !empty($mat_req_info->doc_no)) ? $mat_req_info->doc_no : '-'; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo ucwords(lang('material_request_date')); ?></td>
                        <td><?php echo (isset($mat_req_info->mr_date) && !empty($mat_req_info->mr_date)) ? convertDate($mat_req_info->mr_date, true) : '-'; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo ucwords(lang('material_request_person')); ?></td>
                        <td>
                            <?php
                                echo (isset($mat_requester_info->first_name) && !empty($mat_requester_info->first_name)) ? $mat_requester_info->first_name : '';
                                echo ' ';
                                echo (isset($mat_requester_info->last_name) && !empty($mat_requester_info->last_name)) ? $mat_requester_info->last_name : '';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo ucwords(lang('positioning')); ?></td>
                        <td><?php echo (isset($mat_requester_info->job_title) && !empty($mat_requester_info->job_title)) ? $mat_requester_info->job_title : '-'; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo ucwords(lang('project_refer')); ?></td>
                        <td><?php echo (isset($mat_project_info->title) && !empty($mat_project_info->title)) ? $mat_project_info->title : '-'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div><!--.docheader-->

    <div class="docitem mt2r">
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <td><?php echo lang('details'); ?></td>
                    <td class="text-left w220px" colspan="2"><?php echo lang('stock_restock_name'); ?></td>
                    <td><?php echo lang('quantity'); ?></td>
                    <td><?php echo lang('stock_material_unit'); ?></td>
                    <td></td>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr><td colspan="7">&nbsp;</td></tr>
                <tr>
                    <td colspan="3">
                        <?php if($mat_req_info->status_id == "1"): ?>
                            <p><?php echo modal_anchor(get_uri("materialrequests/item_add"), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("id"=>"add_item_button", "class" => "btn btn-default", "title" => lang('add_item'), "data-post-doc_id" => $mat_req_info->id)); ?></p>
                        <?php endif; ?>
                        <p><input type="text" id="total_in_text" readonly></p>
                    </td>
                    <td colspan="4" class="summary"></td>
                </tr>
            </tfoot>
        </table>
        <?php if (trim($mat_req_info->note) != ""): ?>
            <div class="remark clear">
                <p class="custom-color"><?php echo lang('remark'); ?></p>
                <p><?php echo nl2br($mat_req_info->note); ?></p>
            </div>
        <?php endif; ?>
    </div><!--.docitem-->
    
    <div class="docsignature clear">
        <div class="customer">
            <div class="on_behalf_of"><?php // echo "ในนาม" . $client["company_name"]; ?></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($mat_req_info->status_id != 4): if ($mat_req_info->requester_id != null): if (null != $requester_sign = $this->Users_m->getSignature($mat_req_info->requester_id)): ?>
                                <img src="<?php echo ($requester_sign != null) ? '/' . $requester_sign : ''; ?>">
                            <?php endif; endif; endif; ?>
                        </span>
                    </span>
                    <span class="l2"><?php echo lang('material_request_person'); ?></span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if ($mat_req_info->mr_date != null && $mat_req_info->status_id != 4): ?>
                            <span class="approved_date"><?php echo convertDate($mat_req_info->mr_date, true); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="l2"><?php echo lang('date'); ?></span>
                </div>
            </div>
        </div><!--.customer -->
        <div class="company">
            <div class="on_behalf_of"><?php // echo "ในนาม" . get_setting("company_name"); ?></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($mat_req_info->approved_by != null && $mat_req_info->status_id == 3): if (null != $signature = $this->Users_m->getSignature($mat_req_info->approved_by)): ?>
                                <img src="<?php echo '/' . $signature; ?>">
                            <?php endif; endif; ?>
                        </span>
                    </span>
                    <span class="l2"><?php echo lang('approver'); ?></span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if ($mat_req_info->approved_date != null && $mat_req_info->status_id == 3): ?>
                            <span class="approved_date"><?php echo convertDate($mat_req_info->approved_date, true); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="l2"><?php echo lang('date'); ?></span>
                </div>
            </div>
        </div><!--.company-->
    </div><!--.docsignature-->

    <?php if ($mat_req_info->status_id == 4): ?>
        <div class="page-absolute rejected-logo"><?php echo strtoupper(lang('status_already_rejected')); ?></div>
    <?php endif; ?>
</div><!--#printd-->

<script type="text/javascript">
window.addEventListener('keydown', function(event) {
    if (event.keyCode === 80 && (event.ctrlKey || event.metaKey) && !event.altKey && (!event.shiftKey || window.chrome || window.opera)) {
        event.preventDefault();
        if (event.stopImmediatePropagation)event.stopImmediatePropagation();
        else event.stopPropagation();
        return;
    }
}, true);

$(document).ready(function() {
    loadItems();

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

function loadItems() {
    axios.get('<?php echo_uri('materialrequests/view_items/' . $mat_req_info->id); ?>').then(function (response) {
        data = response.data;

        let tbody = "";
        data.map((item, index) => {
            tbody += `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <p class="desc1">${item.code} - ${item.title}</p>
                        <p class="desc2">${item.description}</p>
                    </td>
                    <td class="text-left" colspan="2">
                        <?php if ($mat_req_info->mr_type == '2'): ?>
                            <a href="<?php echo_uri('stock/restock_item_view/'); ?>${item.stocks == null ? '-' : item.stocks?.id}">${item.stocks == null ? '-' : item.stocks?.name}</a>
                        <?php else: ?>
                            <a href="<?php echo_uri('stock/restock_view/'); ?>${item.stocks == null ? '-' : item.stocks?.id}">${item.stocks == null ? '-' : item.stocks?.name}</a>
                        <?php endif; ?>
                    </td>
                    <td>${item.quantity}</td>
                    <td>${item.unit_type}</td>
                    <td class="edititem">
                    <?php if ($mat_req_info->status_id == 1): ?>
                        ${item.edit}
                        <a class="delete" data-item_id="${item.id}" data-bpim_id="${item.bpim_id}"><i class="fa fa-times fa-fw"></i></a>
                    <?php endif; ?>
                    </td>
                </tr>
            `;
        });

        $(".docitem tbody").empty().append(tbody);
        $(".edititem .delete").click(function() {
            deleteItem($(this).data("item_id"), $(this).data("bpim_id"));
        });
    }).catch(function (error) {
        console.log(error);
    });
}

function deleteItem(item_id, bpim_id) {
    let url = '<?php echo_uri('materialrequests/item_delete'); ?>';
    let data = {
        doc_id: '<?php echo $mat_req_info->id; ?>',
        doc_type: '<?php echo $mat_req_info->mr_type; ?>',
        project_id: '<?php echo $mat_req_info->project_id; ?>',
        bpim_id: bpim_id,
        item_id: item_id
    }

    axios.post(url, data).then(function (response) {
        loadItems();
    });
}
</script>
