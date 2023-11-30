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
        border: 1px 1px;
        border-color: rgb(206, 206, 206);
    }

    .table-category tbody th {
        text-align: center;
        padding: 5px 5px;
        border: 1px 1px;
        border-color: #cecece;
        background-color: rgb(206, 206, 206, .2);
    }

    .table-category tbody td {
        border: 1px 1px;
        padding: 2px 10px;
        border-color: #cecece;
    }

    .table-category tbody td>p:first-child {
        font-weight: bolder;
    }

    .table-category tfoot th {
        border: 1px 1px;
        padding: 5px 10px;
        border-color: #cecece;
        background-color: rgb(206, 206, 206, .2);
    }

    .w-stockname {
        width: 230px;
    }

    .w-quantity {
        width: 180px;
    }

    .w-unit {
        width: 90px;
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

if (isset($mr_header->status_id) && !empty($mr_header->status_id)) {
    if ($mr_header->status_id == 3) {
        $badge_status = '<span class="badge badge-custom badge-already-approved">' . lang("status_already_approved") . '</span>';
    }
    if ($mr_header->status_id == 4) {
        $badge_status = '<span class="badge badge-custom badge-already-rejected">' . lang("status_already_rejected") . '</span>';
    }
}
?>

<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15 clear">
        <h1>
            <?php echo lang("material_request_no"); ?>
            <?php echo $mr_header->doc_no . $badge_status; ?>
        </h1>
        <div class="title-button-group">
            <a href="<?php echo get_uri("materialrequests"); ?>" class="btn btn-default mt0 mb0 back-to-index-btn">
                <i class="fa fa-hand-o-left" aria-hidden="true"></i>
                <?php echo lang("back"); ?>
            </a>

            <?php if ($approve_material_request): ?>
                <?php if ($mr_header->status_id == 1 || $mr_header->status_id == 2): ?>
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

            <?php if ($mr_header->status_id != 4): ?>
                <a class="btn btn-warning mt0 mb0 btn-printing">
                    <i class="fa fa-print"></i>
                    <?php echo lang("print"); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="alert-error" class="alert alert-danger mt15 mb0 hide" role="alert"></div>
</div>

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
            </div>
        </div>

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
                            <?php echo (isset($mr_header->doc_no) && !empty($mr_header->doc_no)) ? $mr_header->doc_no : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo ucwords(lang("material_request_date")); ?>
                        </td>
                        <td>
                            <?php echo (isset($mr_header->mr_date) && !empty($mr_header->mr_date)) ? convertDate($mr_header->mr_date, true) : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo ucwords(lang("material_request_person")); ?>
                        </td>
                        <td>
                            <?php
                                echo (isset($requester_info->first_name) && !empty($requester_info->first_name)) ? $requester_info->first_name : "";
                                echo " ";
                                echo (isset($requester_info->last_name) && !empty($requester_info->last_name)) ? $requester_info->last_name : "";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo ucwords(lang("positioning")); ?>
                        </td>
                        <td>
                            <?php echo (isset($requester_info->job_title) && !empty($requester_info->job_title)) ? $requester_info->job_title : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo ucwords(lang("project_refer")); ?>
                        </td>
                        <td>
                            <?php
                            $project_refer = "-";
                            if (!empty($mr_header->project_name) && !empty($mr_header->project_id)) {
                                $project_refer = anchor(
                                    get_uri("projects/view/" . $mr_header->project_id),
                                    $mr_header->project_name,
                                    ["target" => "_blank"]
                                );
                            }
                            echo $project_refer;
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="item-list">
        <table style="width: 936px;">
            <tr>
                <td>
                    <?php if (isset($mr_detail["categories"]) && !empty($mr_detail["categories"])): ?>
                        <?php foreach ($mr_detail["categories"] as $category): ?>
                            <table border="1" class="table-category">
                                <thead>
                                    <tr style="height: 38px;">
                                        <th colspan="5">
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
                                                <td class="w-stockname">
                                                    <?php
                                                        if (isset($data->stock_info->group_info->id) && !empty($data->stock_info->group_info->id)) {
                                                            if ($category->item_type == "RM") {
                                                                echo anchor(get_uri("stock/restock_view/" . $data->stock_info->group_info->id), $data->stock_info->group_info->name, ["target" => "_blank"]);
                                                            } else {
                                                                echo anchor(get_uri("sfg/restock_view/" . $data->stock_info->group_info->id), $data->stock_info->group_info->name, ["target" => "_blank"]);
                                                            }
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
                                    <th colspan="4">
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
                                    <th colspan="4">
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

    <div class="docsignature clear">
        <div class="customer">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($mr_header->status_id != 4): ?>
                                <?php if (isset($requester_sign) && !empty($requester_sign)): ?>
                                    <img src="<?php echo "/" . $requester_sign; ?>" alt="requester-sign">
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="l2">
                        <?php if (isset($mr_header->requester_id) && !empty($mr_header->requester_id)): ?>
                            <?php
                                if ($mr_header->status_id != 4) {
                                    echo (isset($requester_info->first_name) && !empty($requester_info->first_name)) ? $requester_info->first_name : "";
                                    echo " ";
                                    echo (isset($requester_info->last_name) && !empty($requester_info->last_name)) ? $requester_info->last_name : "";
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
                        <?php if (!empty($mr_header->mr_date) && $mr_header->status_id != 4): ?>
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

        <div class="company">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($mr_header->status_id == 3): ?>
                                <?php if (isset($approver_sign) && !empty($approver_sign)): ?>
                                    <img src="<?php echo "/" . $approver_sign; ?>" alt="approver-sign">
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="l2">
                        <?php if ($mr_header->status_id == 3): ?>
                            <?php
                                echo (isset($approver_info->first_name) && !empty($approver_info->first_name)) ? $approver_info->first_name : "";
                                echo " ";
                                echo (isset($approver_info->last_name) && !empty($approver_info->last_name)) ? $approver_info->last_name : "";
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
                        <?php if (!empty($mr_header->approved_date) && $mr_header->status_id == 3): ?>
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
    </div>

    <?php if ($mr_header->status_id == 4): ?>
        <div class="page-absolute rejected-logo">
            <?php echo strtoupper(lang("status_already_rejected")); ?>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
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

    async function approve() {
        let url = '<?php echo get_uri("materialrequests/approve/" . $mr_header->id); ?>';

        await axios.get(url).then((result) => {
            // console.log(result);

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
        let url = '<?php echo get_uri("materialrequests/disapprove/" . $mr_header->id); ?>';

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
        let url = '<?php echo get_uri("materialrequests/printing/" . $mr_header->id); ?>';
        // console.log(url);
        
        await window.open(url, '_blank');
    }
</script>