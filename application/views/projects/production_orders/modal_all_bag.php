<style type="text/css">
    .modal-dialog {
        width: calc(70% - 15rem);
        transition: width .55s ease;
    }

    .modal-body table {
        width: 100%;
    }

    .category-line {
        border: 1px solid #f2f2f2;
        background-color: rgba(0, 83, 156, .9);
        color: #f2f2f2;
        height: 36px;
    }

    .category-line-sfg {
        border: 1px solid #f2f2f2;
        background-color: rgba(255, 165, 0, .9);
        color: #f2f2f2;
        height: 36px;
    }

    .material-line table {
        width: 97%;
        margin: 10px auto;
    }

    .material-line-sfg table {
        width: 97%;
        margin: 10px auto;
    }

    .material-line th {
        border: 1px solid #f2f2f2;
        height: 30px;
        background-color: rgba(0, 83, 156, .25);
        color: #030303;
    }

    .material-line-sfg th {
        border: 1px solid #f2f2f2;
        height: 30px;
        background-color: rgba(255, 165, 0, .25);
        color: #030303;
    }

    .material-line td,
    .material-line-sfg td {
        border: 1px solid #f2f2f2;
    }

    .rm-name span {
        padding: 0 1rem;
        display: block;
    }

    .rm-quantity {
        width: 160px;
        padding: 0 1rem;
    }

    .font-bold {
        font-weight: bolder;
    }

    .rm-unit {
        width: 80px;
    }

    .rm-cost {
        width: 140px;
        padding: 0 1rem;
    }

    .rm-mr_no {
        width: 112px;
    }

    .material-line-items td {
        font-size: 90%;
    }

    .rm-description {
        max-width: 320px;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .sub-type-rm {
        border: 1px solid rgba(0, 83, 156, .9);
        background-color: rgba(0, 83, 156, 1);
        color: #f2f2f2;
        margin-bottom: 10px;
    }

    .sub-type-sfg {
        border: 1px solid rgba(255, 165, 0, .9);
        background-color: rgba(255, 165, 0, 1);
        color: #f2f2f2;
        margin-bottom: 10px;
    }

    .sub-type-all {
        border: 1px solid rgba(92, 184, 92, .9);
        background-color: rgba(92, 184, 92, 1);
        color: #f2f2f2;
    }

    .sub-type-rm th, .sub-type-sfg th, .sub-type-all th {
        font-size: 110%;
    }

    .sub-type-rm th:last-child, .sub-type-sfg th:last-child, .sub-type-all th:last-child {
        width: 122px;
    }
</style>

<div class="modal-body clearfix">
    <table>
        <?php $project_total = 0.000000; $project_total_cost = 0.000000; ?>
        <?php $grand_total_group = 0.000000; $grand_total_group_cost = 0.000000; ?>
        <?php if (isset($production_items["rm_cate"]) && !empty($production_items["rm_cate"])): ?>
            <?php foreach ($production_items["rm_cate"] as $category): ?>
                <tr class="category-line">
                    <th class="text-center" width="15%"><?php echo lang("category"); ?></th>
                    <th><?php echo $category["item_type"] . " : " . $category["title"]; ?></th>
                </tr>
                <tr class="material-line">
                    <td colspan="2">
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        <?php echo lang("stock_material"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?php echo lang("quantity"); ?>
                                    </th>
                                    <?php if ($auth_read_cost): ?>
                                        <th class="text-center">
                                            <?php echo lang("production_order_rm_cost"); ?>
                                        </th>
                                    <?php endif; ?>
                                    <th class="text-center">
                                        <?php echo lang("material_request_no"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <?php $total_group = 0.000000; $total_group_cost = 0.000000; ?>
                            <tbody>
                                <?php foreach ($production_items["rm_list"] as $rm): ?>
                                    <?php if ($category["id"] == $rm->category_in_bom): ?>
                                        <tr class="material-line-items">
                                            <td class="rm-name">
                                                <span class="font-bold rm-description">
                                                    <?php
                                                        if (!empty($rm->material_info->production_name)) {
                                                            echo $rm->material_info->name . ' - ' . mb_strimwidth($rm->material_info->production_name, 0, 50, '...');
                                                        } else {
                                                            echo $rm->material_info->name;
                                                        }
                                                    ?>
                                                </span>
                                                <?php if (!empty($rm->material_info->description)): ?>
                                                    <span class="rm-description">
                                                        <?php echo $rm->material_info->description; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right rm-quantity">
                                                <?php echo $rm->quantity . ' ' . $rm->material_info->unit; $total_group += $rm->quantity; ?>
                                            </td>
                                            <?php if ($auth_read_cost): ?>
                                                <?php
                                                    $rm_cost = 0;
                                                    if (isset($rm->stock_info->price) && !empty($rm->stock_info->price)) {
                                                        if ($rm->stock_info->price > 0) {
                                                            $price_unit = $rm->stock_info->price / $rm->stock_info->stock;
                                                            $rm_cost = $price_unit * $rm->quantity;
                                                        }
                                                    }
                                                    $total_group_cost += $rm_cost;
                                                ?>
                                                <td class="text-right rm-cost">
                                                    <?php echo number_format($rm_cost, 3) . ' ' . lang("THB"); ?>
                                                </td>
                                            <?php endif; ?>
                                            <td class="text-center rm-mr_no">
                                                <?php echo (isset($rm->mr_info->doc_no) && !empty($rm->mr_info->doc_no)) ? $rm->mr_info->doc_no : '-'; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-center"><?php echo lang("total"); ?></th>
                                    <th class="text-right rm-quantity"><?php echo number_format($total_group, 6) . ' ' . $rm->material_info->unit; ?></th>
                                    <th class="text-right rm-quantity"><?php echo number_format($total_group_cost, 3) . ' ' . lang("THB"); ?></th>
                                    <th class="text-center"><?php echo ''; ?></th>
                                    <?php $grand_total_group += $total_group; $grand_total_group_cost += $total_group_cost; ?>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2">
                    <table class="sub-type-rm">
                        <tr>
                            <th class="text-center"><?php echo lang("total") . lang("stock_materials"); ?></th>
                            <th class="text-right rm-quantity">
                                <?php echo number_format($grand_total_group, 6); ?><br>
                                <?php echo lang("stock_material_unit"); ?>
                                <?php $project_total += $grand_total_group; ?>
                            </th>
                            <th class="text-right rm-quantity">
                                <?php echo number_format($grand_total_group_cost, 3); ?><br>
                                <?php echo lang("THB"); ?>
                                <?php $project_total_cost += $grand_total_group_cost; ?>
                            </th>
                            <th class="text-center"><?php echo ''; ?></th>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php endif; ?>

        <?php $grand_total_group = 0.000000; $grand_total_group_cost = 0.000000; ?>
        <?php if (isset($production_items["sfg_cate"]) && !empty($production_items["sfg_cate"])): ?>
            <?php foreach ($production_items["sfg_cate"] as $category): ?>
                <tr class="category-line-sfg">
                    <th class="text-center" width="15%"><?php echo lang("category"); ?></th>
                    <th><?php echo $category["item_type"] . " : " . $category["title"]; ?></th>
                </tr>
                <tr class="material-line-sfg">
                    <td colspan="2">
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        <?php echo lang("sfg"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?php echo lang("quantity"); ?>
                                    </th>
                                    <?php if ($auth_read_cost): ?>
                                        <th class="text-center">
                                            <?php echo lang("production_order_rm_cost"); ?>
                                        </th>
                                    <?php endif; ?>
                                    <th class="text-center">
                                        <?php echo lang("material_request_no"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <?php $total_group = 0.000000; $total_group_cost = 0.000000; ?>
                            <tbody>
                                <?php foreach ($production_items["sfg_list"] as $sfg): ?>
                                    <?php if ($category["id"] == $sfg->category_in_bom): ?>
                                        <tr class="material-line-items">
                                            <td class="rm-name">
                                                <span class="font-bold">
                                                    <?php
                                                        if (!empty($sfg->item_info->item_code)) {
                                                            echo $sfg->item_info->item_code . ' - ' . mb_strimwidth($sfg->item_info->title, 0, 50, '...');
                                                        } else {
                                                            echo $sfg->item_info->title;
                                                        }
                                                    ?>
                                                </span>
                                                <?php if (!empty($sfg->item_info->description)): ?>
                                                    <span class="rm-description">
                                                        <?php echo $sfg->item_info->description; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right rm-quantity">
                                                <?php echo $sfg->quantity . ' ' . $sfg->item_info->unit_type; $total_group += $sfg->quantity; ?>
                                            </td>
                                            <?php if ($auth_read_cost): ?>
                                                <?php
                                                    $sfg_cost = 0;
                                                    if (isset($sfg->stock_info->price) && !empty($sfg->stock_info->price)) {
                                                        if ($sfg->stock_info->price > 0) {
                                                            $price_unit = $sfg->stock_info->price / $sfg->stock_info->stock;
                                                            $sfg_cost = $price_unit * $sfg->quantity;
                                                        }
                                                    }
                                                    $total_group_cost += $sfg_cost;
                                                ?>
                                                <td class="text-right rm-cost">
                                                    <?php echo number_format($sfg_cost, 3) . ' ' . lang("THB"); ?>
                                                </td>
                                            <?php endif; ?>
                                            <td class="text-center rm-mr_no">
                                                <?php echo (isset($sfg->mr_info->doc_no) && !empty($sfg->mr_info->doc_no)) ? $sfg->mr_info->doc_no : '-'; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-center"><?php echo lang("total"); ?></th>
                                    <th class="text-right rm-quantity"><?php echo number_format($total_group, 6) . ' ' . $sfg->item_info->unit_type; ?></th>
                                    <th class="text-right rm-quantity"><?php echo number_format($total_group_cost, 3) . ' ' . lang("THB"); ?></th>
                                    <th class="text-center"><?php echo ''; ?></th>
                                    <?php $grand_total_group += $total_group; $grand_total_group_cost += $total_group_cost; ?>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2">
                    <table class="sub-type-sfg">
                        <tr>
                            <th class="text-center"><?php echo lang("total") . lang("sfg"); ?></th>
                            <th class="text-right rm-quantity">
                                <?php echo number_format($grand_total_group, 6); ?><br>
                                <?php echo lang("stock_material_unit"); ?>
                                <?php $project_total += $grand_total_group; ?>
                            </th>
                            <th class="text-right rm-quantity">
                                <?php echo number_format($grand_total_group_cost, 3); ?><br>
                                <?php echo lang("THB"); ?>
                                <?php $project_total_cost += $grand_total_group_cost; ?>
                            </th>
                            <th class="text-center"><?php echo ''; ?></th>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php endif; ?>
        <?php if (($project_total > 0) || ($project_total_cost > 0)): ?>
            <tr>
                <td colspan="2">
                    <table class="sub-type-all">
                        <tr>
                            <th class="text-center"><?php echo lang("project_total"); ?></th>
                            <th class="text-right rm-quantity">
                                <?php echo number_format($project_total, 6); ?><br>
                                <?php echo lang("stock_material_unit"); ?>
                            </th>
                            <th class="text-right rm-quantity">
                                <?php echo number_format($project_total_cost, 3); ?><br>
                                <?php echo lang("THB"); ?>
                            </th>
                            <th class="text-center"><?php echo ''; ?></th>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span> 
        <?php echo lang("close"); ?>
    </button>
    <button type="button" class="btn btn-default" id="btn-pdf-download">
        <span class="fa fa-download"></span> 
        <?php echo " " . lang("download_pdf"); ?>
    </button>
</div>

<script type="text/javascript">
$(document).ready(function () {
    $("#btn-pdf-download").on("click", function (e) {
        e.preventDefault();

        let url = '<?php echo get_uri("pdf_export/production_bag_pdf/" . $project_id); ?>';
        window.open(url, '_blank');
    });
});
</script>