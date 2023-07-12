<div id="page-content" class="p20 clearfix">
    <div class="panel clearfix">
        <ul id="pr-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo lang('pr_create_rm'); ?></h4></li>
        </ul>

        <div class="tab-content" id="pr_summarize_content">
            <form id="pr_summarize_form" class="general-form" role="form">
                <div class="table-responsive">
                    <table id="pr_summarize_table" class="display" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center w5p">#</th>
                                <th class="w25p"><?php echo lang('stock_material'); ?></th>
                                <th class="w20p"><?php echo lang('select_a_supplier'); ?></th>
                                <th class="text-right w15p"><?php echo lang('quantity'); ?></th>
                                <th class="w5p"><?php echo lang('stock_material_unit'); ?></th>
                                <th class="w15p"><?php echo lang('rate'); ?></th>
                                <th class="w15p"><?php echo lang('total'); ?></th>
                            </tr>
                        </thead>
                        <?php if (sizeof($select_materials)): foreach ($select_materials as $index => $list): ?>
                        <tbody>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td>
                                    <select name="material_id" id="material_id_<?php echo $index + 1; ?>" class="form-control material-length select-material" style="pointer-events: none;">
                                        <option value="<?php echo $list->material_id; ?>"><?php echo $list->material_name; ?></option>
                                    </select>
                                    <input type="hidden" name="material_name" id="material_name_<?php echo $index + 1; ?>" value="<?php echo $list->material_name; ?>">
                                </td>
                                <td>
                                    <select name="supplier_id" id="supplier_id_<?php echo $index + 1; ?>" class="form-control supplier-length" required>
                                    <?php if (isset($list->fix_supplier->id)): ?>
                                        <option selected value="<?php echo $list->fix_supplier->id; ?>"><?php echo $list->fix_supplier->company_name; ?></option>
                                    <?php endif; ?>
                                    <?php if (sizeof($supplier_dropdown)): foreach ($supplier_dropdown as $key => $supplier): if ($key != 0): ?>
                                        <option value="<?php echo $supplier['id']; ?>"><?php echo $supplier['text']; ?></option>
                                    <?php endif; endforeach; endif; ?>
                                    </select>
                                </td>
                                <?php
                                $start_quantity = $list->pr_quantity;
                                $start_price = 0;
                                $start_total = 0;

                                if (isset($list->fix_supplier->ratio) && $list->fix_supplier->ratio > $start_quantity) {
                                    $start_quantity = $list->fix_supplier->ratio;
                                    $start_price = round($list->fix_supplier->price / $list->fix_supplier->ratio, 3);
                                    $start_total = round($list->fix_supplier->price, 2);
                                }

                                if (isset($list->fix_supplier->ratio) && $list->fix_supplier->ratio < $start_quantity) {
                                    $start_price = round($list->fix_supplier->price / $list->fix_supplier->ratio, 3);
                                    $start_total = round($start_quantity * $start_price, 2);
                                }
                                ?>
                                <td>
                                    <input type="number" name="pr_quantity" id="pr_quantity_<?php echo $index + 1; ?>" class="form-control text-right" value="<?php echo $start_quantity; ?>" min="<?php echo $list->pr_quantity; ?>" step="0.0001" required>
                                </td>
                                <td>
                                    <input type="text" name="pr_unit" id="pr_unit_<?php echo $index + 1; ?>" class="form-control" value="<?php echo $list->unit; ?>" readonly>
                                </td>
                                <td>
                                    <input type="number" name="pr_price" id="pr_price_<?php echo $index + 1; ?>" class="form-control" value="<?php echo $start_price; ?>" min="0.000" step="0.001" required>
                                </td>
                                <td>
                                    <input type="number" name="pr_price_total" id="pr_price_total_<?php echo $index + 1; ?>" class="form-control" value="<?php echo $start_total; ?>" min="0.00" step="0.01" required>
                                </td>
                            </tr>
                        </tbody>
                        <?php endforeach; endif; ?>
                    </table>
                </div>

                <div class="pr-summarize-footer clearfix">
                    <button type="submit" class="btn btn-primary pull-right btn-pr-save">
                        <i class="fa fa-check-circle"></i>
                        <span><?php echo lang('btn_create_pr'); ?></span>
                    </button>
                    <button type="button" class="btn btn-light pull-right btn-pr-back">
                        <i class="fa fa-backward"></i>
                        <span><?php echo lang('back'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center box" style="height: 400px;">
        <div class="box-content" style="vertical-align: middle;">
            <span class="fa fa-shopping-basket" style="font-size: 30rem; color: #d8d8d8;"></span>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#pr_summarize_table').DataTable({
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": false,
            "bSort": false,
            "bInfo": false,
            "bAutoWidth": false
        });

        <?php if (sizeof($select_materials)): foreach ($select_materials as $index => $list): ?>
            $('#material_id_<?php echo $index + 1; ?>').select2();
            $('#supplier_id_<?php echo $index + 1; ?>').select2();

            clickToFocus($('#pr_quantity_<?php echo $index + 1; ?>'));
            clickToFocus($('#pr_price_<?php echo $index + 1; ?>'));
            clickToFocus($('#pr_price_total_<?php echo $index + 1; ?>'));

            $('#pr_quantity_<?php echo $index + 1; ?>').on('keyup', function(e) {
                e.preventDefault();

                $('#pr_price_total_<?php echo $index + 1; ?>').val(totalCalc(
                    $('#pr_quantity_<?php echo $index + 1; ?>').val(),
                    $('#pr_price_<?php echo $index + 1; ?>').val()
                ));
            });

            $('#pr_price_<?php echo $index + 1; ?>').on('keyup', function(e) {
                e.preventDefault();

                $('#pr_price_total_<?php echo $index + 1; ?>').val(totalCalc(
                    $('#pr_quantity_<?php echo $index + 1; ?>').val(),
                    $('#pr_price_<?php echo $index + 1; ?>').val()
                ));
            });

            $('#pr_price_total_<?php echo $index + 1; ?>').on('keyup', function(e) {
                e.preventDefault();

                $('#pr_price_<?php echo $index + 1; ?>').val(priceUnitCalc(
                    $('#pr_quantity_<?php echo $index + 1; ?>').val(),
                    $('#pr_price_total_<?php echo $index + 1; ?>').val()
                ));
            });
        <?php endforeach; endif; ?>

        $('.btn-pr-back').on('click', function() {
            window.location = '<?php echo get_uri('purchaserequests'); ?>';
        });
    });

    const summarizeForm = document.querySelector('#pr_summarize_form');
    summarizeForm.addEventListener('submit', (event) => {
        event.preventDefault();

        let formData = {
            material_ids: getFormData('[name="material_id"]'),
            material_names: getFormData('[name="material_name"]'),
            supplier_ids: getFormData('[name="supplier_id"]'),
            pr_quantitys: getFormData('[name="pr_quantity"]'),
            pr_units: getFormData('[name="pr_unit"]'),
            pr_prices: getFormData('[name="pr_price"]'),
            pr_price_totals: getFormData('[name="pr_price_total"]')
        };
        
        let url = '<?php echo echo_uri('purchaserequests/pr_summarize_save'); ?>';

        if (confirm('Do you submit form?')) {
            ApiFetchPostAsync(url, formData).then((result) => {
                // console.log(result);

                if (result.success) {
                    // appAlert.success(result.message, { duration: 2000 });
                    setTimeout(function() {
                        window.location = '<?php echo echo_uri('purchaserequests/pr_success'); ?>';
                    }, 25)
                } else {
                    // appAlert.error(result.message, { duration: 2000 });
                    setTimeout(function() {
                        window.location = '<?php echo echo_uri('purchaserequests/pr_failure'); ?>';
                    }, 25)
                }
            });
        }
    });

    function getFormData(selector) {
        let items = document.querySelectorAll(selector);
        let data = [];
        
        items.forEach((item) => {
            data.push(item.value);
        });

        return data;
    }

    // click-to-focus
    function clickToFocus(object) {
        object.on('click', function() {
            $(this).select();
        });
    }

    // calc-price-total
    function totalCalc(quantity = 0, priceunit = 0) {
        let result = 0;
        if (quantity != 0 && priceunit != 0) {
            result = quantity * priceunit;
        }

        return result.toFixed(2);
    }

    // calc-price-unit
    function priceUnitCalc(quantity = 0, total = 0) {
        let result = 0;
        if (quantity != 0 && total != 0) {
            result = total / quantity;
        }

        return result.toFixed(3);
    }

    // api-fetch-post-async
    const ApiFetchPostAsync = async (url = "", data = {}) => {
        const response = await fetch(url, {
            method: "POST",
            mode: "cors",
            cache: "no-cache",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            redirect: "follow",
            referrerPolicy: "no-referrer",
            body: JSON.stringify(data)
        });
        return response.json();
    };
</script>

<style type="text/css">
    #pr_summarize_content {
        width: 90%;
        margin: 2.5rem auto;
    }

    #pr_summarize_table {
        border: 1px solid #f2f2f2;
    }

    #pr_summarize_table td {
        padding: .71rem;
    }

    .supplier-length {
        max-width: 400px;
    }

    .material-length {
        max-width: 350px;
    }

    .pr-summarize-footer {
        border: 1px solid #f2f2f2;
        border-top: none;
    }

    .btn-pr-save {
        margin: .6rem .72rem .5rem 0;
    }

    .btn-pr-back {
        margin: .6rem .72rem .5rem 0;
    }
</style>