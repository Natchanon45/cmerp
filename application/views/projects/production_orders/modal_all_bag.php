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
        width: 86%;
        margin: 10px auto;
    }

    .material-line-sfg table {
        width: 86%;
        margin: 10px auto;
    }

    .material-line th {
        border: 1px solid #f2f2f2;
        height: 30px;
        text-align: center;
        background-color: rgba(0, 83, 156, .25);
        color: #030303;
    }

    .material-line-sfg th {
        border: 1px solid #f2f2f2;
        height: 30px;
        text-align: center;
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
        padding: 0 1rem;
    }

    .font-bold {
        font-weight: bolder;
    }

    .rm-quantity {
        width: 250px;
    }

    .rm-unit {
        width: 90px;
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
</style>

<div class="modal-body clearfix">

    <table>

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
                                    <th>
                                        <?php echo lang("stock_material"); ?>
                                    </th>
                                    <th>
                                        <?php echo lang("quantity"); ?>
                                    </th>
                                    <th>
                                        <?php echo lang("stock_material_unit"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <?php $total_group = 0.000000; ?>
                            <tbody>
                                <?php foreach ($production_items["rm_list"] as $rm): ?>
                                    <?php if ($category["id"] == $rm->category_in_bom): ?>
                                        <tr class="material-line-items">
                                            <td class="rm-name">
                                                <span class="font-bold">
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
                                                <?php echo $rm->quantity; $total_group += $rm->quantity; ?>
                                            </td>
                                            <td class="text-center rm-unit">
                                                <?php echo $rm->material_info->unit; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-center"><?php echo lang("gr_total_quantity"); ?></th>
                                    <th colspan="2" class="text-center"><?php echo number_format($total_group, 6); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

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
                                    <th>
                                        <?php echo lang("sfg"); ?>
                                    </th>
                                    <th>
                                        <?php echo lang("quantity"); ?>
                                    </th>
                                    <th>
                                        <?php echo lang("stock_material_unit"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <?php $total_group = 0.000000; ?>
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
                                                <?php echo $sfg->quantity; $total_group += $sfg->quantity; ?>
                                            </td>
                                            <td class="text-center rm-unit">
                                                <?php echo $sfg->item_info->unit_type; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-center"><?php echo lang("gr_total_quantity"); ?></th>
                                    <th colspan="2" class="text-center"><?php echo number_format($total_group, 6); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>
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