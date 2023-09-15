<style type="text/css">
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

    <table id="table-order-detail">
        <thead>
            <tr>
                <th>
                    <?php echo "วัตถุดิบ"; ?>
                </th>
                <th>
                    <?php echo "ชื่อการนำเข้า"; ?>
                </th>
                <th>
                    <?php echo "จำนวน"; ?>
                </th>
                <th>
                    <?php echo "มูลค่าต้นทุน"; ?>
                </th>
                <th>
                    <?php echo "เลขที่ใบเบิก"; ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($production_bom_detail) && !empty($production_bom_detail)): ?>
                <?php $total_rm_cost = 0; foreach ($production_bom_detail as $detail): ?>
                    <tr>
                        <td class="rm-name">
                            <span class="font-bold">
                                <?php echo $detail->material_info->name; ?>
                            </span>
                            <span>
                                <?php echo mb_strimwidth($detail->material_info->production_name, 0, 50, '...'); ?>
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
                                <span class="<?php echo $detail->ratio > 0 ? "color-success" : "color-danger"; ?>"><?php echo number_format($detail->ratio, 3) . " " . $detail->material_info->unit; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php
                            if (!empty($detail->stock_id)):
                                if (isset($detail->stock_info) && !empty($detail->stock_info)):
                                    $rm_cost = 0;
                                    if ($detail->stock_info->price > 0):
                                        $rm_cost = ($detail->stock_info->price / $detail->stock_info->stock) * $detail->ratio;
                                    endif;
                                    echo number_format($rm_cost, 2) . " " . lang("THB");
                                    $total_rm_cost += $rm_cost;
                                else:
                                    echo "-";
                                endif;
                            else:
                                echo "-";
                            endif;
                            ?>
                        </td>
                        <td>
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
        <tfoot>
            <tr>
                <td class="text-center font-bold" colspan="3"><?php echo "รวมต้นทุนวัตถุดิบ"; ?></td>
                <td class="text-right font-bold"><?php echo number_format($total_rm_cost, 2) . " " . lang("THB"); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span> 
        <?php echo lang("close"); ?>
    </button>

    <button type="button" class="btn btn-primary" id="btn-mr-creator" data-dismiss="modal">
        <span class="fa fa-book"></span> 
        <?php echo " " . lang("create_matreq"); ?>
    </button>
</div>

<script type="text/javascript">
async function mrCreation () {
    let url = '<?php echo get_uri("projects/production_order_mr_creation"); ?>';
    let req = {
        projectId: '<?php echo $project_info["id"]; ?>',
        projectName: '<?php echo $project_info["title"]; ?>',
        projectBomId: '<?php echo $production_bom_header->id; ?>'
    };

    await axios.post(url, req).then(res => {
        const { success, target } = res.data;

        if (success) {
            window.open(target, "_blank");
            window.parent.loadProductionOrderList();

            setTimeout(async () => {
                await document.querySelector(`[data-post-reclick_id="${req.projectBomId}"]`).click();
            }, 300);
        } else {
            window.parent.loadProductionOrderList();

            setTimeout(async () => {
                await document.querySelector(`[data-post-reclick_id="${req.projectBomId}"]`).click();
            }, 300);
        }
    }).catch(err => {
        console.log(err);
    });
}

$(document).ready(function () {
    $("#btn-mr-creator").on("click", async function (e) {
        e.preventDefault();
        await mrCreation();
    });
});
</script>
