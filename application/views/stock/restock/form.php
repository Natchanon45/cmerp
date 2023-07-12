<input type="hidden" name="id" value="<?php echo isset($model_info->id) ? $model_info->id : ''; ?>" />
<input type="hidden" name="created_by" value="<?php echo isset($model_info->created_by) ? $model_info->created_by : ''; ?>" />
<input type="hidden" name="view" value="<?php echo isset($view) ? $view : ""; ?>" />

<?php
$readonly = false;
if (empty($model_info->id)) {
    $readonly = isset($can_create) && !$can_create;
} else {
    $readonly = isset($can_update) && !$can_update;
}
?>

<div class="form-group">
    <label for="name" class="<?php echo $label_column; ?>"><?php echo lang('stock_restock_name'); ?>*</label>
    <div class="<?php echo $field_column; ?>">
        <?php
        echo form_input(
            array(
                "id" => "name",
                "name" => "name",
                "value" => $model_info->name,
                "class" => "form-control",
                "placeholder" => lang('stock_restock_name'),
                "autofocus" => true,
                "data-rule-required" => true,
                "data-msg-required" => lang('field_required'),
                "readonly" => $readonly
            )
        );
        ?>
    </div>
</div>
<!-- Restock Name -->

<div class="form-group">
    <label for="po_no" class="<?php echo $label_column; ?>"><?php echo lang('po_no'); ?></label>
    <div class="<?php echo $field_column; ?>">
        <?php
        echo form_input(
            array(
                "id" => "po_no",
                "name" => "po_no",
                "value" => $model_info->po_no,
                "class" => "form-control",
                "placeholder" => lang('po_ref'),
                "autofocus" => true,
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
                "readonly" => $readonly
            )
        );
        ?>
    </div>
</div>
<!-- Purchase Order Number -->

<?php if ($this->login_user->is_admin): ?>
    <div class="form-group">
        <label for="created_by" class="<?php echo $label_column; ?>"><?php echo lang('stock_restock_creator'); ?>*</label>
        <div class="<?php echo $field_column; ?>">
            <?php
            echo form_input(
                array(
                    "id" => "created_by",
                    "name" => "created_by",
                    "value" => $model_info->created_by ? $model_info->created_by : $this->login_user->id,
                    "class" => "form-control",
                    "placeholder" => lang('stock_restock_creator'),
                    "data-rule-required" => true,
                    "data-msg-required" => lang('field_required'),
                    "readonly" => $readonly
                )
            );
            ?>
        </div>
    </div>
<?php endif; ?>
<!-- Restock Creator Name -->

<div class="form-group">
    <label for="created_date" class="<?php echo $label_column; ?>"><?php echo lang('stock_restock_date'); ?>*</label>
    <div <?php if ($readonly) { echo 'style="pointer-events:none;"'; } ?> class="<?php echo $field_column; ?>">
        <?php
        echo form_input(
            array(
                "id" => "created_date",
                "name" => "created_date",
                "value" => is_date_exists($model_info->created_date) ? $model_info->created_date : "",
                "class" => "form-control",
                "placeholder" => lang('stock_restock_date'),
                "autocomplete" => "off",
                "data-rule-required" => true,
                "data-msg-required" => lang('field_required'),
                "readonly" => $readonly
            )
        );
        ?>
    </div>
</div>
<!-- Restock Receipt Date -->

<style type="text/css">
.dropdown-dev {
    <?php if ($can_read_price): ?>
        max-width: 277px;
    <?php else: ?>
        max-width: 330px;
    <?php endif; ?>
}

.dropdown-dev > option {
    word-wrap: break-word;
    white-space: normal;
}

.event-point {
    pointer-events: none;
}

.string-upper {
    text-transform: uppercase;
}
</style>

<?php if (isset($material_restocks)): ?>
    <div id="type-container">
        <table class="display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="supplier-table_info">
            <thead id="table-header">
                <tr role="row">
                    <th style="width: 20%;">
                        <?php echo lang("serial_number"); ?>
                    </th>
                    <th style="width: 30%;">
                        <?php echo lang('stock_materials'); ?>
                    </th>
                    <th style="width: 15%;">
                        <?php echo lang('stock_restock_quantity'); ?>
                    </th>

                    <?php if ($can_read_price): ?>
                        <th style="width: 15%;">
                            <?php echo lang('stock_restock_price'); ?>
                        </th>
                        <th style="width: 15%;">
                            <?php echo lang('rate'); ?>
                        </th>
                    <?php endif; ?>

                    <th style="width: 5%; text-align: center;">
                        <?php if (!$readonly): ?>
                            <?php if ($can_create): ?>
                                <a href="javascript:" id="btn-add-material" class="btn btn-primary w100p">
                                    <span class="fa fa-plus-circle"></span>
                                    <?php echo lang('add'); ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </th>
                </tr>
            </thead>
            <!-- Table Head -->

            <tbody id="table-body">
                <?php 
                foreach ($material_restocks as $k): 
                $pricePerUnit = number_format($k->price / $k->stock, 2, ".", "");
                ?>
                <tr>
                    <td>
                        <input type="text" name="restock_serial[]" <?php if ($readonly) { echo 'readonly'; } if (!$k->can_delete) { echo ' readonly'; } ?> class="form-control data-sern" maxlength="40" value="<?php echo $k->serial_number ? $k->serial_number : ""; ?>">
                    </td>
                    <td>
                        <input type="hidden" name="restock_id[]" value="<?= $k->id ?>" />
                            <select name="material_id[]" class="form-control select-material event-point dropdown-dev w100p" style="pointer-events: none;" <?php if ($readonly) { echo 'readonly'; } ?> required>
                                <option value="" data-unit=""></option>
                                <?php
                                foreach ($material_dropdown as $d) {
                                    $selected = '';
                                    if ($d->id == $k->material_id) {
                                        $selected = 'selected';
                                    }

                                    if ($can_read_material_name) {
                                        echo '<option value="' . $d->id . '" data-unit="' . $d->unit . '" ' . $selected . '>' . strtoupper($d->name) . ' - ' . ucwords(strtolower($d->production_name)) . '</option>';
                                    } else {
                                        echo '<option value="' . $d->id . '" data-unit="' . $d->unit . '" ' . $selected . '>' . strtoupper($d->name) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <div class="input-suffix">
                                <input type="number" name="stock[]" required <?php if ($readonly) { echo 'readonly'; } if (!$k->can_delete) { echo ' readonly'; } ?> class="form-control stock-calc" min="0.0001" step="0.0001" value="<?php echo $k->stock; ?>" />
                                <div class="input-tag string-upper"><?php echo $k->material_unit; ?></div>
                            </div>
                        </td>
                        <?php if ($can_read_price): ?>
                            <td>
                                <div class="input-suffix">
                                    <input 
                                        type="number" name="price[]" required <?php if ($readonly) { echo 'readonly'; } if (!$k->can_delete) { echo ' readonly'; } ?>
                                        class="form-control price-calc" min="0" step="0.01" value="<?php echo $k->price; ?>" 
                                    />
                                    <div class="input-tag-2"><?php echo lang('THB'); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="input-suffix">
                                    <input 
                                        type="number" name="priceunit[]" <?php if ($readonly) { echo 'readonly'; } if (!$k->can_delete) { echo ' readonly'; } ?> 
                                        class="form-control price-per-unit" min="0" step="0.01" value="<?php echo $pricePerUnit; ?>" 
                                    />
                                    <div class="input-tag-2"><?php echo lang('THB'); ?></div>
                                </div>
                            </td>
                        <?php endif; ?>
                        <td style="text-align: center;">
                            <?php if (!$readonly): ?>
                                <?php if ($k->can_delete && $can_delete): ?>
                                    <a href="javascript:void();" class="btn btn-danger btn-delete-material w100p">
                                        <span class="fa fa-trash"></span>
                                        <?php echo lang('delete'); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();

        <?php if ($this->login_user->is_admin): ?>
            $('#created_by').select2({ data: <?php echo $team_members_dropdown; ?> });
        <?php endif; ?>

        setDatePicker("#created_date");

        <?php if (isset($material_restocks)): ?>
            var typeContainer = $('#type-container');
            var tableBody = typeContainer.find('#table-body'), btnAdd = typeContainer.find('#btn-add-material');

            btnAdd.click(function (e) {
                e.preventDefault();
                tableBody.append(`
                <tr>
                    <td>
                        <input type="text" name="restock_serial[]" class="form-control data-sern" maxlength="40" required>
                    </td>
                    <td>
                        <input type="hidden" name="restock_id[]" />
                        <select name="material_id[]" class="form-control select-material dropdown-dev w100p" required>
                            <?php
                            foreach ($material_dropdown as $d) {
                                if ($can_read_material_name) {
                                    echo '<option value="' . $d->id . '" data-unit="' . $d->unit . '">' . strtoupper($d->name) . ' - ' . ucwords(strtolower($d->production_name)) . '</option>';
                                } else {
                                    echo '<option value="' . $d->id . '" data-unit="' . $d->unit . '">' . strtoupper($d->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                    <div class="input-suffix">
                        <input type="number" name="stock[]" required class="form-control stock-calc" min="0.0001" step="0.0001" value="1" />
                        <div class="input-tag string-upper"><?php echo $material_dropdown[0]->unit; ?></div>
                    </div>
                    </td>
                    <?php if ($can_read_price) { ?>
                        <td>
                            <div class="input-suffix">
                                <input 
                                    type="number" name="price[]" required 
                                    class="form-control price-calc" min="0" step="0.01" value="1" 
                                />
                                <div class="input-tag-2"><?php echo lang('THB'); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="input-suffix">
                                <input 
                                    type="number" name="priceunit[]" required 
                                    class="form-control price-per-unit" min="0" step="0.01" value="1" 
                                />
                                <div class="input-tag-2"><?php echo lang('THB'); ?></div>
                            </div>
                        </td>
                    <?php } ?>
                    <td>
                        <a href="javascript:void();" class="btn btn-danger btn-delete-material w100p">
                            <span class="fa fa-trash"></span> <?php echo lang('delete'); ?>
                        </a>
                    </td>
                </tr>
                `);
                processBinding();
            });
            processBinding();

            function processBinding() {
                typeContainer.find('.btn-delete-material').unbind();
                typeContainer.find('.btn-delete-material').click(function (e) {
                    e.preventDefault();
                    $(this).closest('tr').remove();
                    processBinding();
                });

                typeContainer.find('.stock-calc').unbind();
                typeContainer.find('.stock-calc').click(function (e) {
                    e.target.select();
                });
                typeContainer.find('.stock-calc').keypress(function (e) {
                    if (e.key == "Enter") {
                        e.preventDefault();
                        $(this).closest('tr').find('.price-calc').select();
                    }
                });

                typeContainer.find('.price-calc').unbind();
                typeContainer.find('.price-calc').click(function (e) {
                    e.target.select();
                });
                typeContainer.find('.price-calc').keypress(function (e) {
                    if (e.key == "Enter") {
                        e.preventDefault();
                        $(this).closest('tr').find('.price-per-unit').select();
                    }
                });
                typeContainer.find('.price-calc').keyup(function (e) {
                    e.preventDefault();
                    let stock = parseFloat($(this).closest('tr').find('.stock-calc').val());
                    let price = parseFloat($(this).closest('tr').find('.price-calc').val());

                    $(this).closest('tr').find('.price-per-unit').val(priceUnitCalc(stock, price));
                });

                typeContainer.find('.price-per-unit').unbind();
                typeContainer.find('.price-per-unit').click(function (e) {
                    e.target.select();
                });
                typeContainer.find('.price-per-unit').keypress(function (e) {
                    if (e.key == "Enter") {
                        e.preventDefault();
                        $(this).closest('tr').find('.price-calc').select();
                    }
                });
                typeContainer.find('.price-per-unit').keyup(function (e) {
                    e.preventDefault();
                    let stock = parseFloat($(this).closest('tr').find('.stock-calc').val());
                    let price = parseFloat($(this).closest('tr').find('.price-per-unit').val());

                    $(this).closest('tr').find('.price-calc').val(priceTotalCalc(stock, price));
                });

                typeContainer.find('.select-material').select2('destroy');
                typeContainer.find('.select-material').select2();

                typeContainer.find('.select-material').unbind();
                typeContainer.find('.select-material').change(function () {
                    let self = $(this);
                    let option = $(this).find('[value="' + this.value + '"]');
                    self.closest('tr').find('.input-tag').html(option.data('unit'));
                });
            }

            function priceUnitCalc(stock = 0, price = 0) {
                if (stock === 0 || price === 0) {
                    return 0;
                } else {
                    return (price / stock).toFixed(2);
                }
            }

            function priceTotalCalc(stock = 0, price = 0) {
                if (stock === 0 || price === 0) {
                    return 0;
                } else {
                    return (price * stock).toFixed(2);
                }
            }
    <?php endif; ?>
  });
</script>