<style type="text/css">
    .modal-dialog {
        width: calc(70% - 5rem);
        transition: width .55s ease;
    }

    #table-order-header {
        width: 100%;
    }

    #table-order-header thead th {
        border: 1px solid #f2f2f2;
        background-color: #f5f5f5;
        padding: .9rem 1rem;
        text-align: center;
    }

    #table-order-header tbody tr {
        border-bottom: 1px solid #f2f2f2;
    }

    #table-order-header tbody tr:hover {
        background-color: #f2f2f2;
    }

    #table-order-header tbody td {
        padding: .6rem 1rem;
    }

    #table-order-header .w50px {
        width: 50px;
    }

    #table-order-detail {
        margin-top: 1.3rem;
        width: 100%;
    }

    #table-order-detail thead th {
        border: 1px solid #f2f2f2;
        background-color: #f5f5f5;
        padding: .8rem 1rem;
        text-align: center;
    }

    #table-order-detail tbody tr:hover {
        background-color: #f2f2f2;
    }

    #table-order-detail tbody td {
        border: 1px solid #f2f2f2;
        font-size: 92%;
        padding: .28rem 1rem;
    }

    #table-order-detail .rm-name span {
        display: block;
    }

    #table-order-detail tfoot td {
        border: 1px solid #f2f2f2;
        padding: 1rem 1rem;
    }

    .font-bold {
        font-weight: bolder;
    }

    .color-success {
        color: #28a745;
    }

    .color-danger {
        color: #dc3545;
    }

    .color-warning {
        color: #ffc107;
    }

    .stock-notice {
        padding-left: 5px;
        font-size: 120%;
        color: #28a745;
    }

    .stock-name-width {
        width: 208px;
    }

    .quantity-width {
        width: 148px;
    }

    .cost-width {
        width: 148px;
    }

    .mr-width {
        width: 138px;
    }
</style>

<div class="modal-body clearfix">
    <table id="table-order-header">
        <thead>
            <tr>
                <th>
                    <?php echo lang("item"); ?>
                </th>
                <th>
                    <?php echo lang("item_mixing_name"); ?>
                </th>
                <th>
                    <?php echo lang("quantity"); ?>
                </th>
                <th class="w50px">
                    <?php echo lang("stock_material_unit"); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="custom-color">
                <td>
                    <?php echo $production_bom_header->item_info->title; ?>
                </td>
                <td>
                    <?php echo $production_bom_header->mixing_group->name; ?>
                </td>
                <td class="text-center">
                    <?php echo number_format($production_bom_header->quantity, 2); ?>
                </td>
                <td class="text-center">
                    <?php echo $production_bom_header->item_info->unit_type; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <?php $total_rm_cost = 0; $total_sfg_cost = 0; ?>
    <table id="table-order-detail">
        <thead>
            <tr>
                <th class="material-width">
                    <?php echo lang("stock_material"); ?>
                </th>
                <th class="stock-name-width">
                    <?php echo lang("stock_restock_name"); ?>
                </th>
                <th class="quantity-width">
                    <?php echo lang("quantity"); ?>
                </th>
                <?php if ($auth_read_cost): ?>
                    <th class="cost-width">
                        <?php echo lang("production_order_rm_cost"); ?>
                    </th>
                <?php endif; ?>
                <th class="mr-width">
                    <?php echo lang("material_request_no"); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($production_bom_detail->rm) && !empty($production_bom_detail->rm)): ?>
                <?php foreach ($production_bom_detail->rm as $detail): ?>
                    <tr>
                        <td class="rm-name">
                            <span class="font-bold">
                                <?php echo mb_strimwidth($detail->material_info->name . ' - ' . $detail->material_info->production_name, 0, 50, "..."); ?>
                            </span>
                            <span>
                                <?php echo mb_strimwidth($detail->material_info->description, 0, 50, '...'); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            if (!empty($detail->stock_id)):
                                if (isset($detail->stock_info) && !empty($detail->stock_info)):
                                    echo $detail->stock_info->group_info->name;
                                else:
                                    echo "-";
                                endif;
                            else:
                                echo "-";
                            endif;
                            ?>
                        </td>
                        <td class="text-right font-bold">
                            <?php if (isset($detail->ratio) && !empty($detail->ratio)): ?>
                                <span class="<?php echo $detail->ratio > 0 ? "color-success" : "color-danger"; ?>">
                                    <?php echo number_format($detail->ratio, 6) . " " . $detail->material_info->unit; ?> 
                                </span>
                                <span>
                                    <?php
                                        if (isset($detail->actual_total_remain) && isset($detail->required_qty)): 
                                            if (!empty($detail->actual_total_remain) && !empty($detail->required_qty)): 
                                                if ($detail->required_qty <= $detail->actual_total_remain): 
                                    ?>
                                    <i class="fa fa-check-square stock-notice"></i>
                                    <?php
                                                endif;
                                            endif;
                                        endif;
                                    ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php if ($auth_read_cost): ?>
                            <td class="text-right">
                                <?php
                                if (!empty($detail->stock_id)):
                                    if (isset($detail->stock_info) && !empty($detail->stock_info)):
                                        $rm_cost = 0;
                                        if ($detail->stock_info->price > 0):
                                            $rm_cost = ($detail->stock_info->price / $detail->stock_info->stock) * $detail->ratio;
                                        endif;
                                        echo number_format($rm_cost, 3) . " " . lang("THB");
                                        $total_rm_cost += $rm_cost;
                                    else:
                                        echo "-";
                                    endif;
                                else:
                                    echo "-";
                                endif;
                                ?>
                            </td>
                        <?php endif; ?>
                        <td class="text-center">
                            <?php
                            if (!empty($detail->mr_id)):
                                if (isset($detail->mr_info) && !empty($detail->mr_info)):
                                ?>
                                    <a href="<?php echo get_uri("materialrequests/view/" . $detail->mr_id); ?>" target="_blank"><?php echo $detail->mr_info->doc_no; ?></a>
                                <?php
                                else:
                                    echo "-";
                                endif;
                            else:
                                echo "-";
                            endif;
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if ($auth_read_cost): ?>
            <tfoot>
                <tr>
                    <td class="text-center font-bold" colspan="3"><?php echo lang("production_order_rm_cost_total"); ?></td>
                    <td class="text-right font-bold"><?php echo number_format($total_rm_cost, 3) . " " . lang("THB"); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <?php if ($production_bom_header->item_type == "FG"): ?>
        <table id="table-order-detail">
            <thead>
                <tr>
                    <th class="material-width">
                        <?php echo lang("sfg"); ?>
                    </th>
                    <th class="stock-name-width">
                        <?php echo lang("stock_restock_name"); ?>
                    </th>
                    <th class="quantity-width">
                        <?php echo lang("quantity"); ?>
                    </th>
                    <?php if ($auth_read_cost): ?>
                        <th class="cost-width">
                            <?php echo lang("production_order_sfg_cost"); ?>
                        </th>
                    <?php endif; ?>
                    <th class="mr-width">
                        <?php echo lang("material_request_no"); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($production_bom_detail->sfg) && !empty($production_bom_detail->sfg)): ?>
                    <?php foreach ($production_bom_detail->sfg as $detail): ?>
                        <tr>
                            <td class="rm-name">
                                <span class="font-bold">
                                    <?php echo mb_strimwidth($detail->material_info->item_code . ' - ' . $detail->material_info->title, 0, 50, "..."); ?>
                                </span>
                                <span>
                                    <?php echo mb_strimwidth($detail->material_info->description, 0, 50, '...'); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                if (!empty($detail->stock_id)):
                                    if (isset($detail->stock_info) && !empty($detail->stock_info)):
                                        echo $detail->stock_info->group_info->name;
                                    else:
                                        echo "-";
                                    endif;
                                else:
                                    echo "-";
                                endif;
                                ?>
                            </td>
                            <td class="text-right font-bold">
                                <?php if (isset($detail->ratio) && !empty($detail->ratio)): ?>
                                    <span class="<?php echo $detail->ratio >= 0 ? "color-success" : "color-danger"; ?>">
                                        <?php echo number_format($detail->ratio, 6) . " " . $detail->material_info->unit_type; ?> 
                                    </span>
                                    <span>
                                        <?php
                                            if (isset($detail->actual_total_remain) && isset($detail->required_qty)): 
                                                if (!empty($detail->actual_total_remain) && !empty($detail->required_qty)): 
                                                    if ($detail->required_qty <= $detail->actual_total_remain): 
                                        ?>
                                        <i class="fa fa-check-square stock-notice"></i>
                                        <?php
                                                    endif;
                                                endif;
                                            endif;
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <?php if ($auth_read_cost): ?>
                                <td class="text-right">
                                    <?php
                                    if (!empty($detail->stock_id)):
                                        if (isset($detail->stock_info) && !empty($detail->stock_info)):
                                            $sfg_cost = 0;
                                            if ($detail->stock_info->price > 0):
                                                $sfg_cost = ($detail->stock_info->price / $detail->stock_info->stock) * $detail->ratio;
                                            endif;
                                            echo number_format($sfg_cost, 3) . " " . lang("THB");
                                            $total_sfg_cost += $sfg_cost;
                                        else:
                                            echo "-";
                                        endif;
                                    else:
                                        echo "-";
                                    endif;
                                    ?>
                                </td>
                            <?php endif; ?>
                            <td class="text-center">
                                <?php
                                if (!empty($detail->mr_id)):
                                    if (isset($detail->mr_info) && !empty($detail->mr_info)):
                                    ?>
                                        <a href="<?php echo get_uri("materialrequests/view/" . $detail->mr_id); ?>" target="_blank"><?php echo $detail->mr_info->doc_no; ?></a>
                                    <?php
                                    else:
                                        echo "-";
                                    endif;
                                else:
                                    echo "-";
                                endif;
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if ($auth_read_cost): ?>
                <tfoot>
                    <tr>
                        <td class="text-center font-bold" colspan="3"><?php echo lang("production_order_sfg_cost_total"); ?></td>
                        <td class="text-right font-bold"><?php echo number_format($total_sfg_cost, 3) . " " . lang("THB"); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span> 
        <?php echo lang("close"); ?>
    </button>

    <?php if ($can_recalc): ?>
        <button type="button" class="btn btn-warning" id="btn-recalc" data-dismiss="modal">
            <span class="fa fa-refresh"></span> 
            <?php echo " " . lang("production_order_bom_recalc"); ?>
        </button>
    <?php endif; ?>

    <?php if ($can_create_mr): ?>
        <button type="button" class="btn btn-primary" id="btn-mr-creator" data-dismiss="modal">
            <span class="fa fa-book"></span> 
            <?php echo " " . lang("create_matreq"); ?>
        </button>
    <?php endif; ?>

    <?php if ($auth_read_cost): ?>
        <button type="button" class="btn btn-default" id="btn-pdf">
            <span class="fa fa-download"></span> 
            <?php echo " " . lang("download_pdf"); ?>
        </button>
    <?php endif; ?>
</div>

<script type="text/javascript">
const bomRecalcUrl = '<?php echo get_uri("projects/production_order_bom_recalc"); ?>';
const mrCreationUrl = '<?php echo get_uri("projects/production_order_mr_creation"); ?>';

const projectId = '<?php echo $project_info["id"]; ?>';
const projectName = '<?php echo $project_info["title"]; ?>';
const projectBomId = '<?php echo $production_bom_header->id; ?>';

async function bomRecalc () {
    let url = bomRecalcUrl;
    let req = {
        projectId: projectId,
        projectName: projectName,
        projectBomId: projectBomId
    };

    await axios.post(url, req).then(res => {
        // console.log(res);
        window.parent.loadProductionOrderList();

        setTimeout(async () => {
            await document.querySelector(`[data-post-reclick_id="${req.projectBomId}"]`).click();
        }, 300);
    }).catch(err => {
        console.log(err);
    });
}

async function mrCreation () {
    let url = mrCreationUrl;
    let req = {
        projectId: projectId,
        projectName: projectName,
        projectBomId: projectBomId
    };

    await axios.post(url, req).then(res => {
        const { success, target } = res.data;

        if (success) {
            window.open(target, "_blank");
        }
        window.parent.loadProductionOrderList();

        setTimeout(async () => {
            await document.querySelector(`[data-post-reclick_id="${req.projectBomId}"]`).click();
        }, 300);
    }).catch(err => {
        console.log(err);
    });
}

$(document).ready(function () {
    $("#btn-recalc").on("click", async function (e) {
        e.preventDefault();
        await bomRecalc();
    });

    $("#btn-mr-creator").on("click", async function (e) {
        e.preventDefault();
        await mrCreation();
    });

    $("#btn-pdf").on("click", async function (e) {
        e.preventDefault();

        let url = '<?php echo get_uri("pdf_export/production_pdf/" . $project_info["id"] . "/" . $production_bom_header->id); ?>';
        await window.open(url, '_blank');
    });
});
</script>
