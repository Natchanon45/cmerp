<style type="text/css">
    .modal-dialog {
        width: calc(70% - 5rem);
        transition: width .55s ease;
    }

    .main-table-header-sfg {
        border: 1px solid rgba(242, 242, 242, 1);
        background-color: rgba(255, 165, 0, .9);
        color: rgba(242, 242, 242, 1);
        padding: 12px 15px;
    }

    .main-table-header {
        border: 1px solid rgba(242, 242, 242, 1);
        background-color: rgba(0, 83, 156, .9);
        color: rgba(242, 242, 242, 1);
        padding: 12px 15px;
    }

    .main-table-detail {
        border: 1px solid rgba(242, 242, 242, 1);
        width: 100%;
        padding: 10px;
    }

    .sub-table {
        border: 1px solid rgba(242, 242, 242, 1);
        width: 100%;
    }

    .sub-table th {
        border: 1px solid rgba(242, 242, 242, 1);
        padding: 5px 5px;
    }

    .sub-table td {
        border: 1px solid rgba(242, 242, 242, 1);
        padding: 0 10px;
    }

    .custom-material-name span {
        display: block;
        font-size: 90%;
    }

    .custom-material-name span:first-child {
        font-weight: bolder;
    }

    .category-background {
        background-color: rgba(242, 242, 242, 1);
    }

    .custom-stock-name {
        width: 280px;
    }

    .custom-ratio {
        width: 200px;
        color: #28a745;
        font-weight: bolder;
    }

    .custom-unit {
        width: 100px;
    }

    .no-data {
        border: 1px solid rgba(242, 242, 242, 1);
        padding: 10px 10px;
    }

    .footer-question {
        padding-right: 10px;
    }
</style>

<div class="modal-body clearfix">
    <input type="hidden" id="project_id" value="<?php echo $project_id; ?>">
    <input type="hidden" id="project_name" value="<?php echo $project_name; ?>">
    <input type="hidden" id="production_ids" value="<?php echo $production_ids; ?>">
    <input type="hidden" id="post_url" value="<?php echo get_uri("projects/production_order_mr_creation_all_post"); ?>">
    <div class="p3">
        <p style="font-size: 110%;"><b><?php echo lang("mr_preview"); ?></b></p>
    </div>

    <div>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th class="main-table-header"><?php echo lang("mr_creation_rm_will_created"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (sizeof($rm_list)): ?>
                    <?php if (sizeof($rm_categories)): ?>
                        <?php foreach ($rm_categories as $category): ?>
                            <tr>
                                <td class="main-table-detail">
                                    <table class="sub-table">
                                        <tr>
                                            <th colspan="4" class="category-background"><?php echo lang("category") . ' ' . $category->item_type . ': ' . $category->title; ?></th>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><?php echo lang("details"); ?></th>
                                            <th class="text-center"><?php echo lang("stock_restock_name"); ?></th>
                                            <th class="text-center"><?php echo lang("quantity"); ?></th>
                                            <th class="text-center"><?php echo lang("stock_material_unit"); ?></th>
                                        </tr>
                                        <?php $total = 0; ?>
                                        <?php foreach ($rm_list as $rm): ?>
                                            <?php if ($category->id == $rm->category_in_bom): $total += $rm->ratio; ?>
                                                <tr>
                                                    <td class="custom-material-name">
                                                        <?php
                                                            $rm_name = $rm->material_info->name;
                                                            if (isset($rm->material_info->production_name) && !empty($rm->material_info->production_name)) {
                                                                $rm_name = $rm->material_info->name . ' - ' . $rm->material_info->production_name;
                                                            }
                                                        ?>
                                                        <span><?php echo mb_strimwidth($rm_name, 0, 40, '...', 'UTF-8'); ?></span>
                                                        <span><?php echo mb_strimwidth($rm->material_info->description, 0, 40, '...', 'UTF-8'); ?></span>
                                                    </td>
                                                    <td class="custom-stock-name"><?php echo $rm->stock_name; ?></td>
                                                    <td class="text-right"><?php echo $rm->ratio; ?></td>
                                                    <td class="text-center custom-unit"><?php echo $rm->material_info->unit; ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <tr>
                                            <th colspan="2" class="category-background text-center"><?php echo lang("gr_total_quantity"); ?></th>
                                            <th class="category-background text-center custom-ratio"><?php echo number_format($total, 6); ?></th>
                                            <th class="category-background"></th>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center no-data"><?php echo lang("no_data_available"); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div><br>

    <div>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th class="main-table-header-sfg"><?php echo lang("mr_creation_sfg_will_created"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (sizeof($sfg_list)): ?>
                    <?php if (sizeof($sfg_categories)): ?>
                        <?php foreach ($sfg_categories as $category): ?>
                            <tr>
                                <td class="main-table-detail">
                                    <table class="sub-table">
                                        <tr>
                                            <th colspan="4" class="category-background"><?php echo lang("category") . ' ' . $category->item_type . ': ' . $category->title; ?></th>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><?php echo lang("details"); ?></th>
                                            <th class="text-center"><?php echo lang("stock_restock_name"); ?></th>
                                            <th class="text-center"><?php echo lang("quantity"); ?></th>
                                            <th class="text-center"><?php echo lang("stock_material_unit"); ?></th>
                                        </tr>
                                        <?php $total = 0; ?>
                                        <?php foreach ($sfg_list as $sfg): ?>
                                            <?php if ($category->id == $sfg->category_in_bom): $total += $sfg->ratio; ?>
                                                <tr>
                                                    <td class="custom-material-name">
                                                        <?php
                                                            $sfg_name = $sfg->item_info->item_code;
                                                            if (isset($sfg->item_info->title) && !empty($sfg->item_info->title)) {
                                                                $sfg_name = $sfg->item_info->item_code . ' - ' . $sfg->item_info->title;
                                                            }
                                                        ?>
                                                        <span><?php echo mb_strimwidth($sfg_name, 0, 40, '...', 'UTF-8'); ?></span>
                                                        <span><?php echo mb_strimwidth($sfg->item_info->description, 0, 40, '...', 'UTF-8'); ?></span>
                                                    </td>
                                                    <td class="custom-stock-name"><?php echo $sfg->stock_name; ?></td>
                                                    <td class="text-right"><?php echo $sfg->ratio; ?></td>
                                                    <td class="text-center custom-unit"><?php echo $sfg->item_info->unit_type; ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <tr>
                                            <th colspan="2" class="category-background text-center"><?php echo lang("gr_total_quantity"); ?></th>
                                            <th class="category-background text-center custom-ratio"><?php echo number_format($total, 6); ?></th>
                                            <th class="category-background"></th>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center no-data"><?php echo lang("no_data_available"); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <span class="footer-question"><?php echo lang("mr_creation_all_question"); ?></span>

    <button type="button" class="btn btn-primary" id="btn-submit" data-dismiss="modal">
        <span class="fa fa-check-circle"></span> 
        <?php echo lang("yes"); ?>
    </button>

    <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span> 
        <?php echo lang("no"); ?>
    </button>
</div>

<script type="text/javascript">
const projectId = document.querySelector("#project_id").value;
const projectName = document.querySelector("#project_name").value;
const productionIds = document.querySelector("#production_ids").value;
const postUrl = document.querySelector("#post_url").value;

async function mrCreationAll() {
    let url = postUrl;
    let req = {
        projectId: projectId,
        projectName: projectName,
        productionIds: productionIds
    };

    await axios.post(url, req).then(res => {
        const { success, target } = res.data;
        // console.log(success);
        
        if (success) {
            window.open(target, "_blank");
            window.parent.loadProductionOrderList();
        } else {
            window.parent.loadProductionOrderList();
        }
    }).catch(err => {
        console.log(err);
    });
}

$(document).ready(function () {
    $("#btn-submit").on("click", async function () {
        await mrCreationAll();
    });
});
</script>
