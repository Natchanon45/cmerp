<link rel="stylesheet" href="/assets/css/printd.css?time=<?php echo time(); ?>">

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
        padding-right: 1.2rem;
    }

    .badge-custom {
        margin: -2px 0 0 10px;
        padding: 6px 8px;
        border-radius: 3px;
    }

    .badge-waiting-approve {
        background-color: #efc050;
    }

    .badge-already-approved {
        background-color: #009b77;
    }

    .badge-already-rejected {
        background-color: #ff1a1a;
    }
</style>

<?php
// Load status badge
$badge_status = '<span class="badge badge-custom badge-waiting-approve">' . lang("status_waiting_for_approve") . '</span>';

if (isset($mat_req_info->status_id) && !empty($mat_req_info->status_id)) {
    if ($mat_req_info->status_id == 3) {
        $badge_status = '<span class="badge badge-custom badge-already-approved">' . lang("status_already_approved") . '</span>';
    }
    if ($mat_req_info->status_id == 4) {
        $badge_status = '<span class="badge badge-custom badge-already-rejected">' . lang("status_already_rejected") . '</span>';
    }
}
?>

<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15 clear">
        <h1>
            <?php echo lang("material_request_no"); ?>
            <?php echo $mat_req_info->doc_no . $badge_status; ?>

        </h1>
        <div class="title-button-group">
            <a href="<?php echo get_uri("materialrequests"); ?>" class="btn btn-default mt0 mb0 back-to-index-btn">
                <i class="fa fa-hand-o-left" aria-hidden="true"></i>
                <?php echo lang("back"); ?>
            </a>

            <?php if ($approve_material_request): ?>
                <?php if ($mat_req_info->status_id == 1 || $mat_req_info->status_id == 2): ?>
                    <a class="btn btn-success mt0 mb0 btn-approved">
                        <i class="fa fa-check-circle" aria-hidden="true"></i>
                        <?php echo lang("status_already_approved"); ?>
                    </a>

                    <a class="btn btn-danger mt0 mb0 btn-rejected">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                        <?php echo lang("status_already_rejected"); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($mat_req_info->status_id != 4): ?>
                <a class="btn btn-warning mt0 mb0 btn-printing">
                    <i class="fa fa-print"></i>
                    <?php echo lang("print"); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="alert-error" class="alert alert-danger mt15 mb0 hide" role="alert"></div>
</div><!--#dcontroller-->

<div id="printd" class="clear page-relative">
    <div class="docheader clear">
        <div class="l">
            <div class="logo">
                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . get_file_from_setting("estimate_logo", true)) != false): ?>
                    <img src="<?php echo get_file_from_setting("estimate_logo", get_setting("only_file_path")); ?>" />
                <?php else: ?>
                    <span class="nologo">&nbsp;</span>
                <?php endif; ?>
            </div>

            <div class="company">
                <p class="company_name">
                    <?php echo get_setting("company_name"); ?>
                </p>
                <p>
                    <?php echo nl2br(get_setting("company_address")); ?>
                </p>
                <?php if (trim(get_setting("company_phone")) != ''): ?>
                    <p>
                        <?php echo lang("phone") . ': ' . get_setting("company_phone"); ?>
                    </p>
                <?php endif; ?>
                <?php if (trim(get_setting("company_website")) != ''): ?>
                    <p>
                        <?php echo lang("website") . ': ' . get_setting("company_website"); ?>
                    </p>
                <?php endif; ?>
                <?php if (trim(get_setting("company_vat_number")) != "company_vat_number"): ?>
                    <p>
                        <?php echo lang("vat_number") . ': ' . get_setting("company_vat_number"); ?>
                    </p>
                <?php endif; ?>
            </div><!-- .company -->
        </div><!--.l-->

        <div class="r">
            <h1 class="document_name custom-color">
                <?php echo lang("mr_text"); ?>
            </h1>
            <div class="about_company">
                <table class="doc-detail">
                    <tr>
                        <td class="custom-color">
                            <?php echo lang("document_number"); ?>
                        </td>
                        <td>
                            <?php echo (isset($mat_req_info->doc_no) && !empty($mat_req_info->doc_no)) ? $mat_req_info->doc_no : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo ucwords(lang("material_request_date")); ?>
                        </td>
                        <td>
                            <?php echo (isset($mat_req_info->mr_date) && !empty($mat_req_info->mr_date)) ? convertDate($mat_req_info->mr_date, true) : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo ucwords(lang("material_request_person")); ?>
                        </td>
                        <td>
                            <?php
                            echo (isset($mat_requester_info->first_name) && !empty($mat_requester_info->first_name)) ? $mat_requester_info->first_name : "-";
                            echo " ";
                            echo (isset($mat_requester_info->last_name) && !empty($mat_requester_info->last_name)) ? $mat_requester_info->last_name : "-";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo ucwords(lang("positioning")); ?>
                        </td>
                        <td>
                            <?php echo (isset($mat_requester_info->job_title) && !empty($mat_requester_info->job_title)) ? $mat_requester_info->job_title : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo ucwords(lang("project_refer")); ?>
                        </td>
                        <td>
                            <?php
                            $project_refer = "-";
                            if (!empty($mat_req_info->project_name) && !empty($mat_req_info->project_id)) {
                                $project_refer = anchor(
                                    get_uri("projects/view/" . $mat_req_info->project_id),
                                    $mat_req_info->project_name,
                                    ["target" => "_blank"]
                                );
                            }
                            echo $project_refer;
                            ?>
                        </td>
                    </tr>

                    <?php if (isset($mat_req_info->sale_order_id) && !empty($mat_req_info->sale_order_id)): ?>
                        <?php
                        $reference_number = "-";
                        if ($mat_req_info->sale_order_id != 0) {
                            $reference_number = anchor(
                                get_uri("sales_orders/view/" . $mat_req_info->sale_order_id),
                                $mat_req_info->sale_order_no,
                                ["target" => "_blank"]
                            );
                            ?>
                            <tr>
                                <td class="custom-color">
                                    <?php echo lang("sale_order_refer"); ?>
                                </td>
                                <td>
                                    <?php echo $reference_number; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div><!--.docheader-->

    <div class="docitem mt2r">
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
                    <td></td>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <td colspan="7">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <?php if ($mat_req_info->status_id == "1"): ?>
                            <p>
                                <?php echo modal_anchor(get_uri("materialrequests/item_add"), "<i class='fa fa-plus-circle'></i> " . lang("add_item"), array("id" => "add_item_button", "class" => "btn btn-default", "title" => lang("add_item"), "data-post-doc_id" => $mat_req_info->id)); ?>
                            </p>
                        <?php endif; ?>
                        <p><input type="text" id="total_in_text" readonly></p>
                    </td>
                    <td colspan="4" class="summary"></td>
                </tr>
            </tfoot>
        </table>
        <?php if (trim($mat_req_info->note) != "" && !empty($mat_req_info->note)): ?>
            <div class="remark clear">
                <p class="custom-color">
                    <?php echo lang("remark"); ?>
                </p>
                <p>
                    <?php echo nl2br($mat_req_info->note); ?>
                </p>
            </div>
        <?php endif; ?>
    </div><!--.docitem-->

    <div class="docsignature clear">
        <div class="customer">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php $requester_sign = null; ?>
                            <?php if (!empty($mat_req_info->requester_id) && $mat_req_info->status_id != 4): ?>
                                <?php $requester_sign = $this->Users_m->getSignature($mat_req_info->requester_id); ?>
                                <?php if (!empty($requester_sign) && $requester_sign != null): ?>
                                    <img src="<?php echo '/' . $requester_sign; ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
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
                        <?php if (!empty($mat_req_info->mr_date) && $mat_req_info->status_id != 4): ?>
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
        </div><!--.customer -->

        <div class="company">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php $signature = null; ?>
                            <?php if (!empty($mat_req_info->approved_by) && $mat_req_info->status_id == 3): ?>
                                <?php $signature = $this->Users_m->getSignature($mat_req_info->approved_by); ?>
                                <?php if (!empty($signature) && $signature != null): ?>
                                    <img src="<?php echo '/' . $signature; ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
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
                    <span class="l3">
                        <?php echo lang("approver"); ?>
                    </span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if (!empty($mat_req_info->approved_date) && $mat_req_info->status_id == 3): ?>
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
        </div><!--.company-->
    </div><!--.docsignature-->

    <?php if ($mat_req_info->status_id == 4): ?>
        <div class="page-absolute rejected-logo">
            <?php echo strtoupper(lang("status_already_rejected")); ?>
        </div>
    <?php endif; ?>
</div><!--#printd-->

<script type="text/javascript">
    window.addEventListener('keydown', function (event) {
        if (event.keyCode === 80 && (event.ctrlKey || event.metaKey) && !event.altKey && (!event.shiftKey || window.chrome || window.opera)) {
            event.preventDefault();
            if (event.stopImmediatePropagation) event.stopImmediatePropagation();
            else event.stopPropagation();
            return;
        }
    }, true);

    $(document).ready(function () {
        loadItems();

        $(".btn-approved").on("click", async function (e) {
            e.preventDefault();
            await approve();
        });

        $(".btn-rejected").on("click", async function (e) {
            e.preventDefault();
            await reject();
        });

        $(".btn-printing").on("click", async function (e) {
            e.preventDefault();
            await printing();
        });
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
                        <p class="desc1">${item.names}</p>
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
            $(".edititem .delete").click(function () {
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

    async function approve() {
        let url = '<?php echo get_uri("materialrequests/approve/" . $mat_req_info->id); ?>';

        await axios.get(url).then((result) => {
            let { status, message } = result.data;
            if (status) {
                window.location.reload();
            } else {
                $("#alert-error").text(message);
                $("#alert-error").removeClass('hide');

                setTimeout(() => {
                    $("#alert-error").addClass('hide');
                }, 5000);
            }
        }).catch((error) => {
            console.log(error);
        });
    }

    async function reject() {
        let url = '<?php echo get_uri("materialrequests/disapprove/" . $mat_req_info->id); ?>';

        await axios.get(url).then((result) => {
            let { status, message } = result.data;
            if (status) {
                window.location.reload();
            } else {
                $("#alert-error").text(message);
                $("#alert-error").removeClass('hide');

                setTimeout(() => {
                    $("#alert-error").addClass('hide');
                }, 5000);
            }
        }).catch((error) => {
            console.log(error);
        });
    }

    async function printing() {
        let url = '<?php echo get_uri("materialrequests/printing/" . $mat_req_info->id); ?>';
        // console.log(url);
        
        await window.open(url, '_blank');
    }
</script>