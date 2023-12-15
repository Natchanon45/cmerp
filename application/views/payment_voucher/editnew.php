<style type="text/css">
    .modal-dialog {
        width: min(70%, 1024px);
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
</style>

<?php
echo form_open(
    get_uri("payment_voucher/editnew_save"),
    array(
        "id" => "addnew-form",
        "class" => "general-form",
        "role" => "form"
    )
);
?>

<div class="modal-body clearfix">
    <div class="form-group">
        <label for="doc-number" class="col-md-3">
            <?php echo lang("number_of_document"); ?>
        </label>
        <div class="col-md-9">
            <input type="text" value="<?php echo $header_data->doc_number; ?>" class="form-control pointer-none" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="doc-date" class="col-md-3">
            <?php echo lang("document_date"); ?>
        </label>
        <div class="col-md-9">
            <input type="hidden" id="document-id" name="document-id" value="<?php echo $header_data->id; ?>">
            <input type="text" id="doc-date" name="doc-date" class="form-control <?php if ($header_data->status == "A") { echo "pointer-none"; } ?> <?php if ($header_data->po_id != 0) { echo "pointer-none"; } ?>" autocomplete="off" readonly>
        </div>
    </div>

    <!-- <div class="form-group">
        <label for="account_secondary" class="col-md-3">
            <?php // echo lang("account_sub_type"); ?>
        </label>
        <div class="col-md-9">
            <select id="account_secondary" name="account_secondary" class="form-control <?php // if ($header_data->status == "A") { echo "pointer-none"; } ?>" required>
                <option value=""><?php // echo "-- " . lang("account_sub_type_select") . " --"; ?></option>
                <?php // if (!empty($account_secondary)): ?>
                    <?php // if (isset($account_secondary_info->id) && !empty($account_secondary_info->id)): ?>
                        <?php // foreach ($account_secondary as $secondary): ?>
                            <option value="<?php // echo $secondary->id; ?>" <?php // if ($account_secondary_info->id == $secondary->id) { echo "selected"; } ?>>
                                <?php // echo $secondary->thai_name . " (" . $secondary->account_code . ")"; ?>
                            </option>
                        <?php // endforeach; ?>
                    <?php // else: ?>
                        <?php // foreach ($account_secondary as $secondary): ?>
                            <option value="<?php // echo $secondary->id; ?>">
                                <?php // echo $secondary->thai_name . " (" . $secondary->account_code . ")"; ?>
                            </option>
                        <?php // endforeach; ?>
                    <?php // endif; ?>
                <?php // endif; ?>
            </select>
        </div>
    </div> -->

    <!-- <div class="form-group">
        <label for="account_category" class="col-md-3">
            <?php // echo lang("account_expense"); ?>
        </label>
        <div class="col-md-9">
            <select name="account_category" id="account_category" class="form-control pointer-none-appearance <?php // if ($header_data->status == "A") { echo "pointer-none"; } ?>" required>
                <?php // if (isset($account_category_info->id) && !empty($account_category_info->id)): ?>
                    <option value="<?php // echo $account_category_info->id; ?>"><?php // echo $account_category_info->account_code . ' - ' . $account_category_info->thai_name; ?></option>
                <?php // else: ?>
                    <option value=""><?php // echo "-- " . lang("account_expense_select") . " --"; ?></option>
                <?php // endif; ?>
            </select>
        </div>
    </div> -->

    <div class="form-group">
        <label for="internal_reference" class="col-md-3">
            <?php echo lang("pv_internal_reference"); ?>
        </label>
        <div class="col-md-9">
            <input type="text" value="<?php echo $header_data->internal_reference; ?>" name="internal_reference" id="internal_reference" class="form-control <?php if ($header_data->status == "A") { echo "pointer-none"; } ?>" placeholder="<?php echo lang("pv_internal_reference_place_holder"); ?>" maxlength="40">
        </div>
    </div>

    <div class="form-group">
        <label for="project-id" class="col-md-3">
            <?php echo lang("project_refer"); ?>
        </label>
        <div class="col-md-9">
            <select name="project-id" id="project-id" class="form-control select-project <?php if ($header_data->status == "A") { echo "pointer-none"; } ?> <?php if ($header_data->po_id != 0) { echo "pointer-none"; } ?>" required>
                <?php if (isset($header_data->project_id) && $header_data->project_id != 0): ?>
                    <option value="<?php echo $header_data->project_id; ?>"><?php echo $header_data->project_name; ?></option>
                <?php else: ?>
                    <option value="0"><?php echo "-- " . lang("project_refer") . " --"; ?></option>
                <?php endif; ?>
                
                <?php if (sizeof($project_dropdown)): ?>
                    <?php foreach ($project_dropdown as $project): ?>
                        <option value="<?php echo $project["project_id"]; ?>"><?php echo $project["project_name"]; ?></option>
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
                <select id="supplier-id" name="supplier-id" class="form-control select-supplier pointer-none" required>
                    <?php if (isset($header_data->supplier_id) && $header_data->supplier_id != 0): ?>
                        <option value="<?php echo $header_data->supplier_id; ?>"><?php echo $header_data->supplier_name; ?></option>
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
            <input <?php if (isset($header_data->supplier_invoice) && !empty($header_data->supplier_invoice)) { echo 'value="' . $header_data->supplier_invoice . '"'; } ?> type="text" name="invoice-refer" id="invoice-refer" class="form-control select-invoice-refer <?php if ($header_data->status == "A") { echo "pointer-none"; } ?>" placeholder="<?php echo lang("pv_invoice_refer_placeholder"); ?>" maxlength="40">
        </div>
    </div>

    <div class="form-group">
        <label for="remark-text" class="col-md-3">
            <?php echo lang("remark"); ?>
        </label>
        <div class="col-md-9">
            <textarea name="remark-text" id="remark-text" class="form-control <?php if ($header_data->status == "A") { echo "pointer-none"; } ?>" cols="30" rows="10" placeholder="<?php echo lang("pv_remark_placeholder"); ?>"><?php if (isset($header_data->remark) && !empty($header_data->remark)) { echo $header_data->remark; } ?></textarea>
        </div>
    </div>

    <div class="form-group item-table">
        <table>
            <thead>
                <tr>
                    <th width="25%"><?php echo lang("purchase_order"); ?></th>
                    <th width="38%"><?php echo lang("details"); ?></th>
                    <th width="17%"><?php echo lang("quantity"); ?></th>
                    <th width="10%"><?php echo lang("stock_material_unit"); ?></th>
                    
                    <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
                        <?php if ($header_data->status == "W" && $header_data->po_id == 0): ?>
                            <th>
                                <button id="btn-add-item" class="btn btn-primary right-control hide">
                                    <span class="fa fa-plus-circle"></span> 
                                    <?php  echo lang("add"); ?>
                                </button>
                            </th>
                        <?php endif; ?>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody id="tbody-po-select">
                <?php if (sizeof($detail_data)): ?>
                    <?php foreach ($detail_data as $item): ?>
                        <tr>
                            <td>
                                <select name="po_id[]" class="form-control select-order pointer-none" required>
                                    <option value="<?php echo $item->po_info->id; ?>"><?php echo $item->po_info->doc_number; ?></option>
                                </select>
                            </td>
                            <td>
                                <select name="po_item_id[]" class="form-control select-item pointer-none">
                                    <option value="<?php echo $item->po_item_info->id; ?>"><?php echo $item->po_item_info->product_name; ?></option>
                                </select>
                            </td>
                            <td>
                                <input name="quantity[]" class="form-control select-quantity <?php if ($header_data->status == "A") { echo "pointer-none"; } ?>" value="<?php echo $item->quantity; ?>" readonly required>
                                <input name="status_qty[]" type="hidden" class="select-status_qty" value="Y">
                            </td>
                            <td>
                                <input name="unit[]" class="form-control select-unit" value="<?php echo $item->unit; ?>" readonly>
                            </td>

                            <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
                                <?php if ($header_data->status == "W" && $header_data->po_id == 0): ?>
                                    <td>
                                        <button class="btn btn-danger button-delete-edit right-control" data-item_id="<?php echo $item->id; ?>">
                                            <span class="fa fa-trash"></span> 
                                            <?php echo lang("delete"); ?>
                                        </button>
                                    </td>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
        <?php echo lang("close"); ?>
    </button>

    <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
        <?php if ($header_data->status == "W"): ?>
            <button type="submit" id="btn-submit" class="btn btn-primary"><span class="fa fa-check-circle"></span>
                <?php echo lang("edit"); ?>
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    const tableBody = $("#tbody-po-select");
    const supplierId = $("#supplier-id");
    const addNewForm = $("#addnew-form");
    const categoryList = JSON.parse('<?php echo $account_category; ?>');
    const categoryTopSelect = '<?php echo "-- " . lang("account_expense_select") . " --"; ?>';

    let purchaseOrderList = [];
    let purchaseItemList = [];

    async function toggleSupplierId () {
        let trCount = await tableBody.find("tr").length;

        if (trCount) {
            supplierId.addClass('pointer-none');
        } else {
            supplierId.removeClass('pointer-none');
        }
    }

    async function toggleButtonAdd () {
        let trCount = await tableBody.find("tr").length;
        let itemCount = await purchaseItemList.length;
        // console.log(trCount, itemCount);

        if (itemCount === 0 || trCount === itemCount) {
            $("#btn-add-item").addClass('hide');
        } else {
            $("#btn-add-item").removeClass('hide');
        }
    }

    async function getPurchaseOrderList () {
        let url = '<?php echo get_uri('payment_voucher/purchase_order_list'); ?>';
        let req = {
            supplier_id: $("#supplier-id").val()
        };
        // console.log(url, req);

        purchaseOrderList = [];
        purchaseItemList = [];

        await axios.post(url, req).then(res => {
            const { success, data } = res.data;
            
            if (success) {
                purchaseOrderList = data.orders;
                purchaseItemList = data.items;
            }
        }).catch(err => {
            console.log(err);
        });
    }

    async function getPurchaseOrderListEdit () {
        let url = '<?php echo get_uri('payment_voucher/purchase_order_list_edit'); ?>';
        let req = {
            supplier_id: $("#supplier-id").val(),
            document_id: $("#document-id").val()
        };
        // console.log(url, req);

        purchaseOrderList = [];
        purchaseItemList = [];

        await axios.post(url, req).then(res => {
            const { success, data } = res.data;
            // console.log(data);
            
            if (success) {
                let poIdSet = new Set(data.items.map(item => item.po_id));
                let filteredOrders = data.orders.filter(order => poIdSet.has(order.po_id));

                purchaseOrderList = filteredOrders;
                purchaseItemList = data.items;
            }
            // console.log(purchaseOrderList, purchaseItemList);
        }).catch(err => {
            console.log(err);
        });
    }

    async function processBinding () {
        $(".select-order").select2("destroy");
        $(".select-order").select2();

        $(".select-order").unbind();
        $(".select-order").on("change", function () {
            let self = $(this);
            let parent = self.closest("tr");
            let optionItems = purchaseItemList.filter(i => i.po_id == self.val());
            let selectItems = parent.find('[name="po_item_id[]"]');

            selectItems.val('');
            selectItems.find('option').remove();
            selectItems.append('<option></option>');

            if (optionItems.length) {
                optionItems.map((i) => {
                    selectItems.append(`<option value="${i.po_item_id}">${i.product_name}</option>`);
                });
                
                selectItems.removeClass('pointer-none hide');
                selectItems.select2();
            } else {
                selectItems.addClass('pointer-none hide');
            }
        });

        $(".select-item").unbind();
        $(".select-item").on("change", function () {
            let self = $(this);
            let parent = self.closest("tr");
            let quantity = parent.find('[name="quantity[]"]');
            let unit = parent.find('[name="unit[]"]');
            let selfInfo = purchaseItemList.filter(i => i.po_item_id == self.val())[0];

            quantity.attr("data-maximum", selfInfo.quantity);
            quantity.val(selfInfo.quantity);
            unit.val(selfInfo.unit);
        });

        $(".select-quantity").unbind();
        $(".select-quantity").on("click", function (e) {
            $(this).select();
        });

        $(".select-quantity").on("keypress", function (e) {
            if (e.which === 13) {
                e.preventDefault();

                let self = $(this);
                let parent = self.closest("tr");
                let statusQty = parent.find('[name="status_qty[]"]');

                let thisVal = parseFloat(self.val());
                let thisMax = parseFloat(self.data("maximum"));

                if (thisVal < 0 || thisVal > thisMax) {
                    self.removeClass("quantity-done");
                    self.addClass("quantity-over");

                    statusQty.val("N");
                } else {
                    self.removeClass("quantity-over");
                    self.addClass("quantity-done");

                    statusQty.val("Y");
                }
            }
        });

        $(".select-quantity").on("change", function (e) {
            let self = $(this);
            let parent = self.closest("tr");
            let statusQty = parent.find('[name="status_qty[]"]');

            let thisVal = parseFloat(self.val());
            let thisMax = parseFloat(self.data("maximum"));
            
            if (thisVal < 0 || thisVal > thisMax) {
                self.removeClass("quantity-done");
                self.addClass("quantity-over");

                statusQty.val("N");
            } else {
                self.removeClass("quantity-over");
                self.addClass("quantity-done");

                statusQty.val("Y");
            }
        });

        $(".button-delete").unbind();
        $(".button-delete").on("click", function (e) {
            e.preventDefault();

            $(this).closest("tr").remove();
            processBinding();
        });
        
        await getPurchaseOrderListEdit();
        await toggleButtonAdd();
    }

    $(document).ready(function () {
        $("#doc-date").datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy'
        });

        $("#doc-date").datepicker(
            "setDate", "<?php echo date("d/m/Y", strtotime($header_data->doc_date)); ?>"
        );

        $("#project-id").select2();

        $("#supplier-id").select2();

        $("#supplier-id").on("click", function (e) {
            e.preventDefault();
        });

        $(".button-delete-edit").on("click", function (e) {
            e.preventDefault();

            $(this).closest("tr").remove();
            processBinding();
        });

        $("#btn-add-item").on("click", async function (e) {
            e.preventDefault();

            // console.log(purchaseOrderList.length);
            if (purchaseOrderList.length === 0) {
                return;
            }
            
            await tableBody.append(`
                <tr>
                    <td>
                        <select name="po_id[]" class="form-control select-order" required>
                            <option></option>
                            ${purchaseOrderList.map((i) => {
                                return `<option value="${i.po_id}">${i.po_number}</option>`;
                            })}
                        </select>
                    </td>
                    <td>
                        <select name="po_item_id[]" class="form-control select-item pointer-none hide">
                            <option></option>
                        </select>
                    </td>
                    <td>
                        <input name="quantity[]" class="form-control select-quantity" value="1" required>
                        <input name="status_qty[]" type="hidden" class="select-status_qty" value="Y">
                    </td>
                    <td>
                        <input name="unit[]" class="form-control select-unit" readonly>
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

        processBinding();

        addNewForm.appForm({
            onSuccess: function (result) {
                // console.log(result);
                const { post_result } = result;

                if (post_result.trans_status == "T") {
                    setTimeout(async () => {
                        await window.open(post_result.target_url, '_blank');

                        setTimeout(async () => {
                            await window.open(post_result.reload_url, '_self');
                        }, 1000);
                    }, 100);
                }
            }
        });

        // $("#account_secondary").select2();
        // $("#account_secondary").on("change", function (e) {
        //     e.preventDefault();

        //     let self = $(this);
        //     let categoryOption = categoryList.filter(i => i.secondary_id == self.val());
        //     let categorySelect = $("#account_category");

        //     if (categoryOption.length) {
        //         categorySelect.val('');
        //         categorySelect.find('option').remove();
        //         categorySelect.append(`<option value="">${categoryTopSelect}</option>`);

        //         categoryOption.map((i) => {
        //             categorySelect.append(`<option value="${i.id}" data-code="${i.account_code}">${i.account_code} - ${i.thai_name}</option>`);
        //         });

        //         categorySelect.removeClass('pointer-none-appearance');
        //         categorySelect.select2();
        //     }
        // });
    });
</script>
