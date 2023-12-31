<style type="text/css">
    .modal-dialog {
        width: min(78%, 1024px);
    }

    .modal-body {
        max-height: 77vh;
        overflow-y: auto;
    }

    .pointer-none {
        pointer-events: none;
    }

    .pointer-none-appearance {
        pointer-events: none;
        appearance: none;
    }

    .item-table table {
        width: 100%;
    }

    .item-table table th {
        height: 53px;
        text-align: center;
        border: 1px solid #f2f2f2;
    }

    .item-table table th:last-child {
        width: 10%;
        padding: .7rem;
    }

    .item-table table td {
        padding: .7rem;
        border: 1px solid #f2f2f2;
    }

    .item-table table td:last-child {
        text-align: center;
    }

    .select-unit {
        text-align: center;
        border: none !important;
        background: none !important;
    }

    .right-control {
        width: 100%;
    }

    .quantity-over {
        color: #f32013;
    }

    .quantity-done {
        color: #00b900;
    }

    #remark-text {
        resize: none;
    }
</style>

<?php
echo form_open(
    get_uri("payment_voucher/addnew_no_po_save"),
    array(
        "id" => "addnew-form",
        "class" => "general-form",
        "role" => "form"
    )
);
?>

<div class="modal-body clearfix">
    <div class="form-group">
        <label for="doc-date" class=" col-md-3">
            <?php echo lang("document_date"); ?>
        </label>
        <div class="col-md-9">
            <input type="text" id="doc-date" name="doc-date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="internal_reference" class="col-md-3">
            <?php echo lang("pv_internal_reference"); ?>
        </label>
        <div class="col-md-9">
            <input 
                type="text" 
                name="internal_reference" 
                id="internal_reference" 
                class="form-control"
                placeholder="<?php echo lang("pv_internal_reference_place_holder"); ?>" 
                maxlength="40"
            />
        </div>
    </div>

    <div class="form-group">
        <label for="project-id" class="col-md-3">
            <?php echo lang("project_refer"); ?>
        </label>
        <div class="col-md-9">
            <select name="project-id" id="project-id" class="form-control select-project" required>
                <option value="0">
                    <?php echo "-- " . lang("project_refer") . " --"; ?>
                </option>
                <?php if (sizeof($project_dropdown)): ?>
                    <?php foreach ($project_dropdown as $project): ?>
                        <option value="<?php echo $project["project_id"]; ?>">
                            <?php echo $project["project_name"]; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
        <div class="form-group">
            <label for="supplier-id" class="col-md-3">
                <?php echo lang("suppliers"); ?>
            </label>
            <div class="col-md-9">
                <select id="supplier-id" name="supplier-id" class="form-control select-supplier" required>
                    <?php if (sizeof($supplier_dropdown)): ?>
                        <?php foreach ($supplier_dropdown as $supplier): ?>
                            <option value="<?php echo $supplier["supplier_id"]; ?>">
                                <?php echo $supplier["supplier_name"]; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="invoice-refer" class="col-md-3">
            <?php echo lang("pv_invoice_refer"); ?>
        </label>
        <div class="col-md-9">
            <input type="text" name="invoice-refer" id="invoice-refer" class="form-control select-invoice-refer"
                placeholder="<?php echo lang("pv_invoice_refer_placeholder"); ?>" maxlength="40">
        </div>
    </div>

    <div class="form-group">
        <label for="remark-text" class="col-md-3">
            <?php echo lang("remark"); ?>
        </label>
        <div class="col-md-9">
            <textarea name="remark-text" id="remark-text" class="form-control" cols="30" rows="10"
                placeholder="<?php echo lang("pv_remark_placeholder"); ?>"></textarea>
        </div>
    </div>

    <div class="form-group item-table">
        <table>
            <thead>
                <tr>
                    <th width="15%">
                        <?php echo lang("item_type"); ?>
                    </th>
                    <th width="26%">
                        <?php echo lang("entries"); ?>
                    </th>
                    <th width="">
                        <?php echo lang("description"); ?>
                    </th>
                    <th width="12%">
                        <?php echo lang("quantity"); ?>
                    </th>
                    <th width="12%">
                        <?php echo lang("rate"); ?>
                    </th>
                    <th>
                        <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
                            <button id="btn-add-item" class="btn btn-primary right-control">
                                <span class="fa fa-plus-circle"></span>
                                <?php echo lang("add"); ?>
                            </button>
                        <?php endif; ?>
                    </th>
                </tr>
            </thead>

            <tbody id="tbody-po-select"></tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
        <?php echo lang("close"); ?>
    </button>

    <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
        <button type="submit" id="btn-submit" class="btn btn-primary"><span class="fa fa-check-circle"></span>
            <?php echo lang("create"); ?>
        </button>
    <?php endif; ?>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    const tableBody = $("#tbody-po-select");
    const addNewForm = $("#addnew-form");

    const rmDropdown = JSON.parse('<?php echo $rm_dropdown; ?>');
    const fgDropdown = JSON.parse('<?php echo $fg_dropdown; ?>');
    const sfgDropdown = JSON.parse('<?php echo $sfg_dropdown; ?>');
    const svDropdown = JSON.parse('<?php echo $sv_dropdown; ?>');

    async function processBinding() {
        $(".select-order").select2("destroy");
        $(".select-order").select2();

        $(".select-order").unbind();
        $(".select-order").on("change", function () {
            let self = $(this);
            let parent = self.closest("tr");
            let selectItems = parent.find('[name="product_id[]"]');
            let selectDescription = parent.find('[name="product_description[]"]');
            let optionItems = [];

            selectItems.val('');
            selectItems.find('option').remove();
            selectItems.append('<option></option>');

            if (self.val() == 'SV') {
                optionItems = svDropdown;

                if (optionItems.length) {
                    optionItems.map((i) => {
                        selectItems.append(`<option value="${i.id}">${i.text}</option>`);
                    });
                    
                    selectDescription.removeClass('pointer-none hide');
                    selectItems.removeClass('pointer-none hide');
                    selectItems.select2();
                } else {
                    selectItems.addClass('pointer-none hide');
                }
            }

            if (self.val() == 'RM') {
                optionItems = rmDropdown;

                if (optionItems.length) {
                    optionItems.map((i) => {
                        selectItems.append(`<option value="${i.id}">${i.text}</option>`);
                    });
                    
                    selectDescription.addClass('pointer-none hide');
                    selectItems.removeClass('pointer-none hide');
                    selectItems.select2();
                } else {
                    selectItems.addClass('pointer-none hide');
                }
            }

            if (self.val() == 'FG') {
                optionItems = fgDropdown;

                if (optionItems.length) {
                    optionItems.map((i) => {
                        selectItems.append(`<option value="${i.id}">${i.text}</option>`);
                    });
                    
                    selectDescription.addClass('pointer-none hide');
                    selectItems.removeClass('pointer-none hide');
                    selectItems.select2();
                } else {
                    selectItems.addClass('pointer-none hide');
                }
            }

            if (self.val() == 'SFG') {
                optionItems = sfgDropdown;

                if (optionItems.length) {
                    optionItems.map((i) => {
                        selectItems.append(`<option value="${i.id}">${i.text}</option>`);
                    });
                    
                    selectDescription.addClass('pointer-none hide');
                    selectItems.removeClass('pointer-none hide');
                    selectItems.select2();
                } else {
                    selectItems.addClass('pointer-none hide');
                }
            }
        });

        $(".select-item").unbind();
        $(".select-item").on("change", function (e) {
            e.preventDefault();
        });

        $(".select-description").unbind();
        $(".select-description").on("click", function (e) {
            $(this).select();
        });

        $(".select-quantity").unbind();
        $(".select-quantity").on("click", function (e) {
            $(this).select();
        });

        $(".select-price").unbind();
        $(".select-price").on("click", function (e) {
            $(this).select();
        });

        $(".button-delete").unbind();
        $(".button-delete").on("click", async function (e) {
            e.preventDefault();

            $(this).closest("tr").remove();
            await processBinding();
        });
    }

    $(document).ready(function () {
        $("#doc-date").datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy'
        });

        $("#doc-date").datepicker(
            "setDate", "<?php echo date("d/m/Y"); ?>"
        );

        $("#project-id").select2();
        $("#supplier-id").select2();

        $("#btn-add-item").on("click", async function (e) {
            e.preventDefault();

            await tableBody.append(`
                <tr>
                    <td>
                        <select name="item_type[]" class="form-control select-order" required>
                            <option value=""><?php echo "- " . lang("item_type_dropdown") . " -"; ?></option>
                            <option value="RM"><?php echo lang("stock_material"); ?></option>
                            <option value="FG"><?php echo lang("finised_goods"); ?></option>
                            <option value="SFG"><?php echo lang("sfg_column_header"); ?></option>
                            <option value="SV"><?php echo lang("services"); ?></option>
                        </select>
                    </td>
                    <td>
                        <select name="product_id[]" class="form-control select-item pointer-none hide">
                            <option></option>
                        </select>
                    </td>
                    <td>
                        <input name="product_description[]" class="form-control select-description pointer-none hide" maxlength="40">
                    </td>
                    <td>
                        <input name="quantity[]" class="form-control select-quantity" value="1" required>
                    </td>
                    <td>
                        <input name="price[]" class="form-control select-price" value="1" required>
                    </td>
                    <td>
                        <button class="btn btn-danger button-delete right-control">
                            <span class="fa fa-trash"></span> 
                            <?php echo lang("delete"); ?>
                        </button>
                    </td>
                </tr>
            `);

            await processBinding();
        });

        addNewForm.appForm({
            onSuccess: function (result) {
                // console.log(result);

                const { header, reload_url, target_url } = result;

                if (header.id) {
                    setTimeout(async () => {
                        await window.open(target_url, '_blank');

                        setTimeout(async () => {
                            await window.open(reload_url, '_self');
                        }, 1000);
                    }, 100);
                }
            }
        });
    });
</script>