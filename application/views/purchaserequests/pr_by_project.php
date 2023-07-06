<style type="text/css">
.clear-border {
    border: none;
    outline: none;
}
</style>

<div id="page-content" class="p20 clearfix">
    <div class="process-pr-preview">
        <div class="panel panel-default">
            <?php echo form_open(get_uri("purchaserequests/prbyproject_save"), array("id" => "project-pr-form", "class" => "general-form", "role" => "form")); ?>
            <div class="page-title clearfix">
                <h1><?php echo lang('pr_by_project') . $project_info->title; ?></h1>
                <div class="title-button-group"></div>
                <input type="hidden" name="project_id" value="<?php echo $project_info->id; ?>">
                <input type="hidden" name="project_name" value="<?php echo $project_info->title; ?>">
            </div>
            <div class="p20">
                <div class="mb20 ml15 mr15"></div>
                <div class="m15 pb15 mb30">
                    <div class="table-responsive">
                        <table id="pr-item-table" class="display mt0" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th><?php echo lang('stock_material'); ?></th>
                                    <th><?php echo lang('select_a_supplier'); ?></th>
                                    <th class="text-right"><?php echo lang('quantity'); ?></th>
                                    <th><?php echo lang('stock_material_unit'); ?></th>
                                    <th><?php echo lang('rate'); ?></th>
                                    <th><?php echo lang('total'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (sizeof($project_material)) :
                                    foreach ($project_material as $index => $list) :
                                    ?>
                                    <tr>
                                        <td class="text-center w5p">
                                            <input type="text" name="item_no[]" class="form-control text-center bg-white clear-border" value="<?php echo $index + 1; ?>" readonly>
                                        </td>
                                        <td class="w25p">
                                            <select name="material_id[]" class="form-control select-material" id="material_id" style="pointer-events: none;">
                                                <option value="<?php echo $list->material_id; ?>"><?php echo $list->material_name; ?></option>
                                            </select>
                                            <input type="hidden" name="material_name[]" value="<?php echo $list->material_name; ?>">
                                        </td>
                                        <td class="w30p">
                                            <select name="supplier_id[]" id="supplier_id_<?php echo $index + 1; ?>" class="form-control" required>
                                                <?php if (isset($list->fix_supplier->id)): ?>
                                                    <option selected value="<?php echo $list->fix_supplier->id; ?>"><?php echo $list->fix_supplier->company_name; ?></option>
                                                <?php endif; ?>
                                                <?php if (sizeof($supplier_dropdown)): ?>
                                                    <?php foreach ($supplier_dropdown as $key => $supplier): ?>
                                                        <?php if ($key != 0): ?>
                                                            <option value="<?php echo $supplier['id']; ?>"><?php echo $supplier['text']; ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        <td class="text-right w10p">
                                            <?php
                                            $start_quantity = $list->pr_quantity;
                                            $start_price = 0;
                                            $start_total = 0;

                                            if (isset($list->fix_supplier->ratio) && $list->fix_supplier->ratio > $list->pr_quantity) {
                                                $start_quantity = round($list->fix_supplier->ratio, 4);
                                                $start_price = round($list->fix_supplier->price / $list->fix_supplier->ratio, 2);
                                                $start_total = round($start_quantity * $start_price, 2);
                                            }

                                            if (isset($list->fix_supplier->ratio) && $list->fix_supplier->ratio < $list->pr_quantity) {
                                                $start_quantity = round($list->pr_quantity, 4);
                                                $start_price = round($list->fix_supplier->price / $list->fix_supplier->ratio, 2);
                                                $start_total = round($start_quantity * $start_price, 2);
                                            }
                                            ?>
                                            <input type="number" name="pr_quantity[]" id="pr_quantity_<?php echo $index + 1; ?>" class="form-control text-right" value="<?php echo $start_quantity; ?>" min="<?php echo $list->pr_quantity; ?>" step="0.0001" required>
                                        </td>
                                        <td class="w5p">
                                            <input type="text" name="pr_unit[]" class="form-control bg-white clear-border" value="<?php echo $list->unit; ?>" readonly>
                                        </td>
                                        <td class="w10p">
                                            <input type="number" name="pr_price[]" id="pr_price_<?php echo $index + 1; ?>" class="form-control" value="<?php echo $start_price; ?>" min="0.00" step="0.01" required>
                                        </td>
                                        <td class="w10p">
                                            <input type="number" id="price-total_<?php echo $index + 1; ?>" class="form-control" value="<?php echo $start_total; ?>" min="0.00" step="0.01" required>
                                        </td>
                                    </tr>
                                    <?php
                                    endforeach;
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="clearfix">
                        <div class="col-sm-8">
                        </div>
                        <div class="pull-right" id="pr-total-section">
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer clearfix">
                <button type="submit" class="btn btn-primary pull-right ml10">
                    <span class="fa fa-check-circle"></span>
                    <?php echo lang('place_order1'); ?>
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <div class="text-center box" style="height: 400px;">
        <div class="box-content" style="vertical-align: middle">
            <span class="fa fa-shopping-basket" style="font-size: 30rem; color: #d8d8d8;"></span>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#pr-item-table').DataTable({
        "bPaginate": false,
		"bLengthChange": false,
		"bFilter": false,
		"bSort": false,
		"bInfo": false,
		"bAutoWidth": false
    });

    var projectPrForm = $('#project-pr-form');
    projectPrForm.appForm({
        onSuccess: function (result) {
            window.close();
            // appAlert.success(result.message, { duration: 2000 });
            // setTimeout(() => {
            //     window.close();
            // }, 2000);
        }
    });
    
    <?php
    if (sizeof($project_material)):
        foreach ($project_material as $index => $list):
    ?>
        $('#supplier_id_<?php echo $index + 1; ?>').select2();

        $('#pr_quantity_<?php echo $index + 1; ?>').on('click', function() {
            $(this).select();
        });

        $('#pr_price_<?php echo $index + 1; ?>').on('click', function() {
            $(this).select();
        });

        $('#price-total_<?php echo $index + 1; ?>').on('click', function() {
            $(this).select();
        });

        $('#pr_quantity_<?php echo $index + 1; ?>').on('keyup', function(e) {
            e.preventDefault();

            $('#price-total_<?php echo $index + 1; ?>').val(totalCalc(
                $('#pr_quantity_<?php echo $index + 1; ?>').val(),
                $('#pr_price_<?php echo $index + 1; ?>').val()
            ));
        });
        
        $('#pr_price_<?php echo $index + 1; ?>').on('keyup', function(e) {
            e.preventDefault();

            $('#price-total_<?php echo $index + 1; ?>').val(totalCalc(
                $('#pr_quantity_<?php echo $index + 1; ?>').val(),
                $('#pr_price_<?php echo $index + 1; ?>').val()
            ));
        });

        $('#price-total_<?php echo $index + 1; ?>').on('keyup', function(e) {
            e.preventDefault();

            $('#pr_price_<?php echo $index + 1; ?>').val(priceUnitCalc(
                $('#pr_quantity_<?php echo $index + 1; ?>').val(),
                $('#price-total_<?php echo $index + 1; ?>').val()
            ));
        });
    <?php
        endforeach;
    endif;
    ?>

    function totalCalc(quantity = 0, priceunit = 0) {
        let result = 0;
        if (quantity != 0 && priceunit != 0) {
            result = quantity * priceunit;
        }

        return result.toFixed(2);
    }

    function priceUnitCalc(quantity = 0, total = 0) {
        let result = 0;
        if (quantity != 0 && total != 0) {
            result = total / quantity;
        }

        return result.toFixed(2);
    }
});
</script>